<?php

/**
 * @package Admin
 */

if ( ! defined( 'WPSEO_LOCAL_VERSION' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

if ( ! class_exists( 'WPSEO_Local_Admin_Wrappers' ) ) {
	/**
	 * Class WPSEO_Local_Wrappers contains wrapper methods to be sure WPSEO Local is backwards compatible with Yoast SEO
	 */
	class WPSEO_Local_Admin_Wrappers {

		/**
		 * Fallback for admin_header
		 *
		 * @param bool   $form
		 * @param string $option_long_name
		 * @param string $option
		 * @param bool   $contains_files
		 *
		 * @return void|mixed
		 */
		public static function admin_header( $form = true, $option_long_name = 'yoast_wpseo_options', $option = 'wpseo', $contains_files = false ) {

			if ( method_exists( 'Yoast_Form', 'admin_header' ) ) {
				Yoast_Form::get_instance()->admin_header( $form, $option, $contains_files, $option_long_name );

				return;
			}

			echo self::admin_pages()->admin_header( true, 'yoast_wpseo_local_options', 'wpseo_local' );
		}

		/**
		 * Fallback for admin_footer
		 *
		 * @param bool $submit
		 * @param bool $show_sidebar
		 *
		 * @return void|mixed
		 */
		public static function admin_footer( $submit = true, $show_sidebar = true ) {

			if ( method_exists( 'Yoast_Form', 'admin_footer' ) ) {
				Yoast_Form::get_instance()->admin_footer( $submit, $show_sidebar );

				return;
			}

			echo self::admin_pages()->admin_footer( $submit, $show_sidebar );
		}

		/**
		 * Fallback for the textinput method
		 *
		 * @param string $var
		 * @param string $label
		 * @param string $option
		 *
		 * @return void|mixed
		 */
		public static function textinput( $var, $label, $option = '', $attr = array() ) {
			if ( method_exists( 'Yoast_Form', 'textinput' ) ) {
				if ( $option !== '' ) {
					Yoast_Form::get_instance()->set_option( $option );
				}

				Yoast_Form::get_instance()->textinput( $var, $label, $attr );

				return;
			}

			echo self::admin_pages()->textinput( $var, $label, $option );
		}

		/**
		 * Wrapper for select method.
		 *
		 * @param string $var
		 * @param string $label
		 * @param array  $values
		 * @param string $option
		 *
		 * @return void|mixed
		 */
		public static function select( $var, $label, $values, $option = '' ) {
			if ( method_exists( 'Yoast_Form', 'select' ) ) {
				if ( $option !== '' ) {
					Yoast_Form::get_instance()->set_option( $option );
				}

				Yoast_Form::get_instance()->select( $var, $label, $values );

				return;
			}

			echo self::admin_pages()->select( $var, $label, $values, $option );
		}

		/**
		 * Wrapper for checkbox method
		 *
		 * @param        $var
		 * @param        $label
		 * @param bool   $label_left
		 * @param string $option
		 *
		 * @return void|mixed
		 */
		public static function checkbox( $var, $label, $label_left = false, $option = '' ) {
			if ( method_exists( 'Yoast_Form', 'checkbox' ) ) {
				if ( $option !== '' ) {
					Yoast_Form::get_instance()->set_option( $option );
				}

				Yoast_Form::get_instance()->checkbox( $var, $label, $label_left );

				return;
			}

			echo self::admin_pages()->checkbox( $var, $label, $label_left, $option );
		}

		/**
		 * Create a hidden input field.
		 *
		 * @param string $var    The variable within the option to create the hidden input for.
		 * @param string $option The option the variable belongs to.
		 *
		 * @return void|mixed
		 */
		public static function hidden( $var, $option = '' ) {
			if ( method_exists( 'Yoast_Form', 'hidden' ) ) {
				if ( $option !== '' ) {
					Yoast_Form::get_instance()->set_option( $option );
				}

				Yoast_Form::get_instance()->hidden( $var );

				return;
			}

			echo self::admin_pages()->hidden( $var, $option );

		}

		/**
		 * Create a upload field.
		 *
		 * @param string $var    The variable within the option to create the upload field for.
		 * @param string $option The option the variable belongs to.
		 *
		 * @return void|mixed
		 */
		public static function file_upload( $var, $label = '', $option = '' ) {
			if ( method_exists( 'Yoast_Form', 'file_upload' ) ) {
				if ( $option !== '' ) {
					Yoast_Form::get_instance()->set_option( $option );
				}

				Yoast_Form::get_instance()->file_upload( $var, $label );

				return;
			}

			echo self::admin_pages()->file_upload( $var, $label );

		}

		/**
		 * Returns the wpseo_admin pages global variable
		 *
		 * @return mixed
		 */
		private static function admin_pages() {
			global $wpseo_admin_pages;

			return $wpseo_admin_pages;
		}
	} /* End of class */

} /* End of class-exists wrapper */