<?php

/*
  Copyright (c) 2012, SEO Squirrly.
  The copyrights to the software code in this file are licensed under the (revised) BSD open source license.

  Plugin Name: SEO by SQUIRRLY
  Plugin URI: http://www.squirrly.co
  Description: SEO Plugin By Squirrly is for the NON-SEO experts. Get Excellent Seo with Better Content, Ranking and Analytics. For Both Humans and Search Bots.<BR> <a href="http://my.squirrly.co/user" target="_blank"><strong>Check your profile</strong></a>
  Author: calinvingan, florinmuresan, nagy.sorel
  Version: 6.2.5
  Author URI: http://www.squirrly.co
 */

/* SET THE CURRENT VERSION ABOVE AND BELOW */
define('SQ_VERSION', '6.2.5');
/* Call config files */
if (file_exists(dirname(__FILE__) . '/config/config.php')) {
    require(dirname(__FILE__) . '/config/config.php');

    /* important to check the PHP version */
    if (PHP_VERSION_ID >= 5100) {
        /* inport main classes */
        require_once(_SQ_CLASSES_DIR_ . 'SQ_ObjController.php');
        SQ_ObjController::getController('SQ_FrontController', false);
        SQ_ObjController::getController('SQ_Tools', false);

        if (is_admin()) {
            require_once(_SQ_CLASSES_DIR_ . 'SQ_BlockController.php');
            SQ_ObjController::getController('SQ_FrontController', false)->run();

            /**
             *  Upgrade Squirrly call.
             */
            register_activation_hook(__FILE__, array(SQ_ObjController::getController('SQ_Tools', false), 'sq_activate'));
            register_deactivation_hook(__FILE__, array(SQ_ObjController::getController('SQ_Tools', false), 'sq_deactivate'));
        } elseif (SQ_Tools::$options['sq_use'] == 1) {
            SQ_ObjController::getController('SQ_Frontend');
        }

        add_action('sq_processCron', array(SQ_ObjController::getController('SQ_Ranking', false), 'processCron'));
        add_action('sq_processApi', array(SQ_ObjController::getController('SQ_Post'), 'processCron'));
    } else {
        /* Main class call */
        add_action('admin_init', 'sq_phpError');
    }
}

/**
 * Show PHP Error message if PHP is lower the required
 */
function sq_phpError() {
    add_action('admin_notices', 'sq_showError');
}

/**
 * Called in Notice Hook
 */
function sq_showError() {
    echo '<div class="update-nag"><span style="color:red; font-weight:bold;">' . __('For Squirrly to work, the PHP version has to be equal or greater then 5.1', _SQ_PLUGIN_NAME_) . '</span></div>';
}
