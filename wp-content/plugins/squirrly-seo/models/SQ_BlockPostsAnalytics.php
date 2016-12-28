<?php

class Model_SQ_BlockPostsAnalytics extends WP_List_Table
{

    public $_column_headers;
    public $posts; //save post list for Squirrly call
    private $order_posts;

    function __construct()
    {
        parent::__construct();
        $this->posts = array();
        $this->order_posts = array();
    }

    function wp_edit_posts_query($q = false)
    {
        global $current_user;
        $post__in = array(0);
        if (false === $q)
            $q = $_GET;

        $q['m'] = isset($q['m']) ? (int)$q['m'] : 0;
        $q['cat'] = isset($q['cat']) ? (int)$q['cat'] : 0;
        $post_stati = get_post_stati();

        if (isset($q['author'])) {
            $author = $q['author'];
        } else {
            if (isset($current_user->caps['contributor']) || isset($current_user->caps['author'])) {
                $author = $current_user->ID;
            } else {
                $author = '';
            }
        }

        if (isset($q['type']) && in_array($q['type'], get_post_types())) {
            $post_type = $q['type'];
            $avail_post_stati = get_available_post_statuses($post_type);
        } else {
            $post_type = array('post', 'page');
            $avail_post_stati = get_available_post_statuses('post');

            $args = array(
                '_builtin' => false,
            );
            $output = 'objects'; // names or objects
            $post_types = get_post_types($args, $output);

            foreach ($post_types as $other_post_type) {
                if ($other_post_type->public) {
                    array_push($post_type, $other_post_type->query_var);
                }
            }
        }
        if (isset($q['post_status']) && in_array($q['post_status'], $post_stati)) {
            $post_status = $q['post_status'];
            $perm = 'readable';
        } else {
            $post_status = 'publish';
            $perm = 'readable';
        }
        if (!isset($q['orderby'])) {
            $_GET['orderby'] = $q['orderby'] = 'rank';
            $_GET['order'] = $q['order'] = 'asc';
        }

        if (isset($q['orderby']))
            $orderby = $q['orderby'];
        elseif (isset($q['post_status']) && in_array($q['post_status'], array('pending', 'draft')))
            $orderby = 'modified';

        if (isset($q['order']))
            $order = $q['order'];
        elseif (isset($q['post_status']) && 'pending' == $q['post_status'])
            $order = 'ASC';

        ///////////Squirrly Filters
        if (isset($q['orderby']) && $q['orderby'] === 'type') {
            add_filter('posts_request', array($this, 'order_by_type'));
        }

        $meta_key = '';
        $count_p = 99999;
        $posts = SQ_ObjController::getModel('SQ_Post')->getPostWithKeywords();
        if ($posts !== false && !empty($posts)) {
            //sort descending
            foreach ($posts as $post) {
                if (!isset($post->meta_value->keyword) || $post->meta_value->keyword == '') {
                    continue;
                }

                //if rank filter
                if (isset($q['rank'])) {

                    if (!isset($post->meta_value->rank) || $q['rank'] <> $post->meta_value->rank) {
                        continue;
                    }
                }
                //if keyword filter
                if (isset($q['keyword'])) {
                    if (!isset($post->meta_value->keyword) || strtolower($q['keyword']) <> strtolower($post->meta_value->keyword)) {
                        continue;
                    }
                }

                //if rank order
                if (isset($q['orderby']) && $q['orderby'] == 'rank') {
                    if (isset($post->meta_value->rank)) {
                        if ($post->meta_value->rank > 0) {
                            $this->order_posts[$post->meta_value->rank . '_' . $post->post_id] = $post->post_id;
                        } else {
                            $this->order_posts[$count_p . '_' . $post->post_id] = $post->post_id;
                            $count_p++;
                        }
                    } else {
                        $this->order_posts[$count_p . '_' . $post->post_id] = $post->post_id;
                        $count_p++;
                    }
                }

                $post__in[] = $post->post_id;
            }
            if (isset($q['orderby']) && $q['orderby'] === 'rank') {
                ksort($this->order_posts, SORT_NUMERIC);
//                $meta_key = 'sq_post_keyword_rank';
//                $orderby = 'meta_value_num';
                add_filter('posts_request', array($this, 'order_by_rank'));
            }
        }
        //////////////

        $per_page = 'edit_post_per_page';
        $posts_per_page = (int)get_user_option($per_page);
        if (empty($posts_per_page) || $posts_per_page < 1)
            $posts_per_page = 20;

        $posts_per_page = apply_filters($per_page, $posts_per_page);
        $posts_per_page = apply_filters('edit_posts_per_page', $posts_per_page, 'post');
        $query = compact('post_type', 'author', 'post_status', 'perm', 'order', 'orderby', 'meta_key', 'posts_per_page');

        $query['post__in'] = (array)$post__in;

        wp($query);
        return $avail_post_stati;
    }

    function order_by_type($query)
    {
        global $wpdb;
        if (strpos($query, 'ORDER BY') !== false) {
            $query = str_replace("ORDER BY {$wpdb->posts}.post_date", "ORDER BY {$wpdb->posts}.post_type", $query);
        } else {
            $query = str_replace("LIMIT", "ORDER BY {$wpdb->posts}.post_type LIMIT", $query);
        }
        return $query;
    }

    function order_by_rank($query)
    {
        global $wpdb;
        if (!empty($this->order_posts)) {
            if (strpos($query, 'ORDER BY') !== false) {
                $query = str_replace("ORDER BY {$wpdb->posts}.post_date", "ORDER BY FIELD({$wpdb->posts}.ID, " . join(',', $this->order_posts) . ")", $query);
            } else {
                $query = str_replace("LIMIT", "ORDER BY FIELD({$wpdb->posts}.ID, " . join(',', $this->order_posts) . ") LIMIT", $query);

            }
        }
        return $query;
    }

    function prepare_items()
    {
        global $avail_post_stati, $wp_query, $per_page, $mode;

        $avail_post_stati = $this->wp_edit_posts_query();

        $total_items = $wp_query->found_posts;

        $per_page = $this->get_items_per_page('edit_post_per_page');
        $per_page = apply_filters('edit_posts_per_page', $per_page, 'post');

        $total_pages = $wp_query->max_num_pages;

        $mode = empty($_REQUEST['mode']) ? 'list' : $_REQUEST['mode'];

        $this->set_pagination_args(array(
            'total_items' => $total_items,
            'total_pages' => $total_pages,
            'per_page' => $per_page
        ));
    }

    function get_column_info()
    {
        if (isset($this->_column_headers))
            return $this->_column_headers;

        $columns = $this->get_columns();
        $_sortable = apply_filters("manage_edit-post_sortable_columns", $this->get_sortable_columns());

        $sortable = array();
        foreach ($_sortable as $id => $data) {
            if (empty($data))
                continue;

            $data = (array)$data;
            if (!isset($data[1]))
                $data[1] = false;

            $sortable[$id] = $data;
        }

        $this->_column_headers = array($columns, $sortable);

        return $this->_column_headers;
    }

    function get_sortable_columns()
    {
        return array(
            'title' => 'title',
            'type' => 'type',
            'author' => 'author',
            'rank' => 'rank',
            'date' => array('date', true)
        );
    }

    function print_column_headers($with_id = true)
    {
        $strcolumn = '';

        list($columns, $sortable) = $this->get_column_info();

        $current_url = (is_ssl() ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        $current_url = remove_query_arg('paged', $current_url);

        if (isset($_GET['orderby']))
            $current_orderby = $_GET['orderby'];
        else
            $current_orderby = '';

        if (isset($_GET['order']) && 'desc' == $_GET['order'])
            $current_order = 'desc';
        else
            $current_order = 'asc';

        foreach ($columns as $column_key => $column_display_name) {
            $class = array('manage-column', "column-$column_key");

            $style = '';
            $style = ' style="' . $style . '"';

            if ('cb' == $column_key) {
                $class[] = 'check-column';
                $style = ' style="margin:0;padding:0;width:0px;"';
            }

            if (isset($sortable[$column_key])) {
                list($orderby, $desc_first) = $sortable[$column_key];

                if ($current_orderby == $orderby) {
                    $order = 'asc' == $current_order ? 'desc' : 'asc';
                    $class[] = 'sorted';
                    $class[] = $current_order;
                } else {
                    $order = $desc_first ? 'desc' : 'asc';
                    $class[] = 'sortable';
                    $class[] = $desc_first ? 'asc' : 'desc';
                }

                $column_display_name = '<a href="' . esc_url(add_query_arg(compact('orderby', 'order'), $current_url)) . '"><span>' . $column_display_name . '</span><span class="sorting-indicator"></span></a>';
            }

            $id = $with_id ? "id='$column_key'" : '';

            if (!empty($class))
                $class = "class='" . join(' ', $class) . "'";

            $strcolumn .= "<th scope='col' $id $class $style>$column_display_name</th>";
        }
        return $strcolumn;
    }

    function get_columns()
    {
        $post_type = 'post';

        $posts_columns = array();
        /* translators: manage posts column name */
        $posts_columns['cb'] = '';
        $posts_columns['title'] = _x('Title', 'column name');

        $posts_columns['type'] = __('Type');

        if (post_type_supports($post_type, 'author'))
            $posts_columns['author'] = __('Author');

        if (empty($post_type) || is_object_in_taxonomy($post_type, 'post_tag'))
            $posts_columns['keywords'] = __('Keywords');

        $posts_columns['rank'] = sprintf(__('Google.%s Position'), SQ_Tools::$options['sq_google_country']);
        $posts_columns['traffic'] = '';
        $posts_columns['date'] = __('Date');
        return $posts_columns;
    }

    function display_tablenav($which)
    {
        if ('top' == $which)
            wp_nonce_field('bulk-' . $this->_args['plural']);

        $strnav = '';
        ob_start();
        /* includes the block from theme directory */
        ?>
        <div class="tablenav <?php echo esc_attr($which); ?>">
            <div class="alignleft actions">
                <input type="submit" name="" id="post-query-submit" class="button" value="<?php echo __('Reset Filters') ?>" onclick="location.href = '?page=sq_posts';">
            </div>
            <?php if ('top' == $which) { ?>
                <div class="alignleft actions">
                    <input type="search" id="post-search-input" autofocus name="s" value="<?php echo SQ_Tools::getValue('s') ?>" onkeypress="if(sq_check_enter(event)){jQuery('#search-submit').trigger('click')}">
                    <input type="submit" id="search-submit" class="button" onclick="location.href = '?page=sq_posts&s=' + encodeURIComponent(jQuery('#post-search-input').val());" value="<?php echo __('Search Posts') ?>">
                    <script>
                        function sq_check_enter(e) {
                            if (e.keyCode == 13) {
                                return true;
                            }
                            return false;
                        }
                    </script>
                </div>
            <?php } ?>
            <?php
            $this->extra_tablenav($which);
            $this->pagination($which);
            ?>

            <br class="clear"/>
        </div>
        <?php
        $strnav = ob_get_contents();
        ob_end_clean();
        return $strnav;
    }

    function display_rows()
    {
        global $wp_query, $post;
        static $alternate;
        $strrow = '';
        $posts = $wp_query->posts;
        //print_R($posts);
        add_filter('the_title', 'esc_html');

        foreach ($posts as $post) {
            if (get_post_status($post->ID) == 'publish') {
                array_push($this->posts, $post->ID);
            }
            $alternate = 'alternate' == $alternate ? '' : 'alternate';

            $strrow .= '<tr id="post-' . $post->ID . '" class="post-' . $post->ID . ' type-post format-standard ' . $alternate . ' hentry">';
            $strrow .= $this->single_row($post);
            $strrow .= '</tr>';
        }
        return $strrow;
    }

    public function single_row($a_post)
    {
        global $post;

        $strcolumn = '';
        $value = '';
        $class = '';

        $global_post = $post;
        $post = $a_post;
        list($columns) = $this->get_column_info();
        setup_postdata($post);

        if ($post) {
            $edit_link = get_edit_post_link($post->ID);
            $title = _draft_or_post_title();
            $post_type_object = get_post_type_object($post->post_type);
            $can_edit_post = current_user_can($post_type_object->cap->edit_post, $post->ID);
            $json = SQ_ObjController::getModel('SQ_Post')->getKeyword($post->ID);
            foreach ($columns as $key => $column) {
                switch ($key) {
                    case 'title':
                        $value = '';
                        if ($can_edit_post) {
                            $value = '<a class="row-title" href="' . $edit_link . '" title="' . esc_attr(sprintf(__('Edit &#8220;%s&#8221;'), $title)) . '">' . $title . '</a>';
                            $actions = array();
                            if ($can_edit_post && 'trash' != $post->post_status) {
                                $actions['edit'] = '<a href="' . get_edit_post_link($post->ID, true) . '" title="' . esc_attr(__('Edit this item')) . '">' . __('Edit') . '</a>';
                            }
                            if ($post_type_object->public) {
                                if (in_array($post->post_status, array('pending', 'draft', 'future'))) {
                                    if ($can_edit_post)
                                        $actions['view'] = '<a href="' . esc_url(add_query_arg('preview', 'true', get_permalink($post->ID))) . '" title="' . esc_attr(sprintf(__('Preview &#8220;%s&#8221;'), $title)) . '" rel="permalink" target="_blank">' . __('Preview') . '</a>';
                                } elseif ('trash' != $post->post_status) {
                                    $actions['view'] = '<a href="' . get_permalink($post->ID) . '" title="' . esc_attr(sprintf(__('View &#8220;%s&#8221;'), $title)) . '" rel="permalink" target="_blank">' . __('View') . '</a>';
                                }
                            }

                            $value .= $this->row_actions($actions);
                        } else {
                            $value = $title;
                        }
                        break;
                    case 'author':
                        //$author = get_userdata($post->post_author);
                        $value = sprintf('<a href="%s">%s</a>', esc_url(add_query_arg(array('page' => 'sq_posts', 'author' => get_the_author_meta('ID')), 'admin.php')), get_the_author());
                        break;
                    case 'type':
                        $value = sprintf('<a href="%s">%s</a>', esc_url(add_query_arg(array('page' => 'sq_posts', 'type' => $post->post_type), 'admin.php')), ucfirst($post->post_type));
                        break;
                    case 'keywords':
                        $value = '';
                        if (isset($json->keyword)) {
                            $json->keyword = str_replace('\\', '', $json->keyword);
                            $value = sprintf('<a href="%s">%s</a>', esc_attr(add_query_arg(array('page' => 'sq_posts', 'keyword' => strtolower($json->keyword)), 'admin.php')), $json->keyword);
                        } else {
                            $value = __('No Tags');
                        }
                        break;

                    case 'rank':
                        $value = '';
                        if (isset($json->rank)) {
                            if ($json->rank == -2) {
                                $value = __('Could not receive data from google (Err: blocked IP)');
                            } elseif ($json->rank == -1) {
                                $value = __('Not in top 100 for: <br /> "'.$json->keyword.'"');
                            } elseif ($json->rank == 0) {
                                $value = __('The URL is indexed');
                            } elseif ($json->rank > 0) {
                                $value = '<strong style="display:block; font-size: 120%; width: 100px; margin: 0 auto; text-align:right;">' . sprintf(__('%s'), $json->rank) . '</strong>' . ((isset($json->country)) ? ' (' . $json->country . ')' : '');
                            }
                            $value = sprintf('<a id="sq_rank_value' . $post->ID . '" href="%s" style="display:block; width: 120px; margin: 0 auto; text-align:right;">%s</a><span class="sq_rank_column_button_recheck sq_rank_column_button" onclick="sq_recheckRank(' . $post->ID . ')">%s</span>', esc_url(add_query_arg(array('page' => 'sq_posts', 'rank' => $json->rank), 'admin.php')), $value, __('Force recheck', _SQ_PLUGIN_NAME_));
                        } else {
                            $value = sprintf('<a id="sq_rank_value' . $post->ID . '" href="%s" style="display:block; width: 120px; margin: 0 auto; text-align:right;">%s</a><span class="sq_rank_column_button_recheck sq_rank_column_button" onclick="sq_recheckRank(' . $post->ID . ')">%s</span>', esc_url(add_query_arg(array('page' => 'sq_posts', 'rank' => false), 'admin.php')), __('Not yet verified'), __('Check now', _SQ_PLUGIN_NAME_));
                        }

                        break;
                    case 'traffic':
                        $value = '<div class="sq_rank_column_row sq_minloading" ref="' . $post->ID . '"></div>';
                        $class = 'sq_rank_column';
                        break;
                    case 'date':
                        $value = '';

                        if (isset($post->post_date)) {
                            if ('0000-00-00 00:00:00' == $post->post_date) {
                                $h_time = __('Unpublished');
                                $time_diff = 0;
                            } else {
                                $m_time = $post->post_date;
                                $time = get_post_time('G', true, $post);

                                $time_diff = time() - $time;

                                if ($time_diff > 0 && $time_diff < 24 * 60 * 60)
                                    $h_time = sprintf(__('%s ago'), human_time_diff($time));
                                else
                                    $h_time = mysql2date(__('Y/m/d'), $m_time);
                            }
                            $value = $h_time . '<br />';

                            if ('publish' == $post->post_status) {
                                $value .= __('Published');
                            } elseif ('future' == $post->post_status) {
                                if ($time_diff > 0)
                                    $value .= '<strong class="attention">' . __('Missed schedule') . '</strong>';
                                else
                                    $value .= __('Scheduled');
                            } else {
                                $value .= __('Last Modified');
                            }
                        }
                        $class = '';
                        break;
                    default:
                        $value = '';
                        break;
                }
                if ($key == 'cb') {
                    $strcolumn .= '<th scope="row" class="check-column"></th>';
                } else {
                    $strcolumn .= '<td class = "manage-column ' . ($class <> '' ? $class : 'column-' . $key) . '">' . $value . '</td>';
                }
            }
        }
        $post = $global_post;
        return $strcolumn;
    }

    public function hookFooter()
    {
        $this->postlist->setPosts($this->posts);
        $this->postlist->hookFooter();
    }

    public function getScripts()
    {
        return $this->postlist->getScripts();
    }

}
