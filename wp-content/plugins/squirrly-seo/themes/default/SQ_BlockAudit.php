<div id="sq_settings">
    <div class="sq_message sq_error" style="display: none"></div>
    <?php
    SQ_ObjController::getBlock('SQ_BlockSupport')->init();
    ?>
    <div>
        <span class="sq_icon"></span>
        <div id="sq_settings_title"><?php _e('Squirrly Site Audit', _SQ_PLUGIN_NAME_); ?> </div>

    </div>
    <div id="sq_left">
        <div id="sq_settings_body">
            <fieldset>
                <legend>
                    <span class="sq_legend_title"><?php _e('What the Audit does:', _SQ_PLUGIN_NAME_); ?></span>
                    <span><?php echo sprintf(__('%sTracks all the aspects of your Content Marketing Strategy%s: Blogging, Traffic, SEO, Social Signals, Links, Authority. Every single week you get a new report by email.', _SQ_PLUGIN_NAME_), '<strong>', '</strong>'); ?></span>
                    <span><?php echo sprintf(__('%sIt Gives You Professional Advice on How To Fix%s any of those areas that it helps track, so you can easily find out how to improve. Content from SEO Moz (recently just MOZ), Google, Authority Labs, etc.', _SQ_PLUGIN_NAME_), '<strong>', '</strong>'); ?></span>
                    <span><?php echo sprintf(__('%sMonitors Your Progress, week by week%s. You’ll get interesting data about the historical performance of each article you write and find out how to improve its seo ranking.', _SQ_PLUGIN_NAME_), '<strong>', '</strong>'); ?></span>
                    <span><?php echo sprintf(__('%sAnalyze any single article.%s See how it improves over time.', _SQ_PLUGIN_NAME_), '<strong>', '</strong>'); ?></span>
                    <span><?php echo sprintf(__('%sRequest an Audit Now%s', _SQ_PLUGIN_NAME_), '<a href="' . _SQ_DASH_URL_ . 'login/?token=' . SQ_Tools::$options['sq_api'] . '" target="_blank">', '</a>'); ?></span>

                </legend>

                <div>
                    <div id="sq_post_type_option" class="withborder" style="min-height: 520px;">
                        <p style="font-weight: bold;"><?php _e('Your last Site Audit', _SQ_PLUGIN_NAME_); ?>:</p>
                        <ul style="margin-top: 50px;">
                            <li>
                                <?php if (isset($view->blog->score) && $view->blog->score > 0){ ?>
                                    <p class="sq_audit_score"><?php echo __('Score', _SQ_PLUGIN_NAME_) . ': <span>' . $view->blog->score . '/100</span>'; ?></p>
                                    <p class="sq_audit_date"><?php echo __('Date', _SQ_PLUGIN_NAME_) . ': <span>' . date(get_option( 'date_format' ),strtotime($view->blog->datetime)) . '</span>'; ?></p>
                                    <p class="sq_settings_bigbutton" style="margin-bottom:35px;">
                                        <a href="<?php echo  _SQ_DASH_URL_ . 'login/?token=' . SQ_Tools::$options['sq_api']  ?>" target="_blank" ><?php _e('See the Audit', _SQ_PLUGIN_NAME_) ?> &raquo;</a>
                                    </p>
                                <?php }else{?>
                                    <p><?php _e('Seems that no Audit was made yet. You can request a new audit and it should be ready in 5-10 minutes', _SQ_PLUGIN_NAME_); ?>:</p>
                                    <p class="sq_settings_bigbutton" style="margin-bottom:35px;">
                                        <a href="<?php echo  _SQ_DASH_URL_ . 'login/?token=' . SQ_Tools::$options['sq_api']  ?>" target="_blank" ><?php _e('Request an Audit Now', _SQ_PLUGIN_NAME_) ?> &raquo;</a>
                                    </p>
                                <?php }?>
                            </li>
                            <?php if (isset($view->blog->score) && $view->blog->score == 0){ ?>
                            <li>
                                <p>
                                    <?php _e('This is an example of a Site Audit', _SQ_PLUGIN_NAME_); ?>:
                                </p>
                                <p>
                                    <a href="<?php echo  _SQ_DASH_URL_ . 'login/?token=' . SQ_Tools::$options['sq_api']  ?>" target="_blank" >
                                        <img src="https://ps.w.org/squirrly-seo/trunk/screenshot-7.png" alt="" style="max-width: 520px">
                                    </a>
                                </p>
                            </li>
                            <?php }?>
                        </ul>
                    </div>

                </div>
            </fieldset>

        </div>
    </div>

</div>
