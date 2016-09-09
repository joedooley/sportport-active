<?php
require_once 'core/classes/cron.php';

//callback function
function cart_product_activate_plugin(){

	global $wpdb;

	$table_name = $wpdb->prefix . "cp_feeds";
	if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
		$sql = "
			CREATE TABLE $table_name (
			`id` bigint(20) NOT NULL AUTO_INCREMENT,
			`category` varchar(250) NOT NULL,
			`remote_category` varchar(1000) NOT NULL,
			`filename` varchar(250) NOT NULL,
			`url` varchar(500) NOT NULL,
			`type` varchar(50) NOT NULL DEFAULT 'google',
			`own_overrides` int(10),
			`feed_overrides` text,
			`product_count` int,
			`feed_errors` text,
			`feed_title` varchar(250),
			PRIMARY KEY (`id`)
		)";
		//)" ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;
		$wpdb->query( $sql );
	}

	$table_name = $wpdb->prefix . "cp_feeds";
	$sql = "
		CREATE TABLE $table_name (
		id bigint(20) NOT NULL AUTO_INCREMENT,
		category varchar(250) NOT NULL,
		remote_category varchar(1000) NOT NULL,
		filename varchar(250) NOT NULL,
		url varchar(500) NOT NULL,
		type varchar(50) NOT NULL,
		own_overrides int(10),
		feed_overrides text,
		product_count int,
		feed_errors text,
		feed_title varchar(250),
		PRIMARY KEY  (id)
	)";

	$table_name = $wpdb->prefix . "ebay_accounts";
    if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        $sql = "
           CREATE TABLE $table_name (
           `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
          `title` varchar(128) NOT NULL,
          `site_id` int(11) DEFAULT NULL,
          `site_code` varchar(16) DEFAULT NULL,
          `sandbox_mode` varchar(16) DEFAULT NULL,
          `user_name` varchar(32) DEFAULT NULL,
          `user_details` text NOT NULL,
          `active` int(11) DEFAULT NULL,
          `token` text NOT NULL,
          `valid_until` datetime DEFAULT NULL,
          `ebay_motors` int(11) DEFAULT NULL,
          `oosc_mode` int(11) DEFAULT NULL,
          `seller_profiles` int(11) DEFAULT NULL,
          `shipping_profiles` mediumtext,
          `payment_profiles` mediumtext,
          `return_profiles` mediumtext,
          `shipping_discount_profiles` mediumtext,
          `categories_map_ebay` mediumtext,
          `categories_map_store` mediumtext,
          `default_ebay_category_id` bigint(20) DEFAULT NULL,
          `paypal_email` varchar(64) DEFAULT NULL,
          `sync_orders` int(11) DEFAULT NULL,
          `sync_products` int(11) DEFAULT NULL,
          `last_orders_sync` datetime DEFAULT NULL,
          `default_account` tinyint(4) DEFAULT '0',
           PRIMARY KEY (`id`)
        )";
        $wpdb->query( $sql );
    }

    $table_name = $wpdb->prefix . "ebay_accounts";
    $sql = "
       CREATE TABLE $table_name (
       `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
      `title` varchar(128) NOT NULL,
      `site_id` int(11) DEFAULT NULL,
      `site_code` varchar(16) DEFAULT NULL,
      `sandbox_mode` varchar(16) DEFAULT NULL,
      `user_name` varchar(32) DEFAULT NULL,
      `user_details` text NOT NULL,
      `active` int(11) DEFAULT NULL,
      `token` text NOT NULL,
      `valid_until` datetime DEFAULT NULL,
      `ebay_motors` int(11) DEFAULT NULL,
      `oosc_mode` int(11) DEFAULT NULL,
      `seller_profiles` int(11) DEFAULT NULL,
      `shipping_profiles` mediumtext,
      `payment_profiles` mediumtext,
      `return_profiles` mediumtext,
      `shipping_discount_profiles` mediumtext,
      `categories_map_ebay` mediumtext,
      `categories_map_store` mediumtext,
      `default_ebay_category_id` bigint(20) DEFAULT NULL,
      `paypal_email` varchar(64) DEFAULT NULL,
      `sync_orders` int(11) DEFAULT NULL,
      `sync_products` int(11) DEFAULT NULL,
      `last_orders_sync` datetime DEFAULT NULL,
      `default_account` tinyint(4) DEFAULT '0',
       PRIMARY KEY (`id`)
    )";
            

    $table_name = $wpdb->prefix . "ebay_shipping";
    if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        $sql = "
        CREATE TABLE $table_name (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `paypal_email` varchar(255) DEFAULT NULL,
          `paypal_accept` tinyint(4) DEFAULT '1',
          `shippingfee` varchar(255) DEFAULT NULL,
          `site_id` int(11) DEFAULT NULL,
          `dispatchTime` int(11) NOT NULL,
          `ebayShippingType` varchar(255) NOT NULL,
          `default_account` varchar(255) NOT NULL,
          `shipping_service` varchar(255) NOT NULL,
          `categoryMapping` tinyint(4) NOT NULL DEFAULT '1',
          `listingDuration` varchar(255) NOT NULL,
          `listingType` varchar(255) NOT NULL,
          `refundOption` varchar(255) NOT NULL,
          `refundDesc` varchar(255) NOT NULL,
          `quantity` int(11) NOT NULL,
          `returnwithin` varchar(255) NOT NULL,
          `postalcode` varchar(255) NOT NULL,
          `additionalshippingservice` varchar(255) NOT NULL,
          `conditionType` varchar(255) NOT NULL,
          `site_code` varchar(255) NOT NULL,
          `currency_code` varchar(255) NOT NULL,
          `site_abbr` varchar(255) NOT NULL,
            PRIMARY KEY (`id`)
        ) ";
        $wpdb->query( $sql );
    }

    $table_name = $wpdb->prefix . "ebay_shipping";  
    $sql = "
            CREATE TABLE $table_name (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `paypal_email` varchar(255) DEFAULT NULL,
          `paypal_accept` tinyint(4) DEFAULT '1',
          `shippingfee` varchar(255) DEFAULT NULL,
          `site_id` int(11) DEFAULT NULL,
          `dispatchTime` int(11) NOT NULL,
          `ebayShippingType` varchar(255) NOT NULL,
          `default_account` varchar(255) NOT NULL,
          `shipping_service` varchar(255) NOT NULL,
          `categoryMapping` tinyint(4) NOT NULL DEFAULT '1',
          `listingDuration` varchar(255) NOT NULL,
          `listingType` varchar(255) NOT NULL,
          `refundOption` varchar(255) NOT NULL,
          `refundDesc` varchar(255) NOT NULL,
          `quantity` int(11) NOT NULL,
          `returnwithin` varchar(255) NOT NULL,
          `postalcode` varchar(255) NOT NULL,
          `additionalshippingservice` varchar(255) NOT NULL,
          `conditionType` varchar(255) NOT NULL,
          `site_code` varchar(255) NOT NULL,
          `currency_code` varchar(255) NOT NULL,
          `site_abbr` varchar(255) NOT NULL,
            PRIMARY KEY (`id`)
        ) ";


    $table_name = $wpdb->prefix . "ebay_currency";
    if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        $sql = "
        CREATE TABLE $table_name (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `site_id` int(11) NOT NULL,
          `currency_code` varchar(255) NOT NULL,
          `site_abbr` varchar(255) NOT NULL,
          PRIMARY KEY (`id`)
        )";
        $wpdb->query( $sql );
    }

    $table_name = $wpdb->prefix . "ebay_currency";
    $sql = "
    CREATE TABLE $table_name (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `site_id` int(11) NOT NULL,
      `currency_code` varchar(255) NOT NULL,
      `site_abbr` varchar(255) NOT NULL,
      PRIMARY KEY (`id`)
    )";

    $insertQuery = "
    INSERT INTO $table_name (`id`, `site_id`, `currency_code`, `site_abbr`) VALUES
        (1, 0, 'USD', 'US'),
        (2, 2, 'USD', 'CA'),
        (3, 3, 'GBP', 'UK'),
        (4, 15, 'AUD', 'AU'),
        (5, 16, 'EUR', 'AT'),
        (6, 123, 'EUR', ' BENL'),
        (7, 23, 'EUR', 'BEFR'),
        (8, 210, 'USD', 'CAFR'),
        (9, 100, 'USD', 'US'),
        (10, 71, 'EUR', 'FR'),
        (11, 77, 'EUR', 'DE'),
        (12, 201, 'HKD', 'HK'),
        (13, 203, 'INR', 'IN'),
        (14, 101, 'EUR', 'IT'),
        (15, 207, 'MYR', 'MY'),
        (16, 146, 'EUR', 'NL'),
        (17, 211, 'PHP', 'PH'),
        (18, 212, 'PLN', 'PL'),
        (19, 216, 'SGD', 'SG'),
        (20, 186, 'EUR', 'ES'),
        (21, 215, 'RUB', 'RU'),
        (22, 193, 'CHF', 'CH');
    ";
    $wpdb->query($insertQuery);

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );

	/* Not IMPL: Using wp_options
	$table_name = $wpdb->prefix . "cp_feed_options";
	if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
		$sql = "
			CREATE TABLE $table_name (
			id int AUTO_INCREMENT,
			name varchar(255),
			value text,
			kind int,
			PRIMARY KEY (`id`)
		)";
		$wpdb->query( $sql );
	}*/

}

function cart_product_deactivate_plugin() {

	$next_refresh = wp_next_scheduled('update_cartfeeds_hook');
	if ($next_refresh )
		wp_unschedule_event($next_refresh, 'update_cartfeeds_hook');

}