<?php
/**
 * BEWPI_Packing_Slip class.
 *
 * @author      Bas Elbers
 * @category    Class
 * @package     BE_WooCommerce_PDF_Invoices/Class
 * @version     0.0.1
 */

defined( 'ABSPATH' ) or exit;

if ( ! class_exists( 'BEWPI_Packing_Slip' ) ) {

	/**
	 * Class BEWPI_Packing_Slip.
	 */
	class BEWPI_Packing_Slip extends BEWPI_Abstract_Document {
		/**
		 * BEWPI_Packing_Slip constructor.
		 *
		 * @param int $order_id WooCommerce Order ID.
		 */
		public function __construct( $order_id ) {
			$this->order        = wc_get_order( $order_id );
			$this->type         = 'packing-slip/simple';
			$this->filename     = apply_filters( 'bewpi_pdf_packing_slip_filename', sprintf( 'packing-slip-%s.pdf', BEWPI_WC_Order_Compatibility::get_id( $this->order ) ), $this );
			BEWPI()->templater()->set_packing_slip( $this );
			parent::__construct();
		}

		/**
		 * Initialize packing slips hooks.
		 */
		public static function init_hooks() {
			add_action( 'woocommerce_admin_order_actions_end', array( __CLASS__, 'add_packing_slip_pdf' ) );
		}

		/**
		 * Add packing slip link to 'Shop Order' page.
		 *
		 * @param WC_Order $order WooCommerce order object.
		 */
		public static function add_packing_slip_pdf( $order ) {
			$template_options = get_option( 'bewpi_template_settings' );
			if ( $template_options['bewpi_disable_packing_slips'] ) {
				return;
			}

			$order_id = BEWPI_WC_Order_Compatibility::get_id( $order );

			// View Packing Slip.
			$action = 'view_packing_slip';
			$url = wp_nonce_url( add_query_arg( array(
				'post' => $order_id,
				'action' => 'edit',
				'bewpi_action' => $action,
			), admin_url( 'post.php' ) ), $action, 'nonce' );

			$url = apply_filters( 'bewpi_pdf_packing_slip_url', $url, $order_id, $action );

			printf( '<a href="%1$s" title="%2$s" class="button shop-order-action packing-slip wpi" target="_blank">%2$s</a>', $url, __( 'View packing slip', 'woocommerce-pdf-invoices' ) );
		}
	}
}
