<?php
/**
 * Functions that are needed only in admin
 *
 * @package ZodiacPress Atlas
 */
if ( ! defined( 'ABSPATH' ) ) exit;
/**
 * Receive Heartbeat data and respond.
 *
 * Recieves our zpatlas_status request flag, and sends the atlas install status back to the front end.
 *
 * @param array $response Heartbeat response data to pass back to front end.
 * @param array $data Data received from the front end (unslashed).
 */
function zpatlas_receive_heartbeat( $response, $data ) {
	if ( empty( $data['zpatlas_status'] ) ) {
		return $response;
	}
	// If atlas install in complete, Show "Atlas is Ready" admin notice once
	if ( get_transient( 'zp_atlas_ready_once' ) ) {
		delete_transient( 'zp_atlas_ready_once' );
		$response['zpatlas_status_notice'] = __( 'The atlas installation is complete. It is ready for use.', 'zp-atlas' );
		$response['zpatlas_status_field'] = zp_string( 'active' );
		// send DB row count, size, and keys
		$response['zpatlas_status_db'] = array(
			'rows'	=> number_format( ZPAtlas_DB::row_count() ),
			'size'	=> ( $size = zpatlas_get_size() ) ? ( number_format( $size / 1048576, 1 ) . ' MB' ) : $size,
			'key'	=> ZPAtlas_DB::key_exists( 'PRIMARY' ) ? __( 'okay', 'zp-atlas' ) : __( 'missing', 'zp-atlas' ),
			'index'	=> ZPAtlas_DB::key_exists( 'ix_name_country' ) ? __( 'okay', 'zp-atlas' ) : __( 'missing', 'zp-atlas' ),
		);
	} else {
		$response['zpatlas_status_field'] = get_option( 'zp_atlas_db_pending' );
		$admin_notice = get_option( 'zp_atlas_db_notice' );
		// only send admin notice if it has changed
		if ( $admin_notice && get_option( 'zp_atlas_db_previous_notice' ) !== $admin_notice ) {
			$response['zpatlas_status_notice'] = $admin_notice;
			update_option( 'zp_atlas_db_previous_notice', $admin_notice );
		}
	}
	return $response;
}
add_filter( 'heartbeat_received', 'zpatlas_receive_heartbeat', 10, 2 );
/**
 * Get size of the zp_atlas database table including the size of its index.
 *
 * @return int $size in bytes
 */
function zpatlas_get_size() {
	static $size;
	if ( isset( $size ) ) {
		return $size;
	}
	global $zpdb;
	$size = 0;
    $results = $zpdb->get_results( 'SHOW TABLE STATUS', ARRAY_A );
	if ( $results ) {
		foreach ( $results as $table ) {
			if ( "{$zpdb->prefix}zp_atlas" != $table['Name'] ) {
				continue;
			}
		    $size += $table['Data_length'] + $table['Index_length'];
		}
	}
	return $size;
}
