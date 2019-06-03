<?php
/**
 * Atlas Functions
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sets the `$zpdb` global which is an abstraction for $wpdb.
 *
 * Allows atlas to reside in separate database rather than in the WordPress database.
 *
 * @global wpdb $wpdb The WordPress database class.
 * @global zpdb $zpdb The ZP_Atlas database abstraction.
 */
function zpatlas_abstract_db() {
	global $wpdb, $zpdb;
	if ( isset( $zpdb ) ) {
		return;
	}

	/*
	 * Filters $wpdb to allow atlas to reside in a separate database
	 */
	$zpdb = apply_filters( 'zp_atlas_db', $wpdb );

}
add_action( 'plugins_loaded', 'zpatlas_abstract_db' );

/**
 * Gets the atlas option which denotes whether to use the atlas db or GeoNames.org
 */
function zpatlas_option() {
	static $option;
	if ( isset( $option ) ) {
		return $option;
	}
	$settings = get_option( 'zodiacpress_settings' );
	$option = ( isset( $settings['atlas'] ) ? $settings['atlas'] : false );
	return $option;
}

/**
 * Handles ajax request to Run Atlas Installer
 *
 * Creates the database table and begins background process of importing cities.
 */
function zpatlas_ajax_install() {
	check_ajax_referer( 'zp_atlas_install' );
	if ('zp-atlas-try-again' === $_POST['id']) {
		// remove the previous Error admin notice or else it continues to pop up on heartbeat ticks
		delete_option('zp_atlas_db_notice');
	}
	/**
	 * Temporary flag to remove the Atlas Installer button during background installation.
	 */
	update_option( 'zp_atlas_db_installing', true );

	update_option( 'zp_atlas_db_previous_notice', zp_string( 'installing_notice' ) );

	/**
	 * Update the atlas option since the intent is to use the db instead of GeoNames
	 */
	$options = get_option( 'zodiacpress_settings' );
	$options['atlas'] = 'db';
	update_option( 'zodiacpress_settings', $options );
	zpatlas_create_table();
	// Trigger the first async task: download the allCountries.zip file
	do_action('zp_atlas_download');
	wp_die();
}
add_action( 'wp_ajax_zp_atlas_install', 'zpatlas_ajax_install' );

/**
 * Creates the database table.
 * 
 * Is called only when Atlas Installer runs.
 */
function zpatlas_create_table() {
	global $wpdb;
	$collate = $wpdb->get_charset_collate();
	$sql = "CREATE TABLE IF NOT EXISTS " . $wpdb->prefix . "zp_atlas (
		geonameid bigint(20) unsigned NOT NULL,
		name varchar(200) NOT NULL,
		latitude decimal(10,5) NOT NULL,
		longitude decimal(10,5) NOT NULL,
		country varchar(200) NOT NULL,
		admin1 text NOT NULL,
		timezone varchar(40) NOT NULL,
		mod_date date NOT NULL
		) $collate;";
	@$wpdb->query($sql);
}
/**
 * Create both the PRIMARY KEY and the index on the zp_atlas table
 *
 * @return bool|int Returns true if both PRIMARY KEY and INDEX were created, otherwise returns an error code: 1 if only PRIMARY KEY was created, 2 if neither PRIMERY KEY nor INDEX were created.
 *
 */
function zpatlas_table_create_keys() {
	global $wpdb;
	$return = 2;
	$sql_1 = "ALTER TABLE " . $wpdb->prefix . "zp_atlas MODIFY COLUMN geonameid bigint(20) UNSIGNED NOT NULL PRIMARY KEY";
	$sql_2 = "CREATE INDEX ix_name_country ON " . $wpdb->prefix . "zp_atlas (name(50),country(50) DESC)";

	// create PRIMARY KEY

	if ( ! ZPAtlas_DB::key_exists( 'PRIMARY' ) ) {
		
		if ( $wpdb->query( $sql_1 ) === true ) {

			$return = 1;

			// create the INDEX on name,country

			if ( ! ZPAtlas_DB::key_exists( 'ix_name_country' ) ) {

				if ( $wpdb->query( $sql_2 ) === true ) {

					// BOTH KEYS WERE SUCCESSFULLY CREATED
					return true;
				}

			} else {
				$return = true;// INDEX already exists, so now both keys are okay
			}

		}

	} else {
		
		// PRIMARY KEY already exists
		$return = 1;

		/****************************************************
		*
		* BEGIN check if other key exists
		*
		****************************************************/
		if ( ! ZPAtlas_DB::key_exists( 'ix_name_country' ) ) {

			if ( $wpdb->query( $sql_2 ) === true ) {

				// BOTH KEYS OKAY

				$return = true;

			}
		} else {
			$return = true;// INDEX already exists, so both keys exist
		}		
		
		
		/****************************************************
		*
		* END
		*
		****************************************************/
	}

    return $return;
}

/**
 * Attempts to insert cities into the WP database table.
 *
 * This function doesn't check if the cities.txt data file exists, 
 * since it only runs after the data file is downloaded.
 *
 * @return bool True if data was successfully inserted, otherwise returns false.
 */
function zpatlas_load_data_infile() {
	global $wpdb;
    $file = get_temp_dir() . 'cities.txt';
	$sql = "LOAD DATA LOCAL INFILE '$file' IGNORE INTO TABLE " . $wpdb->prefix . "zp_atlas FIELDS TERMINATED BY '\t' LINES TERMINATED BY '" . PHP_EOL . "'(geonameid, name, latitude, longitude, country, admin1, timezone, mod_date);";
	$dbUser = DB_USER;
	$dbHost = DB_HOST;
	$dbPass = DB_PASSWORD;
	$dbName = DB_NAME;
	system("mysql -u $dbUser -h $dbHost --password=$dbPass --local_infile=1 -e \"$sql\" $dbName");
	return ( ZPAtlas_DB::row_count() > 3000000 );
}
