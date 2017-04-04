<?php
/**
 * Settings configuration.
 *
 * Validate and output settings.
 *
 * @author      Bas Elbers
 * @category    Abstract Class
 * @package     BE_WooCommerce_PDF_Invoices/Abstracts
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'BEWPI_Abstract_Setting' ) ) {
	/**
	 * Class BEWPI_Abstract_Setting.
	 */
	abstract class BEWPI_Abstract_Setting {
		/**
		 * Options and settings prefix.
		 *
		 * @var string
		 */
		const PREFIX = 'bewpi_';

		/**
		 * Gets all the tags that are allowed.
		 *
		 * @return string|void
		 */
		protected function allowed_tags_text() {
			$allowed_tags_encoded   = array_map( 'htmlspecialchars', array( '<b>', '<i>', '<br>', '<br/>' ) );
			$allowed_tags_formatted = '<code>' . join( '</code>, <code>', $allowed_tags_encoded ) . '</code>';
			$allowed_tags_text      = sprintf( __( 'Allowed HTML tags: %1$s.', 'woocommerce-pdf-invoices' ), $allowed_tags_formatted );

			return $allowed_tags_text;
		}

		/**
		 * String validation.
		 *
		 * @param string $str the string to validate.
		 *
		 * @return bool
		 */
		protected function strip_str( $str ) {
			$str = preg_replace( '/<([a-z][a-z0-9]*)[^>]*?(\/?)>/i', '<$1$2>', $str );

			return strip_tags( $str, '<b><i><br><br/>' );
		}

		/**
		 * Multiple checkboxes html.
		 *
		 * @param array $args option arguments.
		 */
		public function multiple_checkbox_callback( $args ) {
			include BEWPI_DIR . 'includes/admin/views/html-multiple-checkbox-setting.php';
		}

		/**
		 * Select html.
		 *
		 * @param array $args option arguments.
		 */
		public function select_callback( $args ) {
			$options = get_option( $args['page'] );
			?>
			<select id="<?php echo $args['id']; ?>" name="<?php echo $args['page'] . '[' . $args['name'] . ']'; ?>">
				<?php
				foreach ( $args['options'] as $option ) :
					?>
					<option
						value="<?php echo esc_attr( $option['value'] ); ?>" <?php selected( $options[ $args['name'] ], $option['value'] ); ?>><?php echo esc_html( $option['name'] ); ?></option>
					<?php
				endforeach;
				?>
			</select>
			<div class="bewpi-notes"><?php echo $args['desc']; ?></div>
			<?php
		}

		public function input_callback( $args ) {
			$options     = get_option( $args['page'] );
			$class       = ( isset( $args['class'] ) ) ? $args['class'] : "bewpi-notes";
			$is_checkbox = $args['type'] === 'checkbox';
			if ( $is_checkbox ) { ?>
				<input type="hidden" name="<?php echo $args['page'] . '[' . $args['name'] . ']'; ?>" value="0"/>
			<?php } ?>
			<input id="<?php echo $args['id']; ?>"
			       name="<?php echo $args['page'] . '[' . $args['name'] . ']'; ?>"
			       type="<?php echo $args['type']; ?>"
			       value="<?php echo $is_checkbox ? 1 : esc_attr( $options[ $args['name'] ] ); ?>"

				<?php if ( $is_checkbox ) {
					checked( $options[ $args['name'] ] );
				}

				if ( isset ( $args['attrs'] ) ) {
					foreach ( $args['attrs'] as $attr ) {
						echo $attr . ' ';
					}
				}
				?>
			/>
			<?php if ( $is_checkbox ) { ?>
				<label for="<?php echo $args['id']; ?>"
				       class="<?php echo $class; ?>"><?php echo $args['desc']; ?></label>
			<?php } else { ?>
				<div class="<?php echo $class; ?>"><?php echo $args['desc']; ?></div>
			<?php } ?>
			<?php
		}

		public function textarea_callback( $args ) {
			$options = get_option( $args['page'] );
			?>
			<textarea id="<?php echo $args['id']; ?>"
			          name="<?php echo $args['page'] . '[' . $args['name'] . ']'; ?>"
			          rows="5"
			><?php echo esc_textarea( $options[ $args['name'] ] ); ?></textarea>
			<div class="bewpi-notes"><?php echo $args['desc']; ?></div>
			<?php
		}

		/**
		 * Validate image against modified urls and check for extension.
		 *
		 * @param string $image_url source url of the image.
		 *
		 * @return bool|string false or image url.
		 */
		public function validate_image( $image_url ) {
			$image_url = esc_url_raw( $image_url, array( 'http', 'https' ) );

			$uploads_dir = wp_upload_dir();
			if ( false === strpos( $image_url, $uploads_dir['baseurl'] . '/' ) ) {
				// url points to a place outside of upload directory.
				return false;
			}

			$query = array(
				'post_type'  => 'attachment',
				'fields'     => 'ids',
				'meta_query' => array(
					array(
						'key'     => '_wp_attached_file',
						'value'   => basename( $image_url ),
						'compare' => 'LIKE',
					),
				)
			);

			$ids = get_posts( $query );
			if ( count( $ids ) === 0 ) {
				return false;
			}

			return wp_get_attachment_image_url( $ids[0], 'full' );
		}
	}
}