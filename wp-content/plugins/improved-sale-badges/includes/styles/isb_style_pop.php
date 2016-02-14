<div class="isb_sale_badge <?php echo $isb_class; ?>" data-id="<?php echo $isb_price['id']; ?>">
	<svg xmlns="http://www.w3.org/2000/svg" xml:space="preserve" width="60" height="60" style="shape-rendering:geometricPrecision; text-rendering:geometricPrecision; image-rendering:optimizeQuality; fill-rule:evenodd; clip-rule:evenodd" viewBox="0 0 60 60" xmlns:xlink="http://www.w3.org/1999/xlink">
		<g>
			<polygon class="<?php echo $isb_curr_set['color']; ?>" points="52.4171,10.0358 53.1348,16.901 58.6888,20.9586 56.5673,27.5237 60,33.502 55.4063,38.6319 56.1239,45.4972 49.8522,48.305 47.7307,54.8701 40.8654,54.8701 36.2717,60 30,57.1924 23.7283,60 19.1346,54.8701 12.2693,54.8701 10.1478,48.305 3.8761,45.4972 4.5937,38.6319 -0,33.502 3.4327,27.5237 1.3112,20.9586 6.8652,16.901 7.5829,10.0358 14.298,8.6006 17.7307,2.6224 24.4459,4.0576 30,0 35.5541,4.0576 42.2693,2.6224 45.702,8.6006 "/>
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