<?php
/**
 * Widget class.
 *
 * @since 1.0.0
 *
 * @package Envira_Albums
 * @author  Tim Carr
 */
class Envira_Albums_Widget extends WP_Widget {

    /**
     * Holds the class object.
     *
     * @since 1.0.0
     *
     * @var object
     */
    public static $instance;

    /**
     * Path to the file.
     *
     * @since 1.0.0
     *
     * @var string
     */
    public $file = __FILE__;

    /**
     * Holds the base class object.
     *
     * @since 1.0.0
     *
     * @var object
     */
    public $base;

    /**
     * Constructor. Sets up and creates the widget with appropriate settings.
     *
     * @since 1.0.0
     */
    public function __construct() {

        // Load the base class object.
        $this->base = Envira_Albums::get_instance();

        $widget_ops = apply_filters( 'envira_albums_widget_ops',
            array(
                'classname'   => 'envira-albums',
                'description' => __( 'Place an Envira album into a widgetized area.', 'envira-gallery' )
            )
        );

        $control_ops = apply_filters( 'envira_albums_widget_control_ops',
            array(
                'id_base' => 'envira-albums',
                'height'  => 350,
                'width'   => 225
            )
        );

        parent::__construct( 'envira-albums', apply_filters( 'envira_albums_widget_name', __( 'Envira Album', 'envira-albums' ) ), $widget_ops, $control_ops );

    }

    /**
     * Outputs the widget within the widgetized area.
     *
     * @since 1.0.0
     *
     * @param array $args     The default widget arguments.
     * @param array $instance The input settings for the current widget instance.
     */
    public function widget( $args, $instance ) {

        // Extract arguments into variables.
        extract( $args );

        $title      = apply_filters( 'widget_title', $instance['title'] );
        $album_id = $instance['envira_album_id'];

        do_action( 'envira_albums_widget_before_output', $args, $instance );

        echo $before_widget;

        do_action( 'envira_albums_widget_before_title', $args, $instance );

        // If a title exists, output it.
        if ( $title ) {
            echo $before_title . $title . $after_title;
        }

        do_action( 'envira_albums_widget_before_gallery', $args, $instance );

        // If an album has been selected, output it.
        if ( $album_id ) {
            envira_album( $album_id );
        }

        do_action( 'envira_albums_widget_after_gallery', $args, $instance );

        echo $after_widget;

        do_action( 'envira_albums_widget_after_output', $args, $instance );

    }

    /**
     * Sanitizes and updates the widget.
     *
     * @since 1.0.0
     *
     * @param array $new_instance The new input settings for the current widget instance.
     * @param array $old_instance The old input settings for the current widget instance.
     */
    public function update( $new_instance, $old_instance ) {

        // Set $instance to the old instance in case no new settings have been updated for a particular field.
        $instance = $old_instance;

        // Sanitize user inputs.
        $instance['title']             = trim( $new_instance['title'] );
        $instance['envira_album_id'] = absint( $new_instance['envira_album_id'] );

        return apply_filters( 'envira_album_widget_update_instance', $instance, $new_instance );

    }

    /**
     * Outputs the widget form where the user can specify settings.
     *
     * @since 1.0.0
     *
     * @param array $instance The input settings for the current widget instance.
     */
    public function form( $instance ) {

        // Get all avilable albums and widget properties.
        $albums  	= Envira_Albums::get_instance()->get_albums();
        $title      = isset( $instance['title'] ) ? $instance['title'] : '';
        $gallery_id = isset( $instance['envira_album_id'] ) ? $instance['envira_album_id'] : false;

        do_action( 'envira_albums_widget_before_form', $instance );
        ?>
        <p>
            <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title', 'envira-albums' ); ?></label>
            <input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" style="width: 100%;" />
        </p>
        <?php do_action( 'envira_albums_widget_middle_form', $instance ); ?>
        <p>
            <label for="<?php echo $this->get_field_id( 'envira_album_id' ); ?>"><?php _e( 'Album', 'envira-albums' ); ?></label>
            <select id="<?php echo esc_attr( $this->get_field_id( 'envira_album_id' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'envira_album_id' ) ); ?>" style="width: 100%;">
                <?php
                if ( $albums && is_array( $albums ) ) {
                    foreach ( $albums as $album ) {
                        if ( ! empty( $album['config']['title'] ) ) {
                            $title = $album['config']['title'];
                        } else if ( ! empty( $album['config']['slug'] ) ) {
                            $title = $album['config']['title'];
                        } else {
                            $title = sprintf( __( 'Album ID #%s', 'envira-albums' ), $album['id'] );
                        }

                        echo '<option value="' . absint( $album['id'] ) . '"' . selected( absint( $album['id'] ), $album_id, false ) . '>' . $title . '</option>';
                    }
                }
                ?>
            </select>
        </p>
        <?php
        do_action( 'envira_albums_widget_after_form', $instance );

    }

}