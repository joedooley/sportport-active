<?php
/**
 * Variable Product Add to Cart
 */
global $woocommerce, $product, $post;

$variation_params = woocommerce_swatches_get_variation_form_args();

do_action( 'woocommerce_before_add_to_cart_form' );
?>
<form 
      class="variations_form cart swatches" 
      method="post" 
      enctype='multipart/form-data' 
      data-product_id="<?php echo $post->ID; ?>" 
      data-product_variations="<?php echo esc_attr( json_encode( $variation_params['available_variations'] ) ) ?>"
      data-product_attributes="<?php echo esc_attr( json_encode( $variation_params['attributes_renamed'] ) ); ?>"
      data-product_variations_flat="<?php echo esc_attr( json_encode( $variation_params['available_variations_flat'] ) ); ?>"
      data-variations_map="<?php echo esc_attr( json_encode( $variation_params['variations_map'] ) ); ?>"
      >

	<?php if ( !empty( $variation_params['available_variations'] ) ) : ?>

		<div class="variation_form_section">
			<?php
			$woocommerce_variation_control_output = new WC_Swatch_Picker( $product->id, $attributes, $variation_params['selected_attributes'] );
			$woocommerce_variation_control_output->picker();
			?>

			<div class="clear"></div><a id="variations_clear" href="#reset" style="display:none;"><?php _e( 'Reset selection', 'woocommerce' ); ?></a>

		</div>

		<?php do_action( 'woocommerce_before_add_to_cart_button' ); ?>

		<div class="single_variation_wrap" style="display:none;">
			<?php
				/**
				 * woocommerce_before_single_variation Hook
				 */
				do_action( 'woocommerce_before_single_variation' );

				/**
				 * woocommerce_single_variation hook. Used to output the cart button and placeholder for variation data.
				 * @since 2.4.0
				 * @hooked woocommerce_single_variation - 10 Empty div for variation data.
				 * @hooked woocommerce_single_variation_add_to_cart_button - 20 Qty and cart button.
				 */
				do_action( 'woocommerce_single_variation' );

				/**
				 * woocommerce_after_single_variation Hook
				 */
				do_action( 'woocommerce_after_single_variation' );
			?>
		</div>
		<div>

		</div>

		<?php do_action( 'woocommerce_after_add_to_cart_button' ); ?>
	<?php else : ?>

		<p class="stock out-of-stock"><?php _e( 'This product is currently out of stock and unavailable.', 'woocommerce' ); ?></p>

	<?php endif; ?>
</form>

<?php do_action( 'woocommerce_after_add_to_cart_form' ); ?>
