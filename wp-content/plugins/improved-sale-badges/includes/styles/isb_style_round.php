<div class="isb_sale_badge <?php echo $isb_class; ?>" data-id="<?php echo $isb_price['id']; ?>">
	<svg xmlns="http://www.w3.org/2000/svg" xml:space="preserve" width="60" height="60" style="shape-rendering:geometricPrecision; text-rendering:geometricPrecision; image-rendering:optimizeQuality; fill-rule:evenodd; clip-rule:evenodd" viewBox="0 0 60 60" xmlns:xlink="http://www.w3.org/1999/xlink">
		<g>
			<path class="<?php echo $isb_curr_set['color']; ?>" d="M45.6072 50.4603l5.9557 -10.9207 5.9556 -10.9211c3.3086,-6.0672 3.3088,-13.0122 0,-19.0791 -3.3087,-6.067 -8.99,-9.5394 -15.6072,-9.5394l-11.9112 0 -11.9114 0c-6.6171,0 -12.2984,3.4724 -15.607,9.5394 -3.3088,6.0671 -3.3088,13.0121 -0.0002,19.0791l5.9558 10.9211 5.9556 10.9207c3.3085,6.0669 8.9897,9.5397 15.607,9.5397 6.6174,0 12.2987,-3.4726 15.6073,-9.5397z"/>
			<path fill="#FFFFFF" fill-opacity="0.2" d="M45.6072 50.4603l5.9557 -10.9207 5.9556 -10.9211c3.3086,-6.0672 3.3088,-13.0122 0,-19.0791 -1.3601,-2.4939 -3.1213,-4.5489 -5.1834,-6.105 -17.9885,10.6546 -31.4482,28.1512 -36.7851,48.9051 3.3934,4.8993 8.5495,7.6605 14.4499,7.6605 6.6174,0 12.2987,-3.4726 15.6073,-9.5397z"/>
		</g>
	</svg>
	<div class="isb_sale_percentage">
		<span class="isb_percentage">
			<?php echo $isb_price['percentage']; ?> 
		</span>
		<span class="isb_percentage_text">
			<?php _e('%', 'isbwoo'); ?>
		</span>
	</div>
	<div class="isb_sale_text"><?php _e('OFF', 'isbwoo'); ?></div>
<?php
	if ( isset($isb_price['time']) ) {
?>
	<div class="isb_scheduled_sale isb_scheduled_<?php echo $isb_price['time_mode']; ?> <?php echo $isb_curr_set['color']; ?>">
		<span class="isb_scheduled_text">
			<?php
				if ( $isb_price['time_mode'] == 'start' ) {
					_e('Starts', 'isbwoo');
				}
				else {
					_e('Ends', 'isbwoo');
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