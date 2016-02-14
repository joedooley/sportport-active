;(function($){
    $(function(){
        // Initialize the slider tabs.
        var envira_tabs           = $('#envira-tabs'),
            envira_tabs_nav       = $('#envira-tabs-nav'),
            envira_tabs_hash      = window.location.hash,
            envira_tabs_hash_sani = window.location.hash.replace('!', '');

        // If we have a hash and it begins with "envira-tab", set the proper tab to be opened.
        if ( envira_tabs_hash && envira_tabs_hash.indexOf('envira-tab-') >= 0 ) {
            $('.envira-active').removeClass('envira-active');
            envira_tabs_nav.find('li a[href="' + envira_tabs_hash_sani + '"]').parent().addClass('envira-active');
            envira_tabs.find(envira_tabs_hash_sani).addClass('envira-active').show();

            // Update the post action to contain our hash so the proper tab can be loaded on save.
            var post_action = $('#post').attr('action');
            if ( post_action ) {
                post_action = post_action.split('#')[0];
                $('#post').attr('action', post_action + envira_tabs_hash);
            }
        }

        // Change tabs on click.
        $(document).on('click', '#envira-tabs-nav li a', function(e){
            e.preventDefault();
            var $this = $(this);
            if ( $this.parent().hasClass('envira-active') ) {
                return;
            } else {
                window.location.hash = envira_tabs_hash = this.hash.split('#').join('#!');
                var current = envira_tabs_nav.find('.envira-active').removeClass('envira-active').find('a').attr('href');
                $this.parent().addClass('envira-active');
                envira_tabs.find(current).removeClass('envira-active').hide();
                envira_tabs.find($this.attr('href')).addClass('envira-active').show();

                // Update the post action to contain our hash so the proper tab can be loaded on save.
                var post_action = $('#post').attr('action');
                if ( post_action ) {
                    post_action = post_action.split('#')[0];
                    $('#post').attr('action', post_action + envira_tabs_hash);
                }
            }
        });
    });
}(jQuery));