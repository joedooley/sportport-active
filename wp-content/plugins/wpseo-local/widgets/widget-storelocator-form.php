<?php

if( wpseo_has_multiple_locations() ) {
	add_action( 'widgets_init', create_function( '', 'return register_widget("WPSEO_Storelocator_Form");' ) );
}

class WPSEO_Storelocator_Form extends WP_Widget {
	/** constructor */
	function WPSEO_Storelocator_Form() {
		$widget_options = array(
			'classname'   => 'WPSEO_Storelocator_Form',
			'description' => __( 'Shows form to search the nearest store. Will submit to the page which contains the store locator.', 'yoast-local-seo' )
		);
		parent::__construct( false, $name = __( 'WP SEO - Storelocator form', 'yoast-local-seo' ), $widget_options );
	}

	/** @see WP_Widget::widget */
	function widget( $args, $instance ) {
		$title              = apply_filters( 'widget_title', $instance['title'] );
		$search_label       = !empty( $instance['search_label'] ) ? $instance['search_label'] : __('Enter your postal code or city', 'yoast-local-seo');
		$radius             = !empty( $instance['radius'] ) ? $instance['radius'] : 10;
		$page_id	        = !empty( $instance['page_id'] ) ? $instance['page_id'] : '';

		if ( empty( $page_id ) ) {
			return '';
		}

		if ( isset( $args['before_widget'] ) ) {
			echo $args['before_widget'];
		}

		if ( ! empty( $title ) ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}

		$search_string = isset( $_REQUEST['wpseo-sl-search'] ) ? esc_attr( $_REQUEST['wpseo-sl-search'] ) : '';
		
		?>
		<form action="<?php echo get_permalink( $page_id ); ?>" method="post" id="wpseo-storelocator-form">
			<fieldset>
				<p>
					<label for="wpseo-sl-search"><?php echo $search_label; ?></label>
					<input type="text" name="wpseo-sl-search" id="wpseo-sl-search" value="<?php echo esc_attr( $search_string ); ?>">
				</p>
				<p class="sl-submit">
					<input type="hidden" name="wpseo-sl-radius" id="wpseo-sl-radius" value="<?php echo esc_attr( $radius ); ?>">
					<input type="submit" value="<?php _e('Search', 'yoast-local-seo'); ?>">
				</p>
			</fieldset>
		</form>

		<?php

		if ( isset( $args['after_widget'] ) )
			echo $args['after_widget'];
	}


	/** @see WP_Widget::update */
	function update( $new_instance, $old_instance ) {
		$instance                   = $old_instance;
		$instance['title']          = esc_attr( $new_instance['title'] );
		$instance['search_label']   = esc_attr( $new_instance['search_label'] );
		$instance['radius']         = esc_attr( $new_instance['radius'] );
		$instance['page_id']        = esc_attr( $new_instance['page_id'] );

		return $instance;
	}

	/** @see WP_Widget::form */
	function form( $instance ) {
		$title              = !empty( $instance['title'] ) ? esc_attr( $instance['title'] ) : '';
		$search_label       = !empty( $instance['search_label'] ) ? esc_attr( $instance['search_label'] ) : '';
		$radius             = !empty( $instance['radius'] ) ? esc_attr( $instance['radius'] ) : 10;
		$page_id	        = !empty( $instance['page_id'] ) ? esc_attr( $instance['page_id'] ) : '';
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'yoast-local-seo' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>"/>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'search_label' ); ?>"><?php _e( 'Search label:', 'yoast-local-seo' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'search_label' ); ?>" name="<?php echo $this->get_field_name( 'search_label' ); ?>" type="text" value="<?php echo $search_label; ?>"/>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'radius' ); ?>"><?php _e( 'Default radius:', 'yoast-local-seo' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'radius' ); ?>" name="<?php echo $this->get_field_name( 'radius' ); ?>" type="number" min="0" value="<?php echo $radius; ?>"/><br>
			<small><?php _e('This field will be a hidden field and is only used for calculation.', 'yoast-local-seo'); ?></small>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'page_id' ); ?>"><?php _e( 'Select the page where your store locator shortcode is added. This is the page whereto the form is submitted.', 'yoast-local-seo' ); ?>:</label><br>
			<?php
				$args = array(
					'name' => $this->get_field_name( 'page_id' ),
					'id' => $this->get_field_id( 'page_id' ),
					'class' => 'widefat',
					'selected' => $page_id,
					'show_option_none' => __('Select a page', 'yoast-local-seo')
				);	
				wp_dropdown_pages($args);
			?>
		</p>
	<?php
	}

}
