<div id="sq_settings" >
    <?php SQ_ObjController::getBlock('SQ_BlockSupport')->init(); ?>
    <div>
        <span class="sq_icon"></span>
        <div id="sq_settings_title" ><?php _e('Join Squirrly today!', _SQ_PLUGIN_NAME_); ?> </div>
        <div id="sq_settings_title" >
            <input id="sq_goto_dashboard" type="button" value="<?php _e('Go to dashboard', _SQ_PLUGIN_NAME_) ?> &raquo;" />
        </div>
    </div>
    <div id="sq_helpaffiliateside" class="sq_helpside"></div>
    <div id="sq_left">
        <form id="sq_settings_affiliate_form" name="settings" action="" method="post" enctype="multipart/form-data">
            <div id="sq_settings_body">

                <fieldset>
                    <legend>
                        <span class="sq_legend_title"><?php _e('Join Squirrly today!', _SQ_PLUGIN_NAME_); ?></span>
                        <span><?php echo sprintf(__('%sHow I Started Making Money With the Squirrly Affiliate Program%s', _SQ_PLUGIN_NAME_), '<a href="https://plugin.squirrly.co/wp-content/uploads/Squirrly-Affiliate-Program.pdf" target="_blank">', '</a>'); ?></span>

                        <span><p class="sq_settings_affiliate_bigtitle">
                                <?php _e('Affiliate Benefits', _SQ_PLUGIN_NAME_); ?>
                            </p>
                            <ul class="sq_settings_affiliate_info">
                                <li>
                                    <div>
                                        <span><?php echo sprintf(__('- Recurring 45%s commission', _SQ_PLUGIN_NAME_), '%'); ?></span>
                                    </div>
                                </li>
                                <li>
                                    <div>
                                        <span><?php _e('- No cost', _SQ_PLUGIN_NAME_); ?></span>
                                    </div>
                                </li>
                                <li>
                                    <div>
                                        <span><?php _e('- Monthly payments in your Paypal account', _SQ_PLUGIN_NAME_); ?></span>
                                    </div>
                                </li>
                            </ul></span>
                    </legend>
                    <div>
                        <p class="sq_settings_affiliate_bigbutton" style="margin-bottom:35px;">
                            <?php
                            if (SQ_Tools::$options['sq_api'] <> '') {
                                if (SQ_Tools::$options['sq_affiliate_link'] <> '') {
                                    echo '<span>' . SQ_Tools::$options['sq_affiliate_link'] . '</span>';
                                    echo '<span class="sq_settings_info">' . __('To redirect users to your site, just change "squirrly.co" with your domain.', _SQ_PLUGIN_NAME_) . '</span>';
                                } else {
                                    ?><input type="submit" name="sq_affiliate_link" value="<?php _e('Generate affiliate link', _SQ_PLUGIN_NAME_) ?> &raquo;" /><?php
                                }
                            }
                            ?>
                        </p>

                        <?php
                        if (SQ_Tools::$options['sq_api'] <> '') {
                            if (SQ_Tools::$options['sq_affiliate_link'] <> '') {
                                echo __('Your affiliate account is set and ready to go. Above you have the affiliate link. ', _SQ_PLUGIN_NAME_);
                                echo '<br />';
                                echo sprintf(__('Check your affiliate page: %sAffiliate page%s', _SQ_PLUGIN_NAME_), '<a href="' . _SQ_DASH_URL_ . 'login/?token=' . SQ_Tools::$options['sq_api'] . '&redirect_to=' . _SQ_DASH_URL_ . 'user/affiliate' . '" target="_blank" style="font-weight:bold">', '</a>');
                            }
                        } else {
                            echo __('After you connect to Squirrly you can begin to use your free Squirrly affiliate link immediately!', _SQ_PLUGIN_NAME_);
                        }
                        ?>
                    </div>

                </fieldset>
                <?php if (SQ_Tools::$options['sq_affiliate_link'] <> '') { ?>
                    <fieldset>
                        <legend>
                            <span class="sq_legend_title"><?php _e('Squirrly banners you can use', _SQ_PLUGIN_NAME_); ?></span>
                        </legend>
                        <div>
                            <ul class="sq_settings_affiliate_info">
                                <?php
                                $sq_affiliate_images[] = _SQ_THEME_URL_ . 'img/banners/banner1.jpg';
                                $sq_affiliate_images[] = _SQ_THEME_URL_ . 'img/banners/banner1.png';
                                $sq_affiliate_images[] = _SQ_THEME_URL_ . 'img/banners/banner2.jpg';
                                $sq_affiliate_images[] = _SQ_THEME_URL_ . 'img/banners/banner2.png';

                                foreach ($sq_affiliate_images as $sq_affiliate_image) {
                                    echo '<li><a href="' . SQ_Tools::$options['sq_affiliate_link'] . '" target="_blank"><img src="' . $sq_affiliate_image . '" alt="Seo Plugin by Squirrly" /></a>';
                                    echo '<span class="sq_affiliate_banner" >';
                                    echo '<textarea style="width: 500px; height: 45px;" onclick="this.focus(); this.select();"><a href="' . SQ_Tools::$options['sq_affiliate_link'] . '" target="_blank" title="Seo Plugin by Squirrly"><img src="' . $sq_affiliate_image . '" /></a></textarea>';
                                    echo '</span></li>';
                                }
                                ?>
                            </ul>
                        </div>

                    </fieldset>
                <?php } ?>
            </div>
            <br style="clear: both;"/>
            <div id="sq_settings_title" style="text-align: right">
                <a href="?page=sq_dashboard" id="sq_goto_newpost" style="display:none" /><?php _e('<< START HERE >>', _SQ_PLUGIN_NAME_) ?></a>
            </div>
            <input type="hidden" name="action" value="sq_settings_affiliate" />
            <input type="hidden" name="nonce" value="<?php echo wp_create_nonce(_SQ_NONCE_ID_); ?>" />
        </form>
    </div>
</div>