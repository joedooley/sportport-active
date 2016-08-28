<?php

/**
 * Core class for SQ_Blocksearch
 */
class SQ_Blocksearch extends SQ_BlockController {

    public function action() {
        $start = 0;
        $nbr = 8;
        $exclude = array();

        parent::action();
        switch (SQ_Tools::getValue('action')) {
            case 'sq_type_click':
                SQ_Tools::saveOptions('sq_img_licence', SQ_Tools::getValue('licence'));
                exit();
                break;
            case 'sq_search_blog':
                if (SQ_Tools::getValue('exclude') && SQ_Tools::getValue('exclude') <> 'undefined')
                    $exclude = array((int) SQ_Tools::getValue('exclude'));

                if (SQ_Tools::getValue('start'))
                    $start = array((int) SQ_Tools::getValue('start'));

                if (SQ_Tools::getValue('nrb'))
                    $nrb = (int) SQ_Tools::getValue('nrb');

                if (SQ_Tools::getValue('q') <> '')
                    echo SQ_ObjController::getController('SQ_Post')->model->searchPost(SQ_Tools::getValue('q'), $exclude, (int) $start, (int) $nrb);
                break;
        }

        exit();
    }

    public function hookHead() {
        parent::hookHead();
        echo '<script type="text/javascript">
             var __date = "' . __('date', _SQ_PLUGIN_NAME_) . '"; var __readit = "' . __('Read it!', _SQ_PLUGIN_NAME_) . '"; var __insertit = "' . __('Insert it!', _SQ_PLUGIN_NAME_) . '"; var __reference = "' . __('Reference', _SQ_PLUGIN_NAME_) . '"; var __insertasbox = "' . __('Insert as box', _SQ_PLUGIN_NAME_) . '"; var __notrelevant = "' . __('Not relevant?', _SQ_PLUGIN_NAME_) . '"; var __insertparagraph = "' . __('Insert in your article', _SQ_PLUGIN_NAME_) . '"; var __tinymceerror = "' . __('For Squirrly to work, you have to have tinymce editor installed!', _SQ_PLUGIN_NAME_) . '"; var __ajaxerror = "' . __(':( I lost my squirrel. Please reload the page.', _SQ_PLUGIN_NAME_) . '"; var __nofound = "' . __('No results found!', _SQ_PLUGIN_NAME_) . '"; var __tinymceinactive = "' . __('Switch to Visual editor!', _SQ_PLUGIN_NAME_) . '"; var __morewords = "' . __('Enter one more word to find relevant results', _SQ_PLUGIN_NAME_) . '"; var __toolong = "' . __('Takes too long to check this keyword ...', _SQ_PLUGIN_NAME_) . '"; var __doresearch = "' . __('Do a research!', _SQ_PLUGIN_NAME_) . '"; var __morekeywords = "' . __('Do more research!', _SQ_PLUGIN_NAME_) . '"; var __sq_photo_copyright = "' . __('[ ATTRIBUTE: Please check: %s to find out how to attribute this image ]', _SQ_PLUGIN_NAME_) . '"; var __has_attributes = "' . __('Has creative commons attributes', _SQ_PLUGIN_NAME_) . '"; var __no_attributes = "' . __('No known copyright restrictions', _SQ_PLUGIN_NAME_) . '";
           </script>';
    }

}
