<div class="sq_box" style="display: none">
    <div id="sq_blockseo" style="display: none">
        <div class="sq_header"><?php _e('Squirrly SEO Live Assistant', _SQ_PLUGIN_NAME_); ?></div>
        <div class="sq_tasks"></div>
    </div>
</div>
<div id="sq_canonical" style="display: none">
     <div class="sq_header"><?php _e('Canonical link: ', _SQ_PLUGIN_NAME_); ?></div>
     <div class="sq_canonical_input"><input type="text" name="sq_canonical" size="30" value="<?php global $sq_postID; echo SQ_ObjController::getModel('SQ_Frontend')->getAdvancedMeta($sq_postID, 'canonical') ?>" /></div>
     <div class="sq_information"><em><?php _e('(only for external sources)', _SQ_PLUGIN_NAME_); ?></em></div>
</div>