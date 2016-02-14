<div class="isb_sale_badge <?php echo $isb_class; ?>">
	<div class="isb_sale_percentage">
		<span class="isb_percentage">
			<?php echo $isb_price['percentage']; ?> 
		</span>
		<span class="isb_percentage_text">
			<?php _e('%', 'isbwoo'); ?>
		</span>
	</div>
	<div class="isb_sale_text"><?php _e('DISCOUNT', 'isbwoo'); ?></div>
	<div class="isb_money_saved">
		<span class="isb_saved_text">
			<?php
				if ( $isb_price['type'] == 'simple' || is_singular('product') ) {
					_e('Save', 'isbwoo');
				}
				else {
					_e('Up to', 'isbwoo');
				}
			?> 
		</span>
		<span class="isb_saved">
			<?php echo wc_price( $isb_price['difference'] ); ?>
		</span>
	</div>
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