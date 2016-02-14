<?php

/**
 * Customize Background Image Control Class
 *
 * @package WordPress
 * @subpackage Customize
 * @since 3.4.0
 */
class Child_Epik_Image_Control extends WP_Customize_Image_Control {

	/**
	 * Constructor.
	 *
	 * If $args['settings'] is not defined, use the $id as the setting ID.
	 *
	 * @since 3.4.0
	 * @uses WP_Customize_Upload_Control::__construct()
	 *
	 * @param WP_Customize_Manager $manager
	 * @param string $id
	 * @param array $args
	 */
	public function __construct( $manager, $id, $args ) {
		$this->statuses = array( '' => __( 'No Image', 'epik' ) );

		parent::__construct( $manager, $id, $args );

		$this->add_tab( 'upload-new', __( 'Upload New', 'epik' ), array( $this, 'tab_upload_new' ) );
		$this->add_tab( 'uploaded',   __( 'Uploaded', 'epik' ),   array( $this, 'tab_uploaded' ) );
		
		if ( $this->setting->default )
			$this->add_tab( 'default',  __( 'Default', 'epik' ),  array( $this, 'tab_default_background' ) );

		// Early priority to occur before $this->manager->prepare_controls();
		add_action( 'customize_controls_init', array( $this, 'prepare_control' ), 5 );
	}

	/**
	 * @since 3.4.0
	 * @uses WP_Customize_Image_Control::print_tab_image()
	 */
	public function tab_default_background() {
		$this->print_tab_image( $this->setting->default );
	}
	
}

	global $wp_customize;

	$images = apply_filters( 'epik_images', array( '1', '3', '4', '5', '6', '7', '8', '9', '14'  ) );
	
	$wp_customize->add_section( 'epik-settings', array(
		'title'    => __( 'Background Images', 'epik' ),
		'priority' => 35,
	) );

	foreach( $images as $image ){

		$wp_customize->add_setting( $image .'-image', array(
			'default'  => sprintf( '%s/images/bg-%s.jpg', get_stylesheet_directory_uri(), $image ),
			'type'     => 'option',
		) );

		$wp_customize->add_control( new Child_Epik_Image_Control( $wp_customize, $image .'-image', array(
			'label'    => sprintf( __( 'Home Featured Section %s Image:', 'epik' ), $image ),
			'section'  => 'epik-settings',
			'settings' => $image .'-image',
			'priority' => $image+1,
		) ) );

	}
