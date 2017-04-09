<div id="sq_settings">
    <div class="sq_message sq_error" style="display: none"></div>
    <div>
        <span class="sq_icon"></span>
        <div id="sq_settings_title"><?php _e('Squirrly Customer Service', _SQ_PLUGIN_NAME_); ?> </div>

    </div>
    <div id="sq_left">
        <div id="sq_settings_body">
            <fieldset>
                <legend>
                    <span class="sq_legend_title"><?php _e('Support Channels', _SQ_PLUGIN_NAME_); ?></span>
                    <span><?php echo sprintf(__('%sHowto.squirrly.co%s > Knowledge Base. Find answers to your questions', _SQ_PLUGIN_NAME_), '<a href="https://howto.squirrly.co/wordpress-seo" target="_blank">', '</a>'); ?></span>
                    <span><?php echo sprintf(__('%sPlugin.squirrly.co%s >> case studies, ideas on how to better use Squirrly SEO for Content Marketing', _SQ_PLUGIN_NAME_), '<a href="https://plugin.squirrly.co" target="_blank">', '</a>'); ?></span>
                    <span><?php echo sprintf(__('%sFacebook page%s >> we reply to the messages we receive there', _SQ_PLUGIN_NAME_), '<a href="https://www.facebook.com/Squirrly.co" target="_blank">', '</a>'); ?></span>
                    <span><?php echo sprintf(__('%sSupport Community%s >> on Google Plus', _SQ_PLUGIN_NAME_), '<a href="https://plus.google.com/communities/104196720668136264985" target="_blank">', '</a>'); ?></span>
                    <span><?php echo sprintf(__('%sLive Chat%s >> on Youtube. Thursday 4 PM', _SQ_PLUGIN_NAME_), '<a href="https://www.youtube.com/c/GetGrowthTV/live" target="_blank">', '</a>'); ?></span>
                    <span><?php echo sprintf(__('%sNew Lessons%s >> Mon. and Tue. on Twitter', _SQ_PLUGIN_NAME_), '<a href="https://twitter.com/squirrlyhq" target="_blank">', '</a>'); ?></span>
                    <span><?php echo sprintf(__('%sEmail Support%s >> 10 AM to 4 PM (London Time): Mon-Fri.', _SQ_PLUGIN_NAME_), '<a href="mailto:' . _SQ_SUPPORT_EMAIL_ . '" target="_blank">', '</a>'); ?></span>

                </legend>

                <div>
                    <div id="sq_post_type_option" class="withborder">
                        <p style="font-weight: bold;"><?php _e('Need Help with Squirrly SEO?', _SQ_PLUGIN_NAME_); ?>:</p>
                        <ul class="sq_options_support_popup">
                            <li>
                                <div class="withborder">
                                    <p id="sq_support_msg">
                                        <textarea class="sq_big_input" name="sq_support_message"></textarea></p>
                                    <div id="sq_options_support_error"></div>
                                    <p>
                                        <input id="sq_support_submit" type="button" value="<?php _e('Send Question', _SQ_PLUGIN_NAME_) ?>" style="padding: 9px 30px;background-color: #20bc49;color: white;text-shadow: 1px 1px #333;font-size: 14px;    cursor: pointer;">
                                    </p>
                                </div>
                            </li>
                            <li>
                                <div class="withborder">
                                    <p>
                                        <strong>Find out who we are, Contact our Squirrly team and See Our Company Details</strong>
                                    </p>
                                    <a href="https://www.squirrly.co/more" target="_blank"><img src="<?php echo _SQ_THEME_URL_ . 'img/settings/team.png' ?>" alt="Squirrly Team" style="max-width: 520px;"/></a>
                                </div>
                            </li>

                            <li>
                                <div class="withborder">
                                    <p><strong>Squirrly is registered in the UK as:</strong></p>

                                    <p>Squirrly Limited</p>
                                    <p>Company registration number: <strong>08198658</strong></p>
                                    <p>Incorporation Date: <strong>03 Sept 2012</strong></p>
                                </div>
                                <div class="withborder">
                                    <p><strong>Registered Address for UK:</strong></p>
                                    <p>20-22 Wenlock Road</p>
                                    <p>London, N1 7GU</p>
                                    <p>England</p>
                                </div>
                            </li>

                        </ul>
                    </div>

                </div>
            </fieldset>

        </div>
    </div>

</div>
