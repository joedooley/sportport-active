<div id="sq_settings">
    <?php SQ_ObjController::getBlock('SQ_BlockSupport')->init(); ?>
    <div>
        <span class="sq_icon"></span>

        <div id="sq_settings_title"><?php _e('Settings', _SQ_PLUGIN_NAME_); ?> </div>
        <div id="sq_settings_title">
            <input type="submit" name="sq_update" value="<?php _e('Save settings', _SQ_PLUGIN_NAME_) ?> &raquo;"/>
        </div>
    </div>
    <div id="sq_helpsettingscontent" class="sq_helpcontent"></div>
    <div id="sq_helpsettingsside" class="sq_helpside"></div>
    <div id="sq_left">
        <form id="sq_settings_form" name="settings" action="" method="post" enctype="multipart/form-data">
            <div id="sq_settings_body">
                <fieldset>
                    <legend>
                        <span class="sq_legend_title"><?php _e('Post/Page Edit', _SQ_PLUGIN_NAME_); ?></span>
                        <span><?php echo sprintf(__('%sThe right method in working with Squirrly, SEO plugin%s', _SQ_PLUGIN_NAME_), '<a href="http://www.squirrly.co/the-right-premises-in-working-with-squirrly-wordpress-seo-plugin" target="_blank">', '</a>'); ?></span>
                        <span><?php echo sprintf(__('%sGetting inspired with Squirrly WordPress SEO plugin%s', _SQ_PLUGIN_NAME_), '<a href="http://www.squirrly.co/getting-inspired-with-squirrly-wordpress-seo-plugin" target="_blank">', '</a>'); ?></span>

                        <span><?php echo sprintf(__('%sThere is a New SEO Live Assistant from Squirrly%s', _SQ_PLUGIN_NAME_), '<a href="http://www.squirrly.co/there-is-a-new-seo-live-assistant-from-squirrly" target="_blank">', '</a>'); ?></span>
                        <span><?php echo sprintf(__('%sHow to create Human friendly content with the WordPress SEO plugin?%s', _SQ_PLUGIN_NAME_), '<a href="http://www.squirrly.co/how-to-create-human-friendly-content-with-the-a-wordpress-seo-plugin" target="_blank">', '</a>'); ?></span>

                    </legend>

                    <div>
                        <div id="sq_post_type_option" class="withborder">
                            <p style="font-weight: bold;"><?php _e('Load Squirrly Live Assistant for', _SQ_PLUGIN_NAME_); ?>:</p>
                            <ul id="sq_post_types">
                                <li class="sq_selectall"><input type="checkbox" id="sq_selectall"/>Select All</li>
                                <li>
                                    <input type="checkbox" class="sq_post_types" name="sq_post_types[]" value="post" <?php echo(in_array('post', SQ_Tools::$options['sq_post_types']) ? 'checked="checked"' : ''); ?>><?php _e('Posts', _SQ_PLUGIN_NAME_); ?>
                                </li>
                                <li>
                                    <input type="checkbox" class="sq_post_types" name="sq_post_types[]" value="page" <?php echo(in_array('page', SQ_Tools::$options['sq_post_types']) ? 'checked="checked"' : ''); ?>><?php _e('Pages', _SQ_PLUGIN_NAME_); ?>
                                </li>
                                <?php if (in_array('product', get_post_types())) { //check for ecommerce product ?>
                                    <li>
                                        <input type="checkbox" class="sq_post_types" name="sq_post_types[]" value="product" <?php echo(in_array('product', SQ_Tools::$options['sq_post_types']) ? 'checked="checked"' : ''); ?>><?php _e('Products', _SQ_PLUGIN_NAME_); ?>
                                    </li>
                                <?php } ?>
                                <?php
                                $types = get_post_types();
                                foreach (array('post', 'page', 'revision', 'nav_menu_item', 'product', 'shop_order', 'shop_coupon') as $exclude) {
                                    if (in_array($exclude, $types)) {
                                        unset($types[$exclude]);
                                    }
                                }
                                foreach ($types as $type) {
                                    $type_data = get_post_type_object($type);
                                    if (!isset($type_data->show_ui) || $type_data->show_ui != 1) {
                                        unset($types[$type]);
                                    } else {
                                        //echo '<pre>' . print_r($type_data, true) . '</pre>';
                                        ?>
                                        <li>
                                            <input type="checkbox" class="sq_post_types" name="sq_post_types[]" value="<?php echo $type ?>" <?php echo(in_array($type, SQ_Tools::$options['sq_post_types']) ? 'checked="checked"' : ''); ?>><?php echo $type_data->labels->name; ?>
                                        </li>
                                        <?php
                                    }
                                }
                                ?>
                            </ul>
                        </div>
                        <br/>

                        <div class="sq_option_content">
                            <div class="sq_switch">
                                <input id="sq_keyword_help1" type="radio" class="sq_switch-input" name="sq_keyword_help" value="1" <?php echo((SQ_Tools::$options['sq_keyword_help'] == 1) ? "checked" : '') ?> />
                                <label for="sq_keyword_help1" class="sq_switch-label sq_switch-label-off"><?php _e('Yes', _SQ_PLUGIN_NAME_); ?></label>
                                <input id="sq_keyword_help0" type="radio" class="sq_switch-input" name="sq_keyword_help" value="0" <?php echo((SQ_Tools::$options['sq_keyword_help'] == 0) ? "checked" : '') ?> />
                                <label for="sq_keyword_help0" class="sq_switch-label sq_switch-label-on"><?php _e('No', _SQ_PLUGIN_NAME_); ?></label>
                                <span class="sq_switch-selection"></span>
                            </div>
                            <span><?php _e('Show <strong>Squirrly Tooltips</strong> posting a new article (e.g. "Enter a keyword").', _SQ_PLUGIN_NAME_); ?></span>
                        </div>

                        <div class="sq_option_content">
                            <div class="sq_switch">
                                <input id="sq_keyword_information1" type="radio" class="sq_switch-input" name="sq_keyword_information" value="1" <?php echo((SQ_Tools::$options['sq_keyword_information'] == 1) ? "checked" : '') ?> />
                                <label for="sq_keyword_information1" class="sq_switch-label sq_switch-label-off"><?php _e('Yes', _SQ_PLUGIN_NAME_); ?></label>
                                <input id="sq_keyword_information0" type="radio" class="sq_switch-input" name="sq_keyword_information" value="0" <?php echo((SQ_Tools::$options['sq_keyword_information'] == 0) ? "checked" : '') ?> />
                                <label for="sq_keyword_information0" class="sq_switch-label sq_switch-label-on"><?php _e('No', _SQ_PLUGIN_NAME_); ?></label>
                                <span class="sq_switch-selection"></span>
                            </div>
                            <span><?php _e('Always show <strong>Keyword Research</strong> about the selected keyword.', _SQ_PLUGIN_NAME_); ?></span>
                        </div>


                        <div class="sq_option_content">
                            <div class="sq_switch">
                                <input id="sq_sla1" type="radio" class="sq_switch-input" name="sq_sla" value="1" <?php echo((SQ_Tools::$options['sq_sla'] == 1) ? "checked" : '') ?> />
                                <label for="sq_sla1" class="sq_switch-label sq_switch-label-off"><?php _e('Yes', _SQ_PLUGIN_NAME_); ?></label>
                                <input id="sq_sla0" type="radio" class="sq_switch-input" name="sq_sla" value="0" <?php echo((SQ_Tools::$options['sq_sla'] == 0) ? "checked" : '') ?> />
                                <label for="sq_sla0" class="sq_switch-label sq_switch-label-on"><?php _e('No', _SQ_PLUGIN_NAME_); ?></label>
                                <span class="sq_switch-selection"></span>
                            </div>
                            <span><?php _e('Use <strong> the NEW version of the SEO Live Assistant</strong>.', _SQ_PLUGIN_NAME_); ?></span>
                        </div>
                        <p></p>

                        <div class="sq_option_content withbordertop">
                            <div class="sq_switch">
                                <input id="sq_keywordtag1" type="radio" class="sq_switch-input" name="sq_keywordtag" value="1" <?php echo((SQ_Tools::$options['sq_keywordtag'] == 1) ? "checked" : '') ?> />
                                <label for="sq_keywordtag1" class="sq_switch-label sq_switch-label-off"><?php _e('Yes', _SQ_PLUGIN_NAME_); ?></label>
                                <input id="sq_keywordtag0" type="radio" class="sq_switch-input" name="sq_keywordtag" value="0" <?php echo((SQ_Tools::$options['sq_keywordtag'] == 0) ? "checked" : '') ?> />
                                <label for="sq_keywordtag0" class="sq_switch-label sq_switch-label-on"><?php _e('No', _SQ_PLUGIN_NAME_); ?></label>
                                <span class="sq_switch-selection"></span>
                            </div>
                            <span><?php _e('Add the Post tags in <strong>META keyword</strong>.', _SQ_PLUGIN_NAME_); ?></span>
                        </div>

                        <div class="sq_option_content">
                            <div class="sq_switch">
                                <input id="sq_local_images1" type="radio" class="sq_switch-input" name="sq_local_images" value="1" <?php echo((SQ_Tools::$options['sq_local_images'] == 1) ? "checked" : '') ?> />
                                <label for="sq_local_images1" class="sq_switch-label sq_switch-label-off"><?php _e('Yes', _SQ_PLUGIN_NAME_); ?></label>
                                <input id="sq_local_images0" type="radio" class="sq_switch-input" name="sq_local_images" value="0" <?php echo((SQ_Tools::$options['sq_local_images'] == 0) ? "checked" : '') ?> />
                                <label for="sq_local_images0" class="sq_switch-label sq_switch-label-on"><?php _e('No', _SQ_PLUGIN_NAME_); ?></label>
                                <span class="sq_switch-selection"></span>
                            </div>
                            <span><?php _e('Download <strong>remote images</strong> in your <strong>Media Library</strong> for the new posts.', _SQ_PLUGIN_NAME_); ?></span>
                        </div>


                        <div class="sq_option_content">
                            <div class="sq_switch">
                                <input id="sq_url_fix1" type="radio" class="sq_switch-input" name="sq_url_fix" value="1" <?php echo((SQ_Tools::$options['sq_url_fix'] == 1) ? "checked" : '') ?> />
                                <label for="sq_url_fix1" class="sq_switch-label sq_switch-label-off"><?php _e('Yes', _SQ_PLUGIN_NAME_); ?></label>
                                <input id="sq_url_fix0" type="radio" class="sq_switch-input" name="sq_url_fix" value="0" <?php echo((SQ_Tools::$options['sq_url_fix'] == 0) ? "checked" : '') ?> />
                                <label for="sq_url_fix0" class="sq_switch-label sq_switch-label-on"><?php _e('No', _SQ_PLUGIN_NAME_); ?></label>
                                <span class="sq_switch-selection"></span>
                            </div>
                            <span><?php _e('Correct my <strong>feed links</strong> and <strong>images</strong> (convert from relative to absolute).', _SQ_PLUGIN_NAME_); ?></span>
                        </div>
                    </div>
                </fieldset>

                <fieldset>
                    <legend>
                        <span class="sq_legend_title"><?php _e('Google Rank Options', _SQ_PLUGIN_NAME_); ?></span>
                        <span><?php echo sprintf(__('%sCountry targeting%s', _SQ_PLUGIN_NAME_), '<a href="https://support.google.com/webmasters/answer/62399?hl=en" target="_blank">', '</a>'); ?></span>
                    </legend>
                    <div>
                        <div class="sq_option_content">
                            <p>
                                <span><?php _e('Select the google extension for which Squirrly will check the google rank', _SQ_PLUGIN_NAME_); ?></span>
                            </p>

                            <div class="abh_select withborder">
                                <select id="sq_google_country" name="sq_google_country">
                                    <option value="com"><?php _e('Default', _SQ_PLUGIN_NAME_); ?> - Google.com (http://www.google.com/)</option>
                                    <option value="as"><?php _e('American Samoa', _SQ_PLUGIN_NAME_); ?> (http://www.google.as/)</option>
                                    <option value=".off.ai"><?php _e('Anguilla', _SQ_PLUGIN_NAME_); ?> (http://www.google.off.ai/)</option>
                                    <option value="com.ag"><?php _e('Antigua and Barbuda', _SQ_PLUGIN_NAME_); ?> (http://www.google.com.ag/)</option>
                                    <option value="com.ar"><?php _e('Argentina', _SQ_PLUGIN_NAME_); ?> (http://www.google.com.ar/)</option>
                                    <option value="com.au"><?php _e('Australia', _SQ_PLUGIN_NAME_); ?> (http://www.google.com.au/)</option>
                                    <option value="at"><?php _e('Austria', _SQ_PLUGIN_NAME_); ?> (http://www.google.at/)</option>
                                    <option value="az"><?php _e('Azerbaijan', 'seo-rank-reporter'); ?> (http://www.google.az/)</option>
                                    <option value="be"><?php _e('Belgium', _SQ_PLUGIN_NAME_); ?> (http://www.google.be/)</option>
                                    <option value="com.br"><?php _e('Brazil', _SQ_PLUGIN_NAME_); ?> (http://www.google.com.br/)</option>
                                    <option value="vg"><?php _e('British Virgin Islands', _SQ_PLUGIN_NAME_); ?> (http://www.google.vg/)</option>
                                    <option value="bi"><?php _e('Burundi', _SQ_PLUGIN_NAME_); ?> (http://www.google.bi/)</option>
                                    <option value="bg"><?php _e('Bulgaria', _SQ_PLUGIN_NAME_); ?> (http://www.google.bg/)</option>
                                    <option value="ca"><?php _e('Canada', _SQ_PLUGIN_NAME_); ?> (http://www.google.ca/)</option>
                                    <option value="td"><?php _e('Chad', _SQ_PLUGIN_NAME_); ?> (http://www.google.td/)</option>
                                    <option value="cl"><?php _e('Chile', _SQ_PLUGIN_NAME_); ?> (http://www.google.cl/)</option>
                                    <option value="com.co"><?php _e('Colombia', _SQ_PLUGIN_NAME_); ?> (http://www.google.com.co/)</option>
                                    <option value="co.cr"><?php _e('Costa Rica', _SQ_PLUGIN_NAME_); ?> (http://www.google.co.cr/)</option>
                                    <option value="ci"><?php _e('Côte d\'Ivoire', _SQ_PLUGIN_NAME_); ?> (http://www.google.ci/)</option>
                                    <option value="com.cu"><?php _e('Cuba', _SQ_PLUGIN_NAME_); ?> (http://www.google.com.cu/)</option>
                                    <option value="cz"><?php _e('Czech Republic', _SQ_PLUGIN_NAME_); ?> (http://www.google.cz/)</option>
                                    <option value="cd"><?php _e('Dem. Rep. of the Congo', _SQ_PLUGIN_NAME_); ?> (http://www.google.cd/)</option>
                                    <option value="dk"><?php _e('Denmark', _SQ_PLUGIN_NAME_); ?> (http://www.google.dk/)</option>
                                    <option value="dj"><?php _e('Djibouti', _SQ_PLUGIN_NAME_); ?> (http://www.google.dj/)</option>
                                    <option value="com.do"><?php _e('Dominican Republic', _SQ_PLUGIN_NAME_); ?> (http://www.google.com.do/)</option>
                                    <option value="com.ec"><?php _e('Ecuador', _SQ_PLUGIN_NAME_); ?> (http://www.google.com.ec/)</option>
                                    <option value="com.sv"><?php _e('El Salvador', _SQ_PLUGIN_NAME_); ?> (http://www.google.com.sv/)</option>
                                    <option value="ee"><?php _e('Estonia', _SQ_PLUGIN_NAME_); ?> (http://www.google.ee/)</option>
                                    <option value="fm"><?php _e('Federated States of Micronesia', _SQ_PLUGIN_NAME_); ?> (http://www.google.fm/)</option>
                                    <option value="com.fj"><?php _e('Fiji', _SQ_PLUGIN_NAME_); ?> (http://www.google.com.fj/)</option>
                                    <option value="fi"><?php _e('Finland', _SQ_PLUGIN_NAME_); ?> (http://www.google.fi/)</option>
                                    <option value="fr"><?php _e('France', _SQ_PLUGIN_NAME_); ?> (http://www.google.fr/)</option>
                                    <option value="gm"><?php _e('The Gambia', _SQ_PLUGIN_NAME_); ?> (http://www.google.gm/)</option>
                                    <option value="ge"><?php _e('Georgia', _SQ_PLUGIN_NAME_); ?> (http://www.google.ge/)</option>
                                    <option value="de"><?php _e('Germany', _SQ_PLUGIN_NAME_); ?> (http://www.google.de/)</option>
                                    <option value="com.gi"><?php _e('Gibraltar', _SQ_PLUGIN_NAME_); ?> (http://www.google.com.gi/)</option>
                                    <option value="com.gr"><?php _e('Greece', _SQ_PLUGIN_NAME_); ?> (http://www.google.com.gr/)</option>
                                    <option value="gl"><?php _e('Greenland', _SQ_PLUGIN_NAME_); ?> (http://www.google.gl/)</option>
                                    <option value="gg"><?php _e('Guernsey', _SQ_PLUGIN_NAME_); ?> (http://www.google.gg/)</option>
                                    <option value="hn"><?php _e('Honduras', _SQ_PLUGIN_NAME_); ?> (http://www.google.hn/)</option>
                                    <option value="com.hk"><?php _e('Hong Kong', _SQ_PLUGIN_NAME_); ?> (http://www.google.com.hk/)</option>
                                    <option value="co.hu"><?php _e('Hungary', _SQ_PLUGIN_NAME_); ?> (http://www.google.co.hu/)</option>
                                    <option value="co.in"><?php _e('India', _SQ_PLUGIN_NAME_); ?> (http://www.google.co.in/)</option>
                                    <option value="co.id"><?php _e('Indonesia', _SQ_PLUGIN_NAME_); ?> (http://www.google.co.id/)</option>
                                    <option value="ie"><?php _e('Ireland', _SQ_PLUGIN_NAME_); ?> (http://www.google.ie/)</option>
                                    <option value="co.im"><?php _e('Isle of Man', _SQ_PLUGIN_NAME_); ?> (http://www.google.co.im/)</option>
                                    <option value="co.il"><?php _e('Israel', _SQ_PLUGIN_NAME_); ?> (http://www.google.co.il/)</option>
                                    <option value="it"><?php _e('Italy', _SQ_PLUGIN_NAME_); ?> (http://www.google.it/)</option>
                                    <option value="com.jm"><?php _e('Jamaica', _SQ_PLUGIN_NAME_); ?> (http://www.google.com.jm/)</option>
                                    <option value="co.jp"><?php _e('Japan', _SQ_PLUGIN_NAME_); ?> (http://www.google.co.jp/)</option>
                                    <option value="co.je"><?php _e('Jersey', _SQ_PLUGIN_NAME_); ?> (http://www.google.co.je/)</option>
                                    <option value="kz"><?php _e('Kazakhstan', _SQ_PLUGIN_NAME_); ?> (http://www.google.kz/)</option>
                                    <option value="co.kr"><?php _e('Korea', _SQ_PLUGIN_NAME_); ?> (http://www.google.co.kr/)</option>
                                    <option value="lv"><?php _e('Latvia', _SQ_PLUGIN_NAME_); ?> (http://www.google.lv/)</option>
                                    <option value="co.ls"><?php _e('Lesotho', _SQ_PLUGIN_NAME_); ?> (http://www.google.co.ls/)</option>
                                    <option value="li"><?php _e('Liechtenstein', _SQ_PLUGIN_NAME_); ?> (http://www.google.li/)</option>
                                    <option value="lt"><?php _e('Lithuania', _SQ_PLUGIN_NAME_); ?> (http://www.google.lt/)</option>
                                    <option value="lu"><?php _e('Luxembourg', _SQ_PLUGIN_NAME_); ?> (http://www.google.lu/)</option>
                                    <option value="mw"><?php _e('Malawi', _SQ_PLUGIN_NAME_); ?> (http://www.google.mw/)</option>
                                    <option value="com.my"><?php _e('Malaysia', _SQ_PLUGIN_NAME_); ?> (http://www.google.com.my/)</option>
                                    <option value="com.mt"><?php _e('Malta', _SQ_PLUGIN_NAME_); ?> (http://www.google.com.mt/)</option>
                                    <option value="mu"><?php _e('Mauritius', _SQ_PLUGIN_NAME_); ?> (http://www.google.mu/)</option>
                                    <option value="com.mx"><?php _e('México', _SQ_PLUGIN_NAME_); ?> (http://www.google.com.mx/)</option>
                                    <option value="ms"><?php _e('Montserrat', _SQ_PLUGIN_NAME_); ?> (http://www.google.ms/)</option>
                                    <option value="com.na"><?php _e('Namibia', _SQ_PLUGIN_NAME_); ?> (http://www.google.com.na/)</option>
                                    <option value="com.np"><?php _e('Nepal', _SQ_PLUGIN_NAME_); ?> (http://www.google.com.np/)</option>
                                    <option value="nl"><?php _e('Netherlands', _SQ_PLUGIN_NAME_); ?> (http://www.google.nl/)</option>
                                    <option value="co.nz"><?php _e('New Zealand', _SQ_PLUGIN_NAME_); ?> (http://www.google.co.nz/)</option>
                                    <option value="com.ni"><?php _e('Nicaragua', _SQ_PLUGIN_NAME_); ?> (http://www.google.com.ni/)</option>
                                    <option value="com.nf"><?php _e('Norfolk Island', _SQ_PLUGIN_NAME_); ?> (http://www.google.com.nf/)</option>
                                    <option value="no"><?php _e('Norway', _SQ_PLUGIN_NAME_); ?> (http://www.google.no/)</option>
                                    <option value="com.pk"><?php _e('Pakistan', _SQ_PLUGIN_NAME_); ?> (http://www.google.com.pk/)</option>
                                    <option value="com.pa"><?php _e('Panamá', _SQ_PLUGIN_NAME_); ?> (http://www.google.com.pa/)</option>
                                    <option value="com.py"><?php _e('Paraguay', _SQ_PLUGIN_NAME_); ?> (http://www.google.com.py/)</option>
                                    <option value="com.pe"><?php _e('Perú', _SQ_PLUGIN_NAME_); ?> (http://www.google.com.pe/)</option>
                                    <option value="com.ph"><?php _e('Philippines', _SQ_PLUGIN_NAME_); ?> (http://www.google.com.ph/)</option>
                                    <option value="pn"><?php _e('Pitcairn Islands', _SQ_PLUGIN_NAME_); ?> (http://www.google.pn/)</option>
                                    <option value="pl"><?php _e('Poland', _SQ_PLUGIN_NAME_); ?> (http://www.google.pl/)</option>
                                    <option value="pt"><?php _e('Portugal', _SQ_PLUGIN_NAME_); ?> (http://www.google.pt/)</option>
                                    <option value="com.pr"><?php _e('Puerto Rico', _SQ_PLUGIN_NAME_); ?> (http://www.google.com.pr/)</option>
                                    <option value="cg"><?php _e('Rep. of the Congo', _SQ_PLUGIN_NAME_); ?> (http://www.google.cg/)</option>
                                    <option value="ro"><?php _e('Romania', _SQ_PLUGIN_NAME_); ?> (http://www.google.ro/)</option>
                                    <option value="ru"><?php _e('Russia', _SQ_PLUGIN_NAME_); ?> (http://www.google.ru/)</option>
                                    <option value="rw"><?php _e('Rwanda', _SQ_PLUGIN_NAME_); ?> (http://www.google.rw/)</option>
                                    <option value="sh"><?php _e('Saint Helena', _SQ_PLUGIN_NAME_); ?> (http://www.google.sh/)</option>
                                    <option value="sm"><?php _e('San Marino', _SQ_PLUGIN_NAME_); ?> (http://www.google.sm/)</option>
                                    <option value="com.sa"><?php _e('Saudi Arabia', _SQ_PLUGIN_NAME_); ?> (http://www.google.com.sa/)</option>
                                    <option value="com.sg"><?php _e('Singapore', _SQ_PLUGIN_NAME_); ?> (http://www.google.com.sg/)</option>
                                    <option value="sk"><?php _e('Slovakia', _SQ_PLUGIN_NAME_); ?> (http://www.google.sk/)</option>
                                    <option value="co.za"><?php _e('South Africa', _SQ_PLUGIN_NAME_); ?> (http://www.google.co.za/)</option>
                                    <option value="es"><?php _e('Spain', _SQ_PLUGIN_NAME_); ?> (http://www.google.es/)</option>
                                    <option value="lk"><?php _e('Sri Lanka', _SQ_PLUGIN_NAME_); ?> (http://www.google.lk/)</option>
                                    <option value="se"><?php _e('Sweden', _SQ_PLUGIN_NAME_); ?> (http://www.google.se/)</option>
                                    <option value="ch"><?php _e('Switzerland', _SQ_PLUGIN_NAME_); ?> (http://www.google.ch/)</option>
                                    <option value="com.tw"><?php _e('Taiwan', _SQ_PLUGIN_NAME_); ?> (http://www.google.com.tw/)</option>
                                    <option value="co.th"><?php _e('Thailand', _SQ_PLUGIN_NAME_); ?> (http://www.google.co.th/)</option>
                                    <option value="tt"><?php _e('Trinidad and Tobago', _SQ_PLUGIN_NAME_); ?> (http://www.google.tt/)</option>
                                    <option value="com.tr"><?php _e('Turkey', _SQ_PLUGIN_NAME_); ?> (http://www.google.com.tr/)</option>
                                    <option value="com.ua"><?php _e('Ukraine', _SQ_PLUGIN_NAME_); ?> (http://www.google.com.ua/)</option>
                                    <option value="ae"><?php _e('United Arab Emirates', _SQ_PLUGIN_NAME_); ?> (http://www.google.ae/)</option>
                                    <option value="co.uk"><?php _e('United Kingdom', _SQ_PLUGIN_NAME_); ?> (http://www.google.co.uk/)</option>
                                    <option value="com.uy"><?php _e('Uruguay', _SQ_PLUGIN_NAME_); ?> (http://www.google.com.uy/)</option>
                                    <option value="uz"><?php _e('Uzbekistan', _SQ_PLUGIN_NAME_); ?> (http://www.google.uz/)</option>
                                    <option value="vu"><?php _e('Vanuatu', _SQ_PLUGIN_NAME_); ?> (http://www.google.vu/)</option>
                                    <option value="co.ve"><?php _e('Venezuela', _SQ_PLUGIN_NAME_); ?> (http://www.google.co.ve/)</option>
                                </select>
                            </div>
                            <p>
                                <span><?php echo sprintf(__('Select how many pages to be checked by google rank every hour. %s5 pages (recommended)%s', _SQ_PLUGIN_NAME_), '<br /><span style="color:#aaa;font-size:12px;">', '</span>'); ?></span>
                            </p>

                            <div class="withborder">
                                <select id="sq_google_ranksperhour" name="sq_google_ranksperhour">
                                    <?php for ($i = 0; $i < 10; $i++) {
                                        if ((int)SQ_Tools::$options['sq_google_ranksperhour'] > 10) {
                                            SQ_Tools::$options['sq_google_ranksperhour'] = 9;
                                        }
                                        ?>
                                        <option value="<?php echo $i ?>" <?php echo((SQ_Tools::$options['sq_google_ranksperhour'] == $i) ? "selected='selected'" : '') ?>><?php echo $i ?><?php _e('pages', _SQ_PLUGIN_NAME_); ?></option>
                                    <?php } ?>
                                </select>

                            </div>

                            <div class="sq_option_content">
                                <div class="sq_switch">
                                    <input id="sq_google_country_strict1" type="radio" class="sq_switch-input" name="sq_google_country_strict" value="1" <?php echo((SQ_Tools::$options['sq_google_country_strict'] == 1) ? "checked" : '') ?> />
                                    <label for="sq_google_country_strict1" class="sq_switch-label sq_switch-label-off"><?php _e('Yes', _SQ_PLUGIN_NAME_); ?></label>
                                    <input id="sq_google_country_strict0" type="radio" class="sq_switch-input" name="sq_google_country_strict" value="0" <?php echo((SQ_Tools::$options['sq_google_country_strict'] == 0) ? "checked" : '') ?> />
                                    <label for="sq_google_country_strict0" class="sq_switch-label sq_switch-label-on"><?php _e('No', _SQ_PLUGIN_NAME_); ?></label>
                                    <span class="sq_switch-selection"></span>
                                </div>
                                <span><?php _e('Restricts search results to results originating in the above particular country.', _SQ_PLUGIN_NAME_); ?></span>
                            </div>

                        </div>
                    </div>
                </fieldset>

                <fieldset id="sq_measure_success">
                    <legend>
                        <span class="sq_legend_title"><?php _e('Measure Your Success', _SQ_PLUGIN_NAME_); ?></span>
                        <span><?php echo sprintf(__('%sHow to set the Google Webmaster Tool%s', _SQ_PLUGIN_NAME_), '<a href="http://howto.squirrly.co/wordpress-seo/how-to-set-the-google-webmaster-tool/" target="_blank">', '</a>'); ?></span>
                        <span><?php echo sprintf(__('%sBest practices to help Google find, crawl, and index your site%s', _SQ_PLUGIN_NAME_), '<a href="https://support.google.com/webmasters/answer/35769?hl=en" target="_blank">', '</a>'); ?></span>
                        <span><?php echo sprintf(__('%sBing Webmaster Tools Help & How-To Center%s', _SQ_PLUGIN_NAME_), '<a href="http://www.bing.com/webmaster/help/help-center-661b2d18" target="_blank">', '</a>'); ?></span>

                    </legend>
                    <div>
                        <p class="withborder withcode">
                            <span class="sq_icon sq_icon_googlewt"></span>
                            <?php echo sprintf(__('Google META verification code for %sWebmaster Tool%s:', _SQ_PLUGIN_NAME_), '<a href="https://www.google.com/webmasters" target="_blank">', '</a>'); ?>
                            <br><strong><input type="text" name="sq_google_wt" value="<?php echo((SQ_Tools::$options['sq_google_wt'] <> '') ? SQ_Tools::$options['sq_google_wt'] : '') ?>" size="15"/> (e.g. &lt;meta name="google-site-verification" content="XXXXXXXXXXXXXXXXXX" /&gt;)</strong>
                        </p>

                        <p class="withborder withcode">
                            <span class="sq_icon sq_icon_bingwt"></span>
                            <?php echo sprintf(__('Bing META code (for %sWebmaster Tool%s ):', _SQ_PLUGIN_NAME_), '<a href="http://www.bing.com/toolbox/webmaster/" target="_blank">', '</a>'); ?>
                            <br><strong>
                                <input type="text" name="sq_bing_wt" value="<?php echo((SQ_Tools::$options['sq_bing_wt'] <> '') ? SQ_Tools::$options['sq_bing_wt'] : '') ?>" size="15"/> (e.g. &lt;meta name="msvalidate.01" content="XXXXXXXXXXXXXXXXXX" /&gt;)</strong>
                        </p>

                        <p class="withborder withcode">
                            <span class="sq_icon sq_icon_alexat"></span>
                            <?php echo sprintf(__('Alexa META code (for %sAlexa Tool%s ):', _SQ_PLUGIN_NAME_), '<a href="http://www.alexa.com/pro/subscription/signup?tsver=0&puid=200" target="_blank">', '</a>'); ?>
                            <br><strong><input type="text" name="sq_alexa" value="<?php echo((SQ_Tools::$options['sq_alexa'] <> '') ? SQ_Tools::$options['sq_alexa'] : '') ?>" size="15"/> (e.g. &lt;meta name="alexaVerifyID" content="XXXXXXXXXXXXXXXXXX" /&gt;)</strong>
                        </p>
                    </div>
                </fieldset>

                <div id="sq_settings_submit">
                    <input type="hidden" name="action" value="sq_settings_update"/>
                    <input type="hidden" name="nonce" value="<?php echo wp_create_nonce(_SQ_NONCE_ID_); ?>"/>
                    <input type="submit" name="sq_update" value="<?php _e('Save settings', _SQ_PLUGIN_NAME_) ?> &raquo;"/>
                </div>
            </div>
        </form>

        <div class="sq_settings_backup">
            <form action="" method="POST">
                <input type="hidden" name="action" value="sq_backup"/>
                <input type="hidden" name="nonce" value="<?php echo wp_create_nonce(_SQ_NONCE_ID_); ?>"/>
                <input type="submit" class="sq_button" name="sq_backup" value="<?php _e('Backup Settings', _SQ_PLUGIN_NAME_) ?>"/>
                <input type="button" class="sq_button sq_restore" name="sq_restore" value="<?php _e('Restore Settings', _SQ_PLUGIN_NAME_) ?>"/>
            </form>
        </div>

        <div class="sq_settings_restore sq_popup" style="display: none">
            <span class="sq_close">x</span>
            <span><?php _e('Upload the file with the saved Squirrly Settings', _SQ_PLUGIN_NAME_) ?></span>

            <form action="" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="sq_restore"/>
                <input type="file" name="sq_options" id="favicon" style="float: left;"/>
                <input type="hidden" name="nonce" value="<?php echo wp_create_nonce(_SQ_NONCE_ID_); ?>"/>
                <input type="submit" style="margin-top: 10px;" class="sq_button" name="sq_restore" value="<?php _e('Restore Backup', _SQ_PLUGIN_NAME_) ?>"/>
            </form>
        </div>
    </div>

</div>
