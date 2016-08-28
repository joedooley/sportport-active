<?php
/**
 * @package Yoast\VideoSEO
 */

/**
 * Class WPSEO_Video_Wrappers
 */
class WPSEO_Video_Wrappers {

	/**
	 * Fallback function for WP SEO functionality, Validate INT
	 *
	 * @param int $integer Number to validate.
	 *
	 * @return mixed
	 */
	public static function yoast_wpseo_video_validate_int( $integer ) {
		if ( method_exists( 'WPSEO_Utils', 'validate_int' ) ) {
			return WPSEO_Utils::validate_int( $integer );
		}

		return WPSEO_Option::validate_int( $integer );
	}

	/**
	 * Fallback function for WP SEO functionality, is_url_relative
	 *
	 * @param string $url URL to check.
	 *
	 * @return mixed
	 */
	public static function yoast_wpseo_video_is_url_relative( $url ) {
		if ( method_exists( 'WPSEO_Utils', 'is_url_relative' ) ) {
			return WPSEO_Utils::is_url_relative( $url );
		}

		return wpseo_is_url_relative( $url );
	}

	/**
	 * Fallback for WP SEO functionality, sanitize_url
	 *
	 * @param string $string URL to check.
	 *
	 * @return mixed
	 */
	public static function yoast_wpseo_video_sanitize_url( $string ) {
		if ( method_exists( 'WPSEO_Utils', 'sanitize_url' ) ) {
			return WPSEO_Utils::sanitize_url( $string, array( 'http', 'https', 'ftp', 'ftps' ) );
		}

		return WPSEO_Option::sanitize_url( $string, array( 'http', 'https', 'ftp', 'ftps' ) );
	}

	/**
	 * Fallback for admin_header
	 *
	 * @param bool   $form             Form or not.
	 * @param string $option_long_name Full option name.
	 * @param string $option           Option name.
	 * @param bool   $contains_files   Contains file upload or not.
	 *
	 * @return mixed
	 */
	public static function admin_header( $form = true, $option_long_name = 'yoast_wpseo_options', $option = 'wpseo', $contains_files = false ) {

		if ( method_exists( 'Yoast_Form', 'admin_header' ) ) {
			Yoast_Form::get_instance()->admin_header( $form, $option, $contains_files, $option_long_name );

			return;
		}

		return self::admin_pages()->admin_header( true, $option_long_name, $option );
	}

	/**
	 * Fallback for admin_footer
	 *
	 * @param bool $submit       Submit button or not.
	 * @param bool $show_sidebar Show sidebar or not.
	 *
	 * @return mixed
	 */
	public static function admin_footer( $submit = true, $show_sidebar = true ) {

		if ( method_exists( 'Yoast_Form', 'admin_footer' ) ) {
			Yoast_Form::get_instance()->admin_footer( $submit, $show_sidebar );

			return;
		}

		return self::admin_pages()->admin_footer( $submit, $show_sidebar );
	}

	/**
	 * Fallback for the textinput method
	 *
	 * @param string $var    Variable.
	 * @param string $label  Label.
	 * @param string $option Option.
	 *
	 * @return mixed
	 */
	public static function textinput( $var, $label, $option = '' ) {
		if ( method_exists( 'Yoast_Form', 'textinput' ) ) {
			if ( $option !== '' ) {
				Yoast_Form::get_instance()->set_option( $option );
			}

			Yoast_Form::get_instance()->textinput( $var, $label );

			return;
		}

		return self::admin_pages()->textinput( $var, $label, $option );
	}

	/**
	 * Wrapper for select method.
	 *
	 * @param string $var    Variable.
	 * @param string $label  Label.
	 * @param array  $values Values.
	 * @param string $option Option.
	 */
	public static function select( $var, $label, $values, $option = '' ) {
		if ( method_exists( 'Yoast_Form', 'select' ) ) {
			if ( $option !== '' ) {
				Yoast_Form::get_instance()->set_option( $option );
			}

			Yoast_Form::get_instance()->select( $var, $label, $values );
			return;
		}

		return self::admin_pages()->select( $var, $label, $option );
	}

	/**
	 * Wrapper for checkbox method
	 *
	 * @param string $var        Variable.
	 * @param string $label      Label.
	 * @param bool   $label_left Label left or right.
	 * @param string $option     Option.
	 *
	 * @return mixed
	 */
	public static function checkbox( $var, $label, $label_left = false, $option = '' ) {
		if ( method_exists( 'Yoast_Form', 'checkbox' ) ) {
			if ( $option !== '' ) {
				Yoast_Form::get_instance()->set_option( $option );
			}

			Yoast_Form::get_instance()->checkbox( $var, $label, $label_left );
			return;
		}

		return self::admin_pages()->checkbox( $var, $label, $label_left, $option );
	}

	/**
	 * Returns the wpseo_admin pages global variable
	 *
	 * @return mixed
	 */
	private static function admin_pages() {
		global $wpseo_admin_pages;

		if ( ! $wpseo_admin_pages instanceof WPSEO_Admin_Pages ) {
			$wpseo_admin_pages = new WPSEO_Admin_Pages;
		}

		return $wpseo_admin_pages;
	}

	/**
	 * Returns the result of validate bool from WPSEO_Utils if this class exists, otherwise it will return the result from
	 * validate_bool from WPSEO_Option_Video
	 *
	 * @param bool $bool_to_validate Validate bool.
	 *
	 * @return bool
	 */
	public static function validate_bool( $bool_to_validate ) {
		if ( class_exists( 'WPSEO_Utils' ) &&  method_exists( 'WPSEO_Utils', 'validate_bool' ) ) {
			return WPSEO_Utils::validate_bool( $bool_to_validate );
		}

		return WPSEO_Option_Video::validate_bool( $bool_to_validate );
	}
}
