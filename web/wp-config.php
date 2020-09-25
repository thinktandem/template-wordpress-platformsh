<?php

use Platformsh\ConfigReader\Config;

require __DIR__.'/../vendor/autoload.php';

// Create a new config object to ease reading the Platform.sh environment variables.
// You can alternatively use getenv() yourself.
$config = new Config();

if (!$config->isValidPlatform() & !isset($_SERVER['LANDO'])) {
    die("Not in a Platform.sh Environment.");
}

// Set default scheme and hostname.
$site_scheme = 'http';
$site_host = isset($_SERVER['LANDO']) && php_sapi_name() !== 'cli' ? $_SERVER['HTTP_X_FORWARDED_HOST'] : 'localhost';

// Update scheme and hostname for the requested page.
if (isset($_SERVER['HTTP_HOST'])) {
  $site_host = $_SERVER['HTTP_HOST'];
  $site_scheme = !empty($_SERVER['HTTPS']) ? 'https' : 'http';
}

if ($config->hasRelationship('database')) {
  // This is where we get the relationships of our application dynamically
  // from Platform.sh.

    // Avoid PHP notices on CLI requests.
    if (php_sapi_name() === 'cli') {
      session_save_path("/tmp");
    }

    // Get the database credentials
    $credentials = $config->credentials('database');

    // We are using the first relationship called "database" found in your
    // relationships. Note that you can call this relationship as you wish
    // in your `.platform.app.yaml` file, but 'database' is a good name.
    define( 'DB_NAME', $credentials['path']);
    define( 'DB_USER', $credentials['username']);
    define( 'DB_PASSWORD', $credentials['password']);
    define( 'DB_HOST', $credentials['host']);
    define( 'DB_CHARSET', 'utf8' );
    define( 'DB_COLLATE', '' );

    // Check whether a route is defined for this application in the Platform.sh
    // routes. Use it as the site hostname if so (it is not ideal to trust HTTP_HOST).
    if ($config->routes()) {

      $routes = $config->routes();

      foreach ($routes as $url => $route) {
        if ($route['type'] === 'upstream' && $route['upstream'] === $config->applicationName) {

          // Pick the first hostname, or the first HTTPS hostname if one exists.
          $host = parse_url($url, PHP_URL_HOST);
          $scheme = parse_url($url, PHP_URL_SCHEME);
          if ($host !== false && (!isset($site_host) || ($site_scheme === 'http' && $scheme === 'https'))) {
            $site_host = $host;
            $site_scheme = $scheme ?: 'http';
          }
        }
      }
    }

    // Debug mode should be disabled on Platform.sh. Set this constant to true
    // in a wp-config-local.php file to skip this setting on local development.
    if (!defined( 'WP_DEBUG' )) {
      define( 'WP_DEBUG', false );
    }

    // Set all of the necessary keys to unique values, based on the Platform.sh
    // entropy value.
    if ($config->projectEntropy) {
      $keys = [
        'AUTH_KEY',
        'SECURE_AUTH_KEY',
        'LOGGED_IN_KEY',
        'NONCE_KEY',
        'AUTH_SALT',
        'SECURE_AUTH_SALT',
        'LOGGED_IN_SALT',
        'NONCE_SALT',
      ];
      $entropy = $config->projectEntropy;
      foreach ($keys as $key) {
        if (!defined($key)) {
          define( $key, $entropy . $key );
        }
      }
    }
}
else {
  // Local configuration file should be in project root.
  if (file_exists(__DIR__ . '/wp-config-lando.php')) {
    include(__DIR__ . '/wp-config-lando.php');
  }
  if (file_exists(__DIR__ . '/wp-config-local.php')) {
    include(__DIR__ . '/wp-config-local.php');
  }
}

// Do not put a slash "/" at the end.
// https://codex.wordpress.org/Editing_wp-config.php#WP_HOME
define( 'WP_HOME', $site_scheme . '://' . $site_host );
// Do not put a slash "/" at the end.
// https://codex.wordpress.org/Editing_wp-config.php#WP_SITEURL
define( 'WP_SITEURL', WP_HOME );
define( 'WP_CONTENT_DIR', dirname(__DIR__) . '/web/wp-content' );
define( 'WP_CONTENT_URL', WP_HOME . '/wp-content' );

// Since you can have multiple installations in one database, you need a unique
// prefix.
$table_prefix  = 'wp_';

// Set cookie domain due to caching issues.
define('COOKIE_DOMAIN', $site_host);
define('DOMAIN_CURRENT_SITE', $site_host);
define('COOKIEPATH',  $site_host . '/');
define('SITECOOKIEPATH', $site_host . '/');

// Default PHP settings.
ini_set('session.gc_probability', 1);
ini_set('session.gc_divisor', 100);
ini_set('session.gc_maxlifetime', 200000);
ini_set('session.cookie_lifetime', 2000000);
ini_set('pcre.backtrack_limit', 200000);
ini_set('pcre.recursion_limit', 200000);

if ( !defined( 'FS_METHOD') ) {
  define( 'FS_METHOD', 'direct' );
}

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
  define( 'ABSPATH', dirname( __DIR__ ) . '/' );
}

if (!empty($_ENV['PLATFORM_RELATIONSHIPS']) && extension_loaded('redis')) {
  $relationships = json_decode(base64_decode($_ENV['PLATFORM_RELATIONSHIPS']), true);

  $relationship_name = 'redis';

  if (!empty($relationships[$relationship_name][0])) {
    $redis = $relationships[$relationship_name][0];
    define('WP_REDIS_CLIENT', 'pecl');
    define('WP_REDIS_HOST', $redis['host']);
    define('WP_REDIS_PORT', $redis['port']);
  }
}

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
