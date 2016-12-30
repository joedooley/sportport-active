<?php

/**
 * Class for Sitemap Generator
 */
class SQ_Sitemaps extends SQ_FrontController {
    /* @var string root name */

    var $root = 'sitemap';
    /* @var string post limit */
    var $posts_limit = 1000;
    var $news_limit = 50;

    public function __construct() {
        parent::__construct();
        add_filter('template_redirect', array($this, 'hookPreventRedirect'), 1, 0);
        add_filter('user_trailingslashit', array($this, 'untrailingslashit'));
        add_action('sq_processPing', array($this, 'processCron'));
    }

    public function hookPreventRedirect() {
        global $wp_query;
        if (!empty($wp_query->query_vars["sq_feed"])) {
            $wp_query->is_404 = false;
            $wp_query->is_feed = true;
            $this->feedRequest($wp_query->query_vars);
            $this->showSitemap();
            exit();
        }
    }

    public function refreshSitemap($new_status, $old_status, $post) {
        if ($old_status <> $new_status && $new_status = 'publish') {
            if (SQ_Tools::$options['sq_sitemap_ping'] == 1) {
                wp_schedule_single_event(time() + 5, 'sq_processPing');
            }
        }
    }

    /**
     * Listen the feed call from wordpress
     * @param array $request
     * @return array
     */
    public function feedRequest($request) {
        global $wp_query;

        if (!empty($request['feed'])) {
            $request['sq_feed'] = $request['feed'];
        }

        if (isset($request['sq_feed']) && strpos($request['sq_feed'], 'sitemap') !== false) {
            @ini_set('memory_limit', '512M');

            $this->model->type = $request['sq_feed'];

            //show products
            if ($this->model->type == 'sitemap-product') {
                if (SQ_ObjController::getModel('SQ_BlockSettingsSeo')->isEcommerce() && SQ_Tools::$options['sq_sitemap'][$this->model->type][1] == 2) {
                    SQ_Tools::$options['sq_sitemap'][$this->model->type][1] = 1;
                }
            }

            if (isset(SQ_Tools::$options['sq_sitemap'][$this->model->type]) && SQ_Tools::$options['sq_sitemap'][$this->model->type][1] == 1) {

                add_action('do_feed_' . $request['sq_feed'], array($this, 'showSitemap'));
                //PREPARE CUSTOM QUERIES
                switch ($this->model->type) {

                    case 'sitemap-news':
                        if ($this->model->type == 'sitemap-news') {
                            $this->posts_limit = $this->news_limit;
                        }
                    case 'sitemap-category':
                    case 'sitemap-post_tag':
                    case 'sitemap-custom-tax':
                        remove_all_filters('terms_clauses'); //prevent language filters
                        add_filter('get_terms_fields', array($this, 'customTaxFilter'), 5, 2);

                        break;
                    case 'sitemap-post':
                        add_action('parse_query', array($this, 'postFilter'), 99);
                        break;
                    case 'sitemap-page':
                        add_action('parse_query', array($this, 'pageFilter'), 99);
                        break;
                    case 'sitemap-author':
                        add_filter('sq-sitemap-authors', array($this, 'authorFilter'), 5);
                        break;
                    case 'sitemap-custom-post':
                        add_action('parse_query', array($this, 'customPostFilter'), 99);
                        break;
                    case 'sitemap-product':
                        add_action('parse_query', array($this, 'productFilter'), 99);
                        break;
                    case 'sitemap-archive':
                        add_filter('sq-sitemap-archive', array($this, 'archiveFilter'), 5);
                        break;
                }

                add_filter('post_limits', array($this, 'setLimits'));
            }
        }

        return $request;
    }

    /**
     * Show the Sitemap Header
     * @global integer $blog_id Used for charset
     * @param array $include Include schema
     */
    public function showSitemapHeader($include = array()) {
        @ini_set('memory_limit', apply_filters('admin_memory_limit', WP_MAX_MEMORY_LIMIT));
        global $blog_id;

        header('Status: 200 OK', true, 200);
        header('Content-Type: text/xml; charset=' . get_bloginfo('charset'), true);
//Generate header
        echo '<?xml version="1.0" encoding="' . get_bloginfo('charset') . '"?>' . "\n";
        echo '<?xml-stylesheet type="text/xsl" href="' . wp_make_link_relative(_SQ_THEME_URL_) . 'css/sq_sitemap' . ($this->model->type == 'sitemap' ? 'index' : '') . '.xsl"?>' . "\n";
        echo '<!-- generated-on="' . date('Y-m-d\TH:i:s+00:00') . '" -->' . "\n";
        echo '<!-- generator="Squirrly SEO Sitemap" -->' . "\n";
        echo '<!-- generator-url="https://wordpress.org/plugins/squirrly-seo/" -->' . "\n";
        echo '<!-- generator-version="' . SQ_VERSION . '" -->' . "\n";
        echo '' . "\n";

        $schema = array(
            'image' => 'xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"',
            'video' => 'xmlns:video="http://www.google.com/schemas/sitemap-video/1.1"',
            'news' => 'xmlns:news="http://www.google.com/schemas/sitemap-news/0.9"',
            'mobile' => 'xmlns:mobile="http://www.google.com/schemas/sitemap-mobile/1.0"',
        );

        if (!empty($include))
            $include = array_unique($include);

        switch ($this->model->type) {
            case 'sitemap':
                echo '<sitemapindex xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" '
                . 'xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/siteindex.xsd" '
                . 'xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"';
                foreach ($include as $value) {
                    echo ' ' . $schema[$value] . "\n";
                }
                echo '>' . "\n";
                break;
            case 'sitemap-news':
                array_push($include, 'news');
                $include = array_unique($include);
            default:
                echo '<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" '
                . 'xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" '
                . 'xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"';
                if (!empty($include))
                    foreach ($include as $value) {
                        echo " " . $schema[$value] . " ";
                    }
                echo '>' . "\n";
                break;
        }
    }

    /**
     * Show the Sitemap Footer
     */
    private function showSitemapFooter() {
        switch ($this->model->type) {
            case 'sitemap':
                echo '</sitemapindex>' . "\n";
                break;
            default :
                echo '</urlset>' . "\n";
                break;
        }
    }

    /**
     * Create the XML sitemap
     * @return string
     */
    public function showSitemap() {
        switch ($this->model->type) {
            case 'sitemap':
                $this->showSitemapHeader();

                foreach (SQ_Tools::$options['sq_sitemap'] as $name => $value) {
                    //force to show products if not preset
                    if ($name == 'sitemap-product' && !SQ_ObjController::getModel('SQ_BlockSettingsSeo')->isEcommerce()) {
                        continue;
                    }

                    if ($name !== 'sitemap' && ($value[1] == 1 || $value[1] == 2)) {
                        echo "\t" . '<sitemap>' . "\n";
                        echo "\t" . '<loc>' . $this->getXmlUrl($name) . '</loc>' . "\n";
                        echo "\t" . '<lastmod>' . mysql2date('Y-m-d\TH:i:s+00:00', get_lastpostmodified('gmt'), false) . '</lastmod>' . "\n";
                        echo "\t" . '</sitemap>' . "\n";
                    }
                }
                $this->showSitemapFooter();
                break;
            case 'sitemap-home':
                $this->showPackXml($this->model->getHomeLink());
                break;
            case 'sitemap-news':
                $this->showPackXml($this->model->getListNews());
                break;
            case 'sitemap-category':
            case 'sitemap-post_tag':
            case 'sitemap-custom-tax':
                $this->showPackXml($this->model->getListTerms());
                break;
            case 'sitemap-author':
                $this->showPackXml($this->model->getListAuthors());
                break;
            case 'sitemap-archive':
                $this->showPackXml($this->model->getListArchive());
                break;

            default:
                $this->showPackXml($this->model->getListPosts());
                break;
        }
    }

    /**
     * Pach the XML for each sitemap
     * @param type $xml
     * @return type
     */
    public function showPackXml($xml) {
        if (empty($xml)) {
            return;
        }
        if (!isset($xml['contains'])) {
            $xml['contains'] = '';
        }
        $this->showSitemapHeader($xml['contains']);

        unset($xml['contains']);
        foreach ($xml as $row) {
            echo "\t" . '<url>' . "\n";

            if (is_array($row)) {
                echo $this->getRecursiveXml($row);
            }
            echo "\t" . '</url>' . "\n";
        }
        $this->showSitemapFooter();
        unset($xml);
    }

    public function getRecursiveXml($xml, $pkey = '', $level = 2) {
        $str = '';
        $tab = str_repeat("\t", $level);
        if (is_array($xml)) {
            $cnt = 0;
            foreach ($xml as $key => $data) {
                if (!is_array($data)) {
                    $str .= $tab . '<' . $key . ($key == 'video:player_loc' ? ' allow_embed="yes"' : '') . '>' . $data . ((strpos($data, '?') == false && $key == 'video:player_loc') ? '' : '') . '</' . $key . '>' . "\n";
                } else {
                    if ($this->getRecursiveXml($data) <> '') {
                        if (!$this->_ckeckIntergerArray($data)) {
                            $str .= $tab . '<' . (!is_numeric($key) ? $key : $pkey) . '>' . "\n";
                        }
                        $str .= $this->getRecursiveXml($data, $key, ($this->_ckeckIntergerArray($data) ? $level : $level + 1));
                        if (!$this->_ckeckIntergerArray($data)) {
                            $str .= $tab . '</' . (!is_numeric($key) ? $key : $pkey) . '>' . "\n";
                        }
                    }
                }
                $cnt ++;
            }
        }
        return $str;
    }

    private function _ckeckIntergerArray($data) {
        foreach ($data as $key => $value) {
            if (is_numeric($key)) {
                return true;
            }
            break;
        }
        return false;
    }

    /**
     * Set the query limit
     * @param integer $limits
     * @return string
     */
    public function setLimits($limits) {
        if (isset($this->posts_limit) && $this->posts_limit > 0) {
            return 'LIMIT 0, ' . $this->posts_limit;
        }
    }

    /**
     * Get the url for each sitemap
     * @param string $sitemap
     * @return string
     */
    public function getXmlUrl($sitemap) {
        if (!get_option('permalink_structure')) {
            $sitemap = '?sq_feed=' . str_replace('.xml', '', $sitemap);
        } else {
            if (isset(SQ_Tools::$options['sq_sitemap'][$sitemap])) {
                $sitemap = SQ_Tools::$options['sq_sitemap'][$sitemap][0];
            }

            if (strpos($sitemap, '.xml') === false) {
                $sitemap .= '.xml';
            }
        }

        return esc_url(trailingslashit(home_url())) . $sitemap;
    }

    public function processCron() {
        SQ_ObjController::getController('SQ_Tools', false);
        foreach (SQ_Tools::$options['sq_sitemap'] as $name => $sitemap) {
            if ($sitemap[1] == 1) {
                $this->SendPing($this->getXmlUrl($name));
            }
        }
    }

    /**
     * Ping the sitemap to Google and Bing
     * @param string $sitemapUrl
     * @return boolean
     */
    protected function SendPing($sitemapUrl) {
        $success = true;
        $urls = array(
            "http://www.google.com/webmasters/sitemaps/ping?sitemap=%s",
            "http://www.bing.com/webmaster/ping.aspx?siteMap=%s",
        );

        foreach ($urls as $url) {
            if ($responce = SQ_Tools::sq_remote_get($url)) {
                $success = true;
                sleep(mt_rand(2, 5));
            }
        }

        return $success;
    }

    /**
     * Delete the fizical file if exists
     * @return boolean
     */
    public function deleteSitemapFile() {
        if (isset(SQ_Tools::$options['sq_sitemap'][$this->root])) {
            if (file_exists(ABSPATH . SQ_Tools::$options['sq_sitemap'][$this->root])) {
                @unlink(ABSPATH . SQ_Tools::$options['sq_sitemap'][$this->root]);
                return true;
            }
        }
        return false;
    }

    /**
     * Remove the trailing slash from permalinks that have an extension,
     * such as /sitemap.xml
     *
     * @param string $request
     */
    public function untrailingslashit($request) {
        if (pathinfo($request, PATHINFO_EXTENSION)) {
            return untrailingslashit($request);
        }
        return $request; // trailingslashit($request);
    }

    public function postFilter(&$query) {
        $query->set('tax_query', array());
    }

    public function customTaxFilter($query) {
        global $wpdb;

        $query[] = "(SELECT
                        UNIX_TIMESTAMP(MAX(p.post_date_gmt)) as _mod_date
                 FROM {$wpdb->posts} p, {$wpdb->term_relationships} r
                 WHERE p.ID = r.object_id  AND p.post_status = 'publish'  AND p.post_password = ''  AND r.term_taxonomy_id = tt.term_taxonomy_id
                ) as lastmod";

        return $query;
    }

    public function pageFilter(&$query) {
        $query->set('post_type', array('page'));
        $query->set('tax_query', array());
    }

    public function authorFilter() {
        //get only the author with posts
        add_filter('pre_user_query', array($this, 'userFilter'));
        return get_users();
    }

    public function userFilter($query) {
        $query->query_fields .= ',p.lastmod';
        $query->query_from .= ' LEFT OUTER JOIN (
            SELECT MAX(post_modified) as lastmod, post_author, COUNT(*) as post_count
            FROM wp_posts
            WHERE post_type = "post" AND post_status = "publish"
            GROUP BY post_author
        ) p ON (wp_users.ID = p.post_author)';
        $query->query_where .= ' AND post_count  > 0 ';
    }

    public function customPostFilter(&$query) {
        $types = get_post_types();
        foreach (array('post', 'page', 'attachment', 'revision', 'nav_menu_item', 'product', 'wpsc-product') as $exclude) {
            if (in_array($exclude, $types)) {
                unset($types[$exclude]);
            }
        }

        foreach ($types as $type) {
            $type_data = get_post_type_object($type);
            if ((isset($type_data->rewrite['feeds']) && $type_data->rewrite['feeds'] == 1) || (isset($type_data->feeds) && $type_data->feeds == 1)) {
                continue;
            }
            unset($types[$type]);
        }

        if (empty($types)) {
            array_push($types, 'custom-post');
        }

        $query->set('post_type', $types); // id of page or post
        $query->set('tax_query', array());
    }

    public function productFilter(&$query) {
        if (!$types = SQ_ObjController::getModel('SQ_BlockSettingsSeo')->isEcommerce()) {
            $types = array('custom-post');
        }
        $query->set('post_type', $types); // id of page or post
        $query->set('tax_query', array());
    }

    public function archiveFilter() {
        global $wpdb;
        $archives = $wpdb->get_results("
                        SELECT DISTINCT YEAR(post_date_gmt) as `year`, MONTH(post_date_gmt) as `month`, max(post_date_gmt) as lastmod, count(ID) as posts
                        FROM $wpdb->posts
                        WHERE post_date_gmt < NOW()  AND post_status = 'publish'  AND post_type = 'post'
                        GROUP BY YEAR(post_date_gmt),  MONTH(post_date_gmt)
                        ORDER BY  post_date_gmt DESC
                    ");
        return $archives;
    }

}
