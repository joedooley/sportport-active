<div id="sq_posts" >
    <span class="sq_icon"></span>
    <div id="sq_posts_title" ><?php _e('Squirrly Analytics', _SQ_PLUGIN_NAME_); ?> </div>
    <div id="sq_posts_subtitle" ><?php _e('Don\'t see all your pages here? Make sure you optimize them with Squirrly, so that we can track them, and display you the analytics', _SQ_PLUGIN_NAME_); ?> </div>
    <div id="sq_posts_subtitle" style="font-size: 14px;color: red;padding: 0px;margin: 0;text-align: center;line-height: 15px;"><?php _e('If you recheck the google rank, let 10-20 seconds between requests to prevent google to block your IP for an hour.', _SQ_PLUGIN_NAME_); ?> </div>

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