<?php

/* SINGLE */

	global $product, $isb_set;

	$isb_sale_flash = false;

	if ( $isb_set['single'] == 'yes' ) {

		$curr_badge = get_post_meta(get_the_ID(), '_isb_settings');

	}

	if ( isset( $curr_badge[0]['special'] ) && $curr_badge[0]['special'] !== '' ) {

		$isb_curr_set['special'] = ( isset($curr_badge[0]['special']) && $curr_badge[0]['special'] !== '' ? $curr_badge[0]['special'] : $isb_set['special'] );
		$isb_curr_set['color'] = ( isset($curr_badge[0]['color']) && $curr_badge[0]['color'] !== '' ? $curr_badge[0]['color'] : $isb_set['color'] );
		$isb_curr_set['position'] = 'isb_left';

		$isb_class = implode(' ', $isb_curr_set);

		$isb_curr_set['special_text'] = ( isset($curr_badge[0]['special_text']) && $curr_badge[0]['special_text'] !== '' ? $curr_badge[0]['special_text'] : __( 'Text', 'isbwoo' ) );

		$include = WC_Improved_Sale_Badges::isb_get_path() . 'includes/specials/' . $isb_curr_set['special'] . '.php';
		include($include);

	}
	else {

		if ( $product->is_type( 'simple' ) || $product->is_type('external') ) {

			$sale_price_dates_from = get_post_meta( get_the_ID(), '_sale_price_dates_from', true );
			$sale_price_dates_to = get_post_meta( get_the_ID(), '_sale_price_dates_to', true );

			if ( !empty( $sale_price_dates_from ) && !empty( $sale_price_dates_to ) ) {
				$current_time = current_time( 'mysql', $gmt = 0 );
				$newer_date = strtotime( $current_time );

				$since = $newer_date - $sale_price_dates_from;

				if ( 0 > $since ) {
					$isb_price['time'] = $sale_price_dates_from;
					$isb_price['time_mode'] = 'start';
				}

				if ( !isset($isb_price['time']) ) {
					$since = $newer_date - $sale_price_dates_to;
					if ( 0 > $since ) {
						$isb_price['time'] = $sale_price_dates_to;
						$isb_price['time_mode'] = 'end';
					}
				}
			}


			if ( $product->get_price() > 0 && ( $product->is_on_sale() || isset($isb_price['time']) ) !== false ) {

				$isb_price['type'] = 'simple';

				$isb_price['id'] = get_the_ID();

				$isb_price['regular'] = floatval( $product->get_regular_price() );

				$isb_price['sale'] = floatval( $product->get_sale_price() );

				$isb_price['difference'] = $isb_price['regular'] - $isb_price['sale'];

				$isb_price['percentage'] = round( ( $isb_price['regular'] - $isb_price['sale'] ) * 100 / $isb_price['regular'] );


				if ( $isb_set['single'] == 'yes' ) {

					if ( !isset( $curr_badge ) ) {
						$curr_badge = array();
					}

					if ( empty($curr_badge) ) {
						$isb_curr_set = $isb_set;
						if ( is_array($isb_set) ) {
							$isb_class = $isb_curr_set['style'] . ' ' . $isb_curr_set['color'] . ' isb_left';
						}
						else {
							$isb_class = 'isb_style_basic isb_red isb_left';
						}
					}
					else {
						$isb_curr_set['style'] = ( isset($curr_badge[0]['style']) && $curr_badge[0]['style'] !== '' ? $curr_badge[0]['style'] : $isb_set['style'] );
						$isb_curr_set['color'] = ( isset($curr_badge[0]['color']) && $curr_badge[0]['color'] !== '' ? $curr_badge[0]['color'] : $isb_set['color'] );
						$isb_curr_set['position'] = 'isb_left';
						$isb_curr_set['special'] = ( isset($curr_badge[0]['special']) && $curr_badge[0]['special'] !== '' ? $curr_badge[0]['special'] : $isb_set['special'] );
						$isb_curr_set['special_text'] = ( isset($curr_badge[0]['special_text']) && $curr_badge[0]['special_text'] !== '' ? $curr_badge[0]['special_text'] : $isb_set['special_text'] );

						$isb_class = $isb_curr_set['style'] . ' ' . $isb_curr_set['color'] . ' ' . $isb_curr_set['position'];
					}
				}
				else {
					$isb_curr_set = $isb_set;
					if ( is_array($isb_curr_set) ) {
						$isb_class = $isb_curr_set['style'] . ' ' . $isb_curr_set['color'] . ' ' . $isb_curr_set['position'];
					}
					else {
						$isb_class = 'isb_style_basic isb_red isb_left';
					}
				}

				$include = WC_Improved_Sale_Badges::isb_get_path() . 'includes/styles/' . $isb_curr_set['style'] . '.php';
				include($include);


			}

		}
		else if ( $product->is_type( 'variable' ) ) {

			$isb_variations = $product->get_available_variations();
			$isb_check = 0;
			$isb_check_time = 0;

			foreach( $isb_variations as $var ) {

				$curr_product[$var['variation_id']] = new WC_Product_Variation( $var['variation_id'] );

				$sale_price_dates_from = get_post_meta( $var['variation_id'], '_sale_price_dates_from', true );
				$sale_price_dates_to = get_post_meta( $var['variation_id'], '_sale_price_dates_to', true );

				if ( !empty( $sale_price_dates_from ) && !empty( $sale_price_dates_to ) ) {
					$current_time = current_time( 'mysql', $gmt = 0 );
					$newer_date = strtotime( $current_time );

					$since = $newer_date - $sale_price_dates_from;

					if ( 0 > $since ) {
						$check_time = $sale_price_dates_from;
						$check_time_mode = 'start';
					}

					if ( !isset($check_time) ) {
						$since = $newer_date - $sale_price_dates_to;
						if ( 0 > $since ) {
							$check_time = $sale_price_dates_to;
							$check_time_mode = 'end';
						}
					}

					$isb_price['time'] = $check_time;
					$isb_price['time_mode'] = $check_time_mode;
				}

				if ( $curr_product[$var['variation_id']]->is_on_sale() ) {

					$isb_price['type'] = 'variable';

					$isb_price['id'] = $var['variation_id'];

					$isb_price['regular'] = floatval( $curr_product[$var['variation_id']]->get_regular_price() );

					$isb_price['sale'] = floatval( $curr_product[$var['variation_id']]->get_sale_price() );

					$isb_price['difference'] = $isb_price['regular'] - $isb_price['sale'];

					$isb_price['percentage'] = round( ( $isb_price['regular'] - $isb_price['sale'] ) * 100 / $isb_price['regular'] );

					if ( $isb_set['single'] == 'yes' ) {

						if ( !isset( $curr_badge ) ) {
							$curr_badge = array();
						}

						if ( empty($curr_badge) ) {
							$isb_curr_set = $isb_set;
							if ( is_array($isb_set) ) {
								$isb_class = $isb_curr_set['style'] . ' ' . $isb_curr_set['color'] . ' isb_left';
							}
							else {
								$isb_class = 'isb_style_basic isb_red isb_right';
							}
						}
						else {
							$isb_curr_set['style'] = ( isset($curr_badge[0]['style']) && $curr_badge[0]['style'] !== '' ? $curr_badge[0]['style'] : $isb_set['style'] );
							$isb_curr_set['color'] = ( isset($curr_badge[0]['color']) && $curr_badge[0]['color'] !== '' ? $curr_badge[0]['color'] : $isb_set['color'] );
							$isb_curr_set['position'] = 'isb_left';
							$isb_curr_set['special'] = ( isset($curr_badge[0]['special']) && $curr_badge[0]['special'] !== '' ? $curr_badge[0]['special'] : $isb_set['special'] );
							$isb_curr_set['special_text'] = ( isset($curr_badge[0]['special_text']) && $curr_badge[0]['special_text'] !== '' ? $curr_badge[0]['special_text'] : $isb_set['special_text'] );

							$isb_class = $isb_curr_set['style'] . ' ' . $isb_curr_set['color'] . ' ' . $isb_curr_set['position'];
						}
					}
					else {
						$isb_curr_set = $isb_set;
						if ( is_array($isb_curr_set) ) {
							$isb_class = $isb_curr_set['style'] . ' ' . $isb_curr_set['color'] . ' ' . $isb_curr_set['position'];
						}
						else {
							$isb_class = 'isb_style_basic isb_red isb_right';
						}
					}

					$isb_class = $isb_class . ' isb_variable';

					$include = WC_Improved_Sale_Badges::isb_get_path() . 'includes/styles/' . $isb_curr_set['style'] . '.php';
					include($include);

				}

			}

		}

	}

?>