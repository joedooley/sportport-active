<div id="sq_settings">
    <div class="sq_message sq_error" style="display: none"></div>

    <?php
    SQ_ObjController::getBlock('SQ_BlockSupport')->init();
    ?>
    <div>
        <span class="sq_icon"></span>
        <div id="sq_settings_title"><?php _e('Squirrly Live Assistant', _SQ_PLUGIN_NAME_); ?> </div>
        <div class="sq_subtitles">
            <p>Using the Live Assistant from Squirrly SEO is like having a consultant sitting right next to you and helping you get a 100% optimized page. For both Humans and Search Engine bots.</p>
        </div>
    </div>
    <div id="sq_helpliveassistantside" class="sq_helpside"></div>
    <div id="sq_left">
        <div id="sq_settings_body">

            <fieldset style="background: none !important; box-shadow: none;">
                <div class="sq_subtitles">
                    <div class="sq_button"><a href="post-new.php" target="_blank" style="margin: 10px; font-size: 15px; max-width: 210px;"><?php _e('Use Squirrly Live Assistant',_SQ_PLUGIN_NAME_) ?></a></div>

                    <p>You just have to type in the keyword you want the page to be optimized for.</p>
                    <p><img src="<?php echo _SQ_THEME_URL_ . 'img/help/live_assistant1.png' ?>" alt=""></p>
                    <p>After that, the Live Assistant guides you through the steps you need to take to fully optimize the page.</p>
                    <p><img src="<?php echo _SQ_THEME_URL_ . 'img/help/live_assistant2.png' ?>" alt=""></p>
                    <p><img src="<?php echo _SQ_THEME_URL_ . 'img/help/live_assistant3.png' ?>" alt=""></p>

                    <p>When all those lights turn green, it means you have an excellent SEO article, page or product.</p>
                    <p><a href="post-new.php" target="_blank" style="margin-top: 10px; font-size: 15px; max-width: 210px;"><img src="<?php echo _SQ_THEME_URL_ . 'img/help/live_assistant4.png' ?>" alt=""></a></p>
                    <div class="sq_button"><a href="post-new.php" target="_blank" style="margin-top: 10px; font-size: 15px; max-width: 210px;"><?php _e('Use Squirrly Live Assistant',_SQ_PLUGIN_NAME_) ?></a></div>
                </div>
            </fieldset>
        </div>
    </div>

</div>
