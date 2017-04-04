<?php

/**
 * Core class for SQ_Blocksearch
 */
class SQ_Blockresearch extends SQ_BlockController {

    public function hookHead() {
        parent::hookHead();


        echo '<script type="text/javascript">
                var __infotext = ["' . __('Recent discussions:', _SQ_PLUGIN_NAME_) . '", "' . __('Exact search:', _SQ_PLUGIN_NAME_) . '", "' . __('Competition:', _SQ_PLUGIN_NAME_) . '", "' . __('Trend:', _SQ_PLUGIN_NAME_) . '"];
              
              </script>';
    }

}
