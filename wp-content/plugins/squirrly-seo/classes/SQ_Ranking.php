<?php

/**
 * Class for Google Ranking Record
 */
class SQ_Ranking extends SQ_FrontController {

    private $keyword;
    private $post_id;
    private $error;

    //--
    public function getCountry() {
        if (isset(SQ_Tools::$options['sq_google_country']) && SQ_Tools::$options['sq_google_country'] <> '') {
            return SQ_Tools::$options['sq_google_country'];
        }
        return 'com';
    }

    public function getRefererCountry() {
        $convert_refc = array('com' => 'us', '.off.ai' => 'ai', 'com.ag' => 'ag', 'com.ar' => 'ar', 'com.au' => 'au', 'com.br' => 'br', 'com.co' => 'co', 'co.cr' => 'cr', 'com.cu' => 'cu', 'com.do' => 'do', 'com.ec' => 'ec', 'com.sv' => 'sv', 'com.fj' => 'fj', 'com.gi' => 'gi', 'com.gr' => 'gr', 'com.hk' => 'hk', 'co.hu' => 'hu', 'co.in' => 'in', 'co.im' => 'im', 'co.il' => 'il', 'com.jm' => 'jm', 'co.jp' => 'jp', 'co.je' => 'je', 'co.kr' => 'kr', 'co.ls' => 'ls', 'com.my' => 'my', 'com.mt' => 'mt', 'com.mx' => 'mx', 'com.na' => 'na', 'com.np' => 'np', 'com.ni' => 'ni', 'com.nf' => 'nf', 'com.pk' => 'pk', 'com.pa' => 'pa', 'com.py' => 'py', 'com.pe' => 'pe', 'com.ph' => 'ph', 'com.pr' => 'pr', 'com.sg' => 'sg', 'co.za' => 'za', 'com.tw' => 'tw', 'com.th' => 'th', 'com.tr' => 'tr', 'com.ua' => 'ua', 'com.uk' => 'uk', 'com.uy' => 'uy',);
        $country = $this->getCountry();
        if (array_key_exists($country, $convert_refc)) {
            return $convert_refc[$country];
        }
        return $country;
    }

    /**
     * Get the google language from settings
     * @return type
     */
    public function getLanguage() {
        if (isset(SQ_Tools::$options['sq_google_language']) && SQ_Tools::$options['sq_google_language'] <> '') {
            return SQ_Tools::$options['sq_google_language'];
        }
        return 'en';
    }

    /**
     * Set the Post id
     * @return type
     */
    public function setPost($post_id) {
        $this->post_id = $post_id;
    }

    /**
     * Get the current keyword
     * @param type $keyword
     */
    public function setKeyword($keyword) {
        $this->keyword = str_replace(" ", "+", urlencode(strtolower($keyword)));
    }

    /**
     * Process Ranking on brief request
     * @param type $return
     */
    public function processRanking($post_id, $keyword) {
        $this->setPost($post_id);
        $this->setKeyword(trim($keyword));

        if (isset($this->keyword) && $this->keyword <> '') {
            return $this->getGoogleRank();
        }
        return false;
    }

    /**
     * Call google to get the keyword position
     *
     * @param integer $post_id
     * @param string $keyword
     * @param string $country: com | country extension
     * @param string $language: en | local language
     * @return boolean|int
     */
    public function getGoogleRank() {
        global $wpdb;
        $this->error = '';

        if (trim($this->keyword) == '') {
            $this->error = 'no keyword for post_id:' . $this->post_id;
            return false;
        }

        if (!function_exists('preg_match_all')) {
            return false;
        }

        $arg = array('timeout' => 10);
        $arg['as_q'] = str_replace(" ", "+", strtolower(trim($this->keyword)));
        $arg['hl'] = $this->getLanguage();
        //$arg['gl'] = $this->getRefererCountry();

        if (SQ_Tools::$options['sq_google_country_strict'] == 1) {
            $arg['cr'] = 'country' . strtoupper($this->getRefererCountry());
        }
        $arg['start'] = '0';
        $arg['num'] = '100';

        $arg['safe'] = 'active';
        $arg['pws'] = '0';
        $arg['as_epq'] = '';
        $arg['as_oq'] = '';
        $arg['as_nlo'] = '';
        $arg['as_nhi'] = '';
        $arg['as_qdr'] = 'all';
        $arg['as_sitesearch'] = '';
        $arg['as_occt'] = 'any';
        $arg['tbs'] = '';
        $arg['as_filetype'] = '';
        $arg['as_rights'] = '';

        $country = $this->getCountry();

        if ($country == '' || $arg['hl'] == '') {
            $this->error = 'no country (' . $country . ')';
            return false;
        }

        $user_agents = array(
            'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/43.0.2357.130 Safari/537.36',
            'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:38.0) Gecko/20100101 Firefox/38.0',
            'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/43.0.2357.124 Safari/537.36',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_3) AppleWebKit/600.6.3 (KHTML, like Gecko) Version/8.0.6 Safari/600.6.3',
            'Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/43.0.2357.130 Safari/537.36',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/43.0.2357.130 Safari/537.36',
            'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/43.0.2357.132 Safari/537.36',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/43.0.2357.124 Safari/537.36',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_4) AppleWebKit/600.7.12 (KHTML, like Gecko) Version/8.0.7 Safari/600.7.12',
            'Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/43.0.2357.124 Safari/537.36',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.10; rv:38.0) Gecko/20100101 Firefox/38.0',
            'Mozilla/5.0 (iPad; CPU OS 5_1 like Mac OS X) AppleWebKit/534.46 (KHTML, like Gecko ) Version/5.1 Mobile/9B176 Safari/7534.48.3',
            'Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_8; de-at) AppleWebKit/533.21.1 (KHTML, like Gecko) Version/5.0.5 Safari/533.21.1',
            'Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_7; da-dk) AppleWebKit/533.21.1 (KHTML, like Gecko) Version/5.0.5 Safari/533.21.1',
            'Mozilla/5.0 (Windows; U; Windows NT 6.1; tr-TR) AppleWebKit/533.20.25 (KHTML, like Gecko) Version/5.0.4 Safari/533.20.27',
            'Mozilla/5.0 (Windows; U; Windows NT 6.1; fr-FR) AppleWebKit/533.20.25 (KHTML, like Gecko) Version/5.0.4 Safari/533.20.27',
            'Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US) AppleWebKit/533.20.25 (KHTML, like Gecko) Version/5.0.4 Safari/533.20.27',
            'Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_6; en-us) AppleWebKit/533.20.25 (KHTML, like Gecko) Version/5.0.4 Safari/533.20.27',
            'Mozilla/5.0 (Windows; U; Windows NT 6.0; en-US) AppleWebKit/533.20.25 (KHTML, like Gecko) Version/5.0.3 Safari/533.19.4',
            'Opera/9.80 (X11; Linux i686; Ubuntu/14.10) Presto/2.12.388 Version/12.16',
            'Opera/9.80 (Windows NT 6.0) Presto/2.12.388 Version/12.14',
            'Mozilla/5.0 (Windows NT 6.0; rv:2.0) Gecko/20100101 Firefox/4.0 Opera 12.14',
            'Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.0) Opera 12.14',
            'Opera/12.80 (Windows NT 5.1; U; en) Presto/2.10.289 Version/12.02',
            'Opera/9.80 (Windows NT 6.1; U; es-ES) Presto/2.9.181 Version/12.00',
            'Opera/12.0(Windows NT 5.1;U;en)Presto/22.9.168 Version/12.00',
            'Mozilla/5.0 (Windows NT 6.3; WOW64; rv:38.0) Gecko/20100101 Firefox/38.0',
            'Mozilla/5.0 (Windows NT 6.1; WOW64; Trident/7.0; rv:11.0) like Gecko',
            'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:38.0) Gecko/20100101 Firefox/38.0',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/43.0.2357.130 Safari/537.36',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/43.0.2357.132 Safari/537.36',
            'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/43.0.2357.130 Safari/537.36',
            'Mozilla/5.0 (Windows NT 6.3; WOW64; rv:39.0) Gecko/20100101 Firefox/39.0',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/43.0.2357.132 Safari/537.36',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/43.0.2357.130 Safari/537.36',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.10; rv:39.0) Gecko/20100101 Firefox/39.0',
            'Mozilla/5.0 (Windows NT 6.3; WOW64; Trident/7.0; rv:11.0) like Gecko',
            'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/43.0.2357.130 Safari/537.36',
            'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/43.0.2357.124 Safari/537.36',
        );

        $option = array();
        $option['User-Agent'] = $user_agents[mt_rand(0, count($user_agents) - 1)];
        $option['followlocation'] = true;
        //Grab the remote informations from google
        $response = utf8_decode(SQ_Tools::sq_remote_get("https://www.google.$country/search", $arg, $option));

        //Check the values for block IP
        if (strpos($response, "</h3>") === false) {
            set_transient('google_blocked', 1, (60 * 60 * 1));
            return -2; //return error
        }

        //Get the permalink of the current post
        $permalink = get_permalink($this->post_id);
        if ($permalink == '') {
            $this->error = 'no permalink for post_id:' . $this->post_id;
            return false;
        }

        preg_match_all('/<h3.*?><a href="(.*?)".*?<\/h3>/is', $response, $matches);

        SQ_Tools::dump($matches[1]);
        if (!empty($matches[1])) {
            $pos = -1;
            foreach ($matches[1] as $index => $url) {
                if (strpos($url, rtrim($permalink, '/')) !== false) {
                    $pos = $index + 1;
                    break;
                }
            }
            return $pos;
        }
        $this->error = 'no results returned by google';
        return false;
    }

    /**
     * Do google rank with cron
     * @global type $wpdb
     */
    public function processCron() {
        global $wpdb;
        if (get_transient('google_blocked') !== false) {
            return;
        }
        set_time_limit(3000);
        /* Load the Submit Actions Handler */
        SQ_ObjController::getController('SQ_Tools', false);
        SQ_ObjController::getController('SQ_Action', false);

        //check 20 keyword at one time
        $sql = "SELECT `post_id`, `meta_value`
                       FROM `" . $wpdb->postmeta . "`
                       WHERE (`meta_key` = '_sq_post_keyword')
                       ORDER BY `post_id` DESC";

        if ($rows = $wpdb->get_results($sql)) {
            $count = 0;
            foreach ($rows as $row) {
                if ($count > SQ_Tools::$options['sq_google_ranksperhour']) {
                    break; //check only 10 keywords at the time
                }
                if ($row->meta_value <> '') {
                    $json = json_decode($row->meta_value);
                    //If keyword is set and no rank or last check is 2 days ago
                    if (isset($json->keyword) && $json->keyword <> '' &&
                            (!isset($json->rank) ||
                            (isset($json->update) && (time() - $json->update > (60 * 60 * 24 * 2))) || //if indexed then check every 2 days
                            (isset($json->update) && isset($json->rank) && $json->rank == -1 && (time() - $json->update > (60 * 60 * 24))) //if not indexed than check often
                            )) {

                        $rank = $this->processRanking($row->post_id, $json->keyword);
                        if ($rank == -1) {
                            $count++;
                            sleep(mt_rand(20, 40));
                            //if not indexed with the keyword then find the url
                            if ($this->processRanking($row->post_id, get_permalink($row->post_id)) > 0) {
                                $rank = 0; //for permalink index set 0
                            }
                        }

                        //if there is a success response than save it
                        if (isset($rank) && $rank >= -1) {
                            $json->rank = $rank;
                            $json->country = $this->getCountry();
                            $json->language = $this->getLanguage();
                            SQ_ObjController::getModel('SQ_Post')->saveKeyword($row->post_id, $json);
                        }
                        set_transient('sq_rank' . $row->post_id, $rank, (60 * 60 * 24));
                        //if rank proccess has no error

                        $args = array();
                        $args['post_id'] = $row->post_id;
                        $args['rank'] = (string) $rank;
                        $args['error'] = $this->error;
                        $args['country'] = $this->getCountry();
                        $args['language'] = $this->getLanguage();

                        SQ_Action::apiCall('sq/user-analytics/saveserp', $args);

                        $count++;
                        sleep(mt_rand(20, 40));
                    }
                }
            }
        }
    }

    /**
     * Get keyword from earlier version
     *
     */
    public function getKeywordHistory() {
        global $wpdb;
        //Check if ranks is saved in database
        $sql = "SELECT a.`global_rank`, a.`keyword`, a.`post_id`
                FROM `sq_analytics` as a
                INNER JOIN (SELECT MAX(`id`) as id FROM `sq_analytics` WHERE `keyword` <> '' GROUP BY `post_id`) as a1 ON a1.id = a.id ";

        if ($rows = $wpdb->get_results($sql)) {
            foreach ($rows as $values) {
                if ($json = SQ_ObjController::getModel('SQ_Post')->getKeyword($values->post_id)) {
                    $json->keyword = urldecode($values->keyword);
                    if ($values->global_rank > 0) {
                        $json->rank = $values->global_rank;
                    }
                    SQ_ObjController::getModel('SQ_Post')->saveKeyword($values->post_id, $json);
                } else {
                    $args = array();
                    $args['keyword'] = urldecode($values->keyword);
                    if ($values->global_rank > 0) {
                        $json->rank = $values->global_rank;
                    }
                    SQ_ObjController::getModel('SQ_Post')->saveKeyword($values->post_id, json_decode(json_encode($args)));
                }
            }
        }
    }

}
