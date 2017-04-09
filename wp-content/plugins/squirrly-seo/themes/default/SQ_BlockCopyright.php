<div id="sq_settings">
    <div class="sq_message sq_error" style="display: none"></div>

    <?php
    SQ_ObjController::getBlock('SQ_BlockSupport')->init();
    ?>
    <div>
        <span class="sq_icon"></span>
        <div id="sq_settings_title"><?php _e('Squirrly Copywriting Options', _SQ_PLUGIN_NAME_); ?> </div>
        <div class="sq_subtitles">
            <p><?php _e('We help you find copyright-free images, news sources, awesome tweets by influential people, all of which you can use to support the points you are making in your blog posts.', _SQ_PLUGIN_NAME_); ?></p>
        </div>
    </div>
    <div id="sq_helpcopyrightside" class="sq_helpside"></div>
    <div id="sq_left">
        <div id="sq_settings_body">

            <fieldset style="background: none !important; box-shadow: none;">
                <div class="sq_subtitles">
                    <p><?php _e('The inspiration Box from Squirrly helps you save time on the research you do for each article.', _SQ_PLUGIN_NAME_); ?></p>
                    <p><img src="<?php echo _SQ_THEME_URL_ . 'img/help/copyright_options1.png' ?>" alt=""></p>
                    <p><img src="<?php echo _SQ_THEME_URL_ . 'img/help/copyright_options2.png' ?>" alt=""></p>
                    <p><?php _e("Once you found the image you're looking for, click on it and it will be added in your article content", _SQ_PLUGIN_NAME_); ?></p>
                    <p><img src="<?php echo _SQ_THEME_URL_ . 'img/help/copyright_options3.png' ?>" alt=""></p>
                    <div class="sq_button"><a href="post-new.php" target="_blank" style="margin-top: 10px; font-size: 15px; max-width: 210px;"><?php _e("Use Squirrly's Inspiration box",_SQ_PLUGIN_NAME_) ?></a></div>
                </div>
            </fieldset>
        </div>
    </div>

</div>
