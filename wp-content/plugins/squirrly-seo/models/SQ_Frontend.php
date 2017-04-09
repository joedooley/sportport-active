<?php

class Model_SQ_Frontend {

    public $buffer;

    /** @var object Current post */
    private $post = null;

    private $post_type;
    private $post_types;

    /** @var canonical link */
    private $url;
    private $author;

    /** @var string */
    private $title;

    /** @var string */
    private $description;

    /** @var array */
    private $keywords;

    /** @var array */
    private $thumb_images;
    private $thumb_video;
    private $custom_og_image = false;

    /** @var integer */
    private $min_title_length = 20;

    /** @var integer */
    private $max_title_length = 75;

    /** @var integer */
    private $max_description_length = 170;

    /** @var integer */
    private $min_description_length = 70;

    /** @var integer */
    private $max_keywords = 8;

    /** @var array Meta custom content */
    private $meta = array();

    public function __construct() {
        SQ_ObjController::getController('SQ_Tools', false);
        $this->post_types = SQ_Tools::$options['sq_post_types'];
    }

    /** @var meta from other plugins */
    /**
     * Write the signature
     * @return string
     */
    public function setStart() {
        return "\n\n<!-- Squirrly SEO Plugin " . SQ_VERSION . ", visit: http://plugin.squirrly.co/ -->\n";
    }

    /**
     * Set the line where Squirrly will start the code
     * @return string
     */
    public function setStartTag() {
        $this->setPost();
        if ($this->is_squirrly()) {
            //SQ_Tools::dump('Show Squirrly', 'isHomePage: ' . $this->isHomePage(), 'is_single: ' . is_single(), 'is_preview: ' . is_preview(), 'is_page: ' . is_page(), 'is_archive: ' . is_archive(), 'is_author: ' . is_author(), 'is_category: ' . is_category(), 'is_tag: ' . is_tag(), 'is_search: ' . is_search(), 'in_array: ' . (!empty($this->post_types) && in_array($this->post_type, $this->post_types)));
            return "<squirrly />";
        }
    }

    /**
     * Set the post
     *
     * @param null|WP_Post $newpost
     * @return array|null|object|WP_Post
     */
    public function setPost($newpost = null) {
        global $post;

        if (isset($newpost)) {
            $post = $newpost;
        }

        if (function_exists('is_shop') && is_shop()) {
            $this->post = get_post(wc_get_page_id('shop'));
            $this->post_type = 'shop';
        } else {
            if (isset($post->ID)) {
                $this->post = get_post($post->ID);
            }

            if (function_exists('is_product') && is_product()) {
                $this->post_type = 'product';
            } elseif (function_exists('is_checkout') && is_checkout()) {
                $this->post_type = 'checkout';
            } elseif (is_single()) {
                $this->post_type = 'post';
            } elseif (is_page()) {
                $this->post_type = 'page';
            } elseif (is_category()) {
                $this->post_type = 'category';
            } elseif (is_tag()) {
                $this->post_type = 'tag';
            } elseif (is_author()) {
                $this->post_type = 'author';
            } elseif (is_search()) {
                $this->post_type = 'search';
            } elseif (is_archive()) {
                $this->post_type = 'archive';
            } else {
                $this->post_type = get_post_type();
            }
        }
    }

    /**
     * End the signature
     * @return string
     */
    public function setEnd() {
        return "<!-- /Squirrly SEO Plugin -->\n\n";
    }

    /*     * *****USE BUFFER****** */

    /**
     * Start the buffer record
     * @return type
     */
    public function startBuffer() {

        ob_start(array($this, 'getBuffer'));
    }

    /**
     * Get the loaded buffer and change it
     *
     * @param buffer $buffer
     * @return buffer
     */
    public function getBuffer($buffer) {
        if (isset($this->buffer)) {
            return $this->buffer;
        }

        $this->buffer = $this->setMetaInBuffer($buffer);
        return $this->buffer;
    }

    public function checkHandles() {
        try {
            if (function_exists('ob_list_handlers')) {
                $buffers = @ob_list_handlers();

                if (sizeof($buffers) > 0) {
                    if (!in_array(get_class($this) . '::getBuffer', $buffers)) {
                        $this->startBuffer();
                    }
                }
            }
        } catch (Exception $ex) {
            //error
        }
    }

    /**
     * Flush the header from wordpress
     *
     * @return string
     *
     */
    public function flushHeader() {
        try {
            if (function_exists('ob_list_handlers')) {
                $buffers = @ob_list_handlers();

                if (sizeof($buffers) > 0) {
                    if (is_string($buffers[sizeof($buffers) - 1])) {
                        if (strtolower($buffers[sizeof($buffers) - 1]) == strtolower(get_class($this) . '::getBuffer')) {
                            @ob_end_flush();
                            $buffers = @ob_list_handlers();
                        }
                    }
                }
            }
        } catch (Exception $ex) {
            //error
        }
    }

    public function is_squirrly() {
        if (SQ_Tools::getValue('sq_use') == 'off') {
            return false;
        }

        if (!$this->isHtmlHeader()) {
            return false;
        }

        if ($this->isHomePage() || is_single() || is_preview() || is_page() || is_archive() || is_author() || is_category() || is_tag() || is_search() || (!empty($this->post_types) && in_array($this->post_type, $this->post_types))) {
            return true;
        }

        return false;
    }

    /**
     * Change the title, description and keywords in site's buffer
     *
     * @return string
     */
    public function setMetaInBuffer($buffer) {

        //if the title is already shown
        if (isset($this->url)) {
            return $buffer;
        }
        //get the post from shop if woocommerce is installed
        if (!isset($this->post)) {
            $this->setPost();
        }

        if ($this->is_squirrly()) {
            //update ... please monitor
            if (is_single() || is_page()) {
                if (!isset($this->post->ID)) {
                    return $buffer;
                }
            }

            preg_match("/<head[^>]*>/i", $buffer, $out);
            if (!empty($out)) {
                $this->meta['blogname'] = get_bloginfo('name');
                //Get the url
                $this->url = $this->getCanonicalUrl();
                //Get the title
                $this->title = $this->getCustomTitle();
                //Set the description and Keywords in case of default
                $this->getCustomDescription();
                $this->getCustomKeyword();

                /* Get the thumb image from post */
                $this->thumb_images = $this->getImagesFromContent();

                if ((SQ_Tools::$options['sq_auto_description'] == 1 && $this->isHomePage()) || !$this->isHomePage()) {
                    //clear the existing description and keywords
                    $buffer = @preg_replace('/<meta[^>]*(name|property)=["\'](description|keywords)["\'][^>]*content=["\'][^"\'>]*["\'][^>]*>[\n\r]*/si', '', $buffer, -1);
                }
                if (SQ_Tools::$options['sq_auto_facebook'] == 1) {
                    $buffer = @preg_replace('/<meta[^>]*(name|property)=["\'](og:|article:)[^"\'>]+["\'][^>]*content=["\'][^"\'>]+["\'][^>]*>[\n\r]*/si', '', $buffer, -1);
                }
                if (SQ_Tools::$options['sq_auto_twitter'] == 1) {
                    $buffer = @preg_replace('/<meta[^>]*(name|property)=["\'](twitter:)[^"\'>]+["\'][^>]*content=["\'][^"\'>]+["\'][^>]*>[\n\r]*/si', '', $buffer, -1);
                }

                if (SQ_Tools::$options['sq_auto_favicon'] == 1) {
                    $buffer = @preg_replace('/<link[^>]*rel=[^>]*(shortcut icon)[^>]*>[\n\r]*/si', '', $buffer, -1);
                }

                if (SQ_Tools::$options['sq_auto_canonical'] == 1) {
                    $buffer = @preg_replace('/<link[^>]*rel=[^>]*(canonical)[^>]*>[\n\r]*/si', '', $buffer, -1);
                }
                if (SQ_Tools::$options['sq_auto_jsonld'] == 1) {
                    $buffer = @preg_replace('/<script[^>]*type=["\']application\/ld\+json["\'][^>]*>[^>]*<\/script>[\n\r]*/si', '', $buffer, -1);
                }


                if ((SQ_Tools::$options['sq_auto_title'] == 1 && $this->isHomePage()) || !$this->isHomePage()) {
                    if (isset($this->title) && $this->title <> '') {
                        //replace the existing title
                        $buffer = @preg_replace('/<title[^<>]*>([^<>]*)<\/title>/si', '', $buffer, -1);
                        $buffer = @preg_replace('/(<head[^>]*>)/si', sprintf("$1\n<title>%s</title>", $this->title) . "\n", $buffer, -1);
                    }
                }

                if (strpos($buffer, '<squirrly />') !== false) {
                    $buffer = @preg_replace('/(<squirrly[^>]*>)/si', sprintf("%s", $this->getHeader()) . "\n", $buffer, 1);
                } elseif (strpos($buffer, '</title>') !== false) {
                    $buffer = @preg_replace('/(<\/title>)/si', sprintf("$1\n%s", $this->getHeader()) . "\n", $buffer, 1);
                } else {
                    $buffer = @preg_replace('/(<head[^>]*>)/si', sprintf("$1\n%s", $this->getHeader()) . "\n", $buffer, 1);
                }

                return $buffer;
                //
            }
        } else {
            global $sq_is_sitemap;
            if (isset($sq_is_sitemap) && $sq_is_sitemap) {
                $buffer = @preg_replace('/<\/sitemapindex>(.*)?/si', "</sitemapindex>", $buffer);
                $buffer = @preg_replace('/<\/urlset>(.*)?/si', "</urlset>", $buffer);
                $buffer = trim($buffer);
            }
        }

        if (strpos($buffer, '<squirrly />') !== false) {
            $buffer = str_replace("<squirrly />", "", $buffer);
        }

        return $buffer;
    }

    /*     * ********************** */

    /**
     * Overwrite the header with the correct parameters
     *
     * @return string
     */
    public function getHeader() {
        $ret = '';
        $ret .= $this->setStart();

        //Add description in homepage if is set or add description in other pages if is not home page
        if ((SQ_Tools::$options['sq_auto_description'] == 1 && $this->isHomePage()) || !$this->isHomePage()) {
            $ret .= $this->getCustomDescription() . "\n";
            $ret .= $this->getCustomKeyword() . "\n";
        }

        if (SQ_Tools::$options['sq_auto_canonical'] == 1) {
            $ret .= $this->setCanonical();
            $ret .= $this->setRelPrevNext();
        }

        if (SQ_Tools::$options['sq_auto_sitemap'] == 1) {
            $ret .= $this->getXMLSitemap();
        }
        /* Auto setting */

        if (SQ_Tools::$options['sq_auto_favicon'] == 1) {
            $ret .= $this->getFavicon();
        }

        if (SQ_Tools::$options['sq_auto_meta'] == 1) {
            $ret .= "\n";
            $ret .= $this->getGooglePlusMeta();
            $ret .= $this->getLanguage();
            $ret .= $this->getCopyright();
            $ret .= $this->getDublinCore();
        }
        if (SQ_Tools::$options['sq_auto_facebook'] == 1) {
            add_filter('jetpack_enable_opengraph', '__return_false', 99);
            $ret .= $this->getOpenGraph() . "\n";
        }

        if (SQ_Tools::$options['sq_auto_twitter'] == 1) {
            $ret .= $this->getTwitterCard() . "\n";
        }
        /* SEO optimizer tool */
        $ret .= $this->getGoogleWT();
        $ret .= $this->getGoogleAnalytics();
        $ret .= $this->getFacebookPixel();
        $ret .= $this->getFacebookIns();
        $ret .= $this->getBingWT();
        $ret .= $this->getPinterest();
        $ret .= $this->getAlexaT();

        if (SQ_Tools::$options['sq_auto_jsonld'] == 1) {
            $ret .= $this->getJsonLD() . "\n";
        }

        $ret .= $this->setEnd();
        return $ret;
    }


    public function getTwitterCard() {
        $meta = "\n";

        //Title and Description is required
        if ($this->title == '' || $this->description == '') {
            return;
        }

        $sq_twitter_creator = SQ_Tools::$options['sq_twitter_account'];
        $sq_twitter_site = SQ_Tools::$options['sq_twitter_account'];

        if (SQ_Tools::$options['sq_auto_twittersize'] == 'summary_large_image' && !empty($this->thumb_images)) {
            $meta .= '<meta name="twitter:card" content="summary_large_image" />' . "\n";
        } else {
            $meta .= '<meta name="twitter:card" content="summary" />' . "\n";
        }

        $meta .= (($sq_twitter_creator <> '') ? sprintf('<meta name="twitter:creator" content="%s" />', $this->getTwitterAccount($sq_twitter_creator)) . "\n" : '');
        $meta .= (($sq_twitter_site <> '') ? sprintf('<meta name="twitter:site" content="%s" />', $this->getTwitterAccount($sq_twitter_creator)) . "\n" : '');
        $meta .= sprintf('<meta name="twitter:url" content="%s">', $this->url) . "\n";
        $meta .= sprintf('<meta name="twitter:title" content="%s">', apply_filters('sq_twitter_card_title', $this->title)) . "\n";
        $meta .= (($this->description <> '') ? sprintf('<meta name="twitter:description" content="%s">', apply_filters('sq_twitter_card_description', ($this->description . ' | ' . $this->meta['blogname']))) . "\n" : '');
        $meta .= (!empty($this->thumb_images) ? sprintf('<meta name="twitter:image" content="%s">', $this->thumb_images[0]['src']) . "\n" : '');
        $meta .= (($this->meta['blogname'] <> '') ? sprintf('<meta name="twitter:domain" content="%s">', $this->meta['blogname']) . "\n" : '');

        return apply_filters('sq_twitter_card_meta', $meta);
    }

    /**
     * Get the twitter account from url
     *
     * @param string $account
     * @return string | false
     */
    public function getTwitterAccount($account) {
        if ($account <> '') {
            if (strpos($account, 'twitter.com') !== false) {
                preg_match('/twitter.com\/([@1-9a-z_-]+)/i', $account, $result);
                if (isset($result[1]) && !empty($result[1])) {
                    return '@' . str_replace('@', '', $result[1]);
                }
            } else {
                preg_match('/([@1-9a-z_-]+)/i', $account, $result);
                if (isset($result[1]) && !empty($result[1])) {
                    return '@' . str_replace('@', '', $result[1]);
                }
            }
        } else {
            return '';
        }
        return false;
    }

    /**
     * Get the Open Graph Protocol
     * @return string
     */
    public function getOpenGraph() {
        $meta = "\n";
        $image = '';

        //Title and Description is required
        if ($this->title == '' || $this->description == '') {
            return;
        }

        if (!isset($this->thumb_video) || $this->thumb_video == '') {
            $videos = $this->getVideosFromContent();
            if (isset($videos[0])) {
                $this->thumb_video = $videos[0];
            }
        }

        if ($image == '' && $this->url == '') {
            return;
        }
        //GET THE URL
        $meta .= sprintf('<meta property="og:url" content="%s" />', apply_filters('sq_open_graph_url', $this->url)) . "\n";
        if (!empty($this->thumb_images)) {
            foreach ($this->thumb_images as $image) {
                $meta .= sprintf('<meta property="og:image" content="%s" />', $image['src']) . "\n";
                $meta .= sprintf('<meta property="og:image:width" content="%s" />', ((isset($image['width']) && $image['width'] <> '') ? (int)$image['width'] : 500)) . "\n";
                if (isset($image['height']) && $image['height'] <> '')
                    $meta .= sprintf('<meta property="og:image:height" content="%s" />', (int)$image['height']) . "\n";
            }
        }

        if ((isset($this->thumb_video) && $this->thumb_video <> '')) {
            $this->thumb_video = preg_replace('/(?:http(?:s)?:\/\/)?(?:www\.)?(?:youtu\.be\/|youtube\.com\/(?:(?:watch)?\?(?:.*&)?v(?:i)?=|(?:embed|v|vi|user)\/))([^\?&\"\'>\s]+)/si', "https://www.youtube.com/v/$1", $this->thumb_video);
            $meta .= sprintf('<meta property="og:video" content="%s" />', $this->thumb_video) . "\n";
            $meta .= sprintf('<meta property="og:video:width" content="%s" />', 500) . "\n";
            $meta .= sprintf('<meta property="og:video:height" content="%s" />', 280) . "\n";
        }

        $meta .= sprintf('<meta property="og:title" content="%s" />', apply_filters('sq_open_graph_title', $this->title)) . "\n";
        $meta .= sprintf('<meta property="og:description" content="%s" />', apply_filters('sq_open_graph_description', $this->description)) . "\n";
        $meta .= (($this->meta['blogname'] <> '') ? sprintf('<meta property="og:site_name" content="%s" />', apply_filters('sq_open_graph_site', $this->meta['blogname'])) . "\n" : '');


        $meta .= sprintf('<meta property="og:locale" content="%s" />', SQ_Tools::$options['sq_og_locale']) . "\n";

        if (is_author()) {
            $author = get_queried_object();

            $meta .= sprintf('<meta property="og:type" content="%s" />', 'profile') . "\n";
            $meta .= sprintf('<meta property="profile:first_name" content="%s" />', get_the_author_meta('first_name', $author->ID)) . "\n";
            $meta .= sprintf('<meta property="profile:last_name" content="%s" />', get_the_author_meta('last_name', $author->ID)) . "\n";
        } elseif (function_exists('is_product') && is_product()) {
            $meta .= sprintf('<meta property="og:type" content="%s" />', 'product') . "\n";

            $cat = get_the_terms($this->post->ID, 'product_cat');
            if (!empty($cat) && count($cat) > 0) {
                $meta .= sprintf('<meta property="product:category" content="%s" />', $cat[0]->name) . "\n";
            }

            if (class_exists('WC_Product')) {
                $product = new WC_Product($this->post->ID);
                if (isset($product->regular_price)) {
                    if ((int)$product->regular_price > 0 && $product->regular_price <> $product->price && function_exists('get_woocommerce_currency')) {
                        $meta .= sprintf('<meta property="product:original_price:amount" content="%s" />', $product->regular_price) . "\n";
                        $meta .= sprintf('<meta property="product:original_price:currency" content="%s" />', get_woocommerce_currency()) . "\n";
                    }
                }

                if (isset($product->price)) {
                    if ((int)$product->price > 0 && function_exists('get_woocommerce_currency')) {
                        $meta .= sprintf('<meta property="product:price:amount" content="%s" />', $product->price) . "\n";
                        $meta .= sprintf('<meta property="product:price:currency" content="%s" />', get_woocommerce_currency()) . "\n";
                    }
                }

                if (isset($product->sale_price)) {
                    if ((int)$product->sale_price > 0 && function_exists('get_woocommerce_currency')) {
                        $sales_price_from = get_post_meta($this->post->ID, '_sale_price_dates_from', true);
                        $sales_price_to = get_post_meta($this->post->ID, '_sale_price_dates_to', true);

                        $meta .= sprintf('<meta property="product:sale_price:amount" content="%s" />', $product->sale_price) . "\n";
                        $meta .= sprintf('<meta property="product:sale_price:currency" content="%s" />', get_woocommerce_currency()) . "\n";
                        if ($sales_price_from > 0) {
                            $meta .= sprintf('<meta property="product:sale_price:start" content="%s" />', date("Y-m-d H:i:s", $sales_price_from)) . "\n";
                        }
                        if ($sales_price_to) {
                            $meta .= sprintf('<meta property="product:sale_price:end" content="%s" />', date("Y-m-d H:i:s", $sales_price_to)) . "\n";
                        }

                    }
                }
                if (isset($product->weight)) {
                    if ((int)$product->weight > 0 && get_option('woocommerce_weight_unit') <> '') {
                        $meta .= sprintf('<meta property="product:weight:value" content="%s" />', $product->weight) . "\n";
                        $meta .= sprintf('<meta property="product:weight:units" content="%s" />', get_option('woocommerce_weight_unit')) . "\n";
                    }
                }

            }

        } elseif (!$this->isHomePage() && (is_single() || is_page())) {


            $meta .= sprintf('<meta property="og:type" content="%s" />', 'article') . "\n";
            $meta .= sprintf('<meta property="article:published_time" content="%s" />', get_the_time('c', $this->post->ID)) . "\n";

            $category = get_the_category($this->post->ID);
            if (!empty($category) && $category[0]->cat_name <> 'Uncategorized') {
                $meta .= sprintf('<meta property="article:section" content="%s" />', $category[0]->cat_name) . "\n";
            }
            if ($this->keywords <> '') {
                $keywords = preg_split('/[,]+/', $this->keywords);
                if (is_array($keywords) && !empty($keywords)) {
                    foreach ($keywords as $keyword) {
                        $meta .= sprintf('<meta property="article:tag" content="%s" />', $keyword) . "\n";
                    }
                }
            }
        } else {
            $meta .= sprintf('<meta property="og:type" content="%s" />', 'website') . "\n";
        }

        return apply_filters('sq_open_graph_meta', $meta);
    }

    /**
     * Get the canonical link for the current page
     *
     * @return string
     */
    public function setCanonical() {
        if ($url = $this->getCanonicalUrl(true)) {
            remove_action('wp_head', 'rel_canonical');

            return sprintf("<link rel=\"canonical\" href=\"%s\" />", $url) . "\n";
        }

        return '';
    }

    public function setRelPrevNext() {
        global $paged;
        $meta = "";
        if (!$this->isHomePage()) {
            if (get_previous_posts_link()) {
                $meta .= sprintf('<link rel="prev" href="%s" />', apply_filters('sq_prev_link', get_pagenum_link($paged - 1))) . "\n";
            }
            if (get_next_posts_link()) {
                $meta .= sprintf('<link rel="next" href="%s" />', apply_filters('sq_next_link', get_pagenum_link($paged + 1))) . "\n";
            }
        }

        return (($meta <> '') ? apply_filters('sq_prevnext_meta', $meta) . "\n" : '');
    }

    public function getTitle() {
        $this->getCustomTitle();
        return $this->title;
    }

    /**
     * Get the correct title of the article
     *
     * @return string
     */
    public function getCustomTitle() {
        $sep = ' | ';

        if (isset($this->title)) {
            return $this->title;
        }

        //If its a post/page
        if (!$this->isHomePage()) {
            //If is category
            if (is_category()) { //for category
                $category = get_category(get_query_var('cat'), false);
                $this->title = $category->cat_name;
                if ($this->title == '') {
                    $this->title = $this->grabTitleFromPost();
                }
                if (is_paged()) {
                    $this->title .= $sep . __('Page', _SQ_PLUGIN_NAME_) . " " . (int)get_query_var('paged');
                }
            } elseif (is_author()) { //for author
                if ($this->title == '') {
                    $this->title = $this->grabTitleFromPost() . $sep . ucfirst($this->getAuthor('display_name'));
                }
                if ($this->title == '') {
                    $this->title = __('About') . " " . ucfirst($this->getAuthor('display_name'));
                }
                if (is_paged()) {
                    $this->title .= $sep . __('Page', _SQ_PLUGIN_NAME_) . " " . (int)get_query_var('paged');
                }
            } elseif (is_tag()) { //for tags
                if (is_paged()) {
                    $tag = get_query_var('tag');
                    $this->title = ucfirst(str_replace('-', ' ', $tag)) . $sep . __('Page', _SQ_PLUGIN_NAME_) . " " . (int)get_query_var('paged');
                }
            } elseif (is_archive()) { //for archive and products
                if (isset($this->post) && isset($this->post->ID)) {
                    $this->title = $this->grabTitleFromPost($this->post->ID);

                    //if woocommerce is installed and is a product category
                    if (function_exists('is_product_category') && is_product_category()) {
                        global $wp_query;
                        $cat = $wp_query->get_queried_object();
                        if (!empty($cat) && $cat->name <> '') {
                            $this->title = $cat->name;
                        }
                    } else {
                        $cat = get_the_terms($this->post->ID, 'category');
                        if (!empty($cat)) {
                            $this->title .= $sep . $cat[0]->name;
                        }
                    }
                }

                if (is_paged()) {
                    $this->title .= $sep . __('Page', _SQ_PLUGIN_NAME_) . " " . (int)get_query_var('paged');
                }
            } elseif (is_single() || is_page() || is_singular() || in_array($this->post_type, $this->post_types)) {
                if (isset($this->post) && isset($this->post->ID)) {
                    //is a post page
                    $this->title = $this->grabTitleFromPost($this->post->ID);

                    //if woocommerce is installed and is a product
                    if (function_exists('is_product') && is_product()) {
                        $cat = get_the_terms($this->post->ID, 'product_cat');
                        if (!empty($cat) && count($cat) > 1) {
                            $this->title .= $sep . $cat[0]->name;
                        }
                    }
                }
            }

        } elseif (SQ_Tools::$options ['sq_auto_title'] == 1) { /* Check if is a predefined Title for home page */

            //If the home page is a static page that has custom snippet
            if (is_page() && isset($this->post) && isset($this->post->ID) && $this->getAdvancedMeta($this->post->ID, 'title') <> '') {
                $this->title = $this->getAdvancedMeta($this->post->ID, 'title');
            } elseif (SQ_Tools::$options['sq_fp_title'] <> '') {
                $this->title = SQ_Tools::$options['sq_fp_title'];
            } else {
                if (isset($this->post->ID)) {
                    $this->title = $this->grabTitleFromPost($this->post->ID);
                    if ($this->title <> "" && $this->meta['blogname'] <> '') {
                        $this->title .= $sep . $this->meta['blogname'];
                    }
                } else {
                    $this->title = get_the_title();
                }
            }
        } else {
            $this->title = get_the_title();
        }
        return apply_filters('sq_title', $this->title);
    }

    public function clearTitle($title) {
        if ($title <> '') {
            $title = SQ_Tools::i18n(trim(esc_html(ent2ncr(strip_tags($title)))));
            $title = addcslashes($title, '$');
        }
        return $title;
    }

    /**
     * Get the image from content
     * @global WP_Query $wp_query
     * @param integer $id Post ID
     * @return array
     */
    public function getImagesFromContent($id = null, $all = false) {
        $images = array();
        $post = $this->post;

        if (isset($id) && !$post = get_post($id)) {
            return $images;
        }

        //if not a specific post and description is sqitched on
        if (!isset($id) && SQ_Tools::$options['sq_auto_description'] == 1) { //
            if (($this->isHomePage() && SQ_Tools::$options['sq_fp_ogimage'] <> '')) {
                $images[] = array(
                    'src' => esc_url(SQ_Tools::$options['sq_fp_ogimage']),
                    'title' => $this->clearTitle($this->grabTitleFromPost($post->ID)),
                    'description' => $this->clearDescription($this->grabDescriptionFromPost($post->ID)),
                    'width' => null,
                    'height' => null,
                );

                return $images;
            }
        }

        if ($post && isset($post->ID)) {
            if ($url = $this->getAdvancedMeta($post->ID, 'ogimage')) {
                $images[] = array(
                    'src' => esc_url($url),
                    'title' => $this->clearTitle($this->grabTitleFromPost($post->ID)),
                    'description' => $this->clearDescription($this->grabDescriptionFromPost($post->ID)),
                    'width' => null,
                    'height' => null,
                );
                //don't add other images in OG to overwrite the custom image
                $this->custom_og_image = true;
            }
            if ($all || empty($images)) {
                if (has_post_thumbnail($post->ID)) {
                    $attachment = get_post(get_post_thumbnail_id($post->ID));
                    $url = wp_get_attachment_image_src($attachment->ID, 'full');
                    $images[] = array(
                        'src' => esc_url($url[0]),
                        'title' => $this->clearTitle($attachment->post_title),
                        'description' => $this->clearDescription($attachment->post_excerpt),
                        'width' => $url[1],
                        'height' => $url[2],
                    );
                }
            }
            if ($all || empty($images)) {
                if (isset($post->post_content)) {
                    preg_match('/<img[^>]*src="([^"]*)"[^>]*>/i', $post->post_content, $match);

                    if (!empty($match)) {
                        preg_match('/alt="([^"]*)"/i', $match[0], $alt);

                        if (strpos($match[1], '//') === false) {
                            $match[1] = get_bloginfo('url') . $match[1];
                        }

                        $images[] = array(
                            'src' => esc_url($match[1]),
                            'title' => $this->clearTitle(!empty($alt[1]) ? $alt[1] : ''),
                            'description' => '',
                            'width' => null,
                            'height' => null,
                        );
                    }
                }
            }
        }

        return $images;
    }

    /**
     * Get the video from content
     * @param integer $id Post ID
     * @return type
     */
    public function getVideosFromContent($id = null) {
        $videos = array();

        if (isset($id)) {
            $post = get_post($id);
        } else {
            $post = $this->post;
        }

        if ($post && isset($post->ID)) {
            //if not
            if (!$this->custom_og_image) {
                if (isset($post->post_content)) {
                    preg_match('/(?:http(?:s)?:\/\/)?(?:www\.)?(?:youtu\.be\/|youtube\.com\/(?:embed)\/)([^\?&\"\'>\s]+)/si', $post->post_content, $match);

                    if (isset($match[0])) {
                        if (strpos($match[0], '//') !== false && strpos($match[0], 'http') === false) {
                            $match[0] = 'http:' . $match[0];
                        }
                        $videos[] = esc_url($match[0]);
                    }

//                preg_match('/(?:http(?:s)?:\/\/)?(?:fast\.wistia\.net\/(?:embed)\/(?:iframe)\/)([^\?&\"\'>\s]+)/si', $post->post_content, $match);
//
//                if (isset($match[0])) {
//                    if (strpos($match[0], '//') !== false && strpos($match[0], 'http') === false) {
//                        $match[0] = 'http:' . $match[0];
//                    }
//                    $videos[] = esc_url($match[0]);
//                }

                    preg_match('/(?:http(?:s)?:\/\/)?(?:fwd4\.wistia\.com\/(?:medias)\/)([^\?&\"\'>\s]+)/si', $post->post_content, $match);

                    if (isset($match[0])) {
                        $videos[] = esc_url('http://fast.wistia.net/embed/iframe/' . $match[1]);
                    }

                    preg_match('/class=["|\']([^"\']*wistia_async_([^\?&\"\'>\s]+)[^"\']*["|\'])/si', $post->post_content, $match);

                    if (isset($match[0])) {
                        $videos[] = esc_url('http://fast.wistia.net/embed/iframe/' . $match[2]);
                    }

                    preg_match('/src=["|\']([^"\']*(.mpg|.mpeg|.mp4|.mov|.wmv|.asf|.avi|.ra|.ram|.rm|.flv)["|\'])/i', $post->post_content, $match);

                    if (isset($match[1])) {
                        $videos[] = esc_url($match[1]);
                    }
                }
            }
        }

        return $videos;
    }

    public function getDescription() {
        $this->getCustomDescription();
        return $this->description;
    }

    /**
     * Get the description from last/current article
     *
     * @return string
     */
    public function getCustomDescription() {

        $sep = ' | ';
        $description = '';

        //If not homepage
        if (!$this->isHomePage()) {
            //If is a category
            if (is_category()) { //for categories
                $category = get_category(get_query_var('cat'), false);
                $description = $category->category_description;
                if ($description == '') {
                    $description = $category->cat_name;
                }
                if ($description == '') {
                    $description = $this->grabDescriptionFromPost();
                }

                if (is_paged()) {
                    $description .= $sep . __('Page', _SQ_PLUGIN_NAME_) . " " . (int)get_query_var('paged');
                }

                if ($this->isHomePage() && $description <> '') {
                    if ($this->meta['blogname'] <> '') {
                        $description .= $sep . $this->meta['blogname'];
                    }
                }
            } elseif (is_author()) { //for author
                $description = $this->getAuthor('user_description');
                if ($description == '') {
                    $description = $this->grabDescriptionFromPost() . $sep . $this->getAuthor('display_name');
                }
                if (is_paged()) {
                    $description .= $sep . __('Page', _SQ_PLUGIN_NAME_) . " " . (int)get_query_var('paged');
                }
            } elseif (is_tag()) { //for tags
                $description = tag_description();
                if ($description == '') {
                    $tag = single_tag_title('', false);
                    $description = ucfirst($tag) . $sep . $this->grabDescriptionFromPost();
                }
                if (is_paged()) {
                    $description .= $sep . __('Page', _SQ_PLUGIN_NAME_) . " " . (int)get_query_var('paged');
                }
            } elseif (is_archive()) { //for archive and products
                if (isset($this->post) && isset($this->post->ID)) {
                    $description = $this->grabDescriptionFromPost($this->post->ID);

                    //if woocommerce is installed and is a product category
                    if (function_exists('is_product_category') && is_product_category()) {
                        global $wp_query;
                        $cat = $wp_query->get_queried_object();
                        if (!empty($cat)) {
                            if ($cat->description <> '' && strlen($cat->description) > 10) {
                                $description = $cat->description;
                            } else {
                                $description .= $sep . $cat->name;
                            }
                        }
                    } else {
                        $cat = get_the_terms($this->post->ID, 'category');
                        if (!empty($cat)) {
                            $description .= $sep . $cat[0]->name;
                        }
                    }
                }

                if (is_paged()) {
                    $description .= $sep . __('Page', _SQ_PLUGIN_NAME_) . " " . (int)get_query_var('paged');
                }
            } elseif (is_single() || is_page() || is_singular() || $this->checkPostsPage() || in_array($this->post_type, $this->post_types)) {
                if (isset($this->post) && isset($this->post->ID)) {
                    //is a post page
                    $description .= $this->grabDescriptionFromPost($this->post->ID);

                    //if woocommerce is installed and is a product
                    if (function_exists('is_product') && is_product()) {
                        $cat = get_the_terms($this->post->ID, 'product_cat');
                        if (!empty($cat) && count($cat) > 1) {
                            $description .= $sep . $cat[0]->name;
                        }
                    }
                }
            }
        } elseif (SQ_Tools::$options['sq_auto_description'] == 1) {
            /* Check if is a predefined TitleIn Snippet */
            //If the home page is a static page that has custom snippet
            if (is_page() && isset($this->post) && isset($this->post->ID) && $this->getAdvancedMeta($this->post->ID, 'description') <> '') {
                $description = $this->getAdvancedMeta($this->post->ID, 'description');
            } elseif (SQ_Tools::$options ['sq_fp_description'] <> '') {
                $description = strip_tags(SQ_Tools::$options['sq_fp_description']);
            } else {
                $description = $this->grabDescriptionFromPost();
            }
        } else {
            $description = get_bloginfo('description');
        }

        $description = (($description <> '') ? $description : $this->title);
        if ($description <> '') {

            $this->description = apply_filters('sq_description', $description);
            if ($this->description <> '') { //prevent blank description
                return sprintf("<meta name=\"description\" content=\"%s\" />", $this->description);
            }
        }

        return '';
    }

    public function clearDescription($description) {
        if ($description <> '') {
            $search = array("'<script[^>]*?>.*?<\/script>'si", // strip out javascript
                "/<form.*?<\/form>/si",
                "/<iframe.*?<\/iframe>/si");

            if (function_exists('preg_replace')) {
                $description = preg_replace($search, '', $description);
            }

            $description = SQ_Tools::i18n(trim(esc_html(ent2ncr(strip_tags($description)))));
            $description = addcslashes($description, '$');
        }

        return $description;
    }

    /**
     * Get the keywords from articles
     *
     * @return string
     */
    public function getCustomKeyword() {
        $keywords = '';

        if ($this->checkPostsPage() && SQ_Tools::$options['sq_auto_description'] == 1) {
            $keywords = stripcslashes(SQ_Tools::i18n($this->grabKeywordsFromPost($this->post->ID)));
        } elseif (is_single() || is_page()) {
            $keywords = stripcslashes(SQ_Tools::i18n($this->grabKeywordsFromPost($this->post->ID)));
        } elseif (SQ_Tools::$options['sq_auto_description'] == 1) {
            $keywords = trim(SQ_Tools::i18n($this->grabKeywordsFromPost()));
        }

        /* Check if is a predefined Keyword */
        if (SQ_Tools::$options['sq_auto_description'] == 1) { //
            if (($this->isHomePage() &&
                SQ_Tools::$options['sq_fp_keywords'] <> '')
            ) {
                $keywords = strip_tags(SQ_Tools::$options ['sq_fp_keywords']);
            }
        }

        if (isset($keywords) && !empty($keywords) && !(is_home() && is_paged())) {
            $this->keywords = apply_filters('sq_keywords', str_replace('"', '', $keywords));

            return sprintf("<meta name=\"keywords\" content=\"%s\" />", $this->keywords);
        }

        return false;
    }

    /**
     * Get the copyright meta
     *
     * @return string
     */
    public function getCopyright() {
        $meta = '';

        $name = $this->getAuthor('display_name');
        if ($name == '') {
            $name = $this->meta['blogname'];
        }

        if ($name <> '') {
            $meta = sprintf("<meta name=\"dcterms.rightsHolder\" content=\"%s\" />" . "\n", apply_filters('sq_copyright', $name));
        }

        return apply_filters('sq_copyright_meta', $meta);
    }

    /**
     * Get the Google Plus Author meta
     *
     * @return string
     */
    public function getGooglePlusMeta() {
        $meta = '';
        $author = SQ_Tools::$options['sq_google_plus'];

        if (strpos($author, 'plus.google.com') === false && is_numeric($author)) {
            $author = 'https://plus.google.com/' . $author;
        }

        if ($author <> '' && !class_exists('ABH_Classes_ObjController')) {
            $meta = '<link rel="publisher" href="' . $author . '" />' . "\n";
        }

        return apply_filters('sq_publisher_meta', $meta);
    }

    /**
     * Get the icons for serachengines
     *
     * @return string
     */
    public function getFavicon() {
        $meta = '';
        $rnd = '';

        if (current_user_can('manage_options')) {
            $rnd = '?' . md5(SQ_Tools::$options['favicon']);
        }

        if (SQ_Tools::$options['favicon'] <> '' && file_exists(_SQ_CACHE_DIR_ . SQ_Tools::$options['favicon'])) {
            $meta .= "\n";

            if (!get_option('permalink_structure')) {
                $favicon = get_bloginfo('wpurl') . '/index.php?sq_get=favicon';
                $touchicon = get_bloginfo('wpurl') . '/index.php?sq_get=touchicon';
            } else {
                $favicon = get_bloginfo('wpurl') . '/favicon.icon' . $rnd;
                $touchicon = get_bloginfo('wpurl') . '/touch-icon.png' . $rnd;
            }
            $meta .= sprintf("<link rel=\"shortcut icon\"  href=\"%s\" />" . "\n", $favicon);
            $meta .= sprintf("<link rel=\"apple-touch-icon\"  href=\"%s\" />" . "\n", $touchicon);

            $appleSizes = preg_split('/[,]+/', _SQ_MOBILE_ICON_SIZES);
            foreach ($appleSizes as $size) {
                if (!get_option('permalink_structure')) {
                    $favicon = get_bloginfo('wpurl') . '/index.php?sq_get=touchicon&sq_size=' . $size;
                } else {
                    $favicon = get_bloginfo('wpurl') . '/touch-icon' . $size . '.png' . $rnd;
                }
                $meta .= sprintf("<link rel=\"apple-touch-icon\" sizes=\"" . $size . "x" . $size . "\"  href=\"%s\" />" . "\n", $favicon);
            }
        } else {
            if (file_exists(ABSPATH . 'favicon.ico')) {
                $meta .= sprintf("<link rel=\"shortcut icon\"  href=\"%s\" />" . "\n", get_bloginfo('wpurl') . '/favicon.ico');
            }
        }
        return apply_filters('sq_publisher_meta', $meta);
    }

    /**
     * Get the language meta
     *
     * @return string
     */
    public function getLanguage() {
        $meta = '';
        $language = get_bloginfo('language');

        if ($language <> '') {
            $url = get_bloginfo('url');
            if (strpos($language, '-') !== false) {
                $hreflang = substr($language, 0, strpos($language, '-'));
            }


            $meta .= sprintf("<meta name=\"dc.language\" content=\"%s\" />", $language) . "\n";
        }

        return apply_filters('sq_language_meta', $meta);
    }

    /**
     * Get the DC.publisher meta
     *
     * @return string
     */
    public function getDublinCore() {
        $date = null;
        $meta = '';

        $name = $this->getAuthor('display_name');
        if (!$name) {
            $name = $this->meta['blogname'];
        }

        if ($name <> '') {
            $meta .= sprintf("<meta name=\"dc.publisher\" content=\"%s\" />", $name) . "\n";
        }

        $meta .= sprintf('<meta name="dc.title" content="%s" />', $this->title) . "\n";
        $meta .= sprintf('<meta name="dc.description" content="%s" />', $this->description) . "\n";

        if ($this->isHomePage()) {
            $date = date('Y-m-d', strtotime(get_lastpostmodified('gmt')));
        } elseif (is_single() && isset($this->post->post_date)) {
            $date = date('Y-m-d', strtotime($this->post->post_date));
        }

        if (isset($date)) {
            $meta .= sprintf("<meta name=\"dc.date.issued\" content=\"%s\" />", $date) . "\n";
        }

        return apply_filters('sq_dublin_meta', $meta);
    }

    /**
     * Get the XML Sitemap meta
     *
     * @return string
     */
    public function getXMLSitemap() {
        $meta = '';

        $xml_url = SQ_ObjController::getController('SQ_Sitemaps')->getXmlUrl('sitemap');

        if ($xml_url <> '') {
            $meta = sprintf("<link rel=\"alternate\" type=\"application/rss+xml\" " . (($this->title <> '') ? "title=\"%s\"" : "") . " href=\"%s\" />", $this->title, $xml_url) . "\n";
        }

        return apply_filters('sq_sitemap_meta', $meta);
    }

    /**
     * Get the google Webmaster Tool code
     *
     * @return string
     */
    public function getGoogleWT() {
        $sq_google_wt = SQ_Tools::$options['sq_google_wt'];

        if ($this->isHomePage() && $sq_google_wt <> '') {
            return sprintf("<meta name=\"google-site-verification\" content=\"%s\" />", $sq_google_wt) . "\n";
        }

        return false;
    }

    /**
     * Get the google Analytics code
     *
     * @return string
     */
    public function getGoogleAnalytics() {
        $sq_google_analytics = SQ_Tools::$options['sq_google_analytics'];

        if ($sq_google_analytics <> '') {
            if (SQ_Tools::$options['sq_auto_amp']) {
                return '<script async custom-element="amp-analytics" src="https://cdn.ampproject.org/v0/amp-analytics-0.1.js"></script>' . "\n";
            } else {
                SQ_ObjController::getController('SQ_DisplayController', false)
                    ->loadMedia('https://www.google-analytics.com/analytics.js');

                return sprintf("<script>(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o), m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m) })(window,document,'script','//www.google-analytics.com/analytics.js','ga'); ga('create', '%s', 'auto');ga('send', 'pageview');</script>", $sq_google_analytics);
            }
        }

        return false;
    }

    public function getGoogleAnalyticsAMPBody() {
        $sq_google_analytics = SQ_Tools::$options['sq_google_analytics'];

        if ($sq_google_analytics <> '') {
            if (SQ_Tools::$options['sq_auto_amp']) {
                return sprintf('<amp-analytics type="googleanalytics" id="analytics1"><script type="application/json">{"vars": {"account": "%s"},"triggers": {"trackPageview": {"on": "visible","request": "pageview"}}}</script></amp-analytics>', $sq_google_analytics) . "\n";
            }
        }
        return '';
    }

    public function getFacebookPixel() {
        $sq_facebook_analytics = SQ_Tools::$options['sq_facebook_analytics'];
        if ($sq_facebook_analytics <> '') {
            $this->setPost($this->post);
            $domain = str_replace(array('http://', 'http://', 'www.'), '', get_bloginfo('url'));
            if ($this->isHomePage()) {
                $events[] = array(
                    'type' => 'track',
                    'name' => 'PageView',
                    'params' => array('page' => get_bloginfo('url'), 'domain' => $domain)
                );
            } else {
                if (isset($this->post->ID)) {
                    $params['content_ids'] = array((string)$this->post->ID);
                }

                $params['content_type'] = $this->post_type;

                if ($this->post_type == 'category') {
                    $category = get_category(get_query_var('cat'), false);
                    if (isset($category->name)) {
                        $params['content_category'] = $category->name;
                    }
                } elseif ($this->post_type == 'product') {
                    $params['content_name'] = $this->post->post_title;
                    $cat = get_the_terms($this->post->ID, 'product_cat');
                    if (!empty($cat)) {
                        $params['content_category'] = $cat[0]->name;
                    }

                    if (isset($_POST['product_id']) && isset($params['content_ids']) && isset($params['content_type'])) {
                        if (function_exists('wc_get_product') && function_exists('get_woocommerce_currency')) {
                            if ($product = wc_get_product((int)$_POST['product_id'])) {
                                $params['value'] = $product->get_price();
                                $params['currency'] = get_woocommerce_currency();
                            }
                        }

                        $events[] = array(
                            'type' => 'track',
                            'name' => 'AddToCart',
                            'params' => $params
                        );
                    }
                } elseif ($this->post_type == 'search') {
                    $search = get_search_query(true);
                    if ($search <> '') {
                        $params['search_string'] = $search;
                        $events[] = array(
                            'type' => 'track',
                            'name' => 'Search',
                            'params' => $params
                        );
                    }
                } elseif ($this->post_type == 'checkout' && isset($this->post->ID)) {
                    global $woocommerce;
                    if (isset($woocommerce->cart->total) && $woocommerce->cart->total > 0) {
                        $params['value'] = $woocommerce->cart->total;

                        if (isset($woocommerce->cart->cart_contents) && !empty($woocommerce->cart->cart_contents)) {
                            $quantity = 0;
                            foreach ($woocommerce->cart->cart_contents as $product) {
                                $quantity += $product['quantity'];
                            }
                            if ($quantity > 0) {
                                $params['num_items'] = $quantity;
                            }
                        }
                        $events[] = array(
                            'type' => 'track',
                            'name' => 'InitiateCheckout',
                            'params' => $params
                        );
                    } elseif (SQ_Tools::getIsset('key')) {
                        $params['content_type'] = 'purchase';
                        global $wpdb;
                        $sql = "SELECT `post_id`
                                FROM `" . $wpdb->postmeta . "`
                                WHERE `meta_key` = '_order_key' AND `meta_value`='" . SQ_Tools::getValue('key') . "'";

                        if ($post = $wpdb->get_row($sql)) {
                            if ($order = wc_get_order($post->post_id)) {
                                $params['content_type'] = "checkout";
                                $params['value'] = $order->get_total();
                                $params['currency'] = $order->get_order_currency();

                                $events[] = array(
                                    'type' => 'track',
                                    'name' => 'Purchase',
                                    'params' => $params
                                );
                            }
                        }
                    }


                } else {
                    $cat = get_the_terms($this->post->ID, 'category');
                    if (!empty($cat)) {
                        $params['content_category'] = $cat[0]->name;
                    }
                }

                $params['page'] = $this->getCanonicalUrl();
                $params['domain'] = $domain;

                if (isset($params['content_ids']) && isset($params['content_type'])) {
                    $events[] = array(
                        'type' => 'track',
                        'name' => 'ViewContent',
                        'params' => $params
                    );
                } else {
                    $events[] = array(
                        'type' => 'trackCustom',
                        'name' => 'GeneralEvent',
                        'params' => $params
                    );
                }

                $events[] = array(
                    'type' => 'track',
                    'name' => 'PageView',
                    'params' => array('page' => $params['page'], 'domain' => $params['domain'])
                );
            }
            $track = '';
            foreach ($events as $event) {
                $track .= "fbq('" . $event['type'] . "', '" . $event['name'] . "', '" . json_encode($event['params']) . "');";
            }
            //$track .= json_encode($this->post);
            if ($sq_facebook_analytics <> '') {
                if (SQ_Tools::$options['sq_auto_amp']) {
                    //not yet supported
                } else {
                    return sprintf("<script>!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,document,'script','https://connect.facebook.net/en_US/fbevents.js');fbq('init', '%s');%s</script><noscript><img height='1' width='1' style='display:none'src='https://www.facebook.com/tr?id=%s&ev=PageView&noscript=1'/></noscript>" . "\n", $sq_facebook_analytics, $track, $sq_facebook_analytics);
                }
            }
        }
        return false;
    }

    /**
     * Get the Facebook Insights code
     *
     * @return string
     */
    public function getFacebookIns() {
        $sq_facebook_insights = SQ_Tools::$options ['sq_facebook_insights'];

        if ($this->isHomePage() && $sq_facebook_insights <> '') {
            return sprintf("<meta property=\"fb:admins\" content=\"%s\" />", $sq_facebook_insights) . "\n";
        }

        return false;
    }

    /**
     * Get the Pinterest code
     *
     * @return string
     */
    public function getPinterest() {
        $sq_pinterest = SQ_Tools::$options['sq_pinterest'];

        if ($this->isHomePage() && $sq_pinterest <> '') {
            return sprintf("<meta name=\"p:domain_verify\" content=\"%s\" />", $sq_pinterest) . "\n";
        }

        return false;
    }

    /**
     * Get the Alexa Tool code
     *
     * @return string
     */
    public function getAlexaT() {
        $sq_alexa = SQ_Tools::$options['sq_alexa'];

        if ($this->isHomePage() && $sq_alexa <> '') {
            return sprintf("<meta name=\"alexaVerifyID\" content=\"%s\" />", $sq_alexa) . "\n";
        }

        return false;
    }

    /**
     * Get the bing Webmaster Tool code
     *
     * @return string
     */
    public function getBingWT() {
        $sq_bing_wt = SQ_Tools::$options['sq_bing_wt'];

        if ($this->isHomePage() && $sq_bing_wt <> '') {
            return sprintf("<meta name=\"msvalidate.01\" content=\"%s\" />", $sq_bing_wt) . "\n";
        }

        return false;
    }

    /**
     * Get the JsonLD meta for this site
     * @return string
     */
    public function getJsonLD() {
        $meta = '';
        $sep = ",\n";
        $jsonld = SQ_ObjController::getModelService('JsonLD');
        $markup = array();
        if ($this->isHomePage()) {
            if (isset(SQ_Tools::$options['sq_jsonld'][SQ_Tools::$options['sq_jsonld_type']])) {
                $markup['@type'] = SQ_Tools::$options['sq_jsonld_type'];
                $markup['@id'] = $this->url;
                $markup['url'] = $this->url;

                foreach (SQ_Tools::$options['sq_jsonld'][SQ_Tools::$options['sq_jsonld_type']] as $key => $value) {
                    if ($value <> '') {
                        if (SQ_Tools::$options['sq_jsonld_type'] == 'Organization' && $key == 'contactType') {
                            continue;
                        }
                        if (SQ_Tools::$options['sq_jsonld_type'] == 'Organization' && $key == 'telephone') {
                            $markup['contactPoint'] = array(
                                '@type' => 'ContactPoint',
                                'telephone' => $value,
                                'contactType' => SQ_Tools::$options['sq_jsonld'][SQ_Tools::$options['sq_jsonld_type']]['contactType'],

                            );
                        }

                        if ($key == 'logo') {
                            if (SQ_Tools::$options['sq_jsonld_type'] == 'Person') {
                                $key = 'image';
                            }
                            $markup[$key] = array(
                                '@type' => 'ImageObject',
                                'url' => $value,
                            );
                        } else {
                            $markup[$key] = $value;
                        }

                    }
                }
            }
            if (!empty($markup)) {
                $socials = array();
                if (SQ_Tools::$options['sq_twitter_account'] <> '') {
                    $socials[] = SQ_Tools::$options['sq_twitter_account'];
                }
                if (SQ_Tools::$options['sq_facebook_account'] <> '') {
                    $socials[] = SQ_Tools::$options['sq_facebook_account'];
                }
                if (SQ_Tools::$options['sq_google_plus'] <> '') {
                    $socials[] = SQ_Tools::$options['sq_google_plus'];
                }
                if (SQ_Tools::$options['sq_linkedin_account'] <> '') {
                    $socials[] = SQ_Tools::$options['sq_linkedin_account'];
                }
                if (SQ_Tools::$options['sq_pinterest_account'] <> '') {
                    $socials[] = SQ_Tools::$options['sq_pinterest_account'];
                }
                if (SQ_Tools::$options['sq_instagram_account'] <> '') {
                    $socials[] = SQ_Tools::$options['sq_instagram_account'];
                }

                $markup['potentialAction'] = array(
                    '@type' => 'SearchAction',
                    'target' => get_bloginfo('url') . '?s={search_string}',
                    'query-input' => 'required name=search_string',
                );

                if (!empty($socials)) {
                    $markup['sameAs'] = $socials;
                }
            }
            //add current markup
            $jsonld->set_data($markup);
        } elseif ($this->post_type == 'post') {
            $markup['@type'] = 'Article';
            $markup['@id'] = $this->url;
            $markup['url'] = $this->url;
            if (isset($this->title)) {
                $markup['name'] = $this->truncate($this->title,$this->min_title_length,$this->max_title_length) ;
            }
            if (isset($this->description)) {
                $markup['headline'] = $this->truncate($this->description, $this->min_description_length, 110);
            }
            $markup['mainEntityOfPage'] = array(
                '@type' => 'WebPage',
                'url' =>$this->url
            );

            if (!empty($this->thumb_images)) {
                $markup['thumbnailUrl'] = $this->thumb_images[0]['src'];
            }
            if (isset($this->post->post_date)) {
                $markup['datePublished'] = date('c', strtotime($this->post->post_date));
            }
            if (isset($this->post->post_modified)) {
                $markup['dateModified'] = date('c', strtotime($this->post->post_modified));
            }
            if (!empty($this->thumb_images)) {
                foreach ($this->thumb_images as $image) {
                    $markup['image'] = array(
                        "@type"=> "ImageObject",
                        "url"=> $image['src'],
                        "height"=> ((isset($image['height']) && $image['height'] <> '') ? (int)$image['height'] : 500),
                        "width"=> ((isset($image['width']) && $image['width'] <> '') ? (int)$image['width'] : 700),
                    );
                    break;
                }
            }
            $markup['author'] = array(
                "@type"=> "Person",
                "url"=> $this->getAuthor('user_url'),
                "name"=> $this->getAuthor('display_name'),
            );

            if (SQ_Tools::$options['sq_jsonld_type'] == 'Organization' && isset(SQ_Tools::$options['sq_jsonld'][SQ_Tools::$options['sq_jsonld_type']])) {

                $markup['publisher'] = array(
                    "@type"=> SQ_Tools::$options['sq_jsonld_type'],
                    "url"=> $this->url,
                    "name"=> $this->getAuthor('display_name'),
                );

                foreach (SQ_Tools::$options['sq_jsonld'][SQ_Tools::$options['sq_jsonld_type']] as $key => $value) {
                    if ($value <> '') {
                        if ($key == 'contactType' || $key == 'telephone') {
                            continue;
                        }

                        if ($key == 'logo') {
                            $markup['publisher']['logo'] = array(
                                "@type"=> "ImageObject",
                                "url"=> $value
                            );

                        } else {
                            $markup['publisher'][$key] = $value;
                        }
                    }
                }
            }
            $markup['keywords'] = str_replace(',', '","', $this->grabKeywordsFromPost());

            //add current markup
            $jsonld->set_data($markup);
        }  elseif (is_author()) {
            $markup['@type'] = 'Person';
            $markup['@id'] =  $this->getAuthor('user_url');
            $markup['url'] =  $this->getAuthor('user_url');
            $markup['name'] =  $this->getAuthor('display_name');

            //add current markup
            $jsonld->set_data($markup);
        }

        return apply_filters('sq_json_ld_meta', $jsonld->getStructuredData());
    }

    /**
     * *******************************************************************
     * ******************************************************************** */

    /**
     * Get the title from the curent/last post
     *
     * @return string
     */
    public function grabTitleFromPost($id = null) {
        global $wp_query;
        $post = null;
        $title = '';
        $advtitle = '';

        if (isset($id)) {
            $post = get_post($id);
        }

        if (!$post) {
            if (!empty($wp_query->posts))
                foreach ($wp_query->posts as $post) {
                    $id = (is_attachment()) ? ($post->post_parent) : ($post->ID);
                    $post = get_post($id);

                    break;
                }
        }

        if ($post) {
            if (!$this->isHomePage())
                $title = SQ_Tools::i18n($post->post_title);

            //If there is title saved in database
            if ($advtitle = $this->getAdvancedMeta($post->ID, 'title')) {
                $title = SQ_Tools::i18n($advtitle);
            } elseif ($advtitle = $this->getOtherPluginsMeta($post->ID, 'title')) {
                $title = SQ_Tools::i18n($advtitle);
            }
        }

        return $title;
    }

    /**
     * Get the description from the curent/last post
     *
     * @return string
     */
    public function grabDescriptionFromPost($id = null) {
        global $wp_query;
        $post = null;

        if (isset($id)) {
            $post = get_post($id);
        }

        $description = '';
        $advdescription = '';
        //echo 'post: ' . get_the_ID();
        if (!$post) {
            if (!empty($wp_query->posts))
                foreach ($wp_query->posts as $post) {
                    $id = (is_attachment()) ? ($post->post_parent) : ($post->ID);
                    $post = get_post($id);
                    break;
                }
        }


        if ($post) {
            if (!$this->isHomePage()) {
                $description = $this->truncate(SQ_Tools::i18n($post->post_excerpt), $this->min_description_length, $this->max_description_length);
                if (!$description) {
                    $description = $this->truncate(SQ_Tools::i18n($post->post_content), $this->min_description_length, $this->max_description_length);
                }
            }

            //If there is description saved in database
            if ($advdescription = $this->getAdvancedMeta($post->ID, 'description')) {
                $description = SQ_Tools::i18n($advdescription);
            } elseif ($advdescription = $this->getOtherPluginsMeta($post->ID, 'description')) {
                $description = SQ_Tools::i18n($advdescription);
            }
        }
        // "internal whitespace trim"

        $description = preg_replace("/\s\s+/u", " ", $description);

        return $description;
    }

    /**
     * Get the keywords from the curent/last post and from density
     *
     * @return array
     */
    public function grabKeywordsFromPost($id = null) {
        global $wp_query;

        $this->max_keywords = ($this->max_keywords > 0 ? ($this->max_keywords - 1) : 0);
        if ($this->max_keywords == 0) {
            return;
        }

        $keywords = array();
        $advkeywords = '';


        if (isset($id) && $post = get_post($id)) {
            $density = array();

            if (SQ_Tools::$options['sq_keywordtag'] == 1) {
                foreach (wp_get_post_tags($id) as $keyword) {
                    $keywords[] = SQ_Tools::i18n($keyword->name);
                }
            } else {
                if ($json = SQ_ObjController::getModel('SQ_Post')->getKeyword($post->ID)) {
                    if (isset($json->keyword)) {
                        $keywords[] = SQ_Tools::i18n($json->keyword);
                    }
                }
            }

            if (count($keywords) <= $this->max_keywords) {
                if ($advkeywords = $this->getAdvancedMeta($post->ID, 'keywords')) {
                    $keywords[] = SQ_Tools::i18n($advkeywords);
                }
            }
            if (sizeof($keywords) > $this->max_keywords) {
                $keywords = array_slice($keywords, 0, $this->max_keywords);
            }
        } else {
            if (is_404()) {
                return null;
            }

            if (!is_home() && !is_page() && !is_single() && !$this->checkFrontPage() && !$this->checkPostsPage()) {
                return null;
            }

            if (is_home()) {
                if (isset($wp_query->posts) && !empty($wp_query->posts)) {
                    $posts = (array)$wp_query->posts;

                    if (SQ_Tools::$options['sq_keywordtag'] == 1) {
                        foreach ($posts as $post) {
                            foreach (wp_get_post_tags($post->ID) as $keyword) {
                                $keywords[] = SQ_Tools::i18n($keyword->name);
                            }
                        }
                    }

                    if (sizeof($keywords) > $this->max_keywords) {
                        $keywords = array_slice($keywords, 0, $this->max_keywords);
                    }
                }
            }
            if (count($keywords) <= $this->max_keywords) {
                if (isset($wp_query->posts) && !empty($wp_query->posts)) {
                    $posts = (array)$wp_query->posts;

                    foreach ($posts as $post) {
                        $id = (is_attachment()) ? ($post->post_parent) : ($post->ID);

                        if (SQ_Tools::$options['sq_keywordtag'] == 1) {
                            foreach (wp_get_post_tags($id) as $keyword) {
                                $keywords[] = SQ_Tools::i18n($keyword->name);
                            }
                        }
// autometa
                        $autometa = stripcslashes(get_post_meta($id, 'autometa', true));
                        //$autometa = stripcslashes(get_post_meta($post->ID, "autometa", true));
                        if (isset($autometa) && !empty($autometa)) {

                            $autometa_array = explode(' ', $autometa);
                            foreach ($autometa_array as $e) {
                                $keywords[] = SQ_Tools::i18n($e);
                            }
                        }
                    }
                }
            }
        }

        //If there are keywords saved in database
        if ($advkeywords = $this->getAdvancedMeta($post->ID, 'keyword')) {
            $keywords[] = SQ_Tools::i18n($advkeywords);
        }

        //If there are keywords in other plugins
        if ($advkeywords = $this->getOtherPluginsMeta($post->ID, 'keyword')) {
            $keywords[] = SQ_Tools::i18n($advkeywords);
        }

        return $this->getUniqueKeywords($keywords);
    }

    /**
     * Find the correct canonical url
     *
     * @return string
     */
    public function getCanonicalUrl($external = false) {
        global $wp_query;

        if (!isset($wp_query) || is_404() || is_search()) {
            return false;
        }

        //If external is TRUE, get the original canonical link set
        if ($external) {
            if (isset($this->post->ID) && $link = $this->getAdvancedMeta($this->post->ID, 'canonical')) {
                if ($link <> '') {
                    return apply_filters('sq_canonical', $link);
                }
            }
        }

        //If we have the Post ID
        if (isset($this->post->ID)) {
            $link = get_permalink($this->post->ID);
            $link = $this->getPaged($link);
            if ($link <> '') {
                return apply_filters('sq_canonical', $link);
            }
        }

        //Find the canonical
        $haspost = (count($wp_query->posts) > 0);

        if (get_query_var('m') <> '') {
            $m = preg_replace('/[^0-9]/', '', get_query_var('m'));
            switch (strlen($m)) {
                case 4:
                    $link = get_year_link($m);
                    break;
                case 6:
                    $link = get_month_link(substr($m, 0, 4), substr($m, 4, 2));
                    break;
                case 8:
                    $link = get_day_link(substr($m, 0, 4), substr($m, 4, 2), substr($m, 6, 2));
                    break;
                default:
                    return false;
            }
        } elseif ((is_single() || is_page()) && $haspost) {
            $post = $wp_query->posts[0];
            $link = get_permalink($post->ID);
            $link = $this->getPaged($link);
        } elseif ((is_single() || is_page()) && $haspost) {
            $post = $wp_query->posts[0];
            $link = get_permalink($post->ID);
            $link = $this->getPaged($link);
        } elseif (is_author() && $haspost) {
            $link = $this->getAuthor('user_url');
        } elseif (is_category() && $haspost) {
            $link = $this->getPaged(get_category_link(get_query_var('cat')));
        } else if (is_tag() && $haspost) {
            $tag = get_term_by('slug', get_query_var('tag'), 'post_tag');
            if (!empty($tag->term_id)) {
                $link = get_tag_link($tag->term_id);
            }
            $link = $this->getPaged($link);
        } elseif (is_day() && $haspost) {
            $link = get_day_link(get_query_var('year'), get_query_var('monthnum'), get_query_var('day'));
        } elseif (is_month() && $haspost) {
            $link = get_month_link(get_query_var('year'), get_query_var('monthnum'));
        } elseif (is_year() && $haspost) {
            $link = get_year_link(get_query_var('year'));
        } elseif (is_home()) {
            if ((get_option('show_on_front') == 'page') && ($pageid = get_option('page_for_posts'))) {
                $link = trailingslashit($this->getPaged(get_permalink($pageid)));
            } else {
                if (function_exists('icl_get_home_url')) {
                    $link = icl_get_home_url();
                } else {
                    $link = get_option('home');
                }
                $link = trailingslashit($this->getPaged($link));
            }
        } elseif (is_tax() && $haspost) {
            $taxonomy = get_query_var('taxonomy');
            $term = get_query_var('term');
            $link = $this->getPaged(
                get_term_link($term, $taxonomy));
        } else {
            return false;
        }

        return apply_filters('sq_canonical', $link);
    }

    public function getAuthor($what = 'user_nicename') {
        if (!isset($this->author)) {
            if (is_author()) {
                $this->author = get_userdata(get_query_var('author'));
            } elseif (is_single() && isset($this->post->post_author)) {
                $this->author = get_userdata((int)$this->post->post_author)->data;
            }
        }

        if (isset($this->author)) {

            if ($what == 'user_url' && $this->author->$what == '') {
                return get_author_posts_url($this->author->ID, $this->author->user_nicename);
            }
            if (isset($this->author->$what)) {
                return $this->author->$what;
            }
        }

        return false;
    }

    public function getPaged($link) {
        $page = (int)get_query_var('paged');
        if ($page && $page > 1) {
            $link = trailingslashit($link) . "page/" . "$page/";
        }
        return $link;
    }

    /**
     * Check if is the homepage
     *
     * @return bool
     */
    public function isHomePage() {
        global $wp_query;

        if (isset($wp_query->queried_object_id)) {
            $this->post = get_post($wp_query->queried_object_id);

        }

        //Check if blog posts page
        if (is_home() && $wp_query->is_posts_page) {
            return false;
        }

        return (is_home() || (isset($wp_query->query) && empty($wp_query->query) && !is_preview()));
    }

    public function isHtmlHeader() {
        $headers = headers_list();

        foreach ($headers as $index => $value) {
            if (strpos($value, ':') !== false) {
                $exploded = @explode(': ', $value);
                if (count($exploded) > 1) {
                    $headers[$exploded[0]] = $exploded[1];
                }
            }
        }

        if (isset($headers['Content-Type'])) {
            if (strpos($headers['Content-Type'], 'text/html') !== false) {
                return true;
            }
        } else {
            return true;
        }

        return false;
    }

    /**
     * Check if page is shown in front
     *
     * @return bool
     */
    public function checkFrontPage() {
        return is_page() && get_option('show_on_front') == 'page' && isset($this->post->ID) && $this->post->ID == get_option('page_on_front');
    }

    /**
     * Check if page is shown in home
     *
     * @return bool
     */
    public function checkPostsPage() {
        return is_home() && get_option('show_on_front') == 'page' && isset($this->post->ID) && $this->post->ID == get_option('page_for_posts');
    }

    public function truncate($text, $min, $max) {
        if (function_exists('strip_tags')) {
            $text = strip_tags($text);
        }
        $text = str_replace(']]>', ']]&gt;', $text);
        $text = @preg_replace('|\[(.+?)\](.+?\[/\\1\])?|s', '', $text);
        $text = strip_tags($text);

        if ($max < strlen($text)) {
            while ($text[$max] != ' ' && $max > $min) {
                $max--;
            }
        }
        $text = substr($text, 0, $max);
        return trim(stripcslashes($text));
    }


    /**
     * Show just distinct keywords
     *
     * @return string
     */
    public function getUniqueKeywords($keywords) {
        $all = array();
        if (is_array($keywords)) {
            foreach ($keywords as $word) {
                if (function_exists('mb_strtolower')) {
                    $all[] = mb_strtolower($word, get_bloginfo('charset'));
                } else {
                    $all[] = strtolower($word);
                }
            }
        }

        if (is_array($all) && count($all) > 0) {
            $all = array_unique($all);
            if (sizeof($all) > $this->max_keywords) {
                $all = array_slice($all, 0, $this->max_keywords);
            }

            return implode(',', $all);
        }

        return '';
    }

    /**
     * Check if other plugin are/were installed and don't change the SEO
     *
     * @param type $post_id
     * @return boolean
     */
    public function getAdvancedMeta($post_id, $meta = 'title') {
        global $wpdb;

        $field = '';
        $cond = '';

        if (!isset($post_id) || (int)$post_id == 0) {
            return '';
        }

        switch ($meta) {
            case 'title':
                $field = '_sq_fp_title';
                break;
            case 'description':
                $field = '_sq_fp_description';
                break;
            case 'keyword':
                $field = '_sq_fp_keywords';
                break;
            case 'ogimage':
                $field = '_sq_fp_ogimage';
                break;
            case 'canonical':
                $field = '_sq_canonical';
                break;
            default:
                $field = '_sq_fp_title';
        }

        if ($field <> '' && isset($this->meta[$post_id][$field])) {
            return $this->meta[$post_id][$field];
        }

        // Get the custom Squirrly meta
        //////////////////////////////////////////
        $fields = array('_sq_fp_title' => '', '_sq_fp_description' => '', '_sq_fp_keywords' => '', '_sq_fp_ogimage' => '', '_sq_canonical' => '');

        $sql = "SELECT `meta_key`, `meta_value`
                       FROM `" . $wpdb->postmeta . "`
                        WHERE `post_id`=" . (int)$post_id;

        if ($rows = $wpdb->get_results($sql)) {
            foreach ($rows as $row) {
                if (array_key_exists($row->meta_key, $fields)) {
                    $this->meta[$post_id][$row->meta_key] = $row->meta_value;
                }
            }
        }
        if (isset($this->meta[$post_id]) && is_array($this->meta[$post_id])) {
            $this->meta[$post_id] = array_merge($fields, $this->meta[$post_id]);
        } else {
            $this->meta[$post_id] = $fields;
        }
//////////////////////////////////////////

        if ($field <> '') {
            return $this->meta[$post_id][$field];
        }
/////////////
        return false;
    }

    /**
     * Check if other plugin are/were installed and don't change the SEO
     *
     * @param type $post_id
     * @return boolean
     */
    public function getOtherPluginsMeta($post_id, $meta = 'title') {
        global $wpdb;

        $field = '';
        $cond = '';

        if (!isset($post_id) || (int)$post_id == 0) {
            return '';
        }

        //check yoast
        switch ($meta) {
            case 'title':
                $field = '_yoast_wpseo_title';
                break;
            case 'description':
                $field = '_yoast_wpseo_metadesc';
                break;
            case 'keyword':
                $field = '_yoast_wpseo_focuskw';
                break;
            default:
                $field = '_yoast_wpseo_title';
        }

        if ($field <> '' && isset($this->meta[$post_id][$field])) {
            return $this->meta[$post_id][$field];
        }

// Get the custom Squirrly meta
//////////////////////////////////////////
        $fields = array('_yoast_wpseo_title' => '', '_yoast_wpseo_metadesc' => '', '_yoast_wpseo_focuskw' => '');

        $sql = "SELECT `meta_key`, `meta_value`
                       FROM `" . $wpdb->postmeta . "`
                       WHERE `post_id`=" . (int)$post_id;

        $rows = $wpdb->get_results($sql);
        if ($rows) {
            foreach ($rows as $row) {
                if (array_key_exists($row->meta_key, $fields)) {
                    $this->meta[$post_id][$row->meta_key] = $row->meta_value;
                }
            }
        }
        if (isset($this->meta[$post_id]) && is_array($this->meta[$post_id])) {
            $this->meta[$post_id] = array_merge($fields, $this->meta[$post_id]);
        } else {
            $this->meta[$post_id] = $fields;
        }
//////////////////////////////////////////
        if ($field <> '') {
            return $this->meta[$post_id][$field];
        }
        /////////////
        return false;
    }

    /**
     * ROBOTSTXT
     */
    // add sitemap location in robots.txt generated by WP
    public function robots($content = '') {
        global $blog_id;

        /** display robots.txt */
        header('Status: 200 OK', true, 200);
        header('Content-type: text/plain; charset=' . get_bloginfo('charset'));


        echo "\n# Squirrly SEO Robots";

        if (get_option('blog_public') != 1) {
            echo "\n# Squirrly Sitemaps is disabled. Please see Site Visibility on Settings > Reading.";
        } else {
            if (SQ_Tools::$options['sq_auto_sitemap'] == 1) {
                foreach ((array)SQ_Tools::$options['sq_sitemap'] as $name => $sitemap) {
                    if ($name == 'sitemap-product' && !SQ_ObjController::getModel('SQ_BlockSettingsSeo')->isEcommerce()) {
                        continue;
                    }
                    if ($sitemap[1] == 1 || $sitemap[1] == 2) {
                        echo "\nSitemap: " . trailingslashit(get_bloginfo('url')) . $sitemap[0];
                    }
                }
            }

            if (empty(SQ_Tools::$options['sq_sitemap']))
                echo "\n# No Squirrly SEO Robots found. ";
        }
        echo "\n\n";

        if (!empty(SQ_Tools::$options['sq_robots_permission'])) {
            foreach ((array)SQ_Tools::$options['sq_robots_permission'] as $robot_txt)
                echo $robot_txt . "\n";
        }
        echo "\n\n";

        echo $content;
        exit;
    }

}
