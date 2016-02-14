/* ==========================================================
 * settings.js
 * http://enviragallery.com/
 * ==========================================================
 * Copyright 2014 Thomas Griffin.
 *
 * Licensed under the GPL License, Version 2.0 or later (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.gnu.org/licenses/gpl-2.0.html
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * ========================================================== */
;(function($){
    $(function(){
        // Initialize the slider tabs.
        var envira_tabs           = $('#envira-tabs'),
            envira_tabs_nav       = $('#envira-tabs-nav'),
            envira_tabs_hash      = window.location.hash,
            envira_tabs_hash_sani = window.location.hash.replace('!', '');

        // If we have a hash and it begins with "envira-tab", set the proper tab to be opened.
        if ( envira_tabs_hash && envira_tabs_hash.indexOf('envira-tab-') >= 0 ) {
            $('.envira-active').removeClass('envira-active nav-tab-active');
            envira_tabs_nav.find('a[href="' + envira_tabs_hash_sani + '"]').addClass('envira-active nav-tab-active');
            envira_tabs.find(envira_tabs_hash_sani).addClass('envira-active').show();
        }

        // Change tabs on click.
        $('#envira-tabs-nav a').on('click', function(e){
            e.preventDefault();
            var $this = $(this);
            if ( $this.hasClass('envira-active') ) {
                return;
            } else {
                window.location.hash = envira_tabs_hash = this.hash.split('#').join('#!');
                var current = envira_tabs_nav.find('.envira-active').removeClass('envira-active nav-tab-active').attr('href');
                $this.addClass('envira-active nav-tab-active');
                envira_tabs.find(current).removeClass('envira-active').hide();
                envira_tabs.find($this.attr('href')).addClass('envira-active').show();
            }
        });

        // Re-enable install button if user clicks on it, needs creds but tries to install another addon instead.
        $('#envira-addons-area').on('click.refreshInstallAddon', '.envira-addon-action-button', function(e) {
            var el      = $(this);
            var buttons = $('#envira-addons-area').find('.envira-addon-action-button');
            $.each(buttons, function(i, element) {
                if ( el == element )
                    return true;

                enviraAddonRefresh(element);
            });
        });

        // Process Addon activations for those currently installed but not yet active.
        $('#envira-addons-area').on('click.activateAddon', '.envira-activate-addon', function(e) {
            e.preventDefault();
            var $this = $(this);

            // Remove any leftover error messages, output an icon and get the plugin basename that needs to be activated.
            $('.envira-addon-error').remove();
            $(this).text(envira_gallery_settings.activating);
            $(this).next().css({'display' : 'inline-block', 'margin-top' : '0px'});
            var button  = $(this);
            var plugin  = $(this).attr('rel');
            var el      = $(this).parent().parent();
            var message = $(this).parent().parent().find('.addon-status');

            // Process the Ajax to perform the activation.
            var opts = {
                url:      ajaxurl,
                type:     'post',
                async:    true,
                cache:    false,
                dataType: 'json',
                data: {
                    action: 'envira_gallery_activate_addon',
                    nonce:  envira_gallery_settings.activate_nonce,
                    plugin: plugin
                },
                success: function(response) {
                    // If there is a WP Error instance, output it here and quit the script.
                    if ( response && true !== response ) {
                        $(el).slideDown('normal', function() {
                            $(this).after('<div class="envira-addon-error"><strong>' + response.error + '</strong></div>');
                            $this.next().hide();
                            $('.envira-addon-error').delay(3000).slideUp();
                        });
                        return;
                    }

                    // The Ajax request was successful, so let's update the output.
                    $(button).text(envira_gallery_settings.deactivate).removeClass('envira-activate-addon').addClass('envira-deactivate-addon');
                    $(message).text(envira_gallery_settings.active);
                    $(el).removeClass('envira-addon-inactive').addClass('envira-addon-active');
                    $this.next().hide();
                },
                error: function(xhr, textStatus ,e) {
                    $this.next().hide();
                    return;
                }
            }
            $.ajax(opts);
        });

        // Process Addon deactivations for those currently active.
        $('#envira-addons-area').on('click.deactivateAddon', '.envira-deactivate-addon', function(e) {
            e.preventDefault();
            var $this = $(this);

            // Remove any leftover error messages, output an icon and get the plugin basename that needs to be activated.
            $('.envira-addon-error').remove();
            $(this).text(envira_gallery_settings.deactivating);
            $(this).next().css({'display' : 'inline-block', 'margin-top' : '0px'});
            var button  = $(this);
            var plugin  = $(this).attr('rel');
            var el      = $(this).parent().parent();
            var message = $(this).parent().parent().find('.addon-status');

            // Process the Ajax to perform the activation.
            var opts = {
                url:      ajaxurl,
                type:     'post',
                async:    true,
                cache:    false,
                dataType: 'json',
                data: {
                    action: 'envira_gallery_deactivate_addon',
                    nonce:  envira_gallery_settings.deactivate_nonce,
                    plugin: plugin
                },
                success: function(response) {
                    // If there is a WP Error instance, output it here and quit the script.
                    if ( response && true !== response ) {
                        $(el).slideDown('normal', function() {
                            $(this).after('<div class="envira-addon-error"><strong>' + response.error + '</strong></div>');
                            $this.next().hide();
                            $('.envira-addon-error').delay(3000).slideUp();
                        });
                        return;
                    }

                    // The Ajax request was successful, so let's update the output.
                    $(button).text(envira_gallery_settings.activate).removeClass('envira-deactivate-addon').addClass('envira-activate-addon');
                    $(message).text(envira_gallery_settings.inactive);
                    $(el).removeClass('envira-addon-active').addClass('envira-addon-inactive');
                    $this.next().hide();
                },
                error: function(xhr, textStatus ,e) {
                    $this.next().hide();
                    return;
                }
            }
            $.ajax(opts);
        });

        // Process Addon installations.
        $('#envira-addons-area').on('click.installAddon', '.envira-install-addon', function(e) {
            e.preventDefault();
            var $this = $(this);

            // Remove any leftover error messages, output an icon and get the plugin basename that needs to be activated.
            $('.envira-addon-error').remove();
            $(this).text(envira_gallery_settings.installing);
            $(this).next().css({'display' : 'inline-block', 'margin-top' : '0px'});
            var button  = $(this);
            var plugin  = $(this).attr('rel');
            var el      = $(this).parent().parent();
            var message = $(this).parent().parent().find('.addon-status');

            // Process the Ajax to perform the activation.
            var opts = {
                url:      ajaxurl,
                type:     'post',
                async:    true,
                cache:    false,
                dataType: 'json',
                data: {
                    action: 'envira_gallery_install_addon',
                    nonce:  envira_gallery_settings.install_nonce,
                    plugin: plugin
                },
                success: function(response) {
                    // If there is a WP Error instance, output it here and quit the script.
                    if ( response.error ) {
                        $(el).slideDown('normal', function() {
                            $(button).parent().parent().after('<div class="envira-addon-error"><strong>' + response.error + '</strong></div>');
                            $(button).text(envira_gallery_settings.install);
                            $this.next().hide();
                            $('.envira-addon-error').delay(4000).slideUp();
                        });
                        return;
                    }

                    // If we need more credentials, output the form sent back to us.
                    if ( response.form ) {
                        // Display the form to gather the users credentials.
                        $(el).slideDown('normal', function() {
                            $(this).after('<div class="envira-addon-error">' + response.form + '</div>');
                            $this.next().hide();
                        });

                        // Add a disabled attribute the install button if the creds are needed.
                        $(button).attr('disabled', true);

                        $('#envira-addons-area').on('click.installCredsAddon', '#upgrade', function(e) {
                            // Prevent the default action, let the user know we are attempting to install again and go with it.
                            e.preventDefault();
                            $this.next().hide();
                            $(this).val(envira_gallery_settings.installing);
                            $(this).next().css({'display' : 'inline-block', 'margin-top' : '0px'});

                            // Now let's make another Ajax request once the user has submitted their credentials.
                            var hostname  = $(this).parent().parent().find('#hostname').val();
                            var username  = $(this).parent().parent().find('#username').val();
                            var password  = $(this).parent().parent().find('#password').val();
                            var proceed   = $(this);
                            var connect   = $(this).parent().parent().parent().parent();
                            var cred_opts = {
                                url:      ajaxurl,
                                type:     'post',
                                async:    true,
                                cache:    false,
                                dataType: 'json',
                                data: {
                                    action:   'envira_gallery_install_addon',
                                    nonce:    envira_gallery_settings.install_nonce,
                                    plugin:   plugin,
                                    hostname: hostname,
                                    username: username,
                                    password: password
                                },
                                success: function(response) {
                                    // If there is a WP Error instance, output it here and quit the script.
                                    if ( response.error ) {
                                        $(el).slideDown('normal', function() {
                                            $(button).parent().parent().after('<div class="envira-addon-error"><strong>' + response.error + '</strong></div>');
                                            $(button).text(envira_gallery_settings.install);
                                            $this.next().hide();
                                            $('.envira-addon-error').delay(4000).slideUp();
                                        });
                                        return;
                                    }

                                    if ( response.form ) {
                                        $this.next().hide();
                                        $('.envira-inline-error').remove();
                                        $(proceed).val(envira_gallery_settings.proceed);
                                        $(proceed).after('<span class="envira-inline-error">' + envira_gallery_settings.connect_error + '</span>');
                                        return;
                                    }

                                    // The Ajax request was successful, so let's update the output.
                                    $(connect).remove();
                                    $(button).show();
                                    $(button).text(envira_gallery_settings.activate).removeClass('envira-install-addon').addClass('envira-activate-addon');
                                    $(button).attr('rel', response.plugin);
                                    $(button).removeAttr('disabled');
                                    $(message).text(envira_gallery_settings.inactive);
                                    $(el).removeClass('envira-addon-not-installed').addClass('envira-addon-inactive');
                                    $this.next().hide();
                                },
                                error: function(xhr, textStatus ,e) {
                                    $this.next().hide();
                                    return;
                                }
                            }
                            $.ajax(cred_opts);
                        });

                        // No need to move further if we need to enter our creds.
                        return;
                    }

                    // The Ajax request was successful, so let's update the output.
                    $(button).text(envira_gallery_settings.activate).removeClass('envira-install-addon').addClass('envira-activate-addon');
                    $(button).attr('rel', response.plugin);
                    $(message).text(envira_gallery_settings.inactive);
                    $(el).removeClass('envira-addon-not-installed').addClass('envira-addon-inactive');
                    $this.next().hide();
                },
                error: function(xhr, textStatus ,e) {
                    $this.next().hide();
                    return;
                }
            }
            $.ajax(opts);
        });

        // Function to clear any disabled buttons and extra text if the user needs to add creds but instead tries to install a different addon.
        function enviraAddonRefresh(element) {
            if ( $(element).attr('disabled') )
                $(element).removeAttr('disabled');

            if ( $(element).parent().parent().hasClass('envira-addon-not-installed') )
                $(element).text(envira_gallery_settings.install);
        }
    });
}(jQuery));