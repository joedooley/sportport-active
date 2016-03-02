<?php

class WPSEO_News_Sitemap {

	private $options;

	public function __construct() {
		$this->options = WPSEO_News::get_options();

		add_action( 'init', array( $this, 'init' ), 10 );
		add_filter( 'wpseo_sitemap_index', array( $this, 'add_to_index' ) );
		add_action( 'save_post', array( $this, 'invalidate_sitemap' ) );

		// Setting stylesheet for cached sitemap
		add_action( 'wpseo_sitemap_stylesheet_cache_news', array( $this, 'set_stylesheet_cache' ) );
	}

	/**
	 * Add the XML News Sitemap to the Sitemap Index.
	 *
	 * @param string $str String with Index sitemap content.
	 *
	 * @return string
	 */
	public function add_to_index( $str ) {

		$date = new DateTime( get_lastpostdate( 'gmt' ), new DateTimeZone( new WPSEO_News_Sitemap_Timezone() ) );

		/**
		 * Filter: 'wpseo_news_sitemap_name' - Allow filtering the news sitemap XML URL
		 *
		 * @api string $news_sitemap_xml The news sitemap XML URL
		 */
		$news_sitemap_xml = WPSEO_News::get_sitemap_name( );

		$str .= '<sitemap>' . "\n";
		$str .= '<loc>' . $news_sitemap_xml . '</loc>' . "\n";
		$str .= '<lastmod>' . htmlspecialchars( $date->format( 'c' ) ) . '</lastmod>' . "\n";
		$str .= '</sitemap>' . "\n";

		return $str;
	}

	/**
	 * Register the XML News sitemap with the main sitemap class.
	 */
	public function init() {
		if ( isset( $GLOBALS['wpseo_sitemaps'] ) ) {
			$GLOBALS['wpseo_sitemaps']->register_sitemap( WPSEO_News::get_sitemap_name( false ), array( $this, 'build' ) );
		}
	}

	/**
	 * Method to invalidate the sitemap
	 *
	 * @param integer $post_id
	 */
	public function invalidate_sitemap( $post_id ) {
		// If this is just a revision, don't invalidate the sitemap cache yet.
		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}

		wpseo_invalidate_sitemap_cache( 'news' );
	}

	/**
	 * When sitemap is coming out of the cache there is no stylesheet. Normally it will take the default stylesheet.
	 *
	 * This method is called by a filter that will set the video stylesheet.
	 *
	 * @param object $target_object
	 *
	 * @return object
	 */
	public function set_stylesheet_cache( $target_object ) {
		$target_object->set_stylesheet( "\n" . '<?xml-stylesheet type="text/xsl" href="' . $this->get_sitemap_stylesheet() . '"?>' );
		return $target_object;
	}

	/**
	 * Build the sitemap and push it to the XML Sitemaps Class instance for display.
	 */
	public function build() {
		$GLOBALS['wpseo_sitemaps']->set_stylesheet( '<?xml-stylesheet type="text/xsl" href="' .  $this->get_sitemap_stylesheet() .' "?>' );

		$GLOBALS['wpseo_sitemaps']->set_sitemap( $this->build_sitemap() );
	}

	/**
	 * Building the XML for the sitemap
	 *
	 * @return string
	 */
	public function build_sitemap() {
		$output = '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:news="http://www.google.com/schemas/sitemap-news/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' . "\n";

		$items = $this->get_items();

		// Loop through items
		if ( ! empty( $items ) ) {
			$output .= $this->build_items( $items );
		}

		$output .= '</urlset>';

		return $output;
	}

	private function get_sitemap_stylesheet() {
		return preg_replace( '/^http[s]?:/', '', plugin_dir_url( WPSEO_News::get_file() ) ) . 'assets/xml-news-sitemap.xsl';
	}

	/**
	 * Getting all the items for the sitemap
	 *
	 * @return mixed
	 */
	private function get_items() {
		global $wpdb;

		$post_types = $this->get_post_types();

		// Get posts for the last two days only, credit to Alex Moss for this code.
		// @codingStandardsIgnoreStart
		$sql_query = "
			 SELECT ID, post_content, post_name, post_author, post_parent, post_date_gmt, post_date, post_date_gmt, post_title, post_type
			 FROM {$wpdb->posts}
			 WHERE post_status=%s
			 AND (DATEDIFF(CURDATE(), post_date_gmt)<=2)
			 AND post_type IN ({$post_types})
			 ORDER BY post_date_gmt DESC
			 LIMIT 0, 1000
		 ";

		$items = $wpdb->get_results( $wpdb->prepare( $sql_query, 'publish' ) );
		// @codingStandardsIgnoreEnd

		return $items;
	}

	/**
	 * Loop through all $items and build each one of it
	 *
	 * @param array  $items
	 * @return string $output
	 */
	private function build_items( $items ) {
		$output = '';
		foreach ( $items as $item ) {
			$output .= new WPSEO_News_Sitemap_Item( $item, $this->options );
		}
		return $output;
	}

	/**
	 * Getting the post_types which will be displayed in the sitemap
	 *
	 * @return array|string
	 */
	private function get_post_types() {
		// Get supported post types
		$post_types = WPSEO_News::get_included_post_types();

		if ( count( $post_types ) > 0 ) {
			$post_types = "'" . implode( "','", $post_types ) . "'";
		}

		return $post_types;
	}
}

class WPSEO_News_Sitemap_Timezone {

	public function __toString() {
		return $this->wp_get_timezone_string();
	}

	/**
	 * Returns the timezone string for a site, even if it's set to a UTC offset
	 *
	 * Adapted from http://www.php.net/manual/en/function.timezone-name-from-abbr.php#89155
	 *
	 * @return string valid PHP timezone string
	 */
	private function wp_get_timezone_string() {

		// if site timezone string exists, return it
		if ( $timezone = get_option( 'timezone_string' ) ) {
			return $timezone;
		}

		// get UTC offset, if it isn't set then return UTC
		if ( 0 === ( $utc_offset = get_option( 'gmt_offset', 0 ) ) ) {
			return 'UTC';
		}

		// adjust UTC offset from hours to seconds
		$utc_offset *= 3600;

		// attempt to guess the timezone string from the UTC offset
		$timezone = timezone_name_from_abbr( '', $utc_offset );

		// last try, guess timezone string manually
		if ( false === $timezone ) {

			if ( $timezone_id = $this->get_timezone_id( $utc_offset ) ) {
				return $timezone_id;
			}
		}

		// fallback to UTC
		return 'UTC';
	}


	/**
	 * Getting the timezone id
	 *
	 * @param string $utc_offset
	 *
	 * @return mixed
	 */
	private function get_timezone_id( $utc_offset ) {
		$is_dst = date( 'I' );

		foreach ( timezone_abbreviations_list() as $abbr ) {
			foreach ( $abbr as $city ) {
				if ( $city['dst'] == $is_dst && $city['offset'] == $utc_offset ) {
					return $city['timezone_id'];
				}
			}
		}
	}
}


class WPSEO_News_Sitemap_Item {

	/**
	 * The output which will be return
	 *
	 * @var string
	 */
	private $output = '';

	/**
	 * The current item
	 *
	 * @var object
	 */
	private $item;

	/**
	 * The options
	 * @var array
	 */
	private $options;

	/**
	 * Setting properties and build the item
	 *
	 * @param object $item
	 * @param array  $options
	 */
	public function __construct( $item, $options ) {
		$this->item    = $item;
		$this->options = $options;


		// Check if item should be skipped
		if ( ! $this->skip_build_item() ) {
			$this->build_item();
		}
	}

	/**
	 * Return the output, because the object is converted to a string
	 *
	 * @return string
	 */
	public function __toString() {
		return $this->output;
	}

	/**
	 * Determine if item has to be skipped or not
	 *
	 * @return bool
	 */
	private function skip_build_item() {
		if ( WPSEO_Meta::get_value( 'newssitemap-exclude', $this->item->ID ) == 'on' ) {
			return true;
		}

		if ( false != WPSEO_Meta::get_value( 'meta-robots', $this->item->ID ) && strpos( WPSEO_Meta::get_value( 'meta-robots', $this->item->ID ), 'noindex' ) !== false ) {
			return true;
		}

		if ( 'post' == $this->item->post_type && $this->exclude_item_terms() ) {
			return true;
		}
	}

	/**
	 * Exclude the item when one of his terms is excluded
	 *x
	 * @return bool
	 */
	private function exclude_item_terms() {
		$cats    = get_the_terms( $this->item->ID, 'category' );
		$exclude = 0;

		if ( is_array( $cats ) ) {
			foreach ( $cats as $cat ) {
				if ( isset( $this->options[ 'catexclude_' . $cat->slug ] ) ) {
					$exclude ++;
				}
			}
		}

		if ( $exclude >= count( $cats ) ) {
			return true;
		}
	}

	/**
	 * Building each sitemap item
	 *
	 */
	private function build_item() {
		$this->item->post_status = 'publish';

		$this->output .= '<url>' . "\n";
		$this->output .= "\t<loc>" . get_permalink( $this->item ) . '</loc>' . "\n";

		// Building the news_tag
		$this->build_news_tag();

		// Getting the images for this item
		$this->get_item_images();

		$this->output .= '</url>' . "\n";
	}

	/**
	 * Building the news tag
	 *
	 */
	private function build_news_tag() {

		$keywords      = new WPSEO_News_Meta_Keywords( $this->item->ID );
		$genre         = $this->get_item_genre();
		$stock_tickers = $this->get_item_stock_tickers( $this->item->ID );

		$this->output .= "\t<news:news>\n";

		// Build the publication tag
		$this->build_publication_tag();

		if ( ! empty( $genre ) ) {
			$this->output .= "\t\t<news:genres><![CDATA[" . $genre . ']]></news:genres>' . "\n";
		}

		$this->output .= "\t\t<news:publication_date>" . $this->get_publication_date( $this->item ) . '</news:publication_date>' . "\n";
		$this->output .= "\t\t<news:title><![CDATA[" . $this->item->post_title . ']]></news:title>' . "\n";

		if ( ! empty( $keywords ) ) {
			$this->output .= "\t\t<news:keywords><![CDATA[" . $keywords . ']]></news:keywords>' . "\n";
		}

		if ( ! empty( $stock_tickers ) ) {
			$this->output .= "\t\t<news:stock_tickers><![CDATA[" . $stock_tickers . ']]></news:stock_tickers>' . "\n";
		}

		$this->output .= "\t</news:news>\n";
	}

	/**
	 * Builds the publication tag
	 */
	private function build_publication_tag() {
		$publication_name = ! empty( $this->options['name'] ) ? $this->options['name'] : get_bloginfo( 'name' );
		$publication_lang = $this->get_publication_lang();

		$this->output .= "\t\t<news:publication>" . "\n";
		$this->output .= "\t\t\t<news:name><![CDATA[" . $publication_name . ']]></news:name>' . "\n";
		$this->output .= "\t\t\t<news:language>" . htmlspecialchars( $publication_lang ) . '</news:language>' . "\n";
		$this->output .= "\t\t</news:publication>\n";
	}

	/**
	 * Getting the genre for given $item_id
	 *
	 * @return string
	 */
	private function get_item_genre() {
		$genre = WPSEO_Meta::get_value( 'newssitemap-genre', $this->item->ID );
		if ( is_array( $genre ) ) {
			$genre = implode( ',', $genre );
		}

		if ( $genre === '' && isset( $this->options['default_genre'] ) && $this->options['default_genre'] != '' ) {
			$genre = is_array( $this->options['default_genre'] ) ? implode( ',', $this->options['default_genre'] ) : $this->options['default_genre'];
		}

		$genre = trim( preg_replace( '/^none,?/', '', $genre ) );

		return $genre;
	}

	/**
	 * Getting the publication language
	 *
	 * @return string
	 */
	private function get_publication_lang() {
		$locale = apply_filters( 'wpseo_locale', get_locale() );

		// fallback to 'en', if the length of the locale is less than 2 characters
		if ( strlen( $locale ) < 2 ) {
			$locale = 'en';
		}

		$publication_lang = substr( $locale, 0, 2 );

		return $publication_lang;
	}

	/**
	 * Parses the $item argument into an xml format
	 *
	 * @param string $item
	 *
	 * @return string
	 */
	private function get_publication_date( $item ) {
		if ( $this->is_valid_datetime( $item->post_date_gmt ) ) {
			// Create a DateTime object date in the correct timezone
			return $this->format_date_with_timezone( $item->post_date_gmt );
		}
		elseif ( $this->is_valid_datetime( $item->post_modified_gmt ) ) {
			// Fallback 1: post_modified_gmt
			return $this->format_date_with_timezone( $item->post_modified_gmt );
		}
		elseif ( $this->is_valid_datetime( $item->post_modified ) ) {
			// Fallback 2: post_modified
			return $this->format_date_with_timezone( $item->post_modified );
		}
		elseif ( $this->is_valid_datetime( $item->post_date ) ) {
			// Fallback 3: post_date
			return $this->format_date_with_timezone( $item->post_date );
		}

		return '';
	}

	/**
	 * Format a datestring with a timezone
	 *
	 * @param $item_date
	 *
	 * @return string
	 */
	private function format_date_with_timezone( $item_date ) {

		static $timezone_string;

		if ( $timezone_string === null ) {
			// Get the timezone string
			$timezone_string = new WPSEO_News_Sitemap_Timezone();
		}

		// Create a DateTime object date in the correct timezone
		$datetime = new DateTime( $item_date, new DateTimeZone( $timezone_string ) );

		return $datetime->format( 'c' );
	}

	/**
	 * Getting the stock_tickers for given $item_id
	 *
	 * @param integer $item_id
	 *
	 * @return string
	 */
	private function get_item_stock_tickers( $item_id ) {
		$stock_tickers = explode( ',', trim( WPSEO_Meta::get_value( 'newssitemap-stocktickers', $item_id ) ) );
		$stock_tickers = trim( implode( ', ', $stock_tickers ), ', ' );

		return $stock_tickers;
	}

	/**
	 * Getting the images for current item
	 */
	private function get_item_images() {
		$this->output .= new WPSEO_News_Sitemap_Images( $this->item, $this->options );
	}

	/**
	 * Wrapper function to check if we have a valid datetime (Uses a new util in WPSEO)
	 *
	 * @param string $datetime
	 *
	 * @return bool
	 */
	private function is_valid_datetime( $datetime ) {
		if ( method_exists( 'WPSEO_Utils', 'is_valid_datetime' ) ) {
			return WPSEO_Utils::is_valid_datetime( $datetime );
		}
		return true;
	}
}

class WPSEO_News_Sitemap_Images {

	/**
	 * The current item
	 * @var object
	 */
	private $item;

	/**
	 * The out that will be returned
	 * @var string
	 */
	private $output = '';

	/**
	 * @var array
	 */
	private $options;

	/**
	 * Storage for the images
	 * @var
	 */
	private $images;

	/**
	 * Setting properties and build the item
	 *
	 * @param object $item
	 * @param array  $options
	 */
	public function __construct( $item, $options ) {
		$this->item    = $item;
		$this->options = $options;

		$this->parse_item_images();
	}

	/**
	 * Return the output, because the object is converted to a string
	 *
	 * @return string
	 */
	public function __toString() {
		return $this->output;
	}

	/**
	 * Parsing the images from the item
	 */
	private function parse_item_images() {
		$this->get_item_images();

		if ( isset( $this->images ) && count( $this->images ) > 0 ) {
			foreach ( $this->images as $src => $img ) {
				$this->parse_item_image( $src, $img );
			}
		}
	}

	/**
	 * Getting the images for the given $item
	 */
	private function get_item_images() {
		if ( ( ! isset( $this->options['restrict_sitemap_featured_img'] ) || ! $this->options['restrict_sitemap_featured_img'] ) && preg_match_all( '/<img [^>]+>/', $this->item->post_content, $matches ) ) {
			$this->get_images_from_content( $matches );
		}

		// Also check if the featured image value is set.
		$post_thumbnail_id = get_post_thumbnail_id( $this->item->ID );
		if ( '' != $post_thumbnail_id ) {
			$this->get_item_featured_image( $post_thumbnail_id );
		}
	}

	/**
	 * Getting the images from the content
	 *
	 * @param array $matches
	 */
	private function get_images_from_content( $matches ) {
		foreach ( $matches[0] as $img ) {
			if ( preg_match( '/src=("|\')([^"|\']+)("|\')/', $img, $match ) ) {
				if ( $src = $this->parse_image_source( $match[2] ) ) {
					$this->images[ $src ] = $this->parse_image( $img );
				} else {
					continue;
				}
			}
		}
	}

	/**
	 * Parsing the image source
	 *
	 * @param string $src
	 *
	 * @return string|void
	 */
	private function parse_image_source( $src ) {

		static $home_url;

		if ( $home_url == null ) {
			$home_url = home_url();
		}

		if ( strpos( $src, 'http' ) !== 0 ) {
			if ( $src[0] != '/' ) {
				return;
			}

			$src = $home_url . $src;
		}

		if ( $src != esc_url( $src ) ) {
			return;
		}

		if ( isset( $url['images'][ $src ] ) ) {
			return;
		}

		return $src;
	}

	/**
	 * Setting title and alt for image and returns them in an array
	 *
	 * @param string $img
	 *
	 * @return array
	 */
	private function parse_image( $img ) {
		$image = array();
		if ( preg_match( '/title=("|\')([^"\']+)("|\')/', $img, $match ) ) {
			$image['title'] = str_replace( array( '-', '_' ), ' ', $match[2] );
		}

		if ( preg_match( '/alt=("|\')([^"\']+)("|\')/', $img, $match ) ) {
			$image['alt'] = str_replace( array( '-', '_' ), ' ', $match[2] );
		}

		return $image;
	}

	/**
	 * Parse the XML for given image
	 *
	 * @param string $src
	 * @param string $img
	 *
	 * @return string
	 */
	private function parse_item_image( $src, $img ) {
		/**
		 * Filter: 'wpseo_xml_sitemap_img_src' - Allow changing of sitemap image src
		 *
		 * @api string $src The image source
		 *
		 * @param object $item The post item
		 */
		$src = apply_filters( 'wpseo_xml_sitemap_img_src', $src, $this->item );

		$this->output .= "\t<image:image>\n";
		$this->output .= "\t\t<image:loc>" . htmlspecialchars( $src ) . "</image:loc>\n";

		if ( isset( $img['title'] ) ) {
			$this->output .= "\t\t<image:title>" . htmlspecialchars( $img['title'] ) . "</image:title>\n";
		}

		if ( isset( $img['alt'] ) ) {
			$this->output .= "\t\t<image:caption>" . htmlspecialchars( $img['alt'] ) . "</image:caption>\n";
		}

		$this->output .= "\t</image:image>\n";
	}

	/**
	 * Getting the featured image
	 *
	 * @param integer $post_thumbnail_id
	 *
	 * @return array
	 */
	private function get_item_featured_image( $post_thumbnail_id ) {

		$attachment = $this->get_attachment( $post_thumbnail_id );

		if ( count( $attachment ) > 0 ) {
			$image = array();

			if ( '' != $attachment['title'] ) {
				$image['title'] = $attachment['title'];
			}

			if ( '' != $attachment['alt'] ) {
				$image['alt'] = $attachment['alt'];
			}

			if ( '' != $attachment['src'] ) {
				$this->images[ $attachment['src'] ] = $image;
			} elseif ( '' != $attachment['href'] ) {
				$this->images[ $attachment['href'] ] = $image;
			}
		}
	}

	/**
	 * Get attachment
	 *
	 * @param $attachment_id
	 *
	 * @return array
	 */
	private function get_attachment( $attachment_id ) {
		// Get attachment
		$attachment = get_post( $attachment_id );

		// Check if we've found an attachment
		if ( null == $attachment ) {
			return array();
		}

		// Return props
		return array(
			'alt'         => get_post_meta( $attachment->ID, '_wp_attachment_image_alt', true ),
			'caption'     => $attachment->post_excerpt,
			'description' => $attachment->post_content,
			'href'        => get_permalink( $attachment->ID ),
			'src'         => $attachment->guid,
			'title'       => $attachment->post_title,
		);
	}
}
