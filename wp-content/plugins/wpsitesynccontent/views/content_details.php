<div id="sync-details-container">
	<p><b><?php _e('Content Details:', 'wpsitesync-pull'); ?></b></p>
	<ul style="border:1px solid gray; padding:.2rem; margin: -4px">
		<li><?php printf(__('Target Content Id: %d', 'wpsitesync-pull'), $data['target_post_id']); ?></li>
		<li><?php printf(__('Content Title: %s', 'wpsitesync-pull'), $data['post_title']); ?></li>
		<li><?php printf(__('Content Author: %s', 'wpsitesync-pull'), $data['post_author']); ?></li>
		<li><?php printf(__('Last Modified: %s', 'wpsitesync-pull'),
				date(get_option('date_format') . ' ' . get_option('time_format'), strtotime($data['modified'])) ); ?></li>
		<li><?php printf(__('Content: %s', 'wpsitesync-pull'), $data['content']); ?></li>
		<?php do_action('spectrom_sync_details_view', $data); ?>
	</ul>
	<p><?php _e('Note: Syncing this Content will overwrite data.', 'wpsitesync-pull'); ?></p>
</div>
