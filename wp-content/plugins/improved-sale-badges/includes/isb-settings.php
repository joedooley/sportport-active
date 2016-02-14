<?php

	/*
	 * Improved Sale Badges Settings
	 */
	class WC_Isb_Settings {

		public static $isb_style;
		public static $isb_style_special;
		public static $isb_color;
		public static $isb_position;

		public static function init() {
			self::$isb_style = array(
				'isb_style_arrow' => __( 'Arrow Down', 'isbwoo') . ' CSS',
				'isb_style_arrow_alt' => __( 'Arrow Down Alternative', 'isbwoo') . ' CSS',
				'isb_style_basic' => __( 'Aliexpress Style', 'isbwoo') . ' CSS',
				'isb_style_basic_alt' => __( 'Aliexpress Style Alternative', 'isbwoo') . ' CSS',
				'isb_style_inline' => __( 'Inline', 'isbwoo') . ' CSS',
				'isb_style_plain' => __( 'Plain', 'isbwoo') . ' CSS',
				'isb_style_pop' => __( 'Pop', 'isbwoo') . ' SVG',
				'isb_style_pop_round' => __( 'Pop Round', 'isbwoo') . ' SVG',
				'isb_style_fresh' => __( 'Fresh', 'isbwoo') . ' SVG',
				'isb_style_round' => __( 'Round Triangle', 'isbwoo') . ' SVG',
				'isb_style_tag' => __( 'Tag', 'isbwoo') . ' SVG',
				'isb_style_xmas_1' => __( 'Bonus - Christmas 1', 'isbwoo') . ' SVG',
				'isb_style_xmas_2' => __( 'Bonus - Christmas 2', 'isbwoo') . ' SVG',
				'isb_style_ribbon' => __( 'Ribbon', 'isbwoo') . ' FULL SVG',
				'isb_style_vintage' => __( 'Vintage', 'isbwoo') . ' IMG',
				'isb_style_pure' => __( 'Pure', 'isbwoo') . ' CSS',
				'isb_style_modern' => __( 'Modern', 'isbwoo') . ' CSS',
			);

			self::$isb_style_special = array(
				'isb_special_plain' => __( 'Plain', 'isbwoo') . ' CSS',
				'isb_special_arrow' => __( 'Arrow', 'isbwoo') . ' CSS',
				'isb_special_bigbadge' => __( 'Big Badge', 'isbwoo') . ' CSS'
			);

			self::$isb_color = array(
				'isb_avada_green' => __( 'Avada Green', 'isbwoo'),
				'isb_green' => __( 'Green', 'isbwoo'),
				'isb_orange' => __( 'Orange', 'isbwoo'),
				'isb_pink' => __( 'Pink', 'isbwoo'),
				'isb_red' => __( 'Pale Red', 'isbwoo'),
				'isb_yellow' => __( 'Golden Yellow', 'isbwoo'),
				'isb_tirq' => __( 'Turquoise', 'isbwoo'),
				'isb_brown' => __( 'Brown', 'isbwoo'),
				'isb_plumb' => __( 'Plumb', 'isbwoo'),
				'isb_marine' => __( 'Marine', 'isbwoo'),
				'isb_dark_orange' => __( 'Dark Orange', 'isbwoo'),
				'isb_fuschia' => __( 'Fuschia', 'isbwoo'),
				'isb_sky' => __( 'Sky', 'isbwoo'),
				'isb_ocean' => __( 'Ocean', 'isbwoo'),
				'isb_regular_gray' => __( 'Regular Gray', 'isbwoo'),
				'isb_summer_1' => __( 'Summer Pallete', 'isbwoo') . ' #1',
				'isb_summer_2' => __( 'Summer Pallete', 'isbwoo') . ' #2',
				'isb_summer_3' => __( 'Summer Pallete', 'isbwoo') . ' #3',
				'isb_summer_4' => __( 'Summer Pallete', 'isbwoo') . ' #4',
				'isb_summer_5' => __( 'Summer Pallete', 'isbwoo') . ' #5',
				'isb_trending_1' => __( 'Trending Pallete', 'isbwoo') . ' #1',
				'isb_trending_2' => __( 'Trending Pallete', 'isbwoo') . ' #2',
				'isb_trending_3' => __( 'Trending Pallete', 'isbwoo') . ' #3',
				'isb_trending_4' => __( 'Trending Pallete', 'isbwoo') . ' #4',
				'isb_trending_5' => __( 'Trending Pallete', 'isbwoo') . ' #5',
				'isb_trending_6' => __( 'Trending Pallete', 'isbwoo') . ' #6',
				'isb_trending_7' => __( 'Trending Pallete', 'isbwoo') . ' #7',
				'isb_trending_8' => __( 'Trending Pallete', 'isbwoo') . ' #8',
				'isb_trending_9' => __( 'Trending Pallete', 'isbwoo') . ' #9',
			);
			self::$isb_position = array(
				'isb_left' => __( 'Left', 'isbwoo'),
				'isb_right'=> __( 'Right', 'isbwoo')
			);


			add_filter( 'woocommerce_settings_tabs_array', __CLASS__ . '::isb_add_settings_tab', 50 );
			add_action( 'woocommerce_settings_tabs_isb', __CLASS__ . '::isb_settings_tab' );
			add_action( 'woocommerce_update_options_isb', __CLASS__ . '::isb_save_settings' );
			add_action( 'woocommerce_admin_field_isb_preview', __CLASS__ . '::isb_preview', 10 );
			add_action( 'admin_enqueue_scripts', __CLASS__ . '::isb_scripts' );
			add_action( 'wp_ajax_isb_respond', __CLASS__ . '::isb_respond' );

			global $isb_set;

			if ( get_option( 'wc_settings_isb_overrides', 'no' ) == 'yes' ) {
				add_action( 'woocommerce_product_write_panel_tabs', __CLASS__ . '::isb_add_product_tab' );
				add_action( 'woocommerce_product_write_panels', __CLASS__ . '::isb_product_tab' );
				add_action( 'save_post', __CLASS__ . '::isb_product_save' );
			}

		}

		public static function isb_scripts($hook) {
			if ( isset($_GET['page'], $_GET['tab']) && ($_GET['page'] == 'wc-settings' || $_GET['page'] == 'woocommerce_settings') && $_GET['tab'] == 'isb' ) {
				wp_enqueue_style( 'isb-style', plugins_url( 'assets/css/admin.css', dirname(__FILE__) ) );
				wp_enqueue_script( 'isb-admin', plugins_url( 'assets/js/admin.js', dirname(__FILE__) ), true );

				$curr_args = array(
					'ajax' => admin_url( 'admin-ajax.php' ),
				);
				wp_localize_script( 'isb-admin', 'isb', $curr_args );
			}

			global $post;

			if ( $hook == 'post-new.php' || $hook == 'post.php' && get_option( 'wc_settings_isb_overrides', 'no' ) == 'yes' ) {
				if ( 'product' === $post->post_type ) {
					wp_enqueue_style( 'isb-style', plugins_url( 'assets/css/admin.css', dirname(__FILE__) ) );
					wp_enqueue_script( 'isb-admin', plugins_url( 'assets/js/admin.js', dirname(__FILE__) ), true );

					$curr_args = array(
						'ajax' => admin_url( 'admin-ajax.php' ),
					);
					wp_localize_script( 'isb-admin', 'isb', $curr_args );
				}
			}

		}

		public static function isb_add_settings_tab( $settings_tabs ) {
			$settings_tabs['isb'] = __( 'Improved Sale Badges', 'isbwoo' );
			return $settings_tabs;
		}

		public static function isb_settings_tab() {
			woocommerce_admin_fields( self::isb_get_settings() );
		}

		public static function isb_save_settings() {
			woocommerce_update_options( self::isb_get_settings() );
		}

		public static function isb_preview( $field ) {
			global $woocommerce;
		?>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="<?php echo esc_attr( $field['id'] ); ?>"><?php echo esc_html( $field['title'] ); ?></label>
					<?php echo '<img class="help_tip" data-tip="' . esc_attr( $field['desc'] ) . '" src="' . $woocommerce->plugin_url() . '/assets/images/help.png" height="16" width="16" />'; ?>
				</th>
				<td class="forminp forminp-<?php echo sanitize_title( $field['type'] ) ?>">
					<div id="isb_preview">
				<?php
					global $isb_set;

					$isb_set['style'] = ( isset( $_POST['isb_style'] ) ? $_POST['isb_style'] : get_option( 'wc_settings_isb_style', 'isb_style_basic' ) );
					$isb_set['color'] = ( isset( $_POST['isb_color'] ) ? $_POST['isb_color'] : get_option( 'wc_settings_isb_color', 'isb_red' ) );
					$isb_set['position'] = ( isset( $_POST['isb_position'] ) ? $_POST['isb_position'] : get_option( 'wc_settings_isb_position', 'isb_right' ) );

					$isb_price['type'] = 'simple';
					$isb_price['id'] = get_the_ID();
					$isb_price['regular'] = 32;
					$isb_price['sale'] = 27;
					$isb_price['difference'] = $isb_price['regular'] - $isb_price['sale'];
					$isb_price['percentage'] = round( ( $isb_price['regular'] - $isb_price['sale'] ) * 100 / $isb_price['regular'] );

					if ( is_array($isb_set) ) {
						$isb_class = $isb_set['style'] . ' ' . $isb_set['color'] . ' ' . $isb_set['position'];
					}
					else {
						$isb_class = 'isb_style_basic isb_red isb_right';
					}

					$isb_curr_set = $isb_set;

					$include = WC_Improved_Sale_Badges::$path . 'includes/styles/' . $isb_set['style'] . '.php';
					include($include);
				?>
					</div>
				</td>
			</tr>
		<?php
		}

		public static function isb_get_settings() {

			$settings = array(
				'section_settings_title' => array(
					'name'     => __( 'Improved Sale Badges for WooCommerce - Settings', 'isbwoo' ),
					'type'     => 'title',
					'desc'     => __( 'Setup shop sale badges. Select style and configure basic appearance options.', 'isbwoo' ),
					'id'       => 'isb_settings_title'
				),
				'wc_settings_isb_preview' => array(
					'name'    => __( 'Badge Preview', 'isbwoo' ),
					'type'    => 'isb_preview',
					'desc'    => __( 'Quick sale badge style preview.', 'isbwoo' ),
					'id'      => 'wc_settings_isb_preview',
					'desc_tip' =>  true
				),
				'wc_settings_isb_style' => array(
					'name'    => __( 'Badge Style', 'isbwoo' ),
					'type'    => 'select',
					'desc'    => __( 'Select sale badge style.', 'isbwoo' ),
					'id'      => 'wc_settings_isb_style',
					'default' => 'basic',
					'desc_tip' =>  true,
					'options' => self::$isb_style,
					'css' => 'width:300px;margin-right:12px;'
				),
				'wc_settings_isb_color' => array(
					'name'    => __( 'Badge Color', 'isbwoo' ),
					'type'    => 'select',
					'desc'    => __( 'Select sale badge color.', 'isbwoo' ),
					'id'      => 'wc_settings_isb_color',
					'default'     => 'red',
					'desc_tip' =>  true,
					'options' => self::$isb_color,
					'css' => 'width:300px;margin-right:12px;'
				),
				'wc_settings_isb_position' => array(
					'name'    => __( 'Badge Position', 'isbwoo' ),
					'type'    => 'select',
					'desc'    => __( 'Select sale badge position.', 'isbwoo' ),
					'id'      => 'wc_settings_isb_position',
					'default'     => 'left',
					'desc_tip' =>  true,
					'options' => self::$isb_position,
					'css' => 'width:300px;margin-right:12px;'
				),
				'section_settings_end' => array(
					'type' => 'sectionend',
					'id' => 'isb_settings_end'
				),
				'section_advanced_title' => array(
					'name'     => __( 'Improved Sale Badges for WooCommerce - Advanced', 'isbwoo' ),
					'type'     => 'title',
					'desc'     => __( 'Setup advanced options for the plugin.', 'isbwoo' ),
					'id'       => 'isb_advanced_title'
				),
				'wc_settings_isb_overrides' => array(
					'name'    => __( 'Single Product Overrides', 'isbwoo' ),
					'type'    => 'checkbox',
					'desc'    => __( 'Enable badge overrides for each product.', 'isbwoo' ),
					'id'      => 'wc_settings_isb_overrides',
					'default'     => 'no'
				),
				'wc_settings_isb_archive_action' => array(
					'name' => __( 'Override Default Product Archive Action', 'isbwoo' ),
					'type' => 'text',
					'desc' => __( 'Change default init action on product archives. Use actions initiated in your content-product.php file. Use this option if the badges do not appear on your product archive pages.', 'isbwoo' ),
					'id'   => 'wc_settings_isb_archive_action',
					'default' => '',
					'css' => 'width:300px;margin-right:12px;'
				),
				'wc_settings_isb_single_action' => array(
					'name' => __( 'Override Default Single Product Action', 'isbwoo' ),
					'type' => 'text',
					'desc' => __( 'Change default init action on single product pages. Use actions initiated in your content-single-product.php file. Use this option if the badges do not appear on your single product pages.', 'isbwoo' ),
					'id'   => 'wc_settings_isb_single_action',
					'default' => '',
					'css' => 'width:300px;margin-right:12px;'
				),
				'section_advanced_end' => array(
					'type' => 'sectionend',
					'id' => 'isb_advanced_end'
				)
			);

			return apply_filters( 'wc_isb_settings', $settings );
		}

		public static function isb_respond() {
			if ( !isset($_POST['data']) ) {
				die();
				exit;
			}
			
			$isb_set = array(
				'style' => ( $_POST['data'][0] !== '' ? $_POST['data'][0] : get_option( 'wc_settings_isb_style', 'isb_style_basic' ) ),
				'color' => ( $_POST['data'][1] !== '' ? $_POST['data'][1] : get_option( 'wc_settings_isb_color', 'isb_red' ) ),
				'position' => ( $_POST['data'][2] !== '' ? $_POST['data'][2] : get_option( 'wc_settings_isb_position', 'isb_left' ) ),
				'type' => 'simple'
			);

			if ( isset($_POST['data'][3]) ) {
				$isb_set['special'] = $_POST['data'][3];
			}
			if ( isset($_POST['data'][4]) ) {
				$isb_set['special_text'] = $_POST['data'][4];
			}

			$isb_price['type'] = 'simple';
			$isb_price['regular'] = 32;
			$isb_price['sale'] = 27;
			$isb_price['difference'] = $isb_price['regular'] - $isb_price['sale'];
			$isb_price['percentage'] = round( ( $isb_price['regular'] - $isb_price['sale'] ) * 100 / $isb_price['regular'] );

			if ( is_array($isb_set) ) {
				$isb_class = ( isset($isb_set['special']) && $isb_set['special'] !== '' ? $isb_set['special'] : $isb_set['style'] ) . ' ' . $isb_set['color'] . ' ' . $isb_set['position'];
			}
			else {
				$isb_class = 'isb_style_basic isb_red isb_right';
			}

			$isb_curr_set = $isb_set;

			if ( isset($isb_set['special']) && $isb_set['special'] !== '' ) {
				$include = WC_Improved_Sale_Badges::$path . 'includes/specials/' . $isb_set['special'] . '.php';
			}
			else {
				$include = WC_Improved_Sale_Badges::$path . 'includes/styles/' . $isb_set['style'] . '.php';
			}
			

			ob_start();

			include($include);

			$html = ob_get_clean();

			die($html);
			exit;

		}



		public static function isb_add_product_tab() {
			echo ' <li class="isb_tab"><a href="#isb_tab">'. __('Sale Badges', 'isbwoo') .'</a></li>';
		}

		public static function isb_product_tab() {
			global $post, $isb_set;

			$curr_badge = get_post_meta($post->ID, '_isb_settings');

			$check_settings = array(
					'style' => $isb_set['style'],
					'color' => $isb_set['color'],
					'position' => $isb_set['position'],
					'special' => $isb_set['special'],
					'special_text' => $isb_set['special_text']
				);

			if ( is_array( $curr_badge ) && isset( $curr_badge[0] ) ) {
				$curr_badge = $curr_badge[0];
				foreach ( $check_settings as $k => $v ) {
					$curr_badge[$k] = ( isset( $curr_badge[$k] ) && $curr_badge[$k] !== '' ? $curr_badge[$k] : $v );
				}
				$isb_set = $curr_badge;
			}
			else {
				$curr_badge = $check_settings;
				$isb_set = array(
					'style' => '',
					'color' => '',
					'position' => '',
					'special' => '',
					'special_text' => ''
				);
			}

		?>
		<div id="isb_tab" class="panel woocommerce_options_panel">

			<div class="options_group grouping basic">
				<span class="wc_settings_isb_title"><?php _e('Badge Preview', 'isbwoo'); ?></span>
				<div id="isb_preview">
				<?php

					$isb_curr_set = $curr_badge;

					$isb_price['type'] = 'simple';
					$isb_price['regular'] = 32;
					$isb_price['sale'] = 27;
					$isb_price['difference'] = $isb_price['regular'] - $isb_price['sale'];
					$isb_price['percentage'] = round( ( $isb_price['regular'] - $isb_price['sale'] ) * 100 / $isb_price['regular'] );

					if ( is_array($isb_curr_set) ) {
						$isb_class = ( $isb_curr_set['special'] !== '' ? $isb_curr_set['special'] : $isb_curr_set['style'] ) . ' ' . $isb_curr_set['color'] . ' ' . $isb_curr_set['position'];
					}
					else {
						$isb_class = 'isb_style_basic isb_red isb_right';
					}

					if ( $isb_curr_set['special'] !== '' ) {
						$include = WC_Improved_Sale_Badges::$path . 'includes/specials/' . $isb_curr_set['special'] . '.php';
					}
					else {
						$include = WC_Improved_Sale_Badges::$path . 'includes/styles/' . $isb_curr_set['style'] . '.php';
					}

					ob_start();

					include($include);

					$html = ob_get_clean();

					echo $html;

				?>
				</div>
				<p class="form-field isb_style">
					<label for="wc_settings_isb_style"><?php _e('Badge Style', 'isbwoo'); ?></label>
					<select id="wc_settings_isb_style" name="isb_style_single" class="option select short">
						<option value=""<?php echo ( isset($isb_set['style']) ? ' selected="selected"' : '' ); ?>><?php _e('None', 'isb_woo'); ?></option>
				<?php
					foreach ( self::$isb_style as $k => $v ) {
						printf('<option value="%1$s"%3$s>%2$s</option>', $k, $v, ( $isb_set['style'] == $k ? ' selected="selected"' : '' ) );
					}
				?>
					</select>
				</p>
				<p class="form-field isb_color">
					<label for="wc_settings_isb_color"><?php _e('Badge Color', 'isbwoo'); ?></label>
					<select id="wc_settings_isb_color" name="isb_color_single" class="option select short">
						<option value=""<?php echo ( isset($isb_set['color']) ? ' selected="selected"' : '' ); ?>><?php _e('None', 'isb_woo'); ?></option>
				<?php
					foreach ( self::$isb_color as $k => $v ) {
						printf('<option value="%1$s"%3$s>%2$s</option>', $k, $v, ( $isb_set['color'] == $k ? ' selected="selected"' : '' ) );
					}
				?>
					</select>
				</p>
				<p class="form-field isb_position">
					<label for="wc_settings_isb_position"><?php _e('Badge Position', 'isbwoo'); ?></label>
					<select id="wc_settings_isb_position" name="isb_position_single" class="option select short">
						<option value=""<?php echo ( isset($isb_set['position']) ? ' selected="selected"' : '' ); ?>><?php _e('None', 'isb_woo'); ?></option>
				<?php
					foreach ( self::$isb_position as $k => $v ) {
						printf('<option value="%1$s"%3$s>%2$s</option>', $k, $v, ( $isb_set['position'] == $k ? ' selected="selected"' : '' ) );
					}
				?>
					</select>
				</p>
				<span class="wc_settings_isb_title"><?php _e('Special Badge Settings', 'isbwoo'); ?></span>
				<p class="form-field isb_special_badge">
					<label for="wc_settings_isb_special"><?php _e('Special Badge', 'isbwoo'); ?></label>
					<select id="wc_settings_isb_special" name="isb_style_special" class="option select short">
						<option value=""<?php echo ( isset($isb_set['special']) ? ' selected="selected"' : '' ); ?>><?php _e('None', 'isb_woo'); ?></option>
				<?php
					foreach ( self::$isb_style_special as $k => $v ) {
						printf('<option value="%1$s"%3$s>%2$s</option>', $k, $v, ( isset($isb_set['special']) && $isb_set['special'] == $k ? ' selected="selected"' : '' ) );
					}
				?>
					</select>
				</p>
				<p class="form-field isb_special_text">
					<label for="wc_settings_isb_special_text"><?php _e('Special Badge Text', 'isbwoo'); ?></label>
					<textarea id="wc_settings_isb_special_text" name="isb_style_special_text" class="option short"><?php echo ( isset( $isb_set['special_text'] ) ? $isb_set['special_text'] : '' ); ?></textarea>
				</p>
			</div>

		</div>
		<?php

		}

		public static function isb_product_save( $curr_id ) {

			$curr = array();

			if ( isset($_POST['product-type']) ) {
				$curr = array(
					'style' => ( isset($_POST['isb_style_single']) ? $_POST['isb_style_single'] : '' ),
					'color' => ( isset($_POST['isb_color_single']) ? $_POST['isb_color_single'] : '' ),
					'position' => ( isset($_POST['isb_position_single']) ? $_POST['isb_position_single'] : '' ),
					'special' => ( isset($_POST['isb_style_special']) ? $_POST['isb_style_special'] : '' ),
					'special_text' => ( isset($_POST['isb_style_special_text']) ? $_POST['isb_style_special_text'] : '' )
				);
				update_post_meta( $curr_id, '_isb_settings', $curr );
			}

		}

	}

	add_action( 'init', array( 'WC_Isb_Settings', 'init' ), 999 );

?>