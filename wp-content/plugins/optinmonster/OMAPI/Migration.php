<?php
/**
 * Migration class.
 *
 * @since 1.0.0
 *
 * @package OMAPI
 * @author  Thomas Griffin
 */
class OMAPI_Migration {

	/**
	 * Path to the file.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $file = __FILE__;

	/**
	 * Holds the base class object.
	 *
	 * @since 1.0.0
	 *
	 * @var OMAPI
	 */
	public $base;

	/**
	 * The API class
	 *
	 * @since 1.0.0
	 *
	 * @var OMAPI_Api
	 */
	private $api;

	/**
	 * Class constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct( OMAPI_Api $api ) {

		$this->base = OMAPI::get_instance();
		$this->api  = $api;
		$this->data = $this->gather_data();

	}

	/**
	 * Collects all of the data necessary to migrate
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	protected function gather_data() {

		$data = array();

		// Get site info
		$data['site'] = $this->get_site();

		// Get optins
		$data['optins'] = $this->get_optins();

		// Get integrations
		$data['integrations'] = $this->get_integrations();

		return $data;

	}

	/**
	 * Collects the site information and global settings
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	protected function get_site() {

		$site = array();

		// Get site name
		$site['name'] = get_bloginfo( 'name' );

		// Get site url
		$site['url'] = site_url();

		$site_options = get_option( 'optin_monster' );

		// Return early if options were not found
		if ( ! $site_options ) {
			return $site;
		}

		// Setup site options
		$site['global_cookie']           = isset( $site_options['cookie'] ) ? $site_options['cookie'] : 0;
		$site['affiliate_link']          = isset( $site_options['affiliate_link'] ) ? $site_options['affiliate_link'] : '';
		$site['affiliate_link_position'] = isset( $site_options['affiliate_link_position'] ) ? $site_options['affiliate_link_position'] : 'under';

		return $this->escape_values( $site );

	}

	/**
	 * Collects all of the optins.
	 *
	 * Parent optins then split tests to ensure split tests
	 * can be assigned to an existing parent during migration.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	protected function get_optins() {

		// Get all the optins
		$args   = array(
			'post_type'     => 'optin',
			'post_status'   => 'publish',
			'no_found_rows' => true,
			'cache_results' => false,
			'nopaging'      => true,
			'meta_query'    => array(
				array(
					'key'     => '_om_is_clone',
					'compare' => 'NOT EXISTS',
					'value'   => '',
				)
			),
		);
		$optins = get_posts( $args );

		// Return early if we don't find any optins
		if ( empty( $optins ) ) {
			return false;
		}

		// Get the split tests
		foreach ( $optins as $optin ) {
			$splits = $this->get_split_tests( $optin->ID );

			if ( $splits ) {
				foreach ( $splits as $split ) {
					$optins[] = $split;
				}
			}
		}

		$migration_data = get_option( '_om_migration_data', array( 'migrated_optins' => array() ) );
		foreach ( $optins as $key => $optin ) {
			// Unset the optin if it has already been migrated
			if ( in_array( $optin->ID, $migration_data['migrated_optins'] ) ) {
				unset($optins[$key] );
				continue;
			}

			// Add all of the meta info as WP_Post properties to simplify migration
			$optin->split_note = get_post_meta( $optin->ID, '_om_split_notes', true );
			$optin->has_clone  = get_post_meta( $optin->ID, '_om_has_clone' );
			$optin->is_clone   = get_post_meta( $optin->ID, '_om_is_clone', true );
			$optin->meta       = get_post_meta( $optin->ID, '_om_meta' );

			// Add the image as a property if needed
			$image_id = get_post_meta( $optin->ID, '_thumbnail_id', true );
			if ( $image_id ) {
				$image = wp_get_attachment_metadata( $image_id );
				$optin->image = array();
				$optin->image['filename'] = basename( $image['file'] );
				$optin->image['alt'] = get_post_meta( $image_id, '_wp_attachment_image_alt', true );

				$file = file_get_contents( WP_CONTENT_DIR . '/uploads/' . $image['file'] );
				$optin->image['data'] = base64_encode( $file );
			}
		}

		// Return all the data
		return $this->escape_values( $optins );

	}

	/**
	 * Pulls the integration info
	 *
	 * @since 1.0.0
	 *
	 * @return bool|array
	 */
	protected function get_integrations() {

		$providers = get_option( 'optin_monster_providers' );

		if ( ! $providers ) {
			return array();
		}

		return $this->escape_values( $providers );

	}

	/**
	 * Runs the migration through OMAPI_Api
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function run() {

		$this->api->set_additional_data( $this->data );
		$response = $this->api->request();

		if ( is_wp_error( $response ) ) {
			return false;
		}

		$migration_data = get_option( '_om_migration_data', array( 'migrated_optins' => array() ) );
		$migration_data['errors'] = false;
		if ( property_exists( $response, 'imported_optins' ) ) {
			$migration_data['migrated_optins'] = array_merge( $migration_data['migrated_optins'], $response->imported_optins );
		}

		if ( property_exists( $response, 'integrations' ) ) {
			$migration_data['integrations'] = $response->integrations;
		}

		if ( property_exists( $response, 'site' ) ) {
			$migration_data['site'] = $response->site;
		}

		if ( property_exists( $response, 'errors' ) ) {
			foreach ( $response->errors as $error ) {
				$migration_data['errors'][] = $error;
			}
		}

		if ( property_exists( $response, 'new_optins' ) && $response->new_optins ) {
			foreach ( $response->new_optins as $slug => $optin ) {
				// Maybe update an optin rather than add a new one.
				$local = $this->base->get_optin_by_slug( $slug );
				$data  = array();
				if ( $local ) {
					$data['ID'] 		  = $local->ID;
					$data['post_title']   = $optin->title;
					$data['post_content'] = $optin->output;
					$data['post_status']  = 'publish';
					wp_update_post( $data );
					update_post_meta( $local->ID, '_omapi_type', $optin->type );
					update_post_meta( $local->ID, '_omapi_ids', $optin->ids );
					$post_id = $local->ID;
				} else {
					$data['post_name']    = $slug;
					$data['post_title']   = $optin->title;
					$data['post_excerpt'] = $optin->id;
					$data['post_content'] = $optin->output;
					$data['post_status']  = 'publish';
					$data['post_type']	  = 'omapi';
					$post_id = wp_insert_post( $data );
					update_post_meta( $post_id, '_omapi_type', $optin->type );
					update_post_meta( $post_id, '_omapi_ids', $optin->ids );
				}

				// Now that the data has been saved, let's now try to grab meta from the previous optin for output settings.
				$prev_optin = get_posts(
					array(
						'post_type'		 => 'optin',
						'posts_per_page' => 1,
						'no_found_rows'	 => true,
						'cache_results'	 => false,
						'name'			 => $slug
					)
				);
				if ( empty( $prev_optin ) ) {
					continue;
				}

				// Now grab all the meta for the optin.
				$prev_optin = $prev_optin[0];
				$meta	    = get_post_meta( $prev_optin->ID, '_om_meta', true );
				$test		= get_post_meta( $prev_optin->ID, '_om_test_mode', true );
				$fields 	= $this->base->output->fields;

				// Get all the new fields available and store the data.
				foreach ( $fields as $field ) {
					$value = false;
					switch ( $field ) {
						case 'enabled' :
							$value = false; // Make sure the optins are disabled by default when being added back.
						break;
						case 'global' :
							$value = isset( $meta['display']['global'] ) && $meta['display']['global'] ? true : false;
						break;
						case 'automatic' :
							$value = isset( $meta['display']['automatic'] ) && $meta['display']['automatic'] ? true : false;
						break;
						case 'users' :
							$value = isset( $meta['logged_in'] ) && $meta['logged_in'] ? 'out' : 'all';
						break;
						case 'never' :
							$value = ! empty( $meta['display']['never'] ) ? $meta['display']['never'] : array();
						break;
						case 'only' :
							$value = ! empty( $meta['display']['exclusive'] ) ? $meta['display']['exclusive'] : array();
						break;
						case 'categories' :
							$value = ! empty( $meta['display']['categories'] ) ? $meta['display']['categories'] : array();
						break;
						case 'show' :
							$value = ! empty( $meta['display']['show'] ) ? $meta['display']['show'] : array();
						break;
						case 'test' :
							$value = $test ? true : false;
						break;
						case 'shortcode' :
							$value = false;
						break;
						case 'mailpoet' :
							$provider = isset( $meta['email']['provider'] ) ? $meta['email']['provider'] : 'none';
							$value 	  = 'mailpoet' == $provider ? true : false;

							// If true, we need to set the MailPoet list as well.
							if ( $value ) {
								$list = isset( $meta['email']['list_id'] ) ? $meta['email']['list_id'] : false;
								if ( $list ) {
									update_post_meta( $post_id, '_omapi_mailpoet_list', $list );
								}
							}
						break;
						case 'type' :
						case 'ids'  :
							$value = get_post_meta( $post_id, '_omapi_' . $field, true );
						break;
					}
					update_post_meta( $post_id, '_omapi_' . $field, $value );
				}
			}
		}

		update_option( '_om_migration_data', $migration_data );
		return true;

	}

	/**
	 * Pulls all split tests for a given optin
	 *
	 * @since 1.0.0
	 *
	 * @param $id
	 * @return array|bool
	 */
	private function get_split_tests( $id ) {
		$optin = get_post( $id );
		if ( ! $optin ) {
			return false;
		}
		$clones = get_post_meta( $optin->ID, '_om_has_clone', true );
		if ( empty( $clones ) ) {
			return false;
		}
		// Return the split test objects.
		$objects = array();
		foreach ( $clones as $clone ) {
			$objects[] = get_post( $clone );
		}

		return $objects;
	}

	/**
	 * Escapes all HTML in an optin to prevent insertion errors.
	 *
	 * @since 1.0.0
	 *
	 * @param array $data 	   Array of data to escape.
	 * @return array $new_data Sanitized array of data.
	 */
	private function escape_values( $data = array() ) {

		$new_data = array();
		foreach ( $data as $key => $value ) {
	        if ( is_array( $value ) ) {
	        	if ( is_array( $data ) ) {
	           		unset( $data[ $key ] );
	           	} else {
		           	unset( $data->{$key} );
	           	}
	            $new_data[ $key ] = $this->escape_values( $value );
	        } else if ( is_object( $value ) ) {
		        if ( is_array( $data ) ) {
	           		unset( $data[ $key ] );
	           	} else {
		           	unset( $data->{$key} );
	           	}
				$new_data[ $key ] = (object) $this->escape_values( $value );
		    } else {
			    $new_val = is_string( $value ) ? esc_html( $value ) : $value;
				$new_data[ $key ] = $new_val;
				if ( is_array( $data ) ) {
	           		unset( $data[ $key ] );
	           	} else {
		           	unset( $data->{$key} );
	           	}
	        }
    	}

    	return $new_data;

	}

}