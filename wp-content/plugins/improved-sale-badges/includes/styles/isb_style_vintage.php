<div class="isb_sale_badge <?php echo $isb_class; ?>" data-id="<?php echo $isb_price['id']; ?>">
	<div class="isb_sale_text"><?php _e('SALE', 'isbwoo'); ?></div>
	<div class="isb_sale_percentage isb_color">
		<span class="isb_percentage">
			<?php echo $isb_price['percentage']; ?> 
		</span>
		<span class="isb_percentage_text">
			<?php _e('%', 'isbwoo'); ?>
		</span>
	</div>
	<div class="isb_sale_text"><?php _e('PRICE OFF', 'isbwoo'); ?></div>
	<div class="isb_money_saved">
		<span class="isb_saved_old">
			<?php echo wc_price( $isb_price['regular'] ); ?>
		</span>
		<span class="isb_saved isb_color">
			<?php echo wc_price( $isb_price['sale'] ); ?>
		</span>
	</div>
<?php
	if ( isset($isb_price['time']) ) {
?>
	<div class="isb_scheduled_sale isb_scheduled_<?php echo $isb_price['time_mode']; ?>">
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