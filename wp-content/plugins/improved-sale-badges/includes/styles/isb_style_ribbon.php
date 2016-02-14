<div class="isb_sale_badge <?php echo $isb_class; ?>" data-id="<?php echo $isb_price['id']; ?>">
	<svg xmlns="http://www.w3.org/2000/svg" xml:space="preserve" width="0" height="60" style="shape-rendering:geometricPrecision; text-rendering:geometricPrecision; image-rendering:optimizeQuality; fill-rule:evenodd; clip-rule:evenodd" viewBox="0 0 60 60" xmlns:xlink="http://www.w3.org/1999/xlink">
		<g>
		<?php
			if ( $isb_curr_set['position'] == 'isb_right' ) {
		?>
			<path class="<?php echo $isb_curr_set['color']; ?>" d="M60 60l-60 -60 37.7735 0 7.0024 7.0024 0 -7.0024 15.2241 0 0 15.2241 -7.0024 0 7.0024 7.0024 0 37.7735zm-9.0228 -57.1402l2.8215 0 0 2.1664 -0.5444 4.3484 -1.7437 0 -0.5334 -4.3484 0 -2.1664zm0.0974 7.1765l2.6334 0 0 2.328 -2.6334 0 0 -2.328z"/>
			<path fill="#FFFFFF" fill-opacity="0.1" d="M1.094 0l3.1045 0 55.8015 55.8015 0 3.1045 -58.906 -58.906zm4.6568 0l3.1045 0 51.1447 51.1447 0 3.1045 -54.2492 -54.2492zm4.6568 0l3.1045 0 46.4879 46.4879 0 3.1045 -49.5924 -49.5924zm4.6568 0l3.1045 0 41.8311 41.8311 0 3.1045 -44.9356 -44.9356zm4.6568 0l3.1045 0 37.1743 37.1743 0 3.1045 -40.2788 -40.2788zm4.6567 0l3.1046 0 32.5175 32.5175 0 3.1046 -35.6221 -35.6221zm4.6569 0l3.1045 0 27.8607 27.8607 0 3.1046 -30.9652 -30.9653zm4.6567 0l3.1046 0 23.2039 23.2039 0 3.1046 -26.3085 -26.3085z"/>
			<g transform="rotate(45, 33, 33)">
			<text text-anchor="middle" transform="scale(0.99)" x="31" y="31"  class="isb_sale_diff_shadow"><?php echo strip_tags( woocommerce_price( $isb_price['difference'] ) ); ?> <?php _e('OFF', 'isbwoo'); ?></text>
			</g>
			<g transform="rotate(45, 33, 33)">
			<text text-anchor="middle" transform="scale(0.99)" x="30" y="30"  class="isb_sale_diff"><?php echo strip_tags( woocommerce_price( $isb_price['difference'] ) ); ?> <?php _e('OFF', 'isbwoo'); ?></text>
			</g>
			<g transform="rotate(45, 40, 40)">
			<text text-anchor="middle" transform="scale(0.99)" x="27" y="27" class="isb_sale_percentage_shadow">-<?php echo $isb_price['percentage']; ?> %</text>
			</g>
			<g transform="rotate(45, 40, 40)">
			<text text-anchor="middle" transform="scale(0.99)" x="26" y="26" class="isb_sale_percentage">-<?php echo $isb_price['percentage']; ?> %</text>
			</g>
			<polygon fill="#000000" fill-opacity="0.2" points="44.7759,7.0024 52.9976,15.2241 55.5566,15.2241 44.7759,4.4434"/>
		<?php
			}
			else {
		?>
			<path class="<?php echo $isb_curr_set['color']; ?>" d="M-0 60l60 -60 -37.7735 0 -7.0024 7.0024 0 -7.0024 -15.2241 0 0 15.2241 7.0024 0 -7.0024 7.0024 0 37.7735zm9.0228 -57.1402l-2.8215 0 0 2.1664 0.5444 4.3484 1.7437 0 0.5334 -4.3484 0 -2.1664zm-0.0974 7.1765l-2.6334 0 0 2.328 2.6334 0 0 -2.328z"/>
			<path fill="#FFFFFF" fill-opacity="0.1" d="M58.906 0l-3.1045 0 -55.8015 55.8015 0 3.1045 58.906 -58.906zm-4.6568 0l-3.1045 0 -51.1447 51.1447 0 3.1045 54.2492 -54.2492zm-4.6568 0l-3.1045 0 -46.4879 46.4879 0 3.1045 49.5924 -49.5924zm-4.6568 0l-3.1045 0 -41.8311 41.8311 0 3.1045 44.9356 -44.9356zm-4.6568 0l-3.1045 0 -37.1743 37.1743 0 3.1045 40.2788 -40.2788zm-4.6567 0l-3.1046 0 -32.5175 32.5175 0 3.1046 35.6221 -35.6221zm-4.6569 0l-3.1045 0 -27.8607 27.8607 0 3.1046 30.9652 -30.9653zm-4.6567 0l-3.1046 0 -23.2039 23.2039 0 3.1046 26.3085 -26.3085z"/>
			<g transform="rotate(-45, 0, 0)">
			<text text-anchor="middle" transform="scale(0.99)" x="0" y="39"  class="isb_sale_diff_shadow"><?php echo strip_tags( woocommerce_price( $isb_price['difference'] ) ); ?> <?php _e('OFF', 'isbwoo'); ?></text>
			</g>
			<g transform="rotate(-45, 0, 0)">
			<text text-anchor="middle" transform="scale(0.99)" x="0" y="38"  class="isb_sale_diff"><?php echo strip_tags( woocommerce_price( $isb_price['difference'] ) ); ?> <?php _e('OFF', 'isbwoo'); ?></text>
			</g>
			<g transform="rotate(-45, 0, 0)">
			<text text-anchor="middle" transform="scale(0.99)" x="0" y="30" class="isb_sale_percentage_shadow">-<?php echo $isb_price['percentage']; ?> %</text>
			</g>
			<g transform="rotate(-45, 0, 0)">
			<text text-anchor="middle" transform="scale(0.99)" x="0" y="29" class="isb_sale_percentage">-<?php echo $isb_price['percentage']; ?> %</text>
			</g>
			<polygon fill="#000000" fill-opacity="0.2" points="15.2241,7.0024 7.0024,15.2241 4.4434,15.2241 15.2241,4.4434 "/>
		<?php
			}
		?>
		</g>
	</svg>
<?php
	if ( isset($isb_price['time']) ) {
?>
	<div class="isb_scheduled_sale isb_scheduled_<?php echo $isb_price['time_mode']; ?> <?php echo $isb_curr_set['color']; ?>">
		<span class="isb_scheduled_text">
			<?php
				if ( $isb_price['time_mode'] == 'start' ) {
					_e('Starts in', 'isbwoo');
				}
				else {
					_e('Ends in', 'isbwoo');
				}
			?> 
		</span>
		<span class="isb_scheduled_time isb_scheduled_compact">
			<?php echo $isb_price['time']; ?>
		</span>
	</div>
<?php
	}
?>
</div>