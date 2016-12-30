<div id="sq_posts">
    <span class="sq_icon"></span>

    <div id="sq_posts_title"><?php _e('Squirrly Analytics', _SQ_PLUGIN_NAME_); ?> </div>
    <div id="sq_posts_subtitle"><?php _e('Don\'t see all your pages here? Make sure you optimize them with Squirrly, so that we can track them, and display you the analytics', _SQ_PLUGIN_NAME_); ?> </div>
    <?php
        if (get_transient('google_blocked') !== false) {
            echo '<div id="sq_posts_subtitle" style="font-size: 14px;color: red;padding: 0px;margin: 0 0 15px 0;text-align: center;line-height: 15px;">';
            if (function_exists('curl_init') && !ini_get('open_basedir')) {
                echo sprintf(__('The IP %s is calling the rank too often and google stopped the calls for %s mins. Lower the Rank check rate in Squirrly > Settings > Rank Option. %sMore details%s', _SQ_PLUGIN_NAME_), @gethostbyname(gethostname()), (((get_transient('google_blocked')- time() + 3600) > 0) ?  date('i',(get_transient('google_blocked')- time() + 3600)) : 'an hour'),'<a href="http://howto.squirrly.co/wordpress-seo/could-not-receive-data-from-google-err-blocked-ip/" target="_blank" >','</a>');
            } else {
                if (!function_exists('curl_init')) {
                    echo sprintf(__('To be able to check the RANK please activate cURL for PHP on your server %sDetails%s', _SQ_PLUGIN_NAME_), '<a href="http://stackoverflow.com/questions/1347146/how-to-enable-curl-in-php-xampp" target="_blank">', '</a>');
                } else {
                    echo sprintf(__('To be able to check the RANK please remove the "safe_mode" and "open_basedir" for PHP cURL on your server %sDetails%s', _SQ_PLUGIN_NAME_), '<a href="http://stackoverflow.com/a/6918685" target="_blank">', '</a>');

                }
            }
            echo '</div>';
        }
        ?>

    <?php echo $view->getNavigationTop() ?>
    <table class="wp-list-table widefat fixed posts" cellspacing="0">
        <thead>
        <tr>
            <?php echo $view->getHeaderColumns() ?>
        </tr>
        </thead>

        <tfoot>
        <tr>
            <?php echo $view->getHeaderColumns() ?>
        </tr>
        </tfoot>

        <tbody id="the-list">
        <?php echo $view->getRows() ?>
        </tbody>
    </table>
    <?php echo $view->getNavigationBottom() ?>
    <?php $view->hookFooter(); ?>
    <?php echo $view->getScripts(); ?>
</div>