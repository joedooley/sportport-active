<div class="isb_sale_badge <?php echo $isb_class; ?>" data-id="<?php echo $isb_price['id']; ?>">
	<svg xmlns="http://www.w3.org/2000/svg" xml:space="preserve" width="60" height="65" style="shape-rendering:geometricPrecision; text-rendering:geometricPrecision; image-rendering:optimizeQuality; fill-rule:evenodd; clip-rule:evenodd" viewBox="0 0 60 65" xmlns:xlink="http://www.w3.org/1999/xlink">
		<g>
		<?php
			if ( $isb_set['position'] == 'isb_right' ) {
		?>
			<polygon class="isb_shadow" points="-0,5 21.982,60.6841 60,65 60,14.8506 "/>
			<polygon class="<?php echo $isb_set['color']; ?>" points="60,36.0311 16.9053,42.8237 21.982,55.6841 60,60 "/>
			<polygon class="isb_ui" points="16.9053,42.8237 -0,0 60,9.8506 60,36.0311 "/>
		<?php
			}
			else {
		?>
			<polygon class="isb_shadow" points="60,5 38.018,60.6841 -0,65 -0,14.8506 "/>
			<polygon class="<?php echo $isb_set['color']; ?>" points="-0,36.0311 43.0947,42.8237 38.018,55.6841 -0,60 "/>
			<polygon class="isb_ui" points="43.0947,42.8237 60,0 -0,9.8506 -0,36.0311 "/>
		<?php
			}
		?>
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