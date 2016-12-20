<?php

class SQ_Frontend extends SQ_FrontController {

    public static $options;

    public function __construct() {

        if ($this->_isAjax()) {
            return;
        }

        parent::__construct();

        if (SQ_Tools::$options['sq_use'] == 1) {
            /* Check if sitemap is on  */
            if (SQ_Tools::$options['sq_auto_sitemap'] == 1) {
                /* Load the Sitemap  */
                add_filter('rewrite_rules_array', array($this, 'rewrite_rules'), 1, 1);
                SQ_ObjController::getController('SQ_Sitemaps');
            }

            if (SQ_Tools::$options['sq_auto_feed'] == 1) {
                /* Load the Feed Style  */
                SQ_ObjController::getController('SQ_Feed');
            }

            //validate custom arguments for favicon and sitemap
            add_filter('query_vars', array($this, 'validateParams'), 1, 1);

            if (!$this->_isAjax()) {
                add_filter('sq_title', array($this->model, 'clearTitle'));
                add_filter('sq_description', array($this->model, 'clearDescription'));

                add_action('plugins_loaded', array($this->model, 'startBuffer'));
                //flush the header with the title and removing duplicates
                add_action('wp_head', array($this->model, 'flushHeader'),99);
                add_action('shutdown', array($this->model, 'flushHeader'));
            }

            if (SQ_Tools::$options['sq_url_fix'] == 1) {
                add_action('the_content', array($this, 'fixFeedLinks'), 11);
            }

        }
    }

    public function rewrite_rules($wp_rewrite) {
        if (SQ_Tools::$options['sq_auto_sitemap'] == 1) {
            foreach (SQ_Tools::$options['sq_sitemap'] as $name => $sitemap) {
                $rules[preg_quote($sitemap[0]) . '$'] = 'index.php?sq_feed=' . $name;
            }
        }
        return array_merge($rules, $wp_rewrite);
    }

    private function _isAjax() {
        if (isset($_SERVER['PHP_SELF']) && strpos($_SERVER['PHP_SELF'], '/admin-ajax.php') !== false)
            return true;

        return false;
    }

    /**
     * Hook the Header load
     */
    public function hookFronthead() {

        if (!$this->_isAjax()) {
            if (SQ_Tools::$options['sq_use'] == 1) {
                echo $this->model->setStartTag();
            }

            SQ_ObjController::getController('SQ_DisplayController', false)
                    ->loadMedia(_SQ_THEME_URL_ . 'css/sq_frontend.css');
        }
    }

    /**
     * Called after plugins are loaded
     */
    public function hookPreload() {
        //Check for sitemap and robots
        if (SQ_Tools::$options['sq_use'] == 1) {
            if (isset($_SERVER['REQUEST_URI']) && SQ_Tools::$options['sq_auto_robots'] == 1) {
                if (substr(strrchr($_SERVER['REQUEST_URI'], "/"), 1) == "robots.txt" || $_SERVER['REQUEST_URI'] == "/robots.txt") {
                    $this->model->robots();
                }
            }

            //check the action call
            $this->action();
        }
    }

    /**
     * Change the image path to absolute when in feed
     */
    public function fixFeedLinks($content) {
        if (is_feed()) {
            $find = $replace = $urls = array();

            @preg_match_all('/<img[^>]*src=[\'"]([^\'"]+)[\'"][^>]*>/i', $content, $out);
            if (is_array($out)) {
                if (!is_array($out[1]) || empty($out[1]))
                    return $content;

                foreach ($out[1] as $row) {
                    if (strpos($row, '//') === false) {
                        if (!in_array($row, $urls)) {
                            $urls[] = $row;
                        }
                    }
                }
            }

            @preg_match_all('/<a[^>]*href=[\'"]([^\'"]+)[\'"][^>]*>/i', $content, $out);
            if (is_array($out)) {
                if (!is_array($out[1]) || empty($out[1]))
                    return $content;

                foreach ($out[1] as $row) {
                    if (strpos($row, '//') === false) {
                        if (!in_array($row, $urls)) {
                            $urls[] = $row;
                        }
                    }
                }
            }
            if (!is_array($urls) || (is_array($urls) && empty($urls))) {
                return $content;
            }

            $urls = array_unique($urls);
            foreach ($urls as $url) {
                $find[] = "'" . $url . "'";
                $replace[] = "'" . esc_url(get_bloginfo('url') . $url) . "'";
                $find[] = '"' . $url . '"';
                $replace[] = '"' . esc_url(get_bloginfo('url') . $url) . '"';
            }
            if (!empty($find) && !empty($replace)) {
                $content = str_replace($find, $replace, $content);
            }
        }
        return $content;
    }

    /**
     * Validate the params for getting the basic info from the server
     * eg favicon.ico
     *
     * @param array $vars
     * @return $vars
     */
    public function validateParams($vars) {
        array_push($vars, 'sq_feed');
        array_push($vars, 'sq_get');
        array_push($vars, 'sq_size');
        return $vars;
    }

    public function action() {
        global $wp_query;
        if (!empty($wp_query->query_vars["sq_get"])) {
            $wp_query->is_404 = false;


            switch (get_query_var('sq_get')) {
                case 'favicon':
                    if (SQ_Tools::$options['favicon'] <> '') {
                        //show the favico file
                        SQ_Tools::setHeader('ico');
                        readfile(_SQ_CACHE_DIR_ . SQ_Tools::$options['favicon']);
                        exit();
                    }
                    break;
                case 'touchicon':
                    $size = (int) get_query_var('sq_size');
                    if (SQ_Tools::$options['favicon'] <> '') {
                        //show the favico file
                        SQ_Tools::setHeader('png');
                        if ($size <> '') {
                            readfile(_SQ_CACHE_DIR_ . SQ_Tools::$options['favicon'] . get_query_var('sq_size'));
                        } else {
                            readfile(_SQ_CACHE_DIR_ . SQ_Tools::$options['favicon']);
                        }
                        exit();
                    }
                    break;

                case 'feedcss':
                    readfile(_SQ_THEME_DIR_ . 'css/' . 'sq_feed.css');
                    exit();
                    break;
            }
        }
    }

    public function hookFrontfooter(){
        echo $this->model->getGoogleAnalyticsAMPBody();
    }
}
