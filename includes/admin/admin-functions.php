<?php
/**
 * Functions that are needed only in admin
 *
 * @package ZodiacPress Atlas
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Get size of the zp_atlas database table including the size of its index.
 *
 * @return int $size in bytes
 */
function zp_atlas_get_size() {
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
