<?php

/**
 * Plugin Name: CARES User Activity Extension
 * Author:      David Cavins
 * License:     GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Description: Adds custom actions to the WP User Activity plugin.
 * Version:     1.0.0
 * Text Domain: cares-user-activity-extension
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Initialize WP User Activity
 *
 * @since 1.0.0
 */
function init_cares_user_activity() {

	// Include the files
	$dir = plugin_dir_path( __FILE__ );

	// Include the files
	require_once $dir . 'includes/maps-reports-api.php';

	// Actions
	require_once $dir . 'includes/actions/class-action-maps-reports.php';
}
add_action( 'plugins_loaded', 'init_cares_user_activity', 11 );

/**
 * Add our custom classes to the class loader in the parent plugin.
 *
 * @since 1.0.0
 *
 * @return array
 */
function cares_user_activity_add_custom_types( $types = array() ) {
	$types[] = 'WP_User_Activity_Type_Maps_Reports';
	return $types;
}
add_filter( 'wp_get_default_user_activity_types', 'cares_user_activity_add_custom_types' );

/**
 * Return the plugin URL
 *
 * @since 1.0.0
 *
 * @return string
 */
function cares_user_activity_get_plugin_url() {
	return plugin_dir_url( __FILE__ );
}
