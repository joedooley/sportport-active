<div id="sq_blocklogin" class="sq_box">
    <div class="sq_header"><?php _e('Squirrly.co Login', _SQ_PLUGIN_NAME_); ?></div>
    <div class="sq_body">
        <ul style="display: none;">
            <li>
                <div class="sq_error"></div>
                <div class="sq_message" style="display: none;"></div>
            </li>
            <li><label for="sq_user"><?php _e('Email:', _SQ_PLUGIN_NAME_); ?></label>
                <input type="text" id="sq_user" name="sq_user" /></li>
            <li><label for="sq_password"><?php _e('Password:', _SQ_PLUGIN_NAME_); ?></label>
                <input type="password" id="sq_password" name="sq_password"  /></li>
            <li><input type="button" id="sq_login" value="<?php _e('Login', _SQ_PLUGIN_NAME_); ?>"  /></li>
            <li><a id="sq_signup" href="javascript:void(0);" target="_blank" title="<?php _e('Register', _SQ_PLUGIN_NAME_); ?>"><?php _e('Register to Squirrly.co', _SQ_PLUGIN_NAME_); ?></a> |
                <a href="<?php echo _SQ_DASH_URL_ . 'login/?action=lostpassword' ?>" target="_blank" title="<?php _e('Lost password?', _SQ_PLUGIN_NAME_); ?>"><?php _e('Lost password', _SQ_PLUGIN_NAME_); ?></a></li>
        </ul>
        <div id="sq_autologin" align="center">
            <div class="sq_error"></div>
            <span id="sq_register"><?php _e('Enter your email', _SQ_PLUGIN_NAME_); ?></span><span id="sq_register_wait"></span>
            <div id="sq_register_email" ><label for="sq_email"><?php _e('Your Email:', _SQ_PLUGIN_NAME_); ?></label>
                <input type="text" id="sq_email" name="sq_email"  value="<?php
                $current_user = wp_get_current_user();
                echo $current_user->user_email;
                ?>" />
            </div>
            <div id="sq_loginimage"><?php _e('Sign Up', _SQ_PLUGIN_NAME_); ?></div>
            <div id="sq_signin"><?php _e('I already have an account', _SQ_PLUGIN_NAME_); ?></div>
            <span><?php _e('This email connects you to Squirrly.co', _SQ_PLUGIN_NAME_); ?></span>

        </div>

    </div>

    <script type="text/javascript">
        // autoLogin();
        //
        //Call the autologin
        var __invalid_email = '<?php _e('The email address is invalid!', _SQ_PLUGIN_NAME_); ?>';
        var __try_again = '<?php _e('Click on Sign Up button and try again ...', _SQ_PLUGIN_NAME_); ?>';
        var __error_login = '<?php _e('An error occured while logging in!', _SQ_PLUGIN_NAME_); ?>';
        var __connecting = '<?php _e('Connecting ...', _SQ_PLUGIN_NAME_); ?>';
        jQuery('#sq_loginimage').bind('click', function () {
            sq_autoLogin();
        });

        //listenLogin(); //listen the login
    </script>
</div>
<div class="sq_settings_backup">
    <input type="button" class="sq_button sq_restore" name="sq_restore" value="<?php _e('Restore Squirrly Settings', _SQ_PLUGIN_NAME_) ?>" />
</div>

<div class="sq_settings_restore sq_popup" style="display: none">
    <span class="sq_close">x</span>
    <span><?php _e('Upload the file with the saved Squirrly Settings', _SQ_PLUGIN_NAME_) ?></span>
    <form action="#" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="sq_restore" />
        <input type="file" name="sq_options" id="favicon" style="float: left;" />
        <input type="hidden" name="nonce" value="<?php echo wp_create_nonce(_SQ_NONCE_ID_); ?>" />
        <input type="submit"  style="margin-top: 10px;" class="sq_button" name="sq_restore" value="<?php _e('Restore Backup', _SQ_PLUGIN_NAME_) ?>" />
    </form>
</div>
<div id="sq_login_success" style="display: none;">
    <div class="sq_header"><?php _e('Congratulations! You are ready to use all the features from Squirrly', _SQ_PLUGIN_NAME_); ?><div><img src="<?php echo _SQ_THEME_URL_ . 'img/settings/squirrly.png' ?>"></div></div>

</div>