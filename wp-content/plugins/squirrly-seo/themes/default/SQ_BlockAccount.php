<div id="sq_settings" class="sq_userinfo" >
    <?php SQ_ObjController::getBlock('SQ_BlockSupport')->init(); ?>

    <?php if (SQ_Tools::$options['sq_api'] <> '') { ?>
        <div>
            <span class="sq_icon"></span>
            <div id="sq_settings_title" ><?php _e('Squirrly account information', _SQ_PLUGIN_NAME_); ?> </div>
            <div id="sq_settings_title" style="text-align: right">
                <input id="sq_goto_dashboard" type="button" value="<?php _e('Go to dashboard', _SQ_PLUGIN_NAME_) ?> &raquo;" />
                <br style="clear: both;">
            </div>
        </div>
    <?php } ?>
    <div id="sq_helpaccountside" class="sq_helpside"></div>
    <div id="sq_left">

        <?php if (SQ_Tools::$options['sq_api'] <> '') { ?>
            <div id="sq_settings_body" style="min-height: 400px;">
                <?php if (SQ_Tools::$options['sq_api'] <> '') { ?>
                    <fieldset style="background: none; border: none; box-shadow: none;"><div id="sq_userinfo" class="sq_loading"></div></fieldset>
                    <script type="text/javascript">
                        jQuery(document).ready(function () {
                            sq_getUserStatus();
                        });
                    </script>
                <?php } ?>

            </div>

        <?php } ?>

    </div>
    <div id="sq_sidehelp"></div>
</div>
