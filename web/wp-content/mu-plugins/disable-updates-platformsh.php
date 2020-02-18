<?php
/**
 * Plugin Name: Disable Update on platform.sh
 * Plugin URI: https://thinktandem.io/
 * Description: Disable Updates on platform.sh
 * Version: 0.1
 * Author: John Ouellet
 * Author URI: https://thinktandem.io/
 *
 * Idea and code mostly tweaked from from https://wordpress.org/plugins/disable-wordpress-updates/
 **/

/**
 * Class DisableUpdatesPlatformsh.
 */
class DisableUpdatesPlatformsh {

	/**
	 * Constructor
	 */
	function __construct() {
		add_action( 'admin_init', array(&$this, 'admin_init') );

		// @see https://wordpress.org/support/topic/possible-performance-improvement/#post-8970451
		add_action('schedule_event', array($this, 'filter_cron_events'));

		add_action( 'pre_set_site_transient_update_plugins', array($this, 'last_checked_atm'), 21, 1 );
		add_action( 'pre_set_site_transient_update_themes', array($this, 'last_checked_atm'), 21, 1 );

		// Disable All Automatic Updates.
		add_filter( 'auto_update_translation', '__return_false' );
		add_filter( 'automatic_updater_disabled', '__return_true' );
		add_filter( 'allow_minor_auto_core_updates', '__return_false' );
		add_filter( 'allow_major_auto_core_updates', '__return_false' );
		add_filter( 'allow_dev_auto_core_updates', '__return_false' );
		add_filter( 'auto_update_core', '__return_false' );
		add_filter( 'wp_auto_update_core', '__return_false' );
		add_filter( 'auto_core_update_send_email', '__return_false' );
		add_filter( 'send_core_update_notification_email', '__return_false' );
		add_filter( 'auto_update_plugin', '__return_false' );
		add_filter( 'auto_update_theme', '__return_false' );
		add_filter( 'automatic_updates_send_debug_email', '__return_false' );
		add_filter( 'automatic_updates_is_vcs_checkout', '__return_true' );

		add_filter( 'automatic_updates_send_debug_email ', '__return_false', 1 );
		if( !defined( 'AUTOMATIC_UPDATER_DISABLED' ) ) define( 'AUTOMATIC_UPDATER_DISABLED', true );
		if( !defined( 'WP_AUTO_UPDATE_CORE') ) define( 'WP_AUTO_UPDATE_CORE', false );

		add_filter( 'pre_http_request', array($this, 'block_request'), 10, 3 );
	}

	/**
	 * Initialize and load the plugin stuff
	 */
	function admin_init() {
		if ( !function_exists("remove_action") ) return;

		// Remove 'update plugins' option from bulk operations select list.
		global $current_user;
		$current_user->allcaps['update_plugins'] = 0;

		// Hide maintenance and update nag.
		remove_action( 'admin_notices', 'update_nag', 3 );
		remove_action( 'network_admin_notices', 'update_nag', 3 );
		remove_action( 'admin_notices', 'maintenance_nag' );
		remove_action( 'network_admin_notices', 'maintenance_nag' );

		// Disable Theme Updates.
		remove_action( 'load-update-core.php', 'wp_update_themes' );
		wp_clear_scheduled_hook( 'wp_update_themes' );

		// Disable Plugin Updates.
		remove_action( 'load-update-core.php', 'wp_update_plugins' );
		wp_clear_scheduled_hook( 'wp_update_plugins' );

		//  Disable Core Updates.
		remove_action( 'wp_maybe_auto_update', 'wp_maybe_auto_update' );
		remove_action( 'admin_init', 'wp_maybe_auto_update' );
		remove_action( 'admin_init', 'wp_auto_update_core' );
		wp_clear_scheduled_hook( 'wp_maybe_auto_update' );
	}


	/**
	 * Check the outgoing request.
	 */
	public function block_request($pre, $args, $url) {
		/* Empty url */
		if( empty( $url ) ) {
			return $pre;
		}

		/* Invalid host */
		if( !$host = parse_url($url, PHP_URL_HOST) ) {
			return $pre;
		}

		$url_data = parse_url( $url );

		/* block request */
		if( false !== stripos( $host, 'api.wordpress.org' ) && (false !== stripos( $url_data['path'], 'update-check' ) || false !== stripos( $url_data['path'], 'browse-happy' ) || false !== stripos( $url_data['path'], 'serve-happy' )) ) {
			return true;
		}

		return $pre;
	}

	/**
	 * Filter cron events.
	 */
	public function filter_cron_events($event) {
		switch( $event->hook ) {
			case 'wp_version_check':
			case 'wp_update_plugins':
			case 'wp_update_themes':
			case 'wp_maybe_auto_update':
				$event = false;
				break;
		}
		return $event;
	}

	/**
	 * Override version check info.
	 */
	public function last_checked_atm( $t ) {
		include( ABSPATH . WPINC . '/version.php' );

		$current = new stdClass;
		$current->updates = array();
		$current->version_checked = $wp_version;
		$current->last_checked = time();

		return $current;
	}
}

// This initializes everything if on platform.sh
if (class_exists('DisableUpdatesPlatformsh') && !empty($_ENV['PLATFORM_RELATIONSHIPS'])) {
	$DisableUpdatesPlatformsh = new DisableUpdatesPlatformsh();
}
?>
