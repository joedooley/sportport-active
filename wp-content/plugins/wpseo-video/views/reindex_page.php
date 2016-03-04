<h2>Reindex</h2>

<p><?php _e('Your site is being indexed at the moment, so don\'t close this window. This process may take a few minutes to complete.', 'yoast-video-seo'); ?></p>

<input type="hidden" name="video_seo_percentage" id="video_seo_percentage_hidden" value="0" />
<?php wp_nonce_field( 'videoseo-ajax-nonce-for-reindex', 'videoseo-nonce-ajax' ); ?>

<div id="video_seo_progressbar">
	<div class="bar">
		<p><span class="bar_status">&nbsp;</span></p>
	</div>
</div>

<p>
	<strong><?php _e('Estimated time to go:', 'yoast-video-seo'); ?> <span class="video_seo_timetogo" id="video_seo_total_time">-- : --</span><span id="video_seo_done"><?php printf( __('<a href="%s" class="button-primary">Done! Go back to the Video SEO settings &raquo;</a>', 'yoast-video-seo'), 'admin.php?page=wpseo_video'); ?></span></strong><br />
	<strong><?php _e('Posts to go:', 'yoast-video-seo'); ?> <span class="video_seo_timetogo" id="video_seo_posts_to_go">--</span></strong><br />
	<strong><?php _e('Total posts:', 'yoast-video-seo'); ?> <span class="video_seo_timetogo" id="video_seo_total_posts">--</span></strong><br />
</p>