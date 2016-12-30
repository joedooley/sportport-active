<?php

/**
 * Class for Google Ranking Record
 */
class SQ_Ranking extends SQ_FrontController
{

    private $keyword;
    private $post_id;
    private $error;

    //--
    public function getCountry()
    {
        if (isset(SQ_Tools::$options['sq_google_country']) && SQ_Tools::$options['sq_google_country'] <> '') {
            return SQ_Tools::$options['sq_google_country'];
        }
        return 'com';
    }

    public function getRefererCountry()
    {
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
    public function getLanguage()
    {
        if (isset(SQ_Tools::$options['sq_google_language']) && SQ_Tools::$options['sq_google_language'] <> '') {
            return SQ_Tools::$options['sq_google_language'];
        }
        return 'en';
    }

    /**
     * Set the Post id
     * @return type
     */
    public function setPost($post_id)
    {
        $this->post_id = $post_id;
    }

    /**
     * Get the current keyword
     * @param type $keyword
     */
    public function setKeyword($keyword)
    {
        $this->keyword = str_replace(" ", "+", urlencode(strtolower($keyword)));
    }

    /**
     * Process Ranking on brief request
     * @param type $return
     */
    public function processRanking($post_id, $keyword)
    {
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
     * @param string $country : com | country extension
     * @param string $language : en | local language
     * @return boolean|int
     */
    public function getGoogleRank()
    {
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
            'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2228.0 Safari/537.36',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2227.1 Safari/537.36',
            'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2227.0 Safari/537.36',
            'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2227.0 Safari/537.36',
            'Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2226.0 Safari/537.36',
            'Mozilla/5.0 (Windows NT 6.4; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2225.0 Safari/537.36',
            'Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2225.0 Safari/537.36',
            'Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2224.3 Safari/537.36',
            'Mozilla/5.0 (Windows NT 10.0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/40.0.2214.93 Safari/537.36',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/37.0.2062.124 Safari/537.36',
            'Mozilla/5.0 (Windows NT 6.3; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/37.0.2049.0 Safari/537.36',
            'Mozilla/5.0 (Windows NT 4.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/37.0.2049.0 Safari/537.36',
            'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/36.0.1985.67 Safari/537.36',
            'Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/36.0.1985.67 Safari/537.36',
            'Mozilla/5.0 (X11; OpenBSD i386) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/36.0.1985.125 Safari/537.36',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/36.0.1944.0 Safari/537.36',
            'Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/35.0.3319.102 Safari/537.36',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_3) AppleWebKit/537.75.14 (KHTML, like Gecko) Version/7.0.3 Safari/7046A194A',
            'Mozilla/5.0 (iPad; CPU OS 6_0 like Mac OS X) AppleWebKit/536.26 (KHTML, like Gecko) Version/6.0 Mobile/10A5355d Safari/8536.25',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_6_8) AppleWebKit/537.13+ (KHTML, like Gecko) Version/5.1.7 Safari/534.57.2',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_7_3) AppleWebKit/534.55.3 (KHTML, like Gecko) Version/5.1.3 Safari/534.53.10',
            'Mozilla/5.0 (iPad; CPU OS 5_1 like Mac OS X) AppleWebKit/534.46 (KHTML, like Gecko ) Version/5.1 Mobile/9B176 Safari/7534.48.3',
            'Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_8; de-at) AppleWebKit/533.21.1 (KHTML, like Gecko) Version/5.0.5 Safari/533.21.1',
            'Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_7; da-dk) AppleWebKit/533.21.1 (KHTML, like Gecko) Version/5.0.5 Safari/533.21.1',
            'Mozilla/5.0 (Windows; U; Windows NT 6.1; tr-TR) AppleWebKit/533.20.25 (KHTML, like Gecko) Version/5.0.4 Safari/533.20.27',
            'Mozilla/5.0 (Windows; U; Windows NT 6.1; rv:2.2) Gecko/20110201',
            'Mozilla/5.0 (Windows; U; Windows NT 6.1; it; rv:2.0b4) Gecko/20100818',
            'Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.9a3pre) Gecko/20070330',
            'Mozilla/5.0 (Windows; U; Windows NT 5.0; en-US; rv:1.9.2a1pre) Gecko',
            'Mozilla/5.0 (Windows; U; Windows NT 5.1; pl; rv:1.9.2.3) Gecko/20100401 Lightningquail/3.6.3',
            'Mozilla/5.0 (X11; ; Linux i686; rv:1.9.2.20) Gecko/20110805',
            'Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10.6; en-US; rv:1.9.2.13; ) Gecko/20101203',
            'Mozilla/5.0 (Windows; U; Windows NT 6.0; en-US; rv:1.9.1b3) Gecko/20090305',
            'Mozilla/5.0 (Windows; U; Windows NT 5.1; zh-TW; rv:1.9.0.9) Gecko/2009040821',
            'Mozilla/5.0 (X11; U; Linux i686; ru; rv:1.9.0.8) Gecko/2009032711',
            'Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.9.0.7) Gecko/2009032803',
            'Mozilla/5.0 (Windows; U; Windows NT 6.0; en-GB; rv:1.9.0.7) Gecko/2009021910 MEGAUPLOAD 1.0',
            'Mozilla/5.0 (Windows; U; BeOS; en-US; rv:1.9.0.7) Gecko/2009021910',
            'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.3) Gecko/20070321',
            'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.3) Gecko/20070309 Mozilla/4.8 [en] (Windows NT 5.1; U)',
            'Mozilla/5.0 (Macintosh; U; Intel Mac OS X; nl-NL; rv:1.8.1.3) Gecko/20080722',
            'Mozilla/5.0 (Windows NT 6.1; WOW64; Trident/7.0; AS; rv:11.0) like Gecko',
            'Mozilla/5.0 (compatible, MSIE 11, Windows NT 6.3; Trident/7.0; rv:11.0) like Gecko',
            'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 7.0; InfoPath.3; .NET CLR 3.1.40767; Trident/6.0; en-IN)',
            'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; WOW64; Trident/6.0)',
            'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; Trident/6.0)',
            'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; Trident/5.0)',
            'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; Trident/4.0; InfoPath.2; SV1; .NET CLR 2.0.50727; WOW64)',
            'Mozilla/5.0 (compatible; MSIE 10.0; Macintosh; Intel Mac OS X 10_7_3; Trident/6.0)',
            'Mozilla/4.0 (Compatible; MSIE 8.0; Windows NT 5.2; Trident/6.0)',
            'Mozilla/4.0 (compatible; MSIE 10.0; Windows NT 6.1; Trident/5.0)',
            'Mozilla/1.22 (compatible; MSIE 10.0; Windows 3.1)',
            'Mozilla/5.0 (Windows; U; MSIE 9.0; WIndows NT 9.0; en-US))',
            'Mozilla/5.0 (Windows; U; MSIE 9.0; Windows NT 9.0; en-US)',
            'Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 7.1; Trident/5.0)',
            'Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; WOW64; Trident/5.0; SLCC2; Media Center PC 6.0; InfoPath.3; MS-RTC LM 8; Zune 4.7)',
            'Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; WOW64; Trident/5.0; SLCC2; Media Center PC 6.0; InfoPath.3; MS-RTC LM 8; Zune 4.7',
            'Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; WOW64; Trident/5.0; chromeframe/12.0.742.112)',
            'Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; Win64; x64; Trident/5.0',
            'Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; Trident/5.0; yie8)',
            'Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; Trident/5.0; chromeframe/13.0.782.215)',
            'Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; Trident/5.0; chromeframe/11.0.696.57)',
            'Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; Trident/5.0) chromeframe/10.0.648.205',
            'Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.0; Trident/5.0; chromeframe/11.0.696.57)',
            'Mozilla/5.0 (compatible; MSIE 8.0; Windows NT 5.1; Trident/4.0; .NET CLR 1.1.4322; .NET CLR 2.0.50727)',
            'Mozilla/5.0 (compatible; MSIE 8.0; Windows NT 5.1; SLCC1; .NET CLR 1.1.4322)',
            'Mozilla/5.0 (compatible; MSIE 8.0; Windows NT 5.0; Trident/4.0; InfoPath.1; SV1; .NET CLR 3.0.4506.2152; .NET CLR 3.5.30729; .NET CLR 3.0.04506.30)',
            'Mozilla/5.0 (compatible; MSIE 7.0; Windows NT 5.0; Trident/4.0; FBSMTWB; .NET CLR 2.0.34861; .NET CLR 3.0.3746.3218; .NET CLR 3.5.33652; msn OptimizedIE8;ENUS)',
            'Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.2; Trident/4.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0)',
            'Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1; WOW64; Trident/4.0; SLCC2; Media Center PC 6.0; InfoPath.2; MS-RTC LM 8)',
            'Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1; WOW64; Trident/4.0; SLCC2; Media Center PC 6.0; InfoPath.2; MS-RTC LM 8',
            'Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1; WOW64; Trident/4.0; SLCC2; .NET CLR 2.0.50727; Media Center PC 6.0; .NET CLR 3.5.30729; .NET CLR 3.0.30729; .NET4.0C)',
            'Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1; WOW64; Trident/4.0; SLCC2; .NET CLR 2.0.50727; InfoPath.3; .NET4.0C; .NET4.0E; .NET CLR 3.5.30729; .NET CLR 3.0.30729; MS-RTC LM 8)',
            'Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1; WOW64; Trident/4.0; SLCC2; .NET CLR 2.0.50727; InfoPath.2)',
            'Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1; WOW64; Trident/4.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; Zune 3.0)',
            'Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1; WOW64; Trident/4.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; msn OptimizedIE8;ZHCN)',

        );

        $option = array();
        $option['User-Agent'] = $user_agents[mt_rand(0, count($user_agents) - 1)];
        $option['followlocation'] = true;
        //Grab the remote informations from google
        $response = utf8_decode(SQ_Tools::sq_remote_get("https://www.google.$country/search", $arg, $option));

        //Check the values for block IP
        if (strpos($response, "</h3>") === false) {
            set_transient('google_blocked', time(), 3600);
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
    public function processCron()
    {
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
                        )
                    ) {

                        $rank = $this->processRanking($row->post_id, $json->keyword);

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
                        $args['rank'] = (string)$rank;
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
    public function getKeywordHistory()
    {
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
