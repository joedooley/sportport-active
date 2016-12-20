<?php

class SQ_Post extends SQ_FrontController {

    public $saved;

    /**
     * Initialize the TinyMCE editor for the current use
     *
     * @return void
     */
    public function hookInit() {
        $this->saved = array();

        add_filter('tiny_mce_before_init', array($this->model, 'setCallback'));
        add_filter('mce_external_plugins', array($this->model, 'addHeadingButton'));
        add_filter('mce_buttons', array($this->model, 'registerButton'));

        if (SQ_Tools::$options['sq_api'] == '')
            return;

        add_action('save_post', array($this, 'hookSavePost'), 99);
        add_action('shopp_product_saved', array($this, 'hookShopp'), 11);

        if (SQ_Tools::$options['sq_use'] == 1 && SQ_Tools::$options['sq_auto_sitemap'] == 1) {
            add_action('transition_post_status', array(SQ_ObjController::getController('SQ_Sitemaps'), 'refreshSitemap'), 9999, 3);
        }
    }

    /**
     * hook the Head
     *
     * @global integer $post_ID
     */
    public function hookHead() {
        global $post_ID;
        parent::hookHead();

        /**
         * Add the post ID in variable
         * If there is a custom plugin post or Shopp product
         *
         * Set the global variable $sq_postID for cookie and keyword record
         */
        if ((int)$post_ID == 0) {
            if (SQ_Tools::getIsset('id'))
                $GLOBALS['sq_postID'] = (int)SQ_Tools::getValue('id');
        } else {
            $GLOBALS['sq_postID'] = $post_ID;
        }
        /*         * ****************************** */

        echo '<script type="text/javascript">(function() {this.sq_tinymce = { callback: function () {}, setup: function(ed){} } })(window);</script>';
    }

    /**
     * Hook the Shopp plugin save product
     */
    public function hookShopp($Product) {
        $this->checkSeo($Product->id);
    }

    /**
     * Hook the post save/update
     * @param type $post_id
     */
    public function hookSavePost($post_id) {


        if (!isset($this->saved[$post_id])) {
            $this->saved[$post_id] = false;
        }

        // unhook this function so it doesn't loop infinitely
        remove_action('save_post', array($this, 'hookSavePost'), 99);
        //If the post is a new or edited post
        if ((SQ_Tools::getValue('action')) == 'editpost' &&
            wp_is_post_autosave($post_id) == '' &&
            get_post_status($post_id) != 'auto-draft' &&
            get_post_status($post_id) != 'inherit' &&
            SQ_Tools::getValue('autosave') == ''
        ) {

            if ($this->saved[$post_id] === false) {
                //check for custom SEO
                $this->_checkAdvMeta($post_id);
                //check the SEO from Squirrly Live Assistant
                $this->checkSeo($post_id, get_post_status($post_id));
                //check the remote images
                $this->checkImage($post_id);
            }
            $this->saved[$post_id] = true;
        }


        add_action('save_post', array($this, 'hookSavePost'), 99);
    }

    /**
     * Check if the image is a remote image and save it locally
     *
     * @param integer $post_id
     * @return false|void
     */
    public function checkImage($post_id) {

        //if the option to save the images locally is set on
        if (SQ_Tools::$options['sq_local_images'] == 1) {

            @set_time_limit(90);
            $local_file = false;

            $content = SQ_Tools::getValue('post_content', '', true); //get the content in html format
            $tmpcontent = trim(html_entity_decode($content), "\n");
            $urls = array();

            if (function_exists('preg_match_all')) {

                @preg_match_all('/<img[^>]*src=[\'"]([^\'"]+)[\'"][^>]*>/i', $tmpcontent, $out);
                if (is_array($out)) {

                    if (!is_array($out[1]) || count($out[1]) == 0)
                        return;

                    if (get_bloginfo('wpurl') <> '') {
                        $domain = parse_url(get_bloginfo('wpurl'));

                        foreach ($out[1] as $row) {
                            if (strpos($row, '//') !== false &&
                                strpos($row, $domain['host']) === false
                            ) {
                                if (!in_array($row, $urls)) {
                                    $urls[] = $row;
                                }
                            }
                        }
                    }
                }
            }

            if (!is_array($urls) || (is_array($urls) && count($urls) == 0))
                return;

            $urls = @array_unique($urls);

            $time = microtime(true);
            foreach ($urls as $url) {
                if ($file = $this->model->upload_image($url)) {

                    if (!file_is_valid_image($file['file']))
                        continue;

                    $local_file = $file['url'];
                    if ($local_file !== false) {
                        $content = str_replace($url, $local_file, $content);

                        $attach_id = wp_insert_attachment(array(
                            'post_mime_type' => $file['type'],
                            'post_title' => SQ_Tools::getValue('sq_keyword', preg_replace('/\.[^.]+$/', '', $file['filename'])),
                            'post_content' => urldecode(SQ_Tools::getValue('sq_fp_title', '')),
                            'post_status' => 'inherit',
                            'guid' => $local_file
                        ), $file['file'], $post_id);

                        $attach_data = wp_generate_attachment_metadata($attach_id, $file['file']);
                        wp_update_attachment_metadata($attach_id, $attach_data);
                    }
                }
                if (microtime(true) - $time >= 20) {
                    break;
                }
            }


            if ($local_file !== false) {
                wp_update_post(array(
                        'ID' => $post_id,
                        'post_content' => $content)
                );
            }
        }
    }

    /**
     * Check the SEO from Squirrly Live Assistant
     *
     * @param integer $post_id
     * @param void
     */
    public function checkSeo($post_id, $status = '') {
        $args = array();

        $seo = SQ_Tools::getValue('sq_seo');

        if (is_array($seo) && count($seo) > 0)
            $args['seo'] = implode(',', $seo);

        $args['keyword'] = SQ_Tools::getValue('sq_keyword');

        $args['status'] = $status;
        $args['permalink'] = get_permalink($post_id);
        $args['permalink'] = $this->getPaged($args['permalink']);
        $args['permalink'] = $args['permalink'];
        $args['author'] = (int)SQ_Tools::getUserID();
        $args['post_id'] = $post_id;

        if (SQ_Tools::$options['sq_force_savepost'] == 1) {
            SQ_Action::apiCall('sq/seo/post', $args, 1);
        } else {
            $process = array();
            if (get_transient('sq_seopost') !== false) {
                $process = json_decode(get_transient('sq_seopost'), true);
            }
            $process[] = $args;
            $process = array_reverse($process);

            //save for later send to api
            set_transient('sq_seopost', json_encode($process));

            //prevent lost posts if there are not processed
            if (count($process) > 5){
                SQ_Tools::saveOptions('sq_force_savepost', 1);
                $this->processCron();
            }

            if (get_transient('sq_seopost') !== false) {
                wp_schedule_single_event(time(), 'sq_processApi');
            } else {
                SQ_Action::apiCall('sq/seo/post', $args, 1);
            }
        }

        //Save the keyword for this post
        if ($json = $this->model->getKeyword($post_id)) {
            $json->keyword = addslashes(SQ_Tools::getValue('sq_keyword'));
            $this->model->saveKeyword($post_id, $json);
        } else {
            $args = array();
            $args['keyword'] = addslashes(SQ_Tools::getValue('sq_keyword'));
            $this->model->saveKeyword($post_id, json_decode(json_encode($args)));
        }
    }

    public function getPaged($link) {
        $page = (int)get_query_var('paged');
        if ($page && $page > 1) {
            $link = trailingslashit($link) . "page/" . "$page" . '/';
        }
        return $link;
    }

    /**
     * Called when Post action is triggered
     *
     * @return void
     */
    public function action() {
        parent::action();

        switch (SQ_Tools::getValue('action')) {
            case 'sq_save_meta':
                $return = $this->_checkAdvMeta(SQ_Tools::getValue('post_id'));
                SQ_Tools::setHeader('json');
                echo json_encode($return);
                SQ_Tools::emptyCache();
                break;
            case 'sq_save_ogimage':
                if (!empty($_FILES['ogimage'])) {
                    $return = $this->model->addImage($_FILES['ogimage']);
                }
                if (isset($return['file'])) {
                    $return['filename'] = basename($return['file']);
                    $local_file = str_replace($return['filename'], urlencode($return['filename']), $return['url']);
                    $attach_id = wp_insert_attachment(array(
                        'post_mime_type' => $return['type'],
                        'post_title' => preg_replace('/\.[^.]+$/', '', $return['filename']),
                        'post_content' => '',
                        'post_status' => 'inherit',
                        'guid' => $local_file
                    ), $return['file'], SQ_Tools::getValue('post_id'));

                    $attach_data = wp_generate_attachment_metadata($attach_id, $return['file']);
                    wp_update_attachment_metadata($attach_id, $attach_data);
                }
                SQ_Tools::setHeader('json');
                echo json_encode($return);
                SQ_Tools::emptyCache();

                break;
            case 'sq_get_keyword':
                SQ_Tools::setHeader('json');
                if (SQ_Tools::getIsset('post_id')) {
                    echo json_encode($this->model->getKeywordsFromPost(SQ_Tools::getValue('post_id')));
                } else {
                    echo json_encode(array('error' => true));
                }
                SQ_Tools::emptyCache();
                break;
        }
        exit();
    }

    /**
     * Check if there are advanced settings for meta title, description and keywords
     * @param integer $post_id
     * @return array | false
     *
     */
    private function _checkAdvMeta($post_id) {

        $meta = array();
        if (SQ_Tools::getIsset('sq_canonical') || SQ_Tools::getIsset('sq_fp_title') || SQ_Tools::getIsset('sq_fp_description') || SQ_Tools::getIsset('sq_fp_keywords')) {
            if (SQ_Tools::getIsset('sq_fp_title'))
                $meta[] = array('key' => '_sq_fp_title',
                    'value' => SQ_Tools::getValue('sq_fp_title'));

            if (SQ_Tools::getIsset('sq_fp_description'))
                $meta[] = array('key' => '_sq_fp_description',
                    'value' => SQ_Tools::getValue('sq_fp_description'));

            if (SQ_Tools::getIsset('sq_fp_keywords'))
                $meta[] = array('key' => '_sq_fp_keywords',
                    'value' => SQ_Tools::getValue('sq_fp_keywords'));

            if (SQ_Tools::getIsset('sq_fp_ogimage'))
                $meta[] = array('key' => '_sq_fp_ogimage',
                    'value' => SQ_Tools::getValue('sq_fp_ogimage'));

            if (SQ_Tools::getIsset('sq_canonical'))
                $meta[] = array('key' => '_sq_canonical',
                    'value' => SQ_Tools::getValue('sq_canonical'));

            SQ_Tools::dump($meta);
            $this->model->saveAdvMeta($post_id, $meta);

            return $meta;
        }
        return false;
    }

    public function hookFooter() {
        if (!defined('DISABLE_WP_CRON') || DISABLE_WP_CRON == true) {
            global $pagenow;
            if (in_array($pagenow, array('post.php', 'post-new.php'))) {
                $this->processCron();
            }
        }
    }

    public function processCron() {
        SQ_ObjController::getController('SQ_Tools', false);
        SQ_ObjController::getController('SQ_Action', false);

        if (get_transient('sq_seopost') !== false) {
            $process = json_decode(get_transient('sq_seopost'), true);
            foreach ($process as $key => $call) {

                $response = json_decode(SQ_Action::apiCall('sq/seo/post', $call, 60));

                if (isset($response->saved) && $response->saved == true) {
                    unset($process[$key]);
                }
            }
            set_transient('sq_seopost', json_encode($process));
        }
    }

}
