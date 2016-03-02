<?php

add_action( 'widgets_init', create_function( '', 'return register_widget("WPSEO_Show_OpeningHours");' ) );

class WPSEO_Show_OpeningHours extends WP_Widget {
	/** constructor */
	function WPSEO_Show_OpeningHours() {
		$widget_options = array(
			'classname'   => 'WPSEO_Show_OpeningHours',
			'description' => __( 'Shows opening hours of locations in Schema.org standards.', 'yoast-local-seo' )
		);
		parent::__construct( false, $name = __( 'WP SEO - Show Opening hours', 'yoast-local-seo' ), $widget_options );
	}

	/** @see WP_Widget::widget */
	function widget( $args, $instance ) {
		global $wpseo_local_core;

		$title       = apply_filters( 'widget_title', $instance['title'] );
		$location_id = ! empty( $instance['location_id'] ) ? $instance['location_id'] : '';
		$comment     = ! empty( $instance['comment'] ) ? esc_attr( $instance['comment'] ) : '';

		if ( ( $location_id == '' && wpseo_has_multiple_locations() ) || ( $location_id == 'current' && ! is_singular( 'wpseo_locations' ) ) ) {
			return '';
		}

		$shortcode_args = array(
			'id'           => $location_id,
			'comment'      => $comment,
			'from_widget'  => true,
			'widget_title' => $title,
			'before_title' => $args['before_title'],
			'after_title'  => $args['after_title'],
			'hide_closed'  => $instance['hide_closed'] ? 1 : 0,
		);

		$location_data = $wpseo_local_core->get_location_data( $location_id );
		$location_data = ! empty( $location_data['businesses'] ) ? $location_data['businesses'][0] : null;

		if( null == $location_data ) {
			return '';
		}

		if ( isset( $args['before_widget'] ) ) {
			echo $args['before_widget'];
		}

		if ( ! empty( $title ) ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}

		if ( ! isset ( $instance['hide_closed'] ) ) {
			$instance['hide_closed'] = 0;
		}

		// Displaying location data as <meta> tags, so the schema.org validation will be positive
		echo '<div itemscope itemtype="http://schema.org/' . esc_attr( $location_data['business_type'] ) . '">';
		echo '<meta itemprop="name" content="' . esc_attr( $location_data['business_name'] ) . '">';

		echo wpseo_local_show_opening_hours( $shortcode_args );
		echo '</div>';

		if ( isset( $args['after_widget'] ) ) {
			echo $args['after_widget'];
		}

		return true;
	}

	/** @see WP_Widget::update */
	function update( $new_instance, $old_instance ) {
		$instance                = $old_instance;
		$instance['title']       = esc_attr( $new_instance['title'] );
		$instance['location_id'] = esc_attr( $new_instance['location_id'] );
		$instance['hide_closed'] = esc_attr( $new_instance['hide_closed'] );
		$instance['comment']     = esc_attr( $new_instance['comment'] );

		return $instance;
	}

	/** @see WP_Widget::form */
	function form( $instance ) {
		$title       = ! empty( $instance['title'] ) ? esc_attr( $instance['title'] ) : '';
		$location_id = ! empty( $instance['location_id'] ) ? esc_attr( $instance['location_id'] ) : '';
		$hide_closed = ! empty( $instance['hide_closed'] ) && esc_attr( $instance['hide_closed'] ) == '1';
		$comment     = ! empty( $instance['comment'] ) ? esc_attr( $instance['comment'] ) : '';
		?>
		<p>
			<label
					for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'yoast-local-seo' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>"
						 name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" />
		</p>

		<?php if ( wpseo_has_multiple_locations() ) { ?>
			<p>
				<label
						for="<?php echo $this->get_field_id( 'location_id' ); ?>"><?php _e( 'Location:', 'yoast-local-seo' ); ?></label>
				<?php
				$args = array(
					'post_type'      => 'wpseo_locations',
					'orderby'        => 'name',
					'order'          => 'ASC',
					'posts_per_page' => - 1,
					'fields'		 => 'ids'
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
		<p>
			<label for="<?php echo $this->get_field_id( 'hide_closed' ); ?>">
				<input id="<?php echo $this->get_field_id( 'hide_closed' ); ?>"
							 name="<?php echo $this->get_field_name( 'hide_closed' ); ?>" type="checkbox"
							 value="1" <?php echo ! empty( $hide_closed ) ? ' checked="checked"' : ''; ?> />
				<?php _e( 'Hide closed days', 'yoast-local-seo' ); ?>
			</label>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'comment' ); ?>"><?php _e( 'Extra comment', 'yoast-local-seo' ); ?></label>
			<textarea id="<?php echo $this->get_field_id( 'comment' ); ?>" class="widefat" name="<?php echo $this->get_field_name( 'comment' ); ?>"><?php echo esc_attr( $comment ); ?></textarea>
		</p>

	<?php
	}

}
