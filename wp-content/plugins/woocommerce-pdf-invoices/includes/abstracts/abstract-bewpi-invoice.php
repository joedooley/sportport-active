<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'BEWPI_Abstract_Invoice' ) ) {

	/**
	 * Makes the invoice.
	 * Class BEWPI_Invoice
	 */
	class BEWPI_Abstract_Invoice extends BEWPI_Abstract_Document {
		/**
		 * Type of invoice.
		 *
		 * @var string
		 */
		protected $type;

		/**
		 * Invoice number.
		 *
		 * @var int
		 */
		protected $number;

		/**
		 * MySQL invoice date.
		 *
		 * @var string
		 */
		protected $date = '0000-00-00 00:00:00';

		/**
		 * Year of invoice.
		 *
		 * @var string
		 */
		protected $year;

		/**
		 * Number of columns from products table.
		 *
		 * @deprecated outlining columns will be refactored.
		 * @var int
		 */
		public $columns_count;

		/**
		 * Colspan data to outline products table columns.
		 *
		 * @deprecated outlining columns will be refactored.
		 * @var array
		 */
		protected $colspan;

		/**
		 * Width of the description cell of the product table.
		 *
		 * @deprecated outlining columns will be refactored.
		 * @var string
		 */
		protected $desc_cell_width;

		/**
		 * BEWPI_Abstract_Invoice constructor.
		 *
		 * @param int $order_id WooCommerce Order ID.
		 */
		public function __construct( $order_id ) {
			parent::__construct();

			$this->full_path = self::exists( $order_id );
			if ( $this->full_path ) {
				$this->number    = get_post_meta( $this->order->id, '_bewpi_invoice_number', true );
				$this->date      = get_post_meta( $this->order->id, '_bewpi_invoice_date', true );
				$this->year      = date_i18n( 'Y', strtotime( $this->date ) );
				$this->filename  = basename( $this->full_path );
			}
		}

		/**
		 * Invoice number.
		 *
		 * @return int
		 */
		public function get_number() {
			return (int) $this->number;
		}

		/**
		 * Format invoice number with placeholders.
		 *
		 * @return string
		 */
		public function get_formatted_number() {
			// format number with the number of digits.
			$digitized_invoice_number = sprintf( '%0' . $this->template_options['bewpi_invoice_number_digits'] . 's', $this->number );
			$formatted_invoice_number = str_replace(
				array( '[prefix]', '[suffix]', '[number]', '[order-date]', '[order-number]', '[Y]', '[y]', '[m]' ),
				array(
					$this->template_options['bewpi_invoice_number_prefix'],
					$this->template_options['bewpi_invoice_number_suffix'],
					$digitized_invoice_number,
					apply_filters( 'bewpi_formatted_invoice_number_order_date', $this->get_formatted_order_date() ),
					$this->order->get_order_number(),
					$this->year,
					date_i18n( 'y', strtotime( $this->date ) ),
					date_i18n( 'm', strtotime( $this->date ) ),
				),
				$this->template_options['bewpi_invoice_number_format']
			);

			return apply_filters( 'bewpi_formatted_invoice_number', $formatted_invoice_number, $this->type );
		}

		/**
		 * Date format from user option or get default WordPress date format.
		 *
		 * @return string
		 */
		public function get_date_format() {
			$date_format = $this->template_options['bewpi_date_format'];
			if ( ! empty( $date_format ) ) {
				return (string) $date_format;
			}

			return (string) get_option( 'date_format' );
		}

		/**
		 * Format and localize (MySQL) invoice date.
		 *
		 * @return string
		 */
		public function get_formatted_invoice_date() {
			return date_i18n( $this->get_date_format(), strtotime( $this->date ) );
		}

		/**
		 * Order date formatted with user option format and localized.
		 *
		 * @return string
		 */
		public function get_formatted_order_date() {
			return date_i18n( $this->get_date_format(), strtotime( $this->order->order_date ) );
		}

		/**
		 * Delete invoice PDF files.
		 *
		 * @param int $from_number Invoice number where to start from.
		 */
		private function delete_pdf_invoices( $from_number = 0 ) {
			global $wpdb;

			if ( (bool) $this->template_options['bewpi_reset_counter_yearly'] ) {
				// get formatted numbers by year and greater then given invoice number.
				$files = $wpdb->get_col( $wpdb->prepare(
					"SELECT pm3.meta_value AS pdf_path FROM wp_postmeta pm1
						INNER JOIN wp_postmeta pm2 ON pm1.post_id = pm2.post_id
  						INNER JOIN wp_postmeta pm3 ON pm1.post_id = pm3.post_id
					WHERE (pm1.meta_key = '_bewpi_invoice_date' AND YEAR(pm1.meta_value) = %d)
      						AND (pm2.meta_key = '_bewpi_invoice_number' AND pm2.meta_value >= %d)
      						AND (pm3.meta_key = '_bewpi_invoice_pdf_path')",
					(int) date_i18n( 'Y', current_time( 'timestamp' ) ),
					$from_number
				) ); // db call ok; no-cache ok.
			} else {
				// get formatted numbers greater then given invoice number.
				$files = $wpdb->get_col( $wpdb->prepare(
					"SELECT pm2.meta_value AS pdf_path FROM wp_postmeta pm1
						INNER JOIN wp_postmeta pm2 ON pm1.post_id = pm2.post_id
					WHERE (pm1.meta_key = '_bewpi_invoice_number' AND pm1.meta_value >= %d)
      						AND (pm2.meta_key = '_bewpi_invoice_pdf_path')",
					$from_number
				) ); // db call ok; no-cache ok.
			}

			// delete pdf files.
			foreach ( $files as $pdf_path ) {
				parent::delete( BEWPI_INVOICES_DIR . $pdf_path );
			}
		}

		/**
		 * Delete invoice post meta information.
		 *
		 * @param int $from_number Invoice number from which to delete.
		 *
		 * @return false|int
		 */
		private function delete_invoice_meta( $from_number = 0 ) {
			global $wpdb;

			if ( (bool) $this->template_options['bewpi_reset_counter_yearly'] ) {
				// delete by year and greater then given invoice number.
				$query = $wpdb->prepare(
					"DELETE pm1, pm2, pm3 FROM $wpdb->postmeta pm1
  						INNER JOIN $wpdb->postmeta pm2 ON pm1.post_id = pm2.post_id
  						INNER JOIN $wpdb->postmeta pm3 ON pm1.post_id = pm3.post_id
					WHERE (pm1.meta_key = %s AND YEAR(pm1.meta_value) = %d)
      						AND (pm2.meta_key = %s AND pm2.meta_value >= %d)
      						AND (pm3.meta_key = %s)",
					'_bewpi_invoice_date',
					(int) date_i18n( 'Y', current_time( 'timestamp' ) ),
					'_bewpi_invoice_number',
					$from_number,
					'_bewpi_invoice_pdf_path'
				);
			} else {
				// delete by greater then given invoice number.
				$query = $wpdb->prepare(
					"DELETE pm1, pm2 FROM $wpdb->postmeta pm1
						INNER JOIN $wpdb->postmeta pm2 ON pm1.post_id = pm2.post_id
					WHERE (pm1.meta_key = %s AND pm1.meta_value >= %d)
							AND (pm2.meta_key = %s OR pm2.meta_key = %s)",
					'_bewpi_invoice_number',
					$from_number,
					'_bewpi_invoice_date',
					'_bewpi_invoice_pdf_path'
				);
			}

			return $wpdb->query( $query ); // db call ok; no-cache ok. WPCS: unprepared SQL OK.
		}

		/**
		 * Get next invoice number from db.
		 *
		 * @return int
		 */
		private function get_next_invoice_number() {
			// uses WooCommerce order numbers as invoice numbers?
			if ( 'woocommerce_order_number' === $this->template_options['bewpi_invoice_number_type'] ) {
				return $this->order->id;
			}

			// check if user did a counter reset.
			if ( $this->template_options['bewpi_reset_counter'] && (int) $this->template_options['bewpi_next_invoice_number'] > 0 ) {
				$next_number = (int) $this->template_options['bewpi_next_invoice_number'];
				$this->delete_pdf_invoices( $next_number );
				$this->delete_invoice_meta( $next_number );

				// unset reset counter checkbox.
				$this->template_options['bewpi_reset_counter'] = 0;
				update_option( 'bewpi_template_settings', $this->template_options );

				return $next_number;
			}

			$max_invoice_number = $this->get_max_invoice_number();
			$next_number        = $max_invoice_number + 1;

			// set next invoice number option.
			$this->template_options['bewpi_next_invoice_number'] = $next_number + 1;
			update_option( 'bewpi_template_settings', $this->template_options );

			return $next_number;
		}

		/**
		 * Return highest invoice number.
		 *
		 * @return int
		 */
		public function get_max_invoice_number() {
			global $wpdb;

			if ( (bool) $this->template_options['bewpi_reset_counter_yearly'] ) {
				// get by year.
				$query = $wpdb->prepare(
					"SELECT MAX(CAST(pm2.meta_value AS UNSIGNED)) AS last_invoice_number
					FROM $wpdb->postmeta pm1
						INNER JOIN $wpdb->postmeta pm2 ON pm1.post_id = pm2.post_id
					WHERE pm1.meta_key = %s AND YEAR(pm1.meta_value) = %d AND pm2.meta_key = %s",
					'_bewpi_invoice_date',
					(int) date_i18n( 'Y', current_time( 'timestamp' ) ),
					'_bewpi_invoice_number'
				);
			} else {
				// get all.
				$query = $wpdb->prepare(
					"SELECT MAX(CAST(pm2.meta_value AS UNSIGNED)) AS last_invoice_number
					FROM $wpdb->postmeta pm1
						INNER JOIN $wpdb->postmeta pm2 ON pm1.post_id = pm2.post_id
					WHERE pm2.meta_key = %s",
					'_bewpi_invoice_number'
				);
			}

			return intval( $wpdb->get_var( $query ) ); // db call ok; no-cache ok. WPCS: unprepared SQL OK.
		}

		/**
		 * Save invoice.
		 *
		 * @param string $destination pdf generation mode.
		 *
		 * @return string
		 */
		public function save( $destination = 'F' ) {
			do_action( 'bewpi_before_invoice_content', $this->order->id );

			if ( BEWPI_Invoice::exists( $this->order->id ) ) {
				// delete postmeta and PDF.
				self::delete( $this->order->id );
			}

			$this->date   = current_time( 'mysql' );
			$this->number = $this->get_next_invoice_number();
			$this->year   = date_i18n( 'Y', current_time( 'timestamp' ) );

			// yearly sub-folders.
			if ( (bool) $this->template_options['bewpi_reset_counter_yearly'] ) {
				$pdf_path = $this->year . '/' . $this->get_formatted_number() . '.pdf';
			} else {
				// one folder for all invoices.
				$pdf_path = $this->get_formatted_number() . '.pdf';
			}

			$this->full_path     = BEWPI_INVOICES_DIR . $pdf_path;
			$this->filename      = basename( $this->full_path );

			// update invoice data in db.
			update_post_meta( $this->order->id, '_bewpi_invoice_date', $this->date );
			update_post_meta( $this->order->id, '_bewpi_invoice_number', $this->number );
			update_post_meta( $this->order->id, '_bewpi_invoice_pdf_path', $pdf_path );

			do_action( 'bewpi_before_document_generation', array(
				'type'     => $this->type,
				'order_id' => $this->order->id,
			) );

			parent::generate( $destination, $this->order->is_paid() );

			do_action( 'bewpi_after_invoice_content', $this->order->id );

			return $this->full_path;
		}

		/**
		 * Update invoice.
		 *
		 * @param string $destination pdf generation mode.
		 *
		 * @return string $full_path Full path to PDF invoice file.
		 */
		public function update( $destination = 'F' ) {
			parent::delete( $this->full_path );
			parent::generate( $destination, $this->order->is_paid() );

			return $this->full_path;
		}

		/**
		 * View invoice.
		 */
		public function view() {
			if ( ! BEWPI_Invoice::exists( $this->order->id ) ) {
				wp_die( __( 'Invoice not found. First create invoice and try again.', 'woocommerce-pdf-invoices' ),
					'',
					array( 'response' => 200, 'back_link' => true )
				);
			}

			$this->update();
			parent::view();
		}

		/**
		 * Delete all invoice data from database and pdf file.
		 *
		 * @param int $order_id WooCommerce Order ID.
		 */
		public static function delete( $order_id ) {
			// remove pdf file.
			$full_path = BEWPI_INVOICES_DIR . get_post_meta( $order_id, '_bewpi_invoice_pdf_path', true );
			parent::delete( $full_path );

			// remove invoice postmeta from database.
			delete_post_meta( $order_id, '_bewpi_invoice_number' );
			delete_post_meta( $order_id, '_bewpi_invoice_date' );
			delete_post_meta( $order_id, '_bewpi_invoice_pdf_path' );

			// version 3.6+ not used anymore.
			delete_post_meta( $order_id, '_bewpi_formatted_invoice_number' );
			delete_post_meta( $order_id, '_bewpi_invoice_year' );

			do_action( 'bewpi_after_post_meta_deletion', $order_id );
		}

		/**
		 * Display company logo or name
		 */
		public function get_company_logo_html() {
			$logo_url = $this->template_options['bewpi_company_logo'];
			if ( ! empty( $logo_url ) ) {
				// mPDF' stablest method to display an image is to use their "Image data as a Variable" (https://mpdf.github.io/what-else-can-i-do/images.html) option.
				$src = apply_filters( 'bewpi_company_logo_url', 'var:company_logo' );
				printf( '<img class="company-logo" src="%s"/>', esc_attr( $src ) );
			} else {
				// show company name if company logo isn't uploaded.
				$company_name = $this->template_options['bewpi_company_name'];
				printf( '<h1 class="company-logo">%s</h1>', esc_html( $company_name ) );
			}
		}

		/**
		 * Get VAT number from WooCommerce EU VAT Number plugin
		 */
		public function display_vat_number() {
			$vat_number = get_post_meta( $this->order->id, '_vat_number', true );
			if ( ! empty( $vat_number ) ) {
				echo '<span>' . sprintf( __( 'VAT Number: %s', 'woocommerce-pdf-invoices' ), $vat_number ) . '</span>';
			}
		}

		/**
		 * Get PO Number from WooCommerce Purchase Order Gateway plugin
		 */
		public function display_purchase_order_number() {
			if ( isset( $this->order->payment_method ) && 'woocommerce_gateway_purchase_order' === $this->order->payment_method ) {
				$po_number = get_post_meta( $this->order->id, '_po_number', true );
				if ( ! empty( $po_number ) ) {
					echo '<span>' . sprintf( __( 'Purchase Order Number: %s', 'woocommerce-gateway-purchase-order' ), $po_number ) . '</span>';
				}
			}
		}

		/**
		 * Outline columns for within pdf template files.
		 *
		 * @param int $taxes_count number of tax classes.
		 * @deprecated
		 */
		public function outlining_columns_html( $taxes_count = 0 ) {
			$columns_count = $this->get_columns_count( $taxes_count );
			$colspan       = $this->get_colspan( $columns_count );
			?>
			<style>
				<?php
				// Create css for outlining the product cells.
				$righter_product_row_tds_css = "";
				for ( $td = $colspan['left'] + 1; $td <= $columns_count; $td++ ) {
					if ( $td !== $columns_count ) {
						$righter_product_row_tds_css .= "tr.product-row td:nth-child(" . $td . "),";
					} else {
						  $righter_product_row_tds_css .= "tr.product-row td:nth-child(" . $td . ")";
						  $righter_product_row_tds_css .= "{ width: " . ( 50 / $colspan['right'] ) . "%; }";
					}
				}
				echo $righter_product_row_tds_css;
				?>
				tr.product-row td:nth-child(1) {
					width: <?php echo $this->desc_cell_width; ?>;
				}
			</style>
			<?php
		}

		/**
		 * Number of columns.
		 *
		 * @param int $tax_count number of taxes.
		 *
		 * @deprecated
		 * @return int
		 */
		public function get_columns_count( $tax_count = 0 ) {
			$columns_count = 4;

			if ( $this->template_options['bewpi_show_sku'] ) {
				$columns_count ++;
			}

			if ( $this->template_options['bewpi_show_tax'] && wc_tax_enabled() && empty( $legacy_order ) ) {
				$columns_count += $tax_count;
			}

			return $columns_count;
		}

		/**
		 * Calculates colspan for table footer cells
		 *
		 * @deprecated
		 * @param int $columns_count number of columns.
		 * @return array
		 */
		public function get_colspan( $columns_count = 0 ) {
			$colspan                     = array();
			$number_of_left_half_columns = 3;
			$this->desc_cell_width       = '30%';

			// The product table will be split into 2 where on the right 5 columns are the max.
			if ( $columns_count <= 4 ) :
				$number_of_left_half_columns = 1;
				$this->desc_cell_width       = '48%';
			elseif ( $columns_count <= 6 ) :
				$number_of_left_half_columns = 2;
				$this->desc_cell_width       = '35.50%';
			endif;

			$colspan['left']        = $number_of_left_half_columns;
			$colspan['right']       = $columns_count - $number_of_left_half_columns;
			$colspan['right_left']  = round( ( $colspan['right'] / 2 ), 0, PHP_ROUND_HALF_DOWN );
			$colspan['right_right'] = round( ( $colspan['right'] / 2 ), 0, PHP_ROUND_HALF_UP );

			return $colspan;
		}

		/**
		 * Get the active template directory.
		 *
		 * @return string
		 */
		protected function get_template_dir() {
			$template_name = $this->template_options['bewpi_template_name'];

			// check if a custom template exists.
			$custom_template_dir = BEWPI_CUSTOM_TEMPLATES_INVOICES_DIR . $this->type . '/' . $template_name . '/';
			if ( file_exists( $custom_template_dir ) ) {
				return $custom_template_dir;
			}

			$template_dir = BEWPI_TEMPLATES_DIR . 'invoices/' . $this->type . '/' . $template_name . '/';
			if ( file_exists( $template_dir ) ) {
				return $template_dir;
			}
		}

		public function left_footer_column_html() {
			$left_footer_column_text = $this->template_options['bewpi_left_footer_column'];
			if ( ! empty( $left_footer_column_text ) ) {
				echo '<p>' . nl2br( $this->replace_placeholders( $left_footer_column_text ) ) . '</p>';
			}
		}

		public function right_footer_column_html() {
			$right_footer_column_text = $this->template_options['bewpi_right_footer_column'];
			if ( ! empty( $right_footer_column_text ) ) {
				echo '<p>' . nl2br( $this->replace_placeholders( $right_footer_column_text ) ) . '</p>';
			} else {
				echo '<p>' . sprintf( __( '%s of %s', 'woocommerce-pdf-invoices' ), '{PAGENO}', '{nbpg}' ) . '</p>';
			}
		}

		private function replace_placeholders( $str ) {
			$placeholders = apply_filters( 'bewpi_placeholders', array(
				'[payment_method]'  => $this->order->payment_method_title,
				'[shipping_method]' => $this->order->get_shipping_method(),
			), $this->order->id );

			foreach ( $placeholders as $placeholder => $value ) {
				$str = str_replace( $placeholder, $value, $str );
			}

			return $str;
		}

		/**
		 * Checks if invoice needs to have a zero rated VAT.
		 *
		 * @return bool
		 */
		public function display_zero_rated_vat() {
			$is_vat_valid = get_post_meta( $this->order->id, '_vat_number_is_valid', true );
			if ( ! $is_vat_valid ) {
				return false;
			}

			$is_tax_removed = count( $this->order->get_tax_totals() ) === 0;
			if ( ! $is_tax_removed ) {
				return false;
			}

			return true;
		}

		/**
		 * Check if order has only virtual products.
		 *
		 * @return bool
		 * @since 2.5.3
		 */
		protected function has_only_virtual_products() {
			foreach ( $this->order->get_items( 'line_item' ) as $item ) {
				$product = $this->order->get_product_from_item( $item );
				if ( ! $product || ! $product->is_virtual() ) {
					return false;
				}
			}

			return true;
		}

		/**
		 * Check if invoice exists.
		 *
		 * @param int $order_id WooCommerce Order ID.
		 *
		 * @return bool|string false when no pdf invoice exists or full path when exists.
		 */
		public static function exists( $order_id ) {
			// pdf data exists in database?
			$pdf_path = get_post_meta( $order_id, '_bewpi_invoice_pdf_path', true );
			if ( ! $pdf_path ) {
				return false;
			}

			return parent::exists( BEWPI_INVOICES_DIR . $pdf_path );
		}
	}
}
