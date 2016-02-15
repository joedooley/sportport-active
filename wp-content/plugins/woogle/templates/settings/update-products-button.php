<tr valign="top">
	<th scope="row" class="titledesc">
		Integration
	</th>
	<td class="forminp forminp-update-google-shopping-api">
		<button id="update-google-shopping-api-button" class="button button-secondary" type="button" onclick="window.location = <?php echo esc_html( json_encode( admin_url( 'admin-ajax.php?action=update_google_shopping_api' ) ) ); ?>">Update Products</button>
		<span class="description">Update your proucts in the Google Merchant Center</span>
	</td>
</tr>

<script>
// <![CDATA[[
jQuery(function ($) {
	$('.woogle-input').bind('keyup change', function () {
		var button = document.getElementById('update-google-shopping-api-button');
		button.disabled = true;
		button.innerText = "Please save your changes";
	});
});
// ]]>
</script>