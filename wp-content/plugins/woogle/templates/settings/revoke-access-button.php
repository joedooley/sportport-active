<tr valign="top">
	<th scope="row" class="titledesc">
		Access
	</th>
	<td class="forminp forminp-update-google-shopping-api">
		<button id="update-google-shopping-api-button" class="button button-secondary" type="button" onclick="window.location = <?php echo esc_html( json_encode( admin_url( 'admin-ajax.php?action=google_shopping_api_revoke_access' ) ) ); ?>">Revoke Access</button>
		<span class="description">Clear all access tokens from the system.</span>
	</td>
</tr>