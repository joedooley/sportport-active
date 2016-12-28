<?php

class SQ_Menu extends SQ_FrontController {

    /** @var array snippet */
    private $post_type;

    /** @var array snippet */
    var $options = array();

    public function __construct() {
        parent::__construct();
        add_filter('rewrite_rules_array', array(SQ_ObjController::getBlock('SQ_BlockSettingsSeo'), 'rewrite_rules'), 999, 1);
        add_action('admin_bar_menu', array($this, 'hookTopmenu'), 999);
    }

    /**
     * Hook the Admin load
     */
    public function hookInit() {

        /* add the plugin menu in admin */
        if (current_user_can('manage_options')) {
            //check if activated
            if (get_transient('sq_activate') == 1) {
                // Delete the redirect transient
                delete_transient('sq_activate');
                SQ_Action::apiCall('sq/user/settings', array('settings' => json_encode(SQ_Tools::getBriefOptions())), 10);

                wp_safe_redirect(admin_url('admin.php?page=sq_dashboard'));
                exit();
            }

            if (get_transient('sq_rewrite') == 1) {
                // Delete the redirect transient
                delete_transient('sq_rewrite');
                global $wp_rewrite;
                $wp_rewrite->flush_rules();
            }

            //Check if there are expected upgrades
            SQ_Tools::checkUpgrade();
        }
        //activate the cron job if not exists
        if (!wp_get_schedule('sq_processCron')) {
            wp_schedule_event(time(), 'hourly', 'sq_processCron');
        }
    }

    /**
     * Add a menu in Admin Bar
     *
     * @param WP_Admin_Bar $wp_admin_bar
     */
    public function hookTopmenu($wp_admin_bar) {
        $wp_admin_bar->add_node(array(
            'id' => 'sq_posts',
            'title' => __('See Your Rank on Google', _SQ_PLUGIN_NAME_),
            'href' => admin_url('admin.php?page=sq_posts'),
            'parent' => false
        ));
    }

    /**
     * Creates the Setting menu in Wordpress
     */
    public function hookMenu() {

        $this->post_type = SQ_Tools::$options['sq_post_types'];

        //Push the Analytics Check
        if (strpos($_SERVER['REQUEST_URI'], '?page=sq_dashboard') !== false) {
            SQ_Tools::saveOptions('sq_dashboard', 1);
        }
        if (strpos($_SERVER['REQUEST_URI'], '?page=sq_analytics') !== false) {
            SQ_Tools::saveOptions('sq_analytics', 1);
        }

        $analytics_alert = 0;
        if (SQ_ObjController::getModel('SQ_Post')->countKeywords() > 0 && SQ_Tools::$options['sq_analytics'] == 0) {
            $analytics_alert = 1;
            if (!get_transient('sq_analytics')) {
                set_transient('sq_analytics', time(), (60 * 60 * 24 * 7));
            } else {
                $time_loaded = get_transient('sq_analytics');
                if (time() - $time_loaded > (60 * 60 * 24 * 3) && time() - $time_loaded < (60 * 60 * 24 * 14)) {
                    SQ_Error::setError(__('Check out the Squirrly Analytics section. <a href="admin.php?page=sq_posts" title="Squirrly Analytics">Click here</a>', _SQ_PLUGIN_NAME_));
                }
            }
        }

        $dashboard_alert = (int) (SQ_Tools::$options['sq_dashboard'] == 0);



///////////////

        $this->model->addMenu(array(ucfirst(_SQ_NAME_),
            'Squirrly' . SQ_Tools::showNotices(SQ_Tools::$errors_count, 'errors_count'),
            'edit_posts',
            'sq_dashboard',
            null,
            _SQ_THEME_URL_ . 'img/settings/menu_icon_16.png'
        ));

        $this->model->addSubmenu(array('sq_dashboard',
            ucfirst(_SQ_NAME_) . __(' dashboard', _SQ_PLUGIN_NAME_),
            ( (SQ_Tools::$options['sq_api'] == '') ? __('First Step', _SQ_PLUGIN_NAME_) : __('Dashboard', _SQ_PLUGIN_NAME_)) . SQ_Tools::showNotices($dashboard_alert, 'errors_count'),
            'edit_posts',
            'sq_dashboard',
            array(SQ_ObjController::getBlock('SQ_BlockDashboard'), 'init')
        ));
        if (SQ_Tools::$options['sq_api'] <> '') {

            //IF SERP PLUGIN IS NOT INSTALLED
            if (!class_exists('SRC_Classes_ObjController')){
                $this->model->addSubmenu(array('sq_dashboard',
                    ucfirst(_SQ_NAME_) . __(' post list', _SQ_PLUGIN_NAME_),
                    __('Performance <br />Analytics', _SQ_PLUGIN_NAME_) . SQ_Tools::showNotices($analytics_alert, 'errors_count'),
                    'edit_posts',
                    'sq_posts',
                    array(SQ_ObjController::getBlock('SQ_BlockPostsAnalytics'), 'init')
                ));
            }



            $this->model->addSubmenu(array('sq_dashboard',
                ucfirst(_SQ_NAME_) . __(' settings', _SQ_PLUGIN_NAME_),
                __('SEO', _SQ_PLUGIN_NAME_) . SQ_Tools::showNotices(SQ_Tools::$errors_count, 'errors_count'),
                'manage_options',
                'sq_seo',
                array(SQ_ObjController::getBlock('SQ_BlockSettingsSeo'), 'init')
            ));

            $this->model->addSubmenu(array('sq_dashboard',
                ucfirst(_SQ_NAME_) . __(' Settings', _SQ_PLUGIN_NAME_),
                __('Settings', _SQ_PLUGIN_NAME_),
                'manage_options',
                'sq_settings',
                array(SQ_ObjController::getBlock('SQ_BlockSettings'), 'init')
            ));

            $this->model->addSubmenu(array('sq_dashboard',
                ucfirst(_SQ_NAME_) . __(' account info', _SQ_PLUGIN_NAME_),
                __('Account Info', _SQ_PLUGIN_NAME_),
                'manage_options',
                'sq_account',
                array(SQ_ObjController::getBlock('SQ_BlockAccount'), 'init')
            ));
        }

        $this->model->addSubmenu(array('sq_dashboard',
            __('Become an Affiliate with ', _SQ_PLUGIN_NAME_) . ucfirst(_SQ_NAME_),
            __('Affiliate plan', _SQ_PLUGIN_NAME_),
            'manage_options',
            'sq_affiliate',
            array(SQ_ObjController::getBlock('SQ_BlockAffiliate'), 'init')
        ));

        foreach ($this->post_type as $type)
            $this->model->addMeta(array('post' . _SQ_NAME_,
                ucfirst(_SQ_NAME_),
                array(SQ_ObjController::getController('SQ_Post'), 'init'),
                $type,
                'side',
                'high'
            ));

        //Add the Rank in the Posts list
        $postlist = SQ_ObjController::getController('SQ_PostsList');
        if (is_object($postlist)) {
            $postlist->init();
        }

        //Show bar to go back and finish the help
        if ($this->is_page('edit') || strpos($_SERVER['REQUEST_URI'], 'sq_posts') !== false) {
            if (SQ_Tools::$options['active_help'] <> '' && SQ_Tools::$options['ignore_warn'] == 0) {
                SQ_Error::setError('Go back and complete the Squirrly Tasks for today <a href="admin.php?page=sq_' . SQ_Tools::$options['active_help'] . '" class="sq_button" title="Continue the Help">Continue</a>', 'helpnotice');
            }
        }
    }

    /**
     * Is the user on page name? Default name = post edit page
     * name = 'quirrly'
     *
     * @global array $pagenow
     * @param string $name
     * @return boolean
     */
    public function is_page($name = '') {
        global $pagenow;
        $page = array();
        //make sure we are on the backend
        if (is_admin() && $name <> '') {
            if ($name == 'edit') {
                $page = array('post.php', 'post-new.php');
            } else {
                array_push($page, $name . '.php');
            }

            return in_array($pagenow, $page);
        }

        return false;
    }

}
