<?php

/**
 * Squirrly SEO - Sitemap Model
 *
 * Used to get the sitemap format for each type
 *
 * @class 		Model_SQ_Sitemaps
 */
class Model_SQ_Sitemaps {

    var $args = array();
    var $frequency;
    var $type;

    public function __construct() {
        //For sitemap ping
        $this->args['timeout'] = 5;

        $this->frequency = array();
        $this->frequency['daily'] = array('sitemap-home' => array(1, 'daily'), 'sitemap-product' => array(0.8, 'weekly'), 'sitemap-post' => array(0.8, 'daily'), 'sitemap-page' => array(0.6, 'weekly'), 'sitemap-category' => array(0.5, 'weekly'), 'sitemap-post_tag' => array(0.5, 'daily'), 'sitemap-archive' => array(0.3, 'monthly'), 'sitemap-author' => array(0.3, 'weekly'), 'sitemap-custom-tax' => array(0.3, 'weekly'), 'sitemap-custom-post' => array(0.8, 'daily'));
        $this->frequency['weekly'] = array('sitemap-home' => array(1, 'weekly'), 'sitemap-product' => array(0.8, 'weekly'), 'sitemap-post' => array(0.8, 'weekly'), 'sitemap-page' => array(0.6, 'monthly'), 'sitemap-category' => array(0.3, 'monthly'), 'sitemap-post_tag' => array(0.5, 'weekly'), 'sitemap-archive' => array(0.3, 'monthly'), 'sitemap-author' => array(0.3, 'weekly'), 'sitemap-custom-tax' => array(0.3, 'weekly'), 'sitemap-custom-post' => array(0.8, 'weekly'));
        $this->frequency['monthly'] = array('sitemap-home' => array(1, 'monthly'), 'sitemap-product' => array(0.8, 'weekly'), 'sitemap-post' => array(0.8, 'monthly'), 'sitemap-page' => array(0.6, 'monthly'), 'sitemap-category' => array(0.3, 'monthly'), 'sitemap-post_tag' => array(0.5, 'monthly'), 'sitemap-archive' => array(0.3, 'monthly'), 'sitemap-author' => array(0.3, 'monthly'), 'sitemap-custom-tax' => array(0.3, 'monthly'), 'sitemap-custom-post' => array(0.8, 'monthly'));
        $this->frequency['yearly'] = array('sitemap-home' => array(1, 'monthly'), 'sitemap-product' => array(0.8, 'weekly'), 'sitemap-post' => array(0.8, 'monthly'), 'sitemap-page' => array(0.6, 'yearly'), 'sitemap-category' => array(0.3, 'yearly'), 'sitemap-post_tag' => array(0.5, 'monthly'), 'sitemap-archive' => array(0.3, 'yearly'), 'sitemap-author' => array(0.3, 'yearly'), 'sitemap-custom-tax' => array(0.3, 'yearly'), 'sitemap-custom-post' => array(0.8, 'monthly'));
    }

    /**
     * Add the Sitemap Index
     * @global type $polylang
     * @return type
     */
    public function getHomeLink() {
        $homes = array();
        $homes['contains'] = array();
        global $polylang;

        if (isset($polylang)) {
            foreach ($polylang->get_languages_list() as $term) {
                $xml = array();
                $xml['loc'] = esc_url($polylang->get_home_url($term));
                $xml['lastmod'] = trim(mysql2date('Y-m-d\TH:i:s+00:00', $this->lastModified(), false));
                $xml['changefreq'] = $this->frequency[SQ_Tools::$options['sq_sitemap_frequency']]['sitemap-home'][1];
                $xml['priority'] = $this->frequency[SQ_Tools::$options['sq_sitemap_frequency']]['sitemap-home'][0];
                $homes[] = $xml;
            }
        } else {
            $xml = array();
            $xml['loc'] = home_url();
            $xml['lastmod'] = trim(mysql2date('Y-m-d\TH:i:s+00:00', $this->lastModified(), false));
            $xml['changefreq'] = $this->frequency[SQ_Tools::$options['sq_sitemap_frequency']]['sitemap-home'][1];
            $xml['priority'] = $this->frequency[SQ_Tools::$options['sq_sitemap_frequency']]['sitemap-home'][0];
            if ($post_id = get_option('page_on_front')) {
                if (SQ_Tools::$options['sq_sitemap_show']['images'] == 1) {
                    if ($images = SQ_ObjController::getModel('SQ_Frontend')->getImagesFromContent($post_id, true)) {
                        array_push($homes['contains'], 'image');
                        $xml['image:image'] = array();
                        foreach ($images as $image) {
                            if (empty($image['src'])) {
                                continue;
                            }


                            $xml['image:image'][] = array(
                                'image:loc' => $image['src'],
                                'image:title' => SQ_ObjController::getModel('SQ_Frontend')->clearTitle($image['title']),
                                'image:caption' => SQ_ObjController::getModel('SQ_Frontend')->clearDescription($image['description']),
                            );
                        }
                    }
                }
            }
            $homes[] = $xml;
            unset($xml);
        }

        return $homes;
    }

    /**
     * Add posts/pages in sitemap
     * @return type
     */
    public function getListPosts() {
        global $wp_query;
        $wp_query->get_posts();
        $posts = array();
        $posts['contains'] = array();
        if (have_posts()) {
            while (have_posts()) {
                the_post();

                //do not incude password protected pages in sitemap
                if (post_password_required()) {
                    continue;
                }

                $post = get_post();

                $xml = array();
                $xml['loc'] = esc_url(get_permalink());
                $xml['lastmod'] = trim(mysql2date('Y-m-d\TH:i:s+00:00', $this->lastModified(), false));
                $xml['changefreq'] = $this->frequency[SQ_Tools::$options['sq_sitemap_frequency']][$this->type][1];
                $xml['priority'] = $this->frequency[SQ_Tools::$options['sq_sitemap_frequency']][$this->type][0];

                if (SQ_Tools::$options['sq_sitemap_show']['images'] == 1) {
                    if ($images = SQ_ObjController::getModel('SQ_Frontend')->getImagesFromContent($post->ID, true)) {
                        array_push($posts['contains'], 'image');
                        $xml['image:image'] = array();
                        foreach ($images as $image) {
                            if (empty($image['src'])) {
                                continue;
                            }

                            $xml['image:image'][] = array(
                                'image:loc' => $image['src'],
                                'image:title' => SQ_ObjController::getModel('SQ_Frontend')->clearTitle($image['title']),
                                'image:caption' => SQ_ObjController::getModel('SQ_Frontend')->clearDescription($image['description']),
                            );
                        }
                    }
                }

                if (SQ_Tools::$options['sq_sitemap_show']['videos'] == 1) {
                    $images = SQ_ObjController::getModel('SQ_Frontend')->getImagesFromContent($post->ID, true);
                    if (isset($images[0]['src']) && $videos = SQ_ObjController::getModel('SQ_Frontend')->getVideosFromContent($post->ID)) {
                        array_push($posts['contains'], 'video');
                        $xml['video:video'] = array();

                        foreach ($videos as $video) {
                            if ($video == '') {
                                continue;
                            }

                            $xml['video:video'][$post->ID] = array(
                                'video:player_loc' => $video,
                                'video:thumbnail_loc' => $images[0]['src'],
                                'video:title' => SQ_ObjController::getModel('SQ_Frontend')->clearTitle(SQ_ObjController::getModel('SQ_Frontend')->grabTitleFromPost($post->ID)),
                                'video:description' => SQ_ObjController::getModel('SQ_Frontend')->clearDescription(SQ_ObjController::getModel('SQ_Frontend')->grabDescriptionFromPost($post->ID)),
                            );

                            //set the first keyword for this video
                            $keywords = SQ_ObjController::getModel('SQ_Frontend')->grabKeywordsFromPost($post->ID);
                            $keywords = preg_split('/,/', $keywords);
                            if (is_array($keywords)) {
                                $xml['video:video'][$post->ID]['video:tag'] = $keywords[0];
                            }
                        }
                    }
                }
                $posts[] = $xml;
                unset($xml);
            }
        }

        return $posts;
    }

    /**
     * Add the post news in sitemap
     * If the site is registeres for google news
     * @return type
     */
    public function getListNews() {
        global $wp_query;
        $wp_query->get_posts();

        $posts = array();
        $posts['contains'] = array();

        if (have_posts()) {
            while (have_posts()) {
                the_post();
                $post = get_post();

                $xml = array();

                $xml['loc'] = esc_url(get_permalink());
                $language = convert_chars(strip_tags(get_bloginfo('language')));
                $language = substr($language, 0, strpos($language, '-'));
                if ($language == '')
                    $language = 'en';

                $xml['news:news'][$post->ID] = array(
                    'news:publication' => array(
                        'news:name' => $this->sanitizeString(get_bloginfo('name')),
                        'news:language' => $language
                    )
                );
                $xml['news:news'][$post->ID]['news:publication_date'] = trim(mysql2date('Y-m-d\TH:i:s+00:00', $this->lastModified(), false));
                $xml['news:news'][$post->ID]['news:title'] = $this->sanitizeString(get_the_title());
                $xml['news:news'][$post->ID]['news:keywords'] = (string) SQ_ObjController::getModel('SQ_Frontend')->grabKeywordsFromPost($post->ID);


                if (SQ_Tools::$options['sq_sitemap_show']['images'] == 1) {
                    if ($images = SQ_ObjController::getModel('SQ_Frontend')->getImagesFromContent($post->ID, true)) {
                        array_push($posts['contains'], 'image');
                        $xml['image:image'] = array();
                        foreach ($images as $image) {
                            if (empty($image['src'])) {
                                continue;
                            }

                            $xml['image:image'][] = array(
                                'image:loc' => $image['src'],
                                'image:title' => SQ_ObjController::getModel('SQ_Frontend')->clearTitle($image['title']),
                                'image:caption' => SQ_ObjController::getModel('SQ_Frontend')->clearDescription($image['description']),
                            );
                        }
                    }
                }

                if (SQ_Tools::$options['sq_sitemap_show']['videos'] == 1) {
                    $images = SQ_ObjController::getModel('SQ_Frontend')->getImagesFromContent($post->ID, true);
                    if (isset($images[0]['src']) && $videos = SQ_ObjController::getModel('SQ_Frontend')->getVideosFromContent($post->ID)) {
                        array_push($posts['contains'], 'video');
                        $xml['video:video'] = array();
                        foreach ($videos as $video) {
                            if ($video == '') {
                                continue;
                            }

                            $xml['video:video'][$post->ID] = array(
                                'video:player_loc' => $video,
                                'video:thumbnail_loc' => $images[0]['src'],
                                'video:title' => SQ_ObjController::getModel('SQ_Frontend')->clearTitle(SQ_ObjController::getModel('SQ_Frontend')->grabTitleFromPost($post->ID)),
                                'video:description' => SQ_ObjController::getModel('SQ_Frontend')->clearDescription(SQ_ObjController::getModel('SQ_Frontend')->grabDescriptionFromPost($post->ID)),
                            );

                            //set the first keyword for this video
                            $keywords = SQ_ObjController::getModel('SQ_Frontend')->grabKeywordsFromPost($post->ID);
                            $keywords = preg_split('/,/', $keywords);
                            if (is_array($keywords)) {
                                $xml['video:video'][$post->ID]['video:tag'] = $keywords[0];
                            }
                        }
                    }
                }
                $posts[] = $xml;
                unset($xml);
            }
        }

        return $posts;
    }

    /**
     * Add the Taxonomies in sitemap
     * @param type $type
     * @return type
     */
    public function getListTerms($type = null) {
        if (!isset($type)) {
            $type = $this->type;
        }

        $terms = $array = array();
        $array['contains'] = array();
        if ($type == 'sitemap-custom-tax') {
            $taxonomies = $this->excludeTypes(get_taxonomies(), array('category', 'post_tag', 'nav_menu', 'link_category', 'post_format'));
            if (!empty($taxonomies)) {
                $taxonomies = array_unique($taxonomies);
            }
            foreach ($taxonomies as $taxonomy) {
                $array = array_merge($array, $this->getListTerms($taxonomy));
            }
        } else {
            $terms = get_terms(str_replace('sitemap-', '', $type));
        }

        if (!isset(SQ_Tools::$options['sq_sitemap'][$type])) {
            $type = 'sitemap-custom-tax';
        }

        if (!empty($terms)) {
            foreach ($terms AS $term) {
                $xml = array();
                if (!isset($term->lastmod)){
                    continue;
                }

                $xml['loc'] = esc_url(get_term_link($term, $term->taxonomy));
                $xml['lastmod'] = date('Y-m-d\TH:i:s+00:00', $term->lastmod);
                $xml['changefreq'] = $this->frequency[SQ_Tools::$options['sq_sitemap_frequency']][$type][1];
                $xml['priority'] = $this->frequency[SQ_Tools::$options['sq_sitemap_frequency']][$type][0];

                $array[] = $xml;
            }
        }
        return $array;
    }

    /**
     * Add the authors in sitemap
     * @return type
     */
    public function getListAuthors() {
        $authors = $array = array();
        $authors = apply_filters('sq-sitemap-authors', $this->type);

        if (!empty($authors)) {
            foreach ($authors AS $author) {
                $xml = array();

                $xml['loc'] = get_author_posts_url($author->ID, $author->user_nicename);
                if (isset($author->lastmod) && $author->lastmod <> '')
                    $xml['lastmod'] = date('Y-m-d\TH:i:s+00:00', strtotime($author->lastmod));
                $xml['changefreq'] = $this->frequency[SQ_Tools::$options['sq_sitemap_frequency']][$this->type][1];
                $xml['priority'] = $this->frequency[SQ_Tools::$options['sq_sitemap_frequency']][$this->type][0];

                $array[] = $xml;
            }
        }
        return $array;
    }

    /**
     * Add the archive in sitemap
     * @return type
     */
    public function getListArchive() {
        $array = array();
        $archives = apply_filters('sq-sitemap-archive', $this->type);
        if (!empty($archives)) {
            foreach ($archives as $archive) {
                $xml = array();

                $xml['loc'] = get_month_link($archive->year, $archive->month);
                if (isset($archive->lastmod) && $archive->lastmod <> '')
                    $xml['lastmod'] = date('Y-m-d\TH:i:s+00:00', strtotime($archive->lastmod));

                $xml['changefreq'] = $this->frequency[SQ_Tools::$options['sq_sitemap_frequency']][$this->type][1];
                $xml['priority'] = $this->frequency[SQ_Tools::$options['sq_sitemap_frequency']][$this->type][0];

                $array[] = $xml;
            }
        }

        return $array;
    }

    private function sanitizeString($string) {
        return esc_html(ent2ncr(strip_tags($string)));
    }

    /**
     * Get the last modified date for the specific post/page
     *
     * @global WP_Post $post
     * @param string $sitemap
     * @param string $term
     * @return string
     */
    public function lastModified($sitemap = 'post_type', $term = '') {
        if ('post_type' == $sitemap) :

            global $post;

            if (isset($post->ID)) {
                if (empty($this->postmodified[$post->ID])) {
                    $postmodified = get_post_modified_time('Y-m-d H:i:s', true, $post->ID);
                    $options = get_option('post_types');

                    if (!empty($options[$post->post_type]['update_lastmod_on_comments']))
                        $lastcomment = get_comments(array(
                            'status' => 'approve',
                            'number' => 1,
                            'post_id' => $post->ID,
                        ));

                    if (isset($lastcomment[0]->comment_date_gmt))
                        if (mysql2date('U', $lastcomment[0]->comment_date_gmt) > mysql2date('U', $postmodified))
                            $postmodified = $lastcomment[0]->comment_date_gmt;

                    $this->postmodified[$post->ID] = $postmodified;
                }
            }
            return $this->postmodified[$post->ID];

        elseif (!empty($term)) :

            if (is_object($term)) {
                if (!isset($this->termmodified[$term->term_id])) {
                    // get the latest post in this taxonomy item, to use its post_date as lastmod
                    $posts = get_posts(array(
                        'post_type' => 'any',
                        'numberposts' => 1,
                        'no_found_rows' => true,
                        'update_post_meta_cache' => false,
                        'update_post_term_cache' => false,
                        'update_cache' => false,
                        'tax_query' => array(
                            array(
                                'taxonomy' => $term->taxonomy,
                                'field' => 'slug',
                                'terms' => $term->slug
                            )
                        )
                            )
                    );
                    $this->termmodified[$term->term_id] = isset($posts[0]->post_date_gmt) ? $posts[0]->post_date_gmt : '';
                }
                return $this->termmodified[$term->term_id];
            } else {
                $obj = get_taxonomy($term);
                return get_lastdate('gmt', $obj->object_type);
            }

        else :

            return '0000-00-00 00:00:00';

        endif;
    }

    /**
     * Check if the current page is the home page
     * @global type $polylang
     * @param type $id
     * @return type
     */
    public function is_home($id) {
        $home = array();
        $id = get_option('page_for_posts');

        if (!empty($id)) {
            global $polylang;
            if (isset($polylang)) {
                $home = $polylang->get_translations('post', $id);
            } else {
                $home = array($id);
            }
        }

        return in_array($id, $home);
    }

    /**
     * Excude types from array
     * @param array  $types
     * @param array $exclude
     * @return array
     */
    public function excludeTypes($types, $exclude) {
        foreach ($exclude as $value) {
            if (in_array($value, $types)) {
                unset($types[$value]);
            }
        }
        return $types;
    }

}
