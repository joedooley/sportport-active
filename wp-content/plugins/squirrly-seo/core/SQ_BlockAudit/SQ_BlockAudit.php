<?php

/**
 * Audit Page
 */
class SQ_BlockAudit extends SQ_BlockController {
    public $blog;

    function hookGetContent() {
        $blogs = json_decode(SQ_Action::apiCall('sq/audit/blog-list'));
        if (!empty($blogs)) {
            foreach ($blogs as $blog) {
                if (get_bloginfo('url') == $blog->domain){
                    $this->blog = $blog;
                }
            }
        }
    }
}
