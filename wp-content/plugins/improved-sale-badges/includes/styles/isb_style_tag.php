<div class="isb_sale_badge <?php echo $isb_class; ?>" data-id="<?php echo $isb_price['id']; ?>">
	<svg xmlns="http://www.w3.org/2000/svg" xml:space="preserve" width="60" height="60" style="shape-rendering:geometricPrecision; text-rendering:geometricPrecision; image-rendering:optimizeQuality; fill-rule:evenodd; clip-rule:evenodd" viewBox="0 0 60 60" xmlns:xlink="http://www.w3.org/1999/xlink">
		<g>
			<path fill="#000000" fill-opacity="0.45" d="M45.4386 15.3182l-0.0092 41.5326c-0.0004,1.7349 -1.4156,3.1495 -3.1506,3.1491l-25.1987 -0.0056c-1.735,-0.0004 -3.1496,-1.4158 -3.149,-3.1507l0.0091 -41.5325c0.0002,-0.9162 0.3476,-1.7047 1.0237,-2.3232l12.602 -11.5296c1.2079,-1.1051 3.0458,-1.1047 4.2532,0.001l12.5968 11.5352c0.6758,0.6188 1.0229,1.4074 1.0227,2.3237zm-15.7468 -10.6089c1.3045,0.0003 2.3621,1.0584 2.3618,2.3629 -0.0003,1.3045 -1.0584,2.3622 -2.3629,2.3619 -1.3045,-0.0003 -2.3621,-1.0584 -2.3618,-2.3629 0.0002,-1.3046 1.0584,-2.3622 2.3629,-2.3619z" />
			<path class="<?php echo $isb_curr_set['color']; ?>" d="M46.0688 14.6884l-0.0093 41.5326c-0.0004,1.7349 -1.4156,3.1495 -3.1505,3.1491l-25.1988 -0.0056c-1.735,-0.0004 -3.1496,-1.4158 -3.149,-3.1507l0.0091 -41.5325c0.0002,-0.9163 0.3476,-1.7048 1.0237,-2.3232l12.602 -11.5296c1.2079,-1.1052 3.0458,-1.1047 4.2532,0.001l12.5968 11.5351c0.6759,0.6188 1.023,1.4075 1.0228,2.3238zm-15.7469 -10.6089c1.3045,0.0003 2.3621,1.0584 2.3618,2.3629 -0.0003,1.3045 -1.0584,2.3621 -2.3629,2.3618 -1.3045,-0.0003 -2.3621,-1.0584 -2.3618,-2.3629 0.0003,-1.3045 1.0584,-2.3621 2.3629,-2.3618z"/>
			<path fill="#000000" fill-opacity="0.3" d="M16.83 38.0625l26.34 0c0.1818,0 0.33,0.1482 0.33,0.33l0 0.34c0,0.1818 -0.1482,0.33 -0.33,0.33l-26.34 0c-0.1818,0 -0.33,-0.1482 -0.33,-0.33l0 -0.34c0,-0.1818 0.1482,-0.33 0.33,-0.33z"/>
		</g>
	</svg>
	<div class="isb_sale_text"><?php _e('ON SALE', 'isbwoo'); ?></div>
	<div class="isb_sale_percentage">
		<span class="isb_percentage">
			<?php echo $isb_price['percentage']; ?> 
		</span>
		<span class="isb_percentage_text">
			<?php _e('%', 'isbwoo'); ?>
		</span>
	</div>
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