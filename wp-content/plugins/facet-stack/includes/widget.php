<?php


class Facet_Stack_Widget extends WP_Widget {

	/**
	 * Create widget
	 *
	 * @since 1.0.0
	 */
	function __construct() {
		// Instantiate the parent object
		parent::__construct( 
			false, 
			__( 'Facet Stack', 'facet-stack' ), 
			array(
				'description' => __( 'A stack of facets for FacetWP', 'facet-stack' ),
			)
		);

		// enqueue loader script
		add_filter( 'facetwp_assets', array( $this, 'enqueue_init' ), 50 );

		/**
		 * Runs after Facet Stack widget is initialized
		 *
		 * @since 1.4.0
		 */
		do_action( 'facet_stack_widget_init' );
		
	}

	/**
	 * Widget output
	 *
	 * @since 1.0.0
	 *
	 * @param array $args
	 * @param array $instance
	 */
	function widget( $args, $instance ) {

		if( !empty( $instance['facets'] ) ){

			extract($args, EXTR_SKIP);

			// push an inline script to hide facet widgets
			echo '<style type="text/css">.widget_facet_stack_widget{display:none;}</style>';
			
			if( isset( $instance['load_style'] ) ){ // include the alternat loader file.
				include_once FACET_STACK_PATH . 'includes/load-style.php';
			}

			$facets = explode( ',', $instance['facets'] );

			// Not a multi stack (facets are wrapped in a single widget wrapper )
			if( !isset( $instance['multi_stack'] ) ){
				echo $before_widget;
			}
			
			foreach( $facets as $facet ){
				
				if( isset( $instance['multi_stack'] ) ){
					// multi stack (Each facet gets it's own widget wrapper )
					echo str_replace( $this->id, $this->id .'-' . $facet , $before_widget );
				}

				// load facet by name
				$facet = $facets = FWP()->helper->get_facet_by_name( $facet );				
				if( empty( $facet ) ){
					continue; // facet can't be found. no worries, skip it and carry on.
				}
				
				if( isset( $instance['show_titles'] ) ){
					echo $before_title . $facet['label'] . $after_title;
				}

				echo facetwp_display( 'facet', $facet['name'] );

				if( isset( $instance['multi_stack'] ) ){
					echo $after_widget;
				}
			}

			if( !isset( $instance['multi_stack'] ) ){
				echo $after_widget;
			}
			
		}
	}

	/**
	 * Output script to show the facet widgets
	 *
	 * @since 1.0.0
	 *
	 * @param array $assets
	 *
	 * @return array $assets
	 */
	public function enqueue_init( $assets ){
		$assets['facetstack.min.js'] = FACET_STACK_URL . 'assets/js/facetstack.min.js';
		return $assets;
	}

	/**
	 * Update widget settings
	 *
	 * @since 1.0.0
	 *
	 * @param array $new_instance
	 * @param array $old_instance
	 *
	 * @return array
	 */
	function update( $new_instance, $old_instance ) {
		// Save widget options
		return $new_instance;
	}

	/**
	 * Widget UI form
	 *
	 * @since 1.0.0
	 *
	 * @param array $instance
	 */
	function form( $instance ) {

		// get settings
		$instance = wp_parse_args( (array) $instance, array( 'facets' => '' ) );

		do_action( 'facet_stack_widget_form_start', $instance );
		
		$facets = FWP()->helper->get_facets();

		echo '<div class="facet-stack-wrapper">';

			// include general settings
			include FACET_STACK_PATH . 'includes/general-settings.php';

			// include facet selection
			include FACET_STACK_PATH . 'includes/facet-selection.php';


		echo '</div>';
		// add style sheet
		wp_enqueue_style( 'facet-stack-admin', FACET_STACK_URL . 'assets/css/admin.min.css', null, FACET_STACK_VER );
		wp_enqueue_script( 'facet-stack-admin', FACET_STACK_URL . 'assets/js/admin.min.js', array( 'jquery' ), FACET_STACK_VER );

		//end form
		do_action( 'facet_stack_widget_form_end', $instance, $this );
	}
}

/**
 * Register the Facet Stack widget
 *
 * @uses "widgets_init" hook
 * @since 1.0.0
 *
 */
function facet_stack_register_widget() {
	if( ! did_action( 'facet_stack_widget_init' ) ){
		register_widget( 'Facet_Stack_Widget' );
	}

}
add_action( 'widgets_init', 'facet_stack_register_widget' );