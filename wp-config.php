<?php
/**
 * This config file is yours to hack on. It will work out of the box on Pantheon
 * but you may find there are a lot of neat tricks to be used here.
 *
 * See our documentation for more details:
 *
 * https://pantheon.io/docs
 */

/**
 * Local configuration information.
 *
 * If you are working in a local/desktop development environment and want to
 * keep your config separate, we recommend using a 'wp-config-local.php' file,
 * which you should also make sure you .gitignore.
 */
if (file_exists(dirname(__FILE__) . '/wp-config-local.php') && !isset($_ENV['PANTHEON_ENVIRONMENT'])):
  # IMPORTANT: ensure your local config does not include wp-settings.php
  require_once(dirname(__FILE__) . '/wp-config-local.php');

/**
 * Pantheon platform settings. Everything you need should already be set.
 */
else:
  if (isset($_ENV['PANTHEON_ENVIRONMENT'])):
    // ** MySQL settings - included in the Pantheon Environment ** //
    /** The name of the database for WordPress */
    define('DB_NAME', $_ENV['DB_NAME']);

    /** MySQL database username */
    define('DB_USER', $_ENV['DB_USER']);

    /** MySQL database password */
    define('DB_PASSWORD', $_ENV['DB_PASSWORD']);

    /** MySQL hostname; on Pantheon this includes a specific port number. */
    define('DB_HOST', $_ENV['DB_HOST'] . ':' . $_ENV['DB_PORT']);

    /** Database Charset to use in creating database tables. */
    define('DB_CHARSET', 'utf8');

    /** The Database Collate type. Don't change this if in doubt. */
    define('DB_COLLATE', '');

    /**#@+
     * Authentication Unique Keys and Salts.
     *
     * Change these to different unique phrases!
     * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
     * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
     *
     * Pantheon sets these values for you also. If you want to shuffle them you
     * can do so via your dashboard.
     *
     * @since 2.6.0
     */
    define('AUTH_KEY',         $_ENV['AUTH_KEY']);
    define('SECURE_AUTH_KEY',  $_ENV['SECURE_AUTH_KEY']);
    define('LOGGED_IN_KEY',    $_ENV['LOGGED_IN_KEY']);
    define('NONCE_KEY',        $_ENV['NONCE_KEY']);
    define('AUTH_SALT',        $_ENV['AUTH_SALT']);
    define('SECURE_AUTH_SALT', $_ENV['SECURE_AUTH_SALT']);
    define('LOGGED_IN_SALT',   $_ENV['LOGGED_IN_SALT']);
    define('NONCE_SALT',       $_ENV['NONCE_SALT']);
    /**#@-*/


    /**
     * Required for the custom domain's added from the Pantheon dashboard in the 
     * domain section. We are also using CNAME's for all of our DNS records via Cloudflare. 
     *
     * We are not using any of Cloudflare's Page Rules to redirect to https. We are
     * standardizing on https and redirecting to https://www in the conditional below where
     * we defined WP_HOME and WP_SITEURL constants.
     * 
     * @link https://pantheon.io/docs/guides/cloudflare-enable-https/
     * @since 4.4
     */
    if (isset($_ENV['PANTHEON_ENVIRONMENT'])) {
      if ($_ENV['PANTHEON_ENVIRONMENT'] === 'dev'):
        $domain = 'dev.sportportactive.com';
      elseif ($_ENV['PANTHEON_ENVIRONMENT'] === 'test'):
        $domain = 'test.sportportactive.com';
      elseif ($_ENV['PANTHEON_ENVIRONMENT'] === 'live'):
        $domain = 'www.sportportactive.com';
      else:
        /**
         * Fallback value for multidev or other environments. This covers
         * environment-sitename.pantheonsite.io domains that are generated
         * per environment.
         */
        $domain = $_SERVER['HTTP_HOST'];

      endif;

      /**
       * Define constants for WordPress on Pantheon.
       */
      define('WP_HOME', 'https://' . $domain);
      define('WP_SITEURL', 'https://' . $domain);

      /**
       * Standardizing the live site on https://www with a 301 redirect in our headers
       *
       * @link https://pantheon.io/docs/redirects/
       * @since 4.4
       */
      if (isset($_SERVER['PANTHEON_ENVIRONMENT']) && ($_SERVER['HTTP_X_FORWARDED_PROTO'] != 'https' || $_SERVER['HTTP_HOST'] != $domain) && (php_sapi_name() != "cli")) {
        header('HTTP/1.0 301 Moved Permanently');
        header('Location: https://' . $domain . $_SERVER['REQUEST_URI']);
        header('Cache-Control: public, max-age=3600');
        exit();
      }

    }

    /**
     * Don't show deprecations; useful under PHP 5.5
     */
    error_reporting(E_ALL ^ E_DEPRECATED);

  else:
    /**
     * This block will be executed if you have NO wp-config-local.php and you
     * are NOT running on Pantheon. Insert alternate config here if necessary.
     *
     * If you are only running on Pantheon, you can ignore this block.
     */
    define('DB_NAME',          'database_name');
    define('DB_USER',          'database_username');
    define('DB_PASSWORD',      'database_password');
    define('DB_HOST',          'database_host');
    define('DB_CHARSET', 'utf8');
    define('DB_COLLATE',       '');
    define('AUTH_KEY',         'put your unique phrase here');
    define('SECURE_AUTH_KEY',  'put your unique phrase here');
    define('LOGGED_IN_KEY',    'put your unique phrase here');
    define('NONCE_KEY',        'put your unique phrase here');
    define('AUTH_SALT',        'put your unique phrase here');
    define('SECURE_AUTH_SALT', 'put your unique phrase here');
    define('LOGGED_IN_SALT',   'put your unique phrase here');
    define('NONCE_SALT',       'put your unique phrase here');
  endif;
endif;

/** Standard wp-config.php stuff from here on down. **/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';

/**
 * WordPress Localized Language, defaults to English.
 *
 * Change this to localize WordPress. A corresponding MO file for the chosen
 * language must be installed to wp-content/languages. For example, install
 * de_DE.mo to wp-content/languages and set WPLANG to 'de_DE' to enable German
 * language support.
 */


/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * You may want to examine $_ENV['PANTHEON_ENVIRONMENT'] to set this to be
 * "true" in dev, but false in test and live.
 */

/**
 * For developers: WordPress debugging mode.
 *
 * Debugging is enabled in Dev and Multi-Dev environments and
 * disabled on your Test and Live environments.
 *
 * Unless you have to disable WP_DEBUG on a Dev site you shouldn't have
 * to worry about setting WP_DEBUG to true or false depending on your 
 * environment anymore.
 *
 * @since 4.4
 */
if ( defined( 'PANTHEON_ENVIRONMENT' ) ) {
  if ( ! in_array( PANTHEON_ENVIRONMENT, array( 'test', 'live' ) ) ) {
    define( 'WP_DEBUG', true );
    define( 'WP_DEBUG_LOG', true );
    define( 'WP_DEBUG_DISPLAY', false );
  } else {
    define( 'WP_DEBUG', false );
    define( 'WP_DEBUG_LOG', false );
    define( 'WP_DEBUG_DISPLAY', false );
  }
}


/**
 * Removes Marketing Banner from Plugins Options Screen
 *
 * @package WP Retina @2x plugin
 */
define( 'WP_HIDE_DONATION_BUTTONS',  true );


/* That's all, stop editing! Happy Pressing. */




/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
