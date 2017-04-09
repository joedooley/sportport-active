<div id="sq_settings">
    <div class="sq_message sq_error" style="display: none"></div>

    <?php
    SQ_ObjController::getBlock('SQ_BlockSupport')->init();
    SQ_ObjController::getBlock('SQ_Loading')->loadJsVars();
    ?>
    <div>
        <span class="sq_icon"></span>
        <div id="sq_settings_title"><?php _e('Squirrly Keyword Research', _SQ_PLUGIN_NAME_); ?> </div>
        <div class="sq_subtitles">
            <p>Find Long-Tail Keywords That Are Easy to Rank For. Never Miss a Ranking Opportunity. All the Details We Give Are Personalized For Each Site, Thanks to Squirrly's Market Intelligence Features.</p>
        </div>
    </div>
    <div id="sq_helpkeywordresearchside" class="sq_helpside"></div>
    <div id="sq_left">
        <?php if (SQ_Tools::$options['sq_api'] <> '') { ?>
            <div id="sq_settings_body">

                <?php if (SQ_Tools::$options['sq_api'] <> '') { ?>
                    <fieldset style="background: none !important; box-shadow: none;">
                        <div id="sq_krinfo" class="sq_loading"></div>
                    </fieldset>
                    <script type="text/javascript">
                        jQuery(document).ready(function () {
                            sq_getKR();
                        });
                    </script>
                <?php } ?>

            </div>

        <?php } ?>

    </div>

</div>
