<?php

/**
 * Class for Sitemap Generator
 */
class SQ_Feed extends SQ_FrontController {
    /* @var string post limit */

    var $posts_limit = 10000;

    public function __construct() {
        parent::__construct();
        add_filter('template_redirect', array($this, 'hookPreventRedirect'), 1, 0);
    }

    public function hookPreventRedirect() {
        global $wp_query;
        if (!empty($wp_query->query_vars["feed"])) {
            $wp_query->is_404 = false;
            $wp_query->is_feed = true;

            add_filter('feed_content_type', array($this, 'feedHeader'), 10, 2);
            add_action('rss_tag_pre', array($this, 'feedStyle'));
            add_action('rss2_head', array($this, 'feedCss'));
            add_filter('the_content', array($this, 'addFeedMedia'));
        }
    }

    public function feedCss() {
        echo "\t" . '<feedcss>' . _SQ_THEME_URL_ . 'css/' . 'sq_feed.css</feedcss>' . "\n";
    }

    public function feedStyle() {
        echo '<?xml-stylesheet type="text/xsl" href="' . _SQ_THEME_URL_ . 'css/' . 'sq_feed.xsl' . '"?>';
    }

    public function feedHeader($content_type, $type) {
        if (empty($type)) {
            $type = get_default_feed();
        }

        $types = array(
            'rss' => ' text/xml',
            'rss2' => ' text/xml'
        );
        $content_type = (!empty($types[$type]) ) ? $types[$type] : $content_type;

        return $content_type;
    }

    public function addFeedMedia($content) {
        global $post;
        if (has_post_thumbnail($post->ID)) {
            $attachment = get_post(get_post_thumbnail_id($post->ID));
            $thumb = wp_get_attachment_image_src($attachment->ID, 'large');
            $content = '<div class="thumbnail">
                    <a href="' . get_permalink($post->ID) . '">
                        <img src="' . esc_url($thumb[0]) . '" alt="' . get_the_title($post->ID) . '">
                    </a>
                </div>' . $content;
        }
        return $content;
    }

    function featuredtoRSS($content) {
        global $post;
        if (has_post_thumbnail($post->ID)) {
            $content = '<div>' . get_the_post_thumbnail($post->ID, 'medium') . '</div>' . $content;
        }
        return $content;
    }

    public function refreshSitemap($new_status, $old_status, $post) {
        if ($old_status <> $new_status && $new_status = 'publish') {
            if (SQ_Tools::$options['sq_sitemap_ping'] == 1) {
                wp_schedule_single_event(time() + 5, 'sq_processPing');
            }
        }
    }

}
