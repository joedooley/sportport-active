<?php

/**
 * Handles the parameters and url
 *
 * @author Squirrly
 */
class SQ_Tools extends SQ_FrontController {

    /** @var array Saved options in database */
    public static $options = array();

    /** @var integer Count the errors in site */
    static $errors_count;

    /** @var array */
    private static $debug;
    private static $source_code;

    public function __construct() {
        parent::__construct();

        self::$options = $this->getOptions();

        $this->checkDebug(); //dev mode
    }

    public static function getUserID() {
        global $current_user;
        return $current_user->ID;
    }

    /**
     * This hook will save the current version in database
     *
     * @return void
     */
    function hookInit() {
        //TinyMCE editor required
        //set_user_setting('editor', 'tinymce');

        $this->loadMultilanguage();

        //add setting link in plugin
        add_filter('plugin_action_links', array($this, 'hookActionlink'), 5, 2);
    }

    /**
     * Add a link to settings in the plugin list
     *
     * @param array $links
     * @param type $file
     * @return array
     */
    public function hookActionlink($links, $file) {
        if ($file == _SQ_PLUGIN_NAME_ . '/squirrly.php') {
            $link = '<a href="' . admin_url('admin.php?page=sq_dashboard') . '">' . __('Getting started', _SQ_PLUGIN_NAME_) . '</a>';
            array_unshift($links, $link);
        }

        return $links;
    }

    /**
     * Load the Options from user option table in DB
     *
     * @return void
     */
    public static function getOptions() {
        $default = array(
            'sq_ver' => 0,
            'sq_api' => '',
            'sq_checkedissues' => 0,
            'sq_areissues' => 0,
            'sq_use' => 0,
            'sq_post_types' => array(
                'post', 'page', 'product', 'shopp_page_shopp-products'
            ),
            // --
            'sq_auto_canonical' => 1,
            'sq_auto_sitemap' => 0,
            'sq_auto_feed' => 0,
            'sq_auto_jsonld' => 0,
            'sq_auto_amp' => 0,
            'sq_jsonld_type' => 'Organization',
            'sq_jsonld' => array(
                'Organization' => array(
                    'name' => '',
                    'logo' => '',
                    'telephone' => '',
                    'contactType' => '',
                    'description' => ''
                ),
                'Person' => array(
                    'name' => '',
                    'logo' => '',
                    'telephone' => '',
                    'jobTitle' => '',
                    'description' => ''
                )),
            'sq_sitemap_ping' => 1,
            'sq_sitemap_show' => array(
                'images' => 1,
                'videos' => 0,
            ),
            'sq_sitemap_frequency' => 'weekly',
            'sq_sitemap' => array(
                'sitemap' => array('sitemap.xml', 1),
                'sitemap-home' => array('sitemap-home.xml', 1),
                'sitemap-news' => array('sitemap-news.xml', 0),
                'sitemap-product' => array('sitemap-product.xml', 1),
                'sitemap-post' => array('sitemap-posts.xml', 1),
                'sitemap-page' => array('sitemap-pages.xml', 1),
                'sitemap-category' => array('sitemap-categories.xml', 1),
                'sitemap-post_tag' => array('sitemap-tags.xml', 1),
                'sitemap-archive' => array('sitemap-archives.xml', 1),
                'sitemap-author' => array('sitemap-authors.xml', 0),
                'sitemap-custom-tax' => array('sitemap-custom-taxonomies.xml', 0),
                'sitemap-custom-post' => array('sitemap-custom-posts.xml', 0),
            ),
            'sq_auto_robots' => 1,
            'sq_robots_permission' => array(
                'User-agent: *',
                'Disallow: */trackback/',
                'Disallow: */xmlrpc.php',
                'Disallow: /wp-*.php',
                'Disallow: /cgi-bin/',
                'Disallow: /wp-admin/',
                'Allow: */wp-content/uploads/',),
            'sq_auto_meta' => 1,
            'sq_auto_favicon' => 0,
            'favicon' => '',
            'sq_auto_twitter' => 0,
            'sq_auto_twittersize' => 'summary',
            'sq_auto_facebook' => 0,
            'sq_og_locale' => 'en_US',
            'sq_twitter_account' => '',
            'sq_facebook_account' => '',
            'sq_google_plus' => '',
            'sq_linkedin_account' => '',
            'sq_pinterest_account' => '',
            'sq_instagram_account' => '',
            // --
            'sq_auto_seo' => 1,
            'sq_auto_title' => 1,
            'sq_auto_description' => 1,
            'sq_fp_title' => '',
            'sq_fp_description' => '',
            'sq_fp_keywords' => '',
            'sq_fp_ogimage' => '',
            // --
            'sq_google_wt' => '',
            'sq_google_analytics' => '',
            'sq_facebook_analytics' => '',
            'sq_facebook_insights' => '',
            'sq_bing_wt' => '',
            'sq_pinterest' => '',
            'sq_alexa' => '',
            // --
            'active_help' => '',
            'ignore_warn' => 0,
            'sq_keyword_help' => 1,
            'sq_keyword_information' => 0,
            'sq_url_fix' => 1,
            //
            'sq_google_country' => 'com',
            'sq_google_language' => 'en',
            'sq_google_country_strict' => 0,
            'sq_google_ranksperhour' => 0,
            // --
            'sq_affiliate_link' => '',
            'sq_sla' => 1,
            'sq_keywordtag' => 1,
            'sq_local_images' => 1,
            'sq_force_savepost' => 0,
            //--
            'sq_dashboard' => 0,
            'sq_analytics' => 0,
        );
        $options = json_decode(get_option(SQ_OPTION), true);

        if (is_array($options)) {
            $options = @array_merge($default, $options);
            return $options;
        }

        return $default;
    }

    public static function getBriefOptions() {
        if ($pageId = get_option('page_on_front')) {
            $title = SQ_ObjController::getModel('SQ_Frontend')->getAdvancedMeta($pageId, 'title');
            $description = SQ_ObjController::getModel('SQ_Frontend')->getAdvancedMeta($pageId, 'description');
        } else {
            $title = SQ_Tools::$options['sq_fp_title'];
            $description = SQ_Tools::$options['sq_fp_description'];
        }
        return array(
            'sq_version' => SQ_VERSION_ID,
            'sq_use' => SQ_Tools::$options['sq_use'],
            'sq_checkedissues' => SQ_Tools::$options['sq_checkedissues'],
            'sq_areissues' => SQ_Tools::$options['sq_areissues'],
            'sq_auto_canonical' => SQ_Tools::$options['sq_auto_canonical'],
            'sq_auto_meta' => SQ_Tools::$options['sq_auto_meta'],
            'sq_auto_sitemap' => SQ_Tools::$options['sq_auto_sitemap'],
            'sq_auto_jsonld' => (SQ_Tools::$options['sq_auto_jsonld'] && (SQ_Tools::$options['sq_jsonld']['Organization']['name'] <> '' || SQ_Tools::$options['sq_jsonld']['Person']['name'] <> '')),
            'sq_sitemap_ping' => SQ_Tools::$options['sq_sitemap_ping'],
            'sq_auto_robots' => SQ_Tools::$options['sq_auto_robots'],
            'sq_auto_favicon' => (SQ_Tools::$options['sq_auto_favicon'] && SQ_Tools::$options['favicon'] <> ''),
            'sq_auto_twitter' => SQ_Tools::$options['sq_auto_twitter'],
            'sq_auto_facebook' => SQ_Tools::$options['sq_auto_facebook'],
            'sq_auto_seo' => SQ_Tools::$options['sq_auto_seo'],
            'sq_auto_title' => (int)(SQ_Tools::$options['sq_auto_title'] == 1 && $title <> ''),
            'sq_auto_description' => (int)(SQ_Tools::$options['sq_auto_description'] == 1 && $description <> ''),
            'sq_google_plus' => (int)(SQ_Tools::$options['sq_google_plus'] <> ''),
            'sq_google_wt' => (int)(SQ_Tools::$options['sq_google_wt'] <> ''),
            'sq_google_analytics' => (int)(SQ_Tools::$options['sq_google_analytics'] <> ''),
            'sq_facebook_insights' => (int)(SQ_Tools::$options['sq_facebook_insights'] <> ''),
            'sq_bing_wt' => (int)(SQ_Tools::$options['sq_bing_wt'] <> ''),
            'sq_pinterest' => (int)(SQ_Tools::$options['sq_pinterest'] <> ''),
            'sq_alexa' => (int)(SQ_Tools::$options['sq_alexa'] <> ''),
            'sq_keyword_help' => SQ_Tools::$options['sq_keyword_help'],
            'sq_keyword_information' => SQ_Tools::$options['sq_keyword_information'],
            'sq_google_country_strict' => SQ_Tools::$options['sq_google_country_strict'],
            'sq_keywordtag' => SQ_Tools::$options['sq_keywordtag'],
            'sq_local_images' => SQ_Tools::$options['sq_local_images'],
        );
    }

    /**
     * Save the Options in user option table in DB
     *
     * @return void
     */
    public static function saveOptions($key = null, $value = '') {
        if (isset($key)) {
            self::$options[$key] = $value;
        }

        update_option(SQ_OPTION, json_encode(self::$options));
    }

    /**
     * Set the header type
     * @param type $type
     */
    public static function setHeader($type) {
        if (SQ_Tools::getValue('sq_debug') == 'on')
            return;

        switch ($type) {
            case 'json':
                header('Content-Type: application/json');
                break;
            case 'ico':
                header('Content-Type: image/x-icon');
                break;
            case 'png':
                header('Content-Type: image/png');
                break;
            case'text':
                header("Content-type: text/plain");
                break;
        }
    }

    /**
     * Get a value from $_POST / $_GET
     * if unavailable, take a default value
     *
     * @param string $key Value key
     * @param mixed $defaultValue (optional)
     * @return mixed Value
     */
    public static function getValue($key, $defaultValue = false, $withcode = false) {
        if (!isset($key) OR empty($key) OR !is_string($key))
            return false;
        $ret = (isset($_POST[$key]) ? (is_string($_POST[$key]) ? urldecode($_POST[$key]) : $_POST[$key]) : (isset($_GET[$key]) ? (is_string($_GET[$key]) ? urldecode($_GET[$key]) : $_GET[$key]) : $defaultValue));

        if (is_string($ret) === true && $withcode === false) {
            $ret = sanitize_text_field($ret);
        }

        return !is_string($ret) ? $ret : stripslashes($ret);
    }

    /**
     * Check if the parameter is set
     *
     * @param string $key
     * @return boolean
     */
    public static function getIsset($key) {
        if (!isset($key) OR empty($key) OR !is_string($key))
            return false;
        return isset($_POST[$key]) ? true : (isset($_GET[$key]) ? true : false);
    }

    /**
     * Show the notices to WP
     *
     * @return void
     */
    public static function showNotices($message, $type = 'sq_notices') {
        if (file_exists(_SQ_THEME_DIR_ . 'SQ_notices.php')) {
            ob_start();
            include(_SQ_THEME_DIR_ . 'SQ_notices.php');
            $message = ob_get_contents();
            ob_end_clean();
        }

        return $message;
    }

    /**
     * Load the multilanguage support from .mo
     */
    private function loadMultilanguage() {
        if (!defined('WP_PLUGIN_DIR')) {
            load_plugin_textdomain(_SQ_PLUGIN_NAME_, _SQ_PLUGIN_NAME_ . '/languages/');
        } else {
            load_plugin_textdomain(_SQ_PLUGIN_NAME_, null, _SQ_PLUGIN_NAME_ . '/languages/');
        }
    }

    /**
     * Connect remote with CURL if exists
     */
    public static function sq_remote_get($url, $param = array(), $options = array()) {
        $parameters = '';
        $cookies = array();
        $cookie_string = '';

        $url_domain = parse_url($url);
        if (isset($url_domain['host'])) {
            $url_domain = $url_domain['host'];
        } else {
            $url_domain = '';
        }

        if (isset($param)) {
            foreach ($param as $key => $value) {
                if (isset($key) && $key <> '' && $key <> 'timeout') {
                    $parameters .= ($parameters == "" ? "" : "&") . $key . "=" . $value;
                }
            }
        }

        if ($parameters <> '') {
            $url .= ((strpos($url, "?") === false) ? "?" : "&") . $parameters;
        }

        //send the cookie for preview
        $server_domain = '';
        if (isset($_SERVER['HTTP_HOST'])) {
            $server_domain = $_SERVER['HTTP_HOST'];
        } elseif (isset($_SERVER['SERVER_NAME'])) {
            $server_domain = $_SERVER['SERVER_NAME'];
        }

        if ($url_domain == $server_domain && strpos($url, 'preview=true') !== false) {
            foreach ($_COOKIE as $name => $value) {
                if (strpos($name, 'wordpress') !== false || strpos($name, 'wp') !== false || strpos($name, 'slimstat') !== false || strpos($name, 'sforum') !== false) {
                    $cookies[] = new WP_Http_Cookie(array('name' => $name, 'value' => $value));
                    $cookie_string .= "$name=$value;";
                }
            }
        }

        $options['timeout'] = (isset($options['timeout'])) ? $options['timeout'] : 30;
        if (!isset($options['cookie_string'])) {
            $options['cookies'] = $cookies;
            $options['cookie_string'] = $cookie_string;
        }
        $options['sslverify'] = false;


        if (function_exists('curl_init') && !ini_get('open_basedir')) {
            $response = self::sq_curl($url, $options, 'get');
        } else {
            if (!$response = self::sq_wpcall($url, $options, 'get')) {
                return false;
            }
        }

        return $response;
    }

    /**
     * Connect remote with CURL if exists
     */
    public static function sq_remote_post($url, $param = array(), $options = array()) {
        $parameters = '';
        $cookies = array();
        $cookie_string = '';

        $url_domain = parse_url($url);
        if (isset($url_domain['host'])) {
            $url_domain = $url_domain['host'];
        } else {
            $url_domain = '';
        }

        if (isset($param)) {
            foreach ($param as $key => $value) {
                if (isset($key) && $key <> '' && $key <> 'timeout') {
                    $parameters .= ($parameters == "" ? "" : "&") . $key . "=" . $value;
                }
            }
        }

        if ($parameters <> '') {
            $url .= ((strpos($url, "?") === false) ? "?" : "&") . $parameters;
        }

        //send the cookie for preview
        $server_domain = '';
        if (isset($_SERVER['HTTP_HOST'])) {
            $server_domain = $_SERVER['HTTP_HOST'];
        } elseif (isset($_SERVER['SERVER_NAME'])) {
            $server_domain = $_SERVER['SERVER_NAME'];
        }

        if ($url_domain == $server_domain && strpos($url, 'preview=true') !== false) {
            foreach ($_COOKIE as $name => $value) {
                $cookies[] = new WP_Http_Cookie(array('name' => $name, 'value' => $value));
                $cookie_string .= "$name=$value;";
            }
        }

        $options['timeout'] = (isset($options['timeout'])) ? $options['timeout'] : 30;
        if (!isset($options['cookie_string'])) {
            $options['cookies'] = $cookies;
            $options['cookie_string'] = $cookie_string;
        }
        $options['sslverify'] = false;

        if (function_exists('curl_init') && !ini_get('open_basedir')) {
            $response = self::sq_curl($url, $options, 'post');
        } else {
            if (!$response = self::sq_wpcall($url, $options, 'post')) {
                return false;
            }
        }


        return $response;
    }

    /**
     * Call remote UR with CURL
     * @param string $url
     * @param array $param
     * @return string
     */
    private static function sq_curl($url, $options, $method = 'get') {

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        //--
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        //--
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, $options['timeout']);

        if ($method == 'post') {
            curl_setopt($ch, CURLOPT_POST, 1);
        }

        if (isset($options['sslverify'])) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        } else {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        }

        if (isset($options['followlocation'])) {
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_MAXREDIRS, 1);
        }

        if ($options['cookie_string'] <> '') {
            curl_setopt($ch, CURLOPT_COOKIE, $options['cookie_string']);
        }

        if (isset($options['User-Agent']) && $options['User-Agent'] <> '') {
            curl_setopt($ch, CURLOPT_USERAGENT, $options['User-Agent']);
        }
        $response = curl_exec($ch);
        $response = self::cleanResponce($response);

        self::dump('CURL', $method, $url, $options, $ch, $response); //output debug

        if (curl_errno($ch) == 1 || $response === false) { //if protocol not supported
            if (curl_errno($ch)) {
                self::dump(curl_getinfo($ch), curl_errno($ch), curl_error($ch));
            }
            curl_close($ch);
            $response = self::sq_wpcall($url, $options); //use the wordpress call
        } else {
            curl_close($ch);
        }

        return $response;
    }

    /**
     * Use the WP remote call
     * @param string $url
     * @param array $param
     * @return string
     */
    private static function sq_wpcall($url, $options, $method = 'get') {
        if ($method == 'post') {
            $response = wp_remote_post($url, $options);
        } else {
            $response = wp_remote_get($url, $options);
        }
        if (is_wp_error($response)) {
            self::dump($response);
            return false;
        }

        $response = self::cleanResponce(wp_remote_retrieve_body($response)); //clear and get the body
        self::dump('wp_remote_get', $method, $url, $options, $response); //output debug
        return $response;
    }

    /**
     * Connect remote with CURL if exists
     */
    public static function sq_remote_head($url) {
        $response = array();

        if (function_exists('curl_exec')) {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
            curl_exec($ch);

            $response['headers']['content-type'] = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
            $response['response']['code'] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            return $response;
        } else {
            return wp_remote_head($url, array('timeout' => 30));
        }

        return false;
    }

    /**
     * Get the Json from responce if any
     * @param string $response
     * @return string
     */
    private static function cleanResponce($response) {

        if (function_exists('substr_count'))
            if (substr_count($response, '(') > 1)
                return $response;

        if (strpos($response, '(') !== false && strpos($response, ')') !== false)
            $response = substr($response, (strpos($response, '(') + 1), (strpos($response, ')') - 1));

        return $response;
    }

    /**
     * Check for SEO blog bad settings
     */
    public static function checkErrorSettings($count_only = false) {
        if (current_user_can('manage_options')) {

            $fixit = "<a href=\"javascript:void(0);\"  onclick=\"%s jQuery(this).closest('div').fadeOut('slow'); if(parseInt(jQuery('.sq_count').html())>0) { var notif = (parseInt(jQuery('.sq_count').html()) - 1); if (notif > 0) {jQuery('.sq_count').html(notif); }else{ jQuery('.sq_count').html(notif); jQuery('.sq_count').hide(); } } jQuery.post(ajaxurl, { action: '%s', nonce: '" . wp_create_nonce(_SQ_NONCE_ID_) . "'});\" >" . __("Fix it for me!", _SQ_PLUGIN_NAME_) . "</a>";

            /* IF SEO INDEX IS OFF */
            if (self::getAutoSeoSquirrly()) {
                self::$errors_count++;
                if (!$count_only) {
                    SQ_Error::setError(__('Activate the Squirrly SEO for your blog (recommended)', _SQ_PLUGIN_NAME_) . " <br />" . sprintf($fixit, "jQuery('#sq_use_on').trigger('click');", "sq_fixautoseo") . "", 'settings', 'sq_fix_auto');
                }
            }

            //check only when in seo settings
            self::$source_code = self::sq_remote_get(get_bloginfo('url'), array(), array('timeout' => 5, 'followlocation' => true));
            if (self::$source_code <> '') {
                /* IF TITLE DUPLICATES */
                if (self::getDuplicateTitle()) {
                    self::$errors_count++;
                    if (!$count_only) {
                        SQ_Error::setError(__('You have META Title Duplicates. Disable the Squirrly Title Optimization or disable the other SEO Plugins', _SQ_PLUGIN_NAME_) . " <br />" . sprintf($fixit, "jQuery('#sq_auto_title0').attr('checked', true); jQuery('#sq_automatically').attr('checked', true);", "sq_fix_titleduplicate") . "", 'settings', 'sq_fix_descduplicate');
                    }
                }

                /* IF DESCRIPTION DUPLICATES */
                if (self::getDuplicateDescription()) {
                    self::$errors_count++;
                    if (!$count_only) {
                        SQ_Error::setError(__('You have META Description Duplicates. Disable the Squirrly Description Optimization or disable the other SEO Plugins', _SQ_PLUGIN_NAME_) . " <br />" . sprintf($fixit, "jQuery('#sq_auto_description0').attr('checked', true); jQuery('#sq_automatically').attr('checked', true);", "sq_fix_descduplicate") . "", 'settings', 'sq_fix_descduplicate');
                    }
                }

                /* IF OG DUPLICATES */
                if (self::getDuplicateOG()) {
                    self::$errors_count++;
                    if (!$count_only) {
                        SQ_Error::setError(__('You have Open Graph META Duplicates. Disable the Squirrly SEO Open Graph or disable the other SEO Plugins', _SQ_PLUGIN_NAME_) . " <br />" . sprintf($fixit, "jQuery('#sq_auto_facebook0').attr('checked', true);", "sq_fix_ogduplicate") . "", 'settings', 'sq_fix_ogduplicate');
                    }
                }

                /* IF TWITTER CARD DUPLICATES */
                if (self::getDuplicateTC()) {
                    self::$errors_count++;
                    if (!$count_only) {
                        SQ_Error::setError(__('You have Twitter Card META Duplicates. Disable the Squirrly SEO Twitter Card or disable the other SEO Plugins', _SQ_PLUGIN_NAME_) . " <br />" . sprintf($fixit, "jQuery('#sq_auto_twitter0').attr('checked', true);", "sq_fix_tcduplicate") . "", 'settings', 'sq_fix_tcduplicate');
                    }
                }
            }

            /* IF SEO INDEX IS OFF */
            if (self::getPrivateBlog()) {
                self::$errors_count++;
                if (!$count_only) {
                    SQ_Error::setError(__('You\'re blocking google from indexing your site!', _SQ_PLUGIN_NAME_) . " <br />" . sprintf($fixit, "jQuery('#sq_google_index1').attr('checked',true);", "sq_fixprivate") . "", 'settings', 'sq_fix_private');
                }
            }

            if (self::getBadLinkStructure()) {
                self::$errors_count++;
                if (!$count_only) {
                    SQ_Error::setError(__('It is highly recommended that you include the %postname% variable in the permalink structure. <br />Go to Settings > Permalinks and add /%postname%/ in Custom Structure', _SQ_PLUGIN_NAME_) . " <br /> ", 'settings');
                }
            }

            if (self::$errors_count == 0) {
                self::saveOptions('sq_areissues', 0);
                SQ_Error::setError(__('Great! We didn\'t find any issue in your site.', _SQ_PLUGIN_NAME_) . " <br /> ", 'success');
            } else {
                self::saveOptions('sq_areissues', 1);
            }
        }
    }

    /**
     * Check if the automatically seo si active
     * @return bool
     */
    private static function getAutoSeoSquirrly() {
        if (isset(self::$options['sq_use']))
            return ((int)self::$options['sq_use'] == 0);

        return true;
    }

    /**
     * Check for META duplicates
     * @return boolean
     */
    private static function getDuplicateOG() {
        if (!function_exists('preg_match_all')) {
            return false;
        }

        if (self::$options['sq_use'] == 1 && self::$options['sq_auto_facebook'] == 1) {

            if (self::$source_code <> '') {
                preg_match_all("/<meta[\s+]property=[\"|\']og:url[\"|\'][\s+](content|value)=[\"|\']([^>]*)[\"|\'][^>]*>/i", self::$source_code, $out);
                if (!empty($out) && isset($out[0]) && is_array($out[0])) {
                    return (sizeof($out[0]) > 1);
                }
            }
        }

        return false;
    }

    /**
     * Check for META duplicates
     * @return boolean
     */
    private static function getDuplicateTC() {
        if (!function_exists('preg_match_all')) {
            return false;
        }

        if (self::$options['sq_use'] == 1 && self::$options['sq_auto_twitter'] == 1) {

            if (self::$source_code <> '') {
                preg_match_all("/<meta[\s+]name=[\"|\']twitter:card[\"|\'][\s+](content|value)=[\"|\']([^>]*)[\"|\'][^>]*>/i", self::$source_code, $out);
                if (!empty($out) && isset($out[0]) && is_array($out[0])) {
                    return (sizeof($out[0]) > 1);
                }
            }
        }

        return false;
    }

    /**
     * Check for META duplicates
     * @return boolean
     */
    private static function getDuplicateDescription() {
        if (!function_exists('preg_match_all')) {
            return false;
        }
        $total = 0;

        if (self::$options['sq_use'] == 1 && self::$options['sq_auto_description'] == 1) {
            if (self::$source_code <> '') {
                preg_match_all("/<meta[^>]*name=[\"|\']description[\"|\'][^>]*content=[\"]([^\"]*)[\"][^>]*>/i", self::$source_code, $out);
                if (!empty($out) && isset($out[0]) && is_array($out[0])) {
                    $total += sizeof($out[0]);
                }
                preg_match_all("/<meta[^>]*content=[\"]([^\"]*)[\"][^>]*name=[\"|\']description[\"|\'][^>]*>/i", self::$source_code, $out);
                if (!empty($out) && isset($out[0]) && is_array($out[0])) {
                    $total += sizeof($out[0]);
                }
            }
        }

        return ($total > 1);
    }

    /**
     * Check for META duplicates
     * @return boolean
     */
    private static function getDuplicateTitle() {
        if (!function_exists('preg_match_all')) {
            return false;
        }
        $total = 0;

        if (self::$options['sq_use'] == 1 && self::$options['sq_auto_title'] == 1) {
            if (self::$source_code <> '') {
                preg_match_all("/<title[^>]*>(.*)?<\/title>/i", self::$source_code, $out);

                if (!empty($out) && isset($out[0]) && is_array($out[0])) {
                    $total += sizeof($out[0]);
                }
                preg_match_all("/<meta[^>]*name=[\"|\']title[\"|\'][^>]*content=[\"|\']([^>\"]*)[\"|\'][^>]*>/i", self::$source_code, $out);
                if (!empty($out) && isset($out[0]) && is_array($out[0])) {
                    $total += sizeof($out[0]);
                }
            }
        }

        return ($total > 1);
    }

    /**
     * Check if the blog is in private mode
     * @return bool
     */
    public static function getPrivateBlog() {
        return ((int)get_option('blog_public') == 0);
    }

    /**
     * Check if the blog has a bad link structure
     * @return bool
     */
    private static function getBadLinkStructure() {
        global $wp_rewrite;
        if (function_exists('apache_get_modules')) {
            //Check if mod_rewrite is installed in apache
            if (!in_array('mod_rewrite', apache_get_modules()))
                return false;
        }

        $home_path = get_home_path();
        $htaccess_file = $home_path . '.htaccess';

        if ((!file_exists($htaccess_file) && @is_writable($home_path) && $wp_rewrite->using_mod_rewrite_permalinks()) || @is_writable($htaccess_file)) {
            $link = get_option('permalink_structure');
            if ($link == '')
                return true;
        }
    }

    /**
     * Support for i18n with wpml, polyglot or qtrans
     *
     * @param string $in
     * @return string $in localized
     */
    public static function i18n($in) {
        if (function_exists('langswitch_filter_langs_with_message')) {
            $in = langswitch_filter_langs_with_message($in);
        }
        if (function_exists('polyglot_filter')) {
            $in = polyglot_filter($in);
        }
        if (function_exists('qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage')) {
            $in = qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage($in);
        }
        $in = apply_filters('localization', $in);
        return $in;
    }

    /**
     * Convert integer on the locale format.
     *
     * @param int $number The number to convert based on locale.
     * @param int $decimals Precision of the number of decimal places.
     * @return string Converted number in string format.
     */
    public static function i18n_number_format($number, $decimals = 0) {
        global $wp_locale;
        $formatted = number_format($number, absint($decimals), $wp_locale->number_format['decimal_point'], $wp_locale->number_format['thousands_sep']);
        return apply_filters('number_format_i18n', $formatted);
    }

    public static function getBrowserInfo() {
        $ub = '';
        $u_agent = $_SERVER['HTTP_USER_AGENT'];
        $bname = 'Unknown';
        $platform = 'Unknown';
        $version = "";
        if (!function_exists('preg_match'))
            return false;

        //First get the platform?
        if (preg_match('/linux/i', $u_agent)) {
            $platform = 'linux';
        } elseif (preg_match('/macintosh|mac os x/i', $u_agent)) {
            $platform = 'mac';
        } elseif (preg_match('/windows|win32/i', $u_agent)) {
            $platform = 'windows';
        }

        // Next get the name of the useragent yes seperately and for good reason
        if (preg_match('/MSIE/i', $u_agent) && !preg_match('/Opera/i', $u_agent)) {
            $bname = 'IE';
            $ub = "MSIE";
        } elseif (preg_match('/Firefox/i', $u_agent)) {
            $bname = 'Mozilla Firefox';
            $ub = "Firefox";
        } elseif (preg_match('/Chrome/i', $u_agent)) {
            $bname = 'Google Chrome';
            $ub = "Chrome";
        } elseif (preg_match('/Safari/i', $u_agent)) {
            $bname = 'Apple Safari';
            $ub = "Safari";
        } elseif (preg_match('/Opera/i', $u_agent)) {
            $bname = 'Opera';
            $ub = "Opera";
        } elseif (preg_match('/Netscape/i', $u_agent)) {
            $bname = 'Netscape';
            $ub = "Netscape";
        }

        // finally get the correct version number
        $known = array('Version', $ub, 'other');
        $pattern = '#(?' . join('|', $known) . ')[/ ]+(?[0-9.|a-zA-Z.]*)#';

        if (strpos($u_agent, 'MSIE 7.0;') !== false) {
            $version = 7.0;
        }

        if ($version == null || $version == "") {
            $version = "0";
        }

        return array(
            'userAgent' => $u_agent,
            'name' => $bname,
            'version' => $version,
            'platform' => $platform,
            'pattern' => $pattern
        );
    }

    /**
     *
     * @param string $url
     * @return array
     */
    public static function getSnippet($url) {
        if ($url == '' || !function_exists('preg_match')) {
            return;
        }

        $postid = 0;
        $snippet = array();
        $frontend = SQ_ObjController::getModel('SQ_Frontend');

        if ($postid == 0) {
            $postid = url_to_postid($url);
        }

        if ($postid > 0) {
            self::dump("it's a post", $url, $postid);
            $frontend->setPost(get_post($postid));
            $snippet['title'] = $frontend->getTitle();
            $snippet['description'] = $frontend->getDescription();
            $snippet['url'] = $url;
            self::dump($snippet);
        } elseif ($url == get_bloginfo('url')) {
            self::dump("it's main page", $url, get_bloginfo('url'));

            if (self::$options ['sq_auto_title'] == 1) {
                $snippet['title'] = self::$options['sq_fp_title'];
            }
            if (self::$options['sq_auto_description'] == 1) {
                $snippet['description'] = self::$options['sq_fp_description'];
            }

            $snippet['url'] = $url;
            self::dump($snippet);
        } else {
            $length = array('title' => 66,
                'description' => 240,
                'url' => 45);

            self::$source_code = self::sq_remote_get($url, array(), array('timeout' => 10, 'followlocation' => true));

            $title_regex = "/<title[^>]*>([^<>]*)<\/title>/si";
            preg_match($title_regex, self::$source_code, $title);

            if (is_array($title) && count($title) > 0) {
                $snippet['title'] = $title[1];
                $snippet['title'] = self::i18n(trim(strip_tags($snippet['title'])));
            }

            $description_regex = '/<meta[^>]*(name|property)=["\']description["\'][^>]*content="([^"<>]+)"[^<>]*>/si';
            preg_match($description_regex, self::$source_code, $description);
            if (is_array($description) && count($description) > 0) {
                $snippet['description'] = self::i18n(trim(strip_tags($description[2])));

                if (strlen($snippet['description']) > $length['description'])
                    $snippet['description'] = substr($snippet['description'], 0, ($length['description'] - 1)) . '...';
            }

            $snippet['url'] = $url;
            if (strlen($snippet['url']) > $length['url'])
                $snippet['url'] = substr($snippet['url'], 0, ($length['url'] - 1)) . '...';

        }

        return $snippet;

    }

    /**
     * Check if debug is called
     */
    private function checkDebug() {
        //if debug is called
        if (self::getIsset('sq_debug')) {
            if (self::getValue('sq_debug') == self::$options['sq_api']) {
                $_GET['sq_debug'] = 'on';
            } else {
                $_GET['sq_debug'] = 'off';
            }

            if (self::getValue('sq_debug') === 'on') {
                if (function_exists('register_shutdown_function')) {
                    register_shutdown_function(array($this, 'showDebug'));
                }
            }
        }
    }

    /**
     * Store the debug for a later view
     */
    public static function dump() {
        if (self::getValue('sq_debug') !== 'on') {
            return;
        }

        $output = '';
        $callee = array('file' => '', 'line' => '');
        if (function_exists('func_get_args')) {
            $arguments = func_get_args();
            $total_arguments = count($arguments);
        } else
            $arguments = array();


        $run_time = number_format(microtime(true) - REQUEST_TIME, 3);
        if (function_exists('debug_backtrace'))
            list($callee) = debug_backtrace();

        $output .= '<fieldset style="background: #FFFFFF; border: 1px #CCCCCC solid; padding: 5px; font-size: 9pt; margin: 0;">';
        $output .= '<legend style="background: #EEEEEE; padding: 2px; font-size: 8pt;">' . $callee['file'] . ' Time: ' . $run_time . ' @ line: ' . $callee['line']
            . '</legend><pre style="margin: 0; font-size: 8pt; text-align: left;">';

        $i = 0;
        foreach ($arguments as $argument) {
            if (count($arguments) > 1)
                $output .= "\n" . '<strong>#' . (++$i) . ' of ' . $total_arguments . '</strong>: ';

            // if argument is boolean, false value does not display, so ...
            if (is_bool($argument))
                $argument = ($argument) ? 'TRUE' : 'FALSE';
            else
                if (is_object($argument) && function_exists('array_reverse') && function_exists('class_parents'))
                    $output .= implode("\n" . '|' . "\n", array_reverse(class_parents($argument))) . "\n" . '|' . "\n";

            $output .= htmlspecialchars(print_r($argument, TRUE))
                . ((is_object($argument) && function_exists('spl_object_hash')) ? spl_object_hash($argument) : '');
        }
        $output .= "</pre>";
        $output .= "</fieldset>";

        self::$debug[] = $output;
    }

    /**
     * Show the debug dump
     */
    public static function showDebug() {
        global $wp_query;
        echo "Debug result: <br />" . '<div id="wpcontent">' . @implode('<br />', self::$debug) . '</div>';

        $run_time = number_format(microtime(true) - REQUEST_TIME, 3);
        $pps = number_format(1 / $run_time, 0);
        $memory_avail = ini_get('memory_limit');
        $memory_used = number_format(memory_get_usage(true) / (1024 * 1024), 2);
        $memory_peak = number_format(memory_get_peak_usage(true) / (1024 * 1024), 2);

        echo PHP_EOL . " Load: {$memory_avail} (avail) / {$memory_used}M (used) / {$memory_peak}M (peak)";
        echo "  | Time: {$run_time}s | {$pps} req/sec";
        echo "<pre>" . print_r($wp_query, true) . "</pre>";
    }

    public function sq_activate() {
        set_transient('sq_activate', true);
        set_transient('sq_rewrite', true);
    }

    public function sq_deactivate() {
        //clear the cron job
        wp_clear_scheduled_hook('sq_processCron');

        $args = array();
        $args['type'] = 'deact';
        SQ_Action::apiCall('sq/user/log', $args, 5);

        remove_filter('rewrite_rules_array', array(SQ_ObjController::getBlock('SQ_BlockSettingsSeo'), 'rewrite_rules'));
        global $wp_rewrite;
        $wp_rewrite->flush_rules();
    }

    public static function emptyCache($post_id = null) {
        if (function_exists('w3tc_pgcache_flush')) {
            w3tc_pgcache_flush();
        }
        if (function_exists('wp_cache_clear_cache')) {
            wp_cache_clear_cache();
        }
        if (function_exists('wp_cache_post_edit') && isset($post_id)) {
            wp_cache_post_edit($post_id);
        }

        if (class_exists("WpFastestCache")) {
            $wpfc = new WpFastestCache();
            $wpfc->deleteCache();
        }
    }

    public static function checkUpgrade() {
        if (self::$options['sq_ver'] == 0 || self::$options['sq_ver'] < SQ_VERSION_ID) {
            //Delete the old versions table
            global $wpdb;
            $wpdb->query("UPDATE " . $wpdb->postmeta . " SET `meta_key` = '_sq_fp_title' WHERE `meta_key` = 'sq_fp_title'");
            $wpdb->query("UPDATE " . $wpdb->postmeta . " SET `meta_key` = '_sq_fp_description' WHERE `meta_key` = 'sq_fp_description'");
            $wpdb->query("UPDATE " . $wpdb->postmeta . " SET `meta_key` = '_sq_fp_ogimage' WHERE `meta_key` = 'sq_fp_ogimage'");
            $wpdb->query("UPDATE " . $wpdb->postmeta . " SET `meta_key` = '_sq_fp_keywords' WHERE `meta_key` = 'sq_fp_keywords'");
            $wpdb->query("UPDATE " . $wpdb->postmeta . " SET `meta_key` = '_sq_post_keyword' WHERE `meta_key` = 'sq_post_keyword'");

            self::saveOptions('sq_ver', SQ_VERSION_ID);

            global $wp_rewrite;
            $wp_rewrite->flush_rules();
        }
    }

}
