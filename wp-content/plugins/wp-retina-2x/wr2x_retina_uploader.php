<?php

// Based on code provided by gmazzap on
// http://wordpress.stackexchange.com/questions/126969/what-filter-should-i-use-to-insert-a-button-inside-on-mediaadd-new

class WR2x_Retina_Uploader {

  static function getLabel() {
    // change here the label of your custom upload button
    return 'Add Full Retina';
  }

  static function getUrl() {
    // change here the url of your custom upload button
    return add_query_arg( array( 'page' => 'my-custom-upload' ), admin_url( 'upload.php' ) );
  }

  function render() {
    // this is the function that render your custom upload system
    if ( !current_user_can( 'upload_files' ) ) {
      echo '<h2>Sorry, you are not allowed to upload files.</h2>';
      return;
    }
  ?>
    <div class="wrap">
    <h2>Upload New Media as a Retina for Full Size</h2>
    <p>The image you are uploading here will be used as a Retina for Full Size. It means that whatever its size is, the normal size will be created depending on it (width and height divided by two)</p>
    <div id="plupload-upload-ui" class="hide-if-no-js drag-drop">
    <div id="drag-drop-area" style="position: relative;">
    	<div class="drag-drop-inside">
    	<p class="drag-drop-info">Drop files here</p>
    	<p>or</p>
    	<p class="drag-drop-buttons"><input id="plupload-browse-button" type="button" value="Select Files" class="button" style="position: relative; z-index: 1;"></p>
    	</div>
    </div>


    </div>
  <?php
  }

  function __construct() {
    add_action( 'load-upload.php', array( $this, 'indexButton' ) );
    add_action( 'admin_menu', array( $this, 'submenu') );
    add_action( 'wp_before_admin_bar_render', array( $this, "adminBar" ) );
    add_action( 'post-plupload-upload-ui', array( $this, 'mediaButton' ) );
  }

  function submenu() {
    add_media_page( self::getLabel(), self::getLabel(), 'upload_files', 'my-custom-upload', array( $this, 'render') );
  }

  function adminBar() {
    if ( !current_user_can( 'upload_files' ) || !is_admin_bar_showing() ) return;
    global $wp_admin_bar;
    $wp_admin_bar->add_node( array(
      'parent' => 'new-content',
      'id' => 'custom-upload-link',
      'title' => self::getLabel(),
      'href' => self::getUrl()
    ) );
  }


  function mediaButton() {
    if ( current_user_can( 'upload_files' ) ) {
      echo '<div><p align="center">';
      echo '<input id="custom-browse-button" type="button" value="' . self::getLabel() . '" class="button" />';
      echo '</p></div>';
      $this->mediaButtonScript();
    }
  }

  function mediaButtonScript() {
    if ( !current_user_can( 'upload_files' ) ) return;
  ?>
    <script>
    jQuery(document).on('click', '#custom-browse-button', function(e) {
      e.preventDefault();
      window.location = '<?php echo self::getUrl(); ?>';
    });
    </script>
  <?php
  }

  function indexButton() {
    if ( !current_user_can( 'upload_files' ) ) return;
    add_filter( 'esc_html', array(__CLASS__, 'h2Button'), 999, 2 );
  }

  static function h2Button( $safe_text, $text ) {
    if ( !current_user_can( 'upload_files' ) ) return $safe_text;
    if ( $text === __('Media Library') && did_action( 'all_admin_notices' ) ) {
      remove_filter( 'esc_html', array(__CLASS__, 'h2Button'), 999, 2 );
      $format = ' <a href="%s" class="add-new-h2">%s</a>';
      $mybutton = sprintf($format, esc_url(self::getUrl()), esc_html(self::getLabel()) );
      $safe_text .= $mybutton;
    }
    return $safe_text;
  }

}

$ui = new WR2x_Retina_Uploader;
