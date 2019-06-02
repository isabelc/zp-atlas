<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package     ZodiacPress
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

if ( is_multisite() ) {
	global $wpdb;
	$blogs = $wpdb->get_results( "SELECT blog_id FROM {$wpdb->blogs}", ARRAY_A );
	if ( $blogs ) {
		foreach ( $blogs as $blog ) {
			switch_to_blog( $blog['blog_id'] );
			zp_uninstall();
			restore_current_blog();
		}
	}
}
else {
	zp_uninstall();
}
/**
 * Uninstall function.
 * @return void
 */
function zp_uninstall() {
	$options = get_option( 'zodiacpress_settings' );
	// Make sure that the user wants to remove all the data.
	if ( isset( $options['remove_data'] ) && '1' == $options['remove_data'] ) {
		global $wpdb;// only delete atlas table from wpdb, not a custom atlas database ($zpdb)
		$keys = array(
			'zp_atlas_db_installing',
			'zp_atlas_db_notice',
			'zp_atlas_db_pending',
			'zp_atlas_db_previous_notice',
			'zp_atlas_db_version'
		);
		foreach ( $keys as $key ) {
			delete_option( $key );
		}
		// Delete the zp_atlas database table
		$wpdb->query( "DROP TABLE IF EXISTS " . $wpdb->prefix . "zp_atlas" );
	}
}