<?php

class SQ_Loading extends SQ_BlockController {

    public function hookHead() {
        global $sq_postID;

        parent::hookHead();
        $exists = false;
        $browser = false;



        /* Check the squirrly.js file if exists */
        $browser = SQ_Tools::getBrowserInfo();

        if ((isset($browser) && $browser != false && is_array($browser) && $browser['name'] == 'IE' && (int) $browser['version'] < 9 && (int) $browser['version'] > 0)) {
            echo '<script type="text/javascript">
                    jQuery("#sq_preloading").removeClass("sq_loading");
                    jQuery("#sq_preloading").addClass("sq_error")
                    jQuery("#sq_preloading").html("' . __('For Squirrly to work properly you have to use a higher version of Internet Explorer. <br /> We recommend you to use Chrome or Mozilla.', _SQ_PLUGIN_NAME_) . '");
                    jQuery("#sq_options").hide();
                    jQuery("#sq_blocklogin").hide();
                  </script>';
        } else {
            $keyword = SQ_ObjController::getModel('SQ_Post')->getKeywordsFromPost($sq_postID);
            echo '<script type="text/javascript">
                    var sq_use = "' . SQ_Tools::$options['sq_use'] . '";
                    var sq_baseurl = "' . _SQ_STATIC_API_URL_ . '";
                    var sq_uri = "' . SQ_URI . '"; var sq_language = "' . get_bloginfo('language') . '";
                    var sq_version = "' . SQ_VERSION_ID . '";  var sq_wpversion = "' . WP_VERSION_ID . '";  var sq_phpversion = "' . PHP_VERSION_ID . '"; var sq_seoversion = "' . (SQ_Tools::$options['sq_sla'] + 1) . '";
                    var __postID = "' . $sq_postID . '";
                    var __prevNonce = "' . wp_create_nonce('post_preview_' . $sq_postID) . '";
                    var __token = "' . SQ_Tools::$options['sq_api'] . '";
                    var sq_keyword_information = "' . ((isset(SQ_Tools::$options['sq_keyword_information'])) ? SQ_Tools::$options['sq_keyword_information'] : '0') . '";
                    var __noopt = "' . __('You haven`t used Squirrly SEO to optimize your article. Do you want to optimize for a keyword before publishing?', _SQ_PLUGIN_NAME_) . '";
                    var sq_keywordtag = "' . SQ_Tools::$options['sq_keywordtag'] . '";
                    var sq_frontend_css = "' . _SQ_THEME_URL_ . 'css/sq_frontend.css";
                    ' . (($keyword <> '') ? 'var sq_keyword_from_post = "' . $keyword . '";' : '') . '
                    if (typeof sq_script === "undefined"){
                        var sq_script = document.createElement(\'script\');
                        sq_script.src = "' . _SQ_STATIC_API_URL_ . SQ_URI . '/js/squirrly.js?ver=' . SQ_VERSION_ID . '2";
                        var site_head = document.getElementsByTagName ("head")[0] || document.documentElement;
                        site_head.insertBefore(sq_script, site_head.firstChild);
                    }
                    jQuery(document).ready(function() {
                        jQuery("#sq_preloading").html("");
                    });
                  </script>';
        }
    }

}
