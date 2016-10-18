<?php

	if ( !function_exists( 'by_jordy_meow' ) ) {
		function by_jordy_meow( $hide_ads = false ) {
			echo '<div><span style="font-size: 13px; position: relative; top: -6px;">Developed by <a style="text-decoration: none;" target="_blank" href="http://meowapps.com">Jordy Meow</a></span>';
		}
	}

	if ( !function_exists( 'jordy_meow_donation' ) ) {
		function jordy_meow_donation( $showWPE = true ) {
			if ( defined( 'WP_HIDE_DONATION_BUTTONS' ) && WP_HIDE_DONATION_BUTTONS == true )
				return;
			if ( $showWPE ) {
				$url = plugins_url( '/img/wpengine.png', dirname( __FILE__ ) . '/..' );
				echo '<a style="float: right;" target="_blank" href="http://www.shareasale.com/r.cfm?B=917225&U=767054&M=41388&urllink=">
				<img src="' . $url . '" height="60" border="0" /></a>';
			}
		}
	}

	if ( !function_exists('jordy_meow_footer') ) {
		function jordy_meow_footer() {
			?>
			<div style=" color: #32595E; border: 1px solid #DFDFDF; position: absolute;margin-right: 20px;right: 0px;left: 0px;font-family: Tahoma;z-index: 10;background: white;margin-top: 15px;font-size: 7px;padding: 0px 10px;">
			<p style="font-size: 11px; font-family: Tahoma;"><b>This plugin is actively developed and maintained by <a href='http://www.meow.fr'>Jordy Meow</a></b>.<br />More of my tools are available on <a target='_blank' href="http://meowapps.com">Meow Apps</a> and my website is <a target='_blank' href='http://jordymeow.com'>Jordy Meow's Offbeat Guide to Japan</a>.
			</div>
			<?php
		}
	}
?>
