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
    }

}
