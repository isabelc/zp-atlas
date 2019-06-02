<?php 
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * ZodiacPress ZP_Atlas_DB.
 *
 * Interacts with the atlas database.
 *
 * @package  ZodiacPress
 * @since 1.8
 */
class ZP_Atlas_DB {

	private static $table_exists;
	private static $row_count;
	private static $keys;
	private static $installed;
	private static $use;
	private static $separate_db;

	/**
	 * Check if the zp_atlas table exists in the database.
	 *
	 * @return bool True if the table exists in the database, otherwise false.
	 */
	public static function table_exists() {
		if ( isset( self::$table_exists ) ) {
			return self::$table_exists;
		}		
		global $zpdb;
		$result = $zpdb->query( "SHOW TABLES LIKE '" . $zpdb->prefix . "zp_atlas'" );
		self::$table_exists = ( 1 === $result );
		return self::$table_exists;
	}

	/**
	 * Count rows in the zp_atlas table.
	 * 
	 * @return int
	 */
	public static function row_count() {
		if ( isset( self::$row_count ) ) {
			return self::$row_count;
		}
		global $zpdb;
		self::$row_count = 0;
		$results = $zpdb->get_results( 'SHOW TABLE STATUS', ARRAY_A );
		if ( $results ) {
			foreach ( $results as $table ) {
				if ( "{$zpdb->prefix}zp_atlas" != $table['Name'] ) {
					continue;
				}
				self::$row_count = (int) $table['Rows'];
			}
		}
		return self::$row_count;
	}

	/**
	 * Check if a specific INDEX or KEY exists in the zp_atlas table
	 * @param string $key The key name
	 * @return bool
	 */
	public static function key_exists( $key ) {
		if ( ! isset( self::$keys ) ) {
			self::$keys = array();
			global $zpdb;
			$result = $zpdb->get_results( 'SHOW INDEX FROM ' . $zpdb->prefix . 'zp_atlas' );
			if ( $result ) {
				foreach ( $result as $index ) {
					self::$keys[ $index->Key_name ] = 1;
				}
			}

		}
		return isset( self::$keys[ $key ] );
	}

	/**
	 * Checks if the default Atlas in $wpdb is done being installed.
	 *
	 * @return bool
	 */
	public static function is_installed() {
		if ( isset( self::$installed ) ) {
			return self::$installed;
		}
		self::$installed = (bool) get_option( 'zp_atlas_db_version' );
		return self::$installed;
	}

	/**
	 * Checks whether to use the Atlas db live instead of GeoNames, regardless of whether its in $wbdp or a separate database.
	 *
	 * @return bool
	 */
	public static function use_db() {
		if ( isset( self::$use ) ) {
			return self::$use;
		}
		if ( 'db' === zp_atlas_option() ) {
			self::$use = ( ZP_Atlas_DB::is_installed() || ZP_Atlas_DB::is_separate_db() );
			return self::$use;
		}
		self::$use = false;
		return false;
	}

	/**
	 * Checks whether the atlas is in a separate database rather than in $wpdb.
	 * @return bool	 
	 */
	public static function is_separate_db() {
		if ( isset( self::$separate_db ) ) {
			return self::$separate_db;
		}		
		self::$separate_db = apply_filters( 'zp_atlas_separate_db', false );
		return self::$separate_db;
	}

}
