<?php

add_action( 'widgets_init', create_function( '', 'return register_widget("WPSEO_Show_Map");' ) );

class WPSEO_Show_Map extends WP_Widget {
	/** constructor */
	function WPSEO_Show_Map() {
		$widget_options = array(
			'classname'   => 'WPSEO_Show_Map',
			'description' => __( 'Shows Google Map of your location', 'yoast-local-seo' )
		);
		parent::__construct( false, $name = __( 'WP SEO - Show Map', 'yoast-local-seo' ), $widget_options );
	}

	/** @see WP_Widget::widget */
	function widget( $args, $instance ) {
		$title              = apply_filters( 'widget_title', isset( $instance['title'] ) ? $instance['title'] : '' );
		$location_id        = !empty( $instance['location_id'] ) ? $instance['location_id'] : '';
		$show_all_locations = !empty( $instance['show_all_locations'] ) && $instance['show_all_locations'] == '1';
		$width              = !empty( $instance['width'] ) ? $instance['width'] : 200;
		$height             = !empty( $instance['height'] ) ? $instance['height'] : 150;
		$zoom               = !empty( $instance['zoom'] ) ? $instance['zoom'] : 10;
		$show_route         = !empty( $instance['show_route'] ) && $instance['show_route'] == '1';
		$show_state         = !empty( $instance['show_state'] ) && $instance['show_state'] == '1';
		$show_country       = !empty( $instance['show_country'] ) && $instance['show_country'] == '1';
		$show_url           = !empty( $instance['show_url'] ) && $instance['show_url'] == '1';


		if ( ( $location_id == '' && wpseo_has_multiple_locations() ) || ( $location_id == 'current' && ! is_singular( 'wpseo_locations' ) ) ) {
			return '';
		}

		if ( isset( $args['before_widget'] ) ) {
			echo $args['before_widget'];
		}

		if ( ! empty( $title ) ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}

		$map_args = array(
			'width'        => $width,
			'height'       => $height,
			'zoom'         => $zoom,
			'id'           => $show_all_locations ? 'all' : $location_id,
			'show_route'   => $show_route,
			'show_state'   => $show_state,
			'show_country' => $show_country,
			'show_url'     => $show_url
		);

		echo wpseo_local_show_map( $map_args );

		if ( isset( $args['after_widget'] ) ) {
			echo $args['after_widget'];
		}

		return true;
	}

	/** @see WP_Widget::update */
	function update( $new_instance, $old_instance ) {
		$instance                       = $old_instance;
		$instance['title']              = esc_attr( $new_instance['title'] );
		$instance['location_id']        = esc_attr( $new_instance['location_id'] );
		$instance['show_all_locations'] = esc_attr( $new_instance['show_all_locations'] );
		$instance['width']              = esc_attr( $new_instance['width'] );
		$instance['height']             = esc_attr( $new_instance['height'] );
		$instance['zoom']               = esc_attr( $new_instance['zoom'] );
		$instance['show_route']         = esc_attr( $new_instance['show_route'] );
		$instance['show_state']         = esc_attr( $new_instance['show_state'] );
		$instance['show_country']       = esc_attr( $new_instance['show_country'] );
		$instance['show_url']           = esc_attr( $new_instance['show_url'] );

		return $instance;
	}

	/** @see WP_Widget::form */
	function form( $instance ) {
		$title              = !empty( $instance['title'] ) ? esc_attr( $instance['title'] ) : '';
		$location_id        = !empty( $instance['location_id'] ) ? esc_attr( $instance['location_id'] ) : '';
		$show_all_locations = !empty( $instance['show_all_locations'] ) && esc_attr( $instance['show_all_locations'] ) == '1';
		$width              = !empty( $instance['width'] ) ? $instance['width'] : 400;
		$height             = !empty( $instance['height'] ) ? $instance['height'] : 300;
		$zoom               = !empty( $instance['zoom'] ) ? $instance['zoom'] : 10;
		$show_route         = !empty( $instance['show_route'] ) && esc_attr( $instance['show_route'] ) == '1';
		$show_state         = !empty( $instance['show_state'] ) && esc_attr( $instance['show_state'] ) == '1';
		$show_country       = !empty( $instance['show_country'] ) && esc_attr( $instance['show_country'] ) == '1';
		$show_url           = !empty( $instance['show_url'] ) && esc_attr( $instance['show_url'] ) == '1';

		?>
		<p>
			<label
				for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'yoast-local-seo' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>"
				   name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>"/>
		</p>

		<?php if ( wpseo_has_multiple_locations() ) { ?>

			<p><?php _e( 'Choose to show all your locations in the map, otherwise just pick one in the selectbox below', 'yoast-local-seo' ); ?></p>
			<p id="wpseo-checkbox-multiple-locations-wrapper">
				<label for="<?php echo $this->get_field_id( 'show_all_locations' ); ?>">
					<input id="<?php echo $this->get_field_id( 'show_all_locations' ); ?>"
						   name="<?php echo $this->get_field_name( 'show_all_locations' ); ?>" type="checkbox"
						   value="1" <?php echo !empty( $show_all_locations ) ? ' checked="checked"' : ''; ?> />
					<?php _e( 'Show all locations', 'yoast-local-seo' ); ?>
				</label>
			</p>

			<p id="wpseo-locations-wrapper" <?php echo $show_all_locations ? 'style="display: none;"' : ''; ?>>
				<label
					for="<?php echo $this->get_field_id( 'location_id' ); ?>"><?php _e( 'Location:', 'yoast-local-seo' ); ?></label>
				<?php
				$args = array(
					'post_type'      => 'wpseo_locations',
					'orderby'        => 'name',
					'order'          => 'ASC',
					'posts_per_page' => -1,
					'fields'	 	 => 'ids'
				);
				$locations = get_posts( $args );
				?>
				<select name="<?php echo $this->get_field_name( 'location_id' ); ?>"
						id="<?php echo $this->get_field_id( 'location_id' ); ?>">
					<option value=""><?php _e( 'Select a location', 'yoast-local-seo' ); ?></option>
					<option value="current" <?php selected( $location_id, 'current' ); ?>><?php _e( 'Use current location', 'yoast-local-seo' ); ?></option>
					<?php foreach ( $locations as $loc_id ) { ?>
						<option
							value="<?php echo $loc_id; ?>" <?php selected( $location_id, $loc_id ); ?>><?php echo get_the_title( $loc_id ); ?></option>
					<?php } ?>
				</select>
			</p>

		<?php } ?>

		<h4><?php _e( 'Maps settings', 'yoast-local-seo' ); ?></h4>
		<p>
			<label
				for="<?php echo $this->get_field_id( 'width' ); ?>"><?php _e( 'Width:', 'yoast-local-seo' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'width' ); ?>"
				   name="<?php echo $this->get_field_name( 'width' ); ?>" type="text" value="<?php echo $width; ?>"/>
		</p>
		<p>
			<label
				for="<?php echo $this->get_field_id( 'height' ); ?>"><?php _e( 'Height:', 'yoast-local-seo' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'height' ); ?>"
				   name="<?php echo $this->get_field_name( 'height' ); ?>" type="text" value="<?php echo $height; ?>"/>
		</p>
		<p>
			<?php
			$nr_zoom_levels = 21;
			?>
			<label
				for="<?php echo $this->get_field_id( 'zoom' ); ?>"><?php _e( 'Zoom level:', 'yoast-local-seo' ); ?></label>
			<select class="" id="<?php echo $this->get_field_id( 'zoom' ); ?>"
					name="<?php echo $this->get_field_name( 'zoom' ); ?>">
				<?php for ( $i = 0; $i <= $nr_zoom_levels; $i++ ) { ?>
					<option
						value="<?php echo $i; ?>"<?php echo $zoom == $i ? ' selected="selected"' : ''; ?>><?php echo $i; ?></option>
				<?php } ?>
			</select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'show_state' ); ?>">
				<input id="<?php echo $this->get_field_id( 'show_state' ); ?>"
					   name="<?php echo $this->get_field_name( 'show_state' ); ?>" type="checkbox"
					   value="1" <?php echo !empty( $show_state ) ? ' checked="checked"' : ''; ?> />
				<?php _e( 'Show state in info-window', 'yoast-local-seo' ); ?>
			</label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'show_country' ); ?>">
				<input id="<?php echo $this->get_field_id( 'show_country' ); ?>"
					   name="<?php echo $this->get_field_name( 'show_country' ); ?>" type="checkbox"
					   value="1" <?php echo !empty( $show_country ) ? ' checked="checked"' : ''; ?> />
				<?php _e( 'Show country in info-window', 'yoast-local-seo' ); ?>
			</label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'show_url' ); ?>">
				<input id="<?php echo $this->get_field_id( 'show_url' ); ?>"
					   name="<?php echo $this->get_field_name( 'show_url' ); ?>" type="checkbox"
					   value="1" <?php echo !empty( $show_url ) ? ' checked="checked"' : ''; ?> />
				<?php _e( 'Show URL in info-window', 'yoast-local-seo' ); ?>
			</label>
		</p>
		

		<p>
			<label for="<?php echo $this->get_field_id( 'show_route' ); ?>">
				<input id="<?php echo $this->get_field_id( 'show_route' ); ?>"
					   name="<?php echo $this->get_field_name( 'show_route' ); ?>" type="checkbox"
					   value="1" <?php echo !empty( $show_route ) ? ' checked="checked"' : ''; ?> />
				<?php _e( 'Show route planner', 'yoast-local-seo' ); ?>
			</label>
		</p>
	<?php
	}

}
