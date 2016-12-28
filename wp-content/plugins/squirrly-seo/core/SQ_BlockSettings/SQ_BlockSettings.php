<?php

/**
 * Account settings
 */
class SQ_BlockSettings extends SQ_BlockController {

    function hookGetContent() {
        parent::preloadSettings();
        SQ_ObjController::getController('SQ_Error', false)->hookNotices();
        echo '<script type="text/javascript">
                    jQuery(document).ready(function () {
                        jQuery("#sq_settings").find("select[name=sq_google_country]").val("' . SQ_Tools::$options['sq_google_country'] . '");
                    });
             </script>';
    }

    /**
     * Called when Post action is triggered
     *
     * @return void
     */
    public function action() {
        parent::action();



        switch (SQ_Tools::getValue('action')) {

            case 'sq_settings_update':
                if (!current_user_can('manage_options')){
                    return;
                }

                if (SQ_Tools::getIsset('sq_post_types')) {
                    SQ_Tools::$options['sq_post_types'] = array();
                    foreach (SQ_Tools::getValue('sq_post_types') as $key) {
                        array_push(SQ_Tools::$options['sq_post_types'], $key);
                    }

                    if (!in_array('product', get_post_types())) {
                        array_push(SQ_Tools::$options['sq_post_types'], 'product');
                    }
                }

                SQ_Tools::saveOptions('sq_google_country', SQ_Tools::getValue('sq_google_country'));
                SQ_Tools::saveOptions('sq_google_country_strict', SQ_Tools::getValue('sq_google_country_strict'));
                SQ_Tools::saveOptions('sq_google_ranksperhour', SQ_Tools::getValue('sq_google_ranksperhour'));

                SQ_Tools::saveOptions('sq_keyword_help', (int) SQ_Tools::getValue('sq_keyword_help'));
                SQ_Tools::saveOptions('sq_keyword_information', (int) SQ_Tools::getValue('sq_keyword_information'));
                SQ_Tools::saveOptions('sq_sla', (int) SQ_Tools::getValue('sq_sla'));
                SQ_Tools::saveOptions('sq_keywordtag', (int) SQ_Tools::getValue('sq_keywordtag'));
                SQ_Tools::saveOptions('sq_local_images', (int) SQ_Tools::getValue('sq_local_images'));


                SQ_Tools::saveOptions('sq_google_wt', SQ_ObjController::getModel('SQ_BlockSettingsSeo')->checkGoogleWTCode(SQ_Tools::getValue('sq_google_wt','',true)));
                SQ_Tools::saveOptions('sq_bing_wt', SQ_ObjController::getModel('SQ_BlockSettingsSeo')->checkBingWTCode(SQ_Tools::getValue('sq_bing_wt','',true)));
                SQ_Tools::saveOptions('sq_alexa', SQ_ObjController::getModel('SQ_BlockSettingsSeo')->checkBingWTCode(SQ_Tools::getValue('sq_alexa','',true)));


                SQ_Action::apiCall('sq/user/settings', array('settings' => json_encode(SQ_Tools::getBriefOptions())), 10);
                SQ_Tools::emptyCache();
                break;
        }
    }

}
