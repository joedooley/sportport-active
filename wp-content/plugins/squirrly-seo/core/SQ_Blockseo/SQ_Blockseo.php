<?php

class SQ_Blockseo extends SQ_BlockController {

    public function action() {

    }

    public function hookHead() {
        parent::hookHead();
        $metas = array();
        $metas = $this->model->getAdvSeo();

        echo '<script type="text/javascript">
             var __snippetsavechanges = "' . __('Save changes', _SQ_PLUGIN_NAME_) . '";
             var __snippetsavecancel = "' . __('Cancel', _SQ_PLUGIN_NAME_) . '";
             var __snippetreset = "' . __('Reset', _SQ_PLUGIN_NAME_) . '";

             var __snippetcustomize = "' . __('Customize Meta', _SQ_PLUGIN_NAME_) . '";
             var __snippetkeyword = "' . __('manage keywords', _SQ_PLUGIN_NAME_) . '";
             var __snippetshort = "' . __('Too short', _SQ_PLUGIN_NAME_) . '";
             var __snippetlong = "' . __('Too long', _SQ_PLUGIN_NAME_) . '";

             var __snippetname = "' . __('Squirrly Snippet', _SQ_PLUGIN_NAME_) . '";
             var __snippetrefresh = "' . __('Update', _SQ_PLUGIN_NAME_) . '";
             var __sq_snippet = "' . __('snippet', _SQ_PLUGIN_NAME_) . '";

             var __snippetclickrefresh = "' . __('Click the Update button (to the right) to see the snippet from your website.', _SQ_PLUGIN_NAME_) . '";
             var __snippetentertitle = "' . __('Enter a title above for the snippet to get data.', _SQ_PLUGIN_NAME_) . '";' . "\n";

        if (is_array($metas)) {
            foreach ($metas as $key => $meta) {
                echo 'var _' . $key . ' = "' . str_replace('"', '\"', $meta) . '";' . "\n";
            }
        }

        echo '</script>';
    }

}
