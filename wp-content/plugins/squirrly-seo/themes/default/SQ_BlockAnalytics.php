<div id="sq_analytics" >
    <?php
    foreach ($view->audit->content as $key => $audit) {
        $current_grup = $view->audit->groups->$key;
        if (!isset($current_grup->total)){
            continue;
        }
        ?>
        <div class="sq_analytics_groups" >
            <ul class="sq_analytics_list" >
                <li>
                    <?php if ($key <> 'rank') { ?>
                        <div class="sq_separator"></div>
                    <?php } ?>
                    <table>
                        <tr>
                            <td id="sq_analytics_tasks_header_<?php echo $key ?>" class="sq_analytics_tasks_header" colspan="4">
                                <div class="sq_tooltip" title="<?php echo $current_grup->tooltip ?>">
                                    <span class="persist-header sq_analytics_tasks_header_title <?php echo $current_grup->color . '_text' ?>">
                                        <?php echo ucfirst($key) ?>
                                    </span>

                                    <span class="sq_analytics_task_completed <?php echo $current_grup->color ?>"  >
                                        <?php echo ((isset($current_grup->total) && $current_grup->total >= 0) ? $current_grup->total : 'N/A') ?>
                                    </span>
                                </div>
                            </td>
                        </tr>
                    </table>


                    <ul>
                        <?php
                        if (!empty($audit))
                            foreach ($audit as $key => $task) {
                                $task->id = $view->post_id . $task->id;
                                $replace = '';
                                if (is_array($task->value)) {
                                    foreach ($task->value as $value) {
                                        if (is_object($value)) {
                                            if ($value->value <> '')
                                                $replace .= '<span><a href="' . $value->url . '" rel="nofollow" target="_blank" rel="nofollow">' . $value->value . '</a></span>';
                                            else
                                                $replace .= '<span><a href="' . $value->url . '" rel="nofollow" target="_blank" rel="nofollow">' . $value->url . '</a></span>';
                                        } else {
                                            $replace .= '<span>' . $value . '</span>';
                                        }
                                    }
                                } elseif (is_object($task->value)) {
                                    if ($task->name === 'Links') {
                                        if (isset($task->value->mozLinks)) {
                                            $task->value->mozLinks = number_format_i18n($task->value->mozLinks);
                                            $replace .= '<ul class="sq_analytics_values" >
                                                    <li>
                                                        <div class="sq_analytics_values_title">' . __('Moz Inbound Links') . '</div>
                                                        <div class="sq_analytics_values_value">' . $task->value->mozLinks . '</div>
                                                        <div class="sq_rank_sprite sq_rank_seomoz_inbound"></div>
                                                    </li>
                                                    ' .
                                                    ((isset($task->value->ahrefsLinks)) ?
                                                            '<li>
                                                        <div class="sq_analytics_values_title">' . __('Ahrefs Inbound Links') . '</div>
                                                        <div class="sq_analytics_values_value">' . $task->value->ahrefsLinks . '</div>
                                                        <div class="sq_rank_sprite sq_rank_ahrefs_rank"></div>
                                                    </li>' : '') . '
                                                </ul>';
                                        }
                                    } elseif ($task->name === 'Authority') {
                                        $task->value->mozAuthority = number_format_i18n($task->value->mozAuthority);
                                        $task->value->mozRank = number_format_i18n($task->value->mozRank);

                                        $replace .= '<ul class="sq_analytics_values" >
                                                    <li>
                                                        <div class="sq_analytics_values_title">' . __('Moz Authority') . '</div>
                                                        <div class="sq_analytics_values_value">' . $task->value->mozAuthority . '</div>
                                                        <div class="sq_rank_sprite sq_rank_seomoz_authority"></div>
                                                    </li>
                                                    <li>
                                                        <div class="sq_analytics_values_title">' . __('Moz Rank') . '</div>
                                                        <div class="sq_analytics_values_value">' . $task->value->mozRank . '</div>
                                                        <div class="sq_rank_sprite sq_rank_seomoz_rank"></div>
                                                    </li>
                                                    ' .
                                                ((isset($task->value->googleRank)) ?
                                                        '<li>
                                        <div class = "sq_analytics_values_title">' . __('Google Page Rank') . '</div>
                                        <div class = "sq_analytics_values_value">' . $task->value->googleRank . '</div>
                                        <div class = "sq_rank_sprite sq_rank_google_pagerank"></div>
                                        </li>' : '') .
                                                ((isset($task->value->ahrefsRank)) ?
                                                        '<li>
                                        <div class = "sq_analytics_values_title">' . __('Ahrefs Rank') . '</div>
                                        <div class = "sq_analytics_values_value">' . $task->value->ahrefsRank . '</div>
                                        <div class = "sq_rank_sprite sq_rank_ahrefs_rank"></div>
                                        </li>' : '') . '
                                                </ul>';
                                    } elseif ($task->name === 'Shares') {
                                        $task->value->facebookShare = number_format_i18n($task->value->facebookShare);
                                        $task->value->facebookLike = number_format_i18n($task->value->facebookLike);
                                        $task->value->linkedinShare = number_format_i18n($task->value->linkedinShare);
                                        $task->value->plusShare = number_format_i18n($task->value->plusShare);
                                        $task->value->redditShare = number_format_i18n($task->value->redditShare);
                                        $task->value->stumbleShare = number_format_i18n($task->value->stumbleShare);
                                        $task->value->twitterShare = number_format_i18n($task->value->twitterShare);

                                        $replace .= '<ul class="sq_analytics_values" >
                                                    <li>
                                                        <div class="sq_analytics_values_title">' . __('Facebook shares') . '</div>
                                                        <div class="sq_analytics_values_value">' . $task->value->facebookShare . '</div>
                                                        <div class="sq_rank_sprite sq_rank_flag_facebook"></div>
                                                    </li>
                                                    <li>
                                                        <div class="sq_analytics_values_title">' . __('Facebook likes') . '</div>
                                                        <div class="sq_analytics_values_value">' . $task->value->facebookLike . '</div>
                                                        <div class="sq_rank_sprite sq_rank_flag_facebook_like"></div>
                                                    </li>
                                                    <li>
                                                        <div class="sq_analytics_values_title">' . __('Twitter shares') . '</div>
                                                        <div class="sq_analytics_values_value">' . $task->value->twitterShare . '</div>
                                                        <div class="sq_rank_sprite sq_rank_flag_twitter"></div>
                                                    </li>
                                                    <li>
                                                        <div class="sq_analytics_values_title">' . __('Google+ shares') . '</div>
                                                        <div class="sq_analytics_values_value">' . $task->value->plusShare . '</div>
                                                        <div class="sq_rank_sprite sq_rank_flag_googleplus"></div>
                                                    </li>
                                                    <li>
                                                        <div class="sq_analytics_values_title">' . __('LinkedIn Shares') . '</div>
                                                        <div class="sq_analytics_values_value">' . $task->value->linkedinShare . '</div>
                                                        <div class="sq_rank_sprite sq_rank_flag_linkedin"></div>
                                                    </li>
                                                    <li>
                                                        <div class="sq_analytics_values_title">' . __('StumbleUpon shares') . '</div>
                                                        <div class="sq_analytics_values_value">' . $task->value->stumbleShare . '</div>
                                                        <div class="sq_rank_sprite sq_rank_flag_stumbleupon"></div>
                                                    </li>
                                                    <li>
                                                        <div class="sq_analytics_values_title">' . __('Reddit shares') . '</div>
                                                        <div class="sq_analytics_values_value">' . $task->value->redditShare . '</div>
                                                        <div class="sq_rank_sprite sq_rank_flag_reddit"></div>
                                                    </li>
                                                </ul>';
                                        //$replace .= '<span>' . $key . ': ' . $value . '</span>';
                                    } else {
                                        foreach ($task->value as $key => $value) {

                                            if (is_object($value)) {
                                                if (isset($value->url) && isset($value->value)) {

                                                    if ($value->value <> '')
                                                        $replace .= '<span><a href="' . $value->url . '" rel="nofollow" target="_blank" rel="nofollow">' . $value->value . '</a></span>';
                                                    else
                                                        $replace .= '<span><a href="' . $value->url . '" rel="nofollow" target="_blank" rel="nofollow">' . $value->url . '</a></span>';
                                                }
                                            }
                                            else {
                                                $replace .= '<span>' . $value . '</span>';
                                            }
                                        }
                                    }
                                } else {
                                    $replace .= '<strong>' . $task->value . '</strong>';
                                }
                                $replace = str_replace(array('{keyword}', '{optimized}'), array($view->audit->keyword, $view->audit->optimized), $replace);
                                $task->title = str_replace(array('{keyword}', '{optimized}'), array($view->audit->keyword, $view->audit->optimized . '%'), $task->title);
                                ?>
                                <li id="sq_analytics_task_<?php echo $task->id ?>" class="sq_analytics_tasks_row">
                                    <table>
                                        <tr>
                                            <td rowspan="2" class="sq_first_header_column"><span class="<?php echo ((int) $task->complete == 1) ? 'sq_analytics_tasks_pass' : 'sq_analytics_tasks_fail' ?> sq_tooltip" title="<?php echo ((int) $task->complete == 1) ? __('Nicely done! Now you can focus on the other tasks', _SQ_PLUGIN_NAME_) : __('I know you can improve this. Please follow the documentation for a quicker progress', _SQ_PLUGIN_NAME_) ?>"></span></td>
                                            <td colspan="2"  class="sq_second_header_column"><?php if ($task->title <> '') { ?><span class="sq_analytics_tasks_title"><?php echo $task->title ?></span><?php } ?>
                                                <span class="sq_analytics_tasks_value sq_analytics_tasks_value<?php echo ((int) $task->complete == 1) ? '_pass' : '_fail' ?>">
                                                    <?php
                                                    if ($task->complete) {
                                                        echo sprintf($task->success, $replace);
                                                    } else {
                                                        echo sprintf($task->error, $replace);
                                                    }
                                                    ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="sq_analytics_tasks_description sq_second_column">
                                                <?php
                                                if (isset($task->alias) && !is_array($task->alias)) {
                                                    $task->alias = array($task->alias);
                                                }
                                                //print_R($task->alias);
                                                if (isset($task->alias) && is_array($task->alias)) {
                                                    foreach ($task->alias as $alias) {

                                                        $replace_alias = '';
                                                        if (is_array($alias->value) || is_object($alias->value)) {
                                                            foreach ($alias->value as $value) {
                                                                // print_R($value);
                                                                if (is_object($value)) {
                                                                    if (isset($value->alias->url) && isset($value->alias->value))
                                                                        $replace_alias .= '<span><a href="' . $value->url . '" rel="nofollow" target="_blank" rel="nofollow">' . $value->alias->value . '</a></span>';
                                                                } else
                                                                    $replace_alias .= '<span>' . $value . '</span>';
                                                            }
                                                        } else {
                                                            $replace_alias .= '<strong>' . $alias->value . '</strong>';
                                                        }

                                                        if ($alias->complete)
                                                            echo '<span class="sq_analytics_tasks_alias">' . $alias->title . ' <strong class="sq_analytics_tasks_value sq_analytics_tasks_value_pass">' . sprintf($alias->success, $replace_alias) . '</strong></span>';
                                                        else
                                                            echo '<span class="sq_analytics_tasks_alias">' . $alias->title . ' <strong class="sq_analytics_tasks_value sq_analytics_tasks_value_fail">' . sprintf($alias->error, $replace_alias) . '</strong></span>';
                                                    }
                                                }
                                                if ($task->complete) {
                                                    $task->description = preg_replace('/<span[^>]*class="(sq_fix|sq_list_error_title|sq_list_error)">.*?<\/span>/is', '', $task->description);
                                                } else {
                                                    $task->description = preg_replace('/<span[^>]*class="(sq_list_success_title|sq_list_success)">.*?<\/span>/is', '', $task->description);
                                                }
                                                $task->description = str_replace(array('{keyword}', '{optimized}'), array($view->audit->keyword, $view->audit->optimized), $task->description);

                                                $array = array();
                                                for ($i = 0; $i < substr_count($task->description, '%s'); $i++)
                                                    $array[] = $replace;


                                                if (isset($task->graph) && !empty($task->graph) && isset($task->video) && $task->video <> '') {
                                                    ?>
                                                    <div style="position: relative; text-align: center; margin-bottom: 15px;">
                                                        <div id="sq_analytics_tasks_video_cover_<?php echo $task->id ?>" class="sq_analytics_tasks_video_cover" rel="<?php echo $task->video ?>" ></div>
                                                        <a href="http://www.youtube.com/watch?v=<?php echo $task->video ?>" target="_blank"  rel="nofollow" >Go to video</a>
                                                    </div>
                                                    <?php
                                                }
                                                echo @vsprintf($task->description, $array);
                                                ?>
                                            </td>
                                            <td class="sq_third_column">
                                                <div style="position:relative">
                                                    <?php
                                                    if (isset($task->graph) && !empty($task->graph)) {
                                                        $data = array();

                                                        if ($task->name == 'Traffic') {
                                                            $data[] = array('', __('Visits', _SQ_PLUGIN_NAME_));
                                                        } else {
                                                            $data[] = array('', '');
                                                        }
                                                        foreach ($task->graph as $key => $value) {
                                                            if ($task->name == 'Traffic') {
                                                                $data[] = array($key, (int) number_format_i18n($value));
                                                            } else {
                                                                $data[] = array('', (int) number_format_i18n($value));
                                                            }
                                                        }
                                                        if (isset($task->value) && $task->name == 'GoogleSerp') {
                                                            echo '<div class="sq_chart_text">'
                                                            . (($task->value->value > 0) ? __('Current: ', _SQ_PLUGIN_NAME_) . $task->value->value : '') . '<br />'
                                                            . (($task->value->min > 0) ? __('Lowest: ', _SQ_PLUGIN_NAME_) . $task->value->min : '') . '<br />'
                                                            . (($task->value->max > 0) ? __('Highest: ', _SQ_PLUGIN_NAME_) . $task->value->max : '') . '<br />'
                                                            . '</div>';
                                                            echo '<div id="sq_chart_' . $task->id . '" class="sq_chart" style="margin:0 auto; width:200px; height:60px;"></div><script>var sq_chart_' . $task->id . '_val = drawChart("sq_chart_' . $task->id . '", ' . json_encode($data) . ',true); </script>';
                                                        } else {
                                                            echo '<div class="sq_chart_text">' . __('last 30 days', _SQ_PLUGIN_NAME_) . '</div>';
                                                            echo '<div id="sq_chart_' . $task->id . '" class="sq_chart" style="margin:0 auto; width:200px; height:60px;"></div><script>var sq_chart_' . $task->id . '_val = drawChart("sq_chart_' . $task->id . '", ' . json_encode($data) . ',false); </script>';
                                                        }
                                                    } elseif (isset($task->video) && $task->video <> '') {
                                                        ?>
                                                        <div id="sq_analytics_tasks_video_cover_<?php echo $task->id ?>" class="sq_analytics_tasks_video_cover" rel="<?php echo $task->video ?>" ></div>
                                                        <a href="http://www.youtube.com/watch?v=<?php echo $task->video ?>" target="_blank"  rel="nofollow" >Go to video</a>
                                                        <?php
                                                    } elseif (isset($task->side) && $task->side <> '') {
                                                        echo $task->side;
                                                    }
                                                    ?>
                                                </div>
                                            </td>
                                        </tr>
                                    </table>
                                </li>
                            <?php } ?>
                    </ul>
                </li>
            </ul>
        </div>
    <?php } ?>
    <?php echo $view->audit->message ?>
</div>

<script>
    jQuery('.sq_analytics_tasks_video_cover').unbind('click').bind('click', function () {
        jQuery(this).html('<object width="280" height="158"><param name="movie" value="https://www.youtube.com/v/' + jQuery(this).attr('rel') + ((jQuery(this).attr('rel').indexOf('?') != -1) ? '&' : '?') + 'version=3&amp;hl=en_US&amp;autoplay=1"></param><param name="allowFullScreen" value="true"></param><param name="allowscriptaccess" value="always"></param><embed src="https://www.youtube.com/v/' + jQuery(this).attr('rel') + ((jQuery(this).attr('rel').indexOf('?') != -1) ? '&' : '?') + 'version=3&amp;hl=en_US&amp;autoplay=1" type="application/x-shockwave-flash" width="280" height="158" allowscriptaccess="always" allowfullscreen="true"></embed></object>');
    });
</script>