 <?php
if ( ! defined( 'ABSPATH' ) ) exit;
/**
 * Instantiates the async tasks so they will run when their actions are fired.
 */
function zp_atlas_async_tasks() {
	new ZP_Atlas_Import();
	new ZP_Atlas_Insert_DB();
}
add_action( 'plugins_loaded', 'zp_atlas_async_tasks' );

/**
 * Task 1: Hooks onto the zp_atlas_import async action to download the cities.txt datafile
 *
 * Imports the cities.txt data file.
 * Copies the file from download server to local temp folder.
 * This will not run if the Atlas db is already in use.
 */
add_action( 'wp_async_zp_atlas_import', function () {
	$out = false;
	$error = '';
	$datafile = 'cities.txt';
	$size = 275665461;// Current filesize of cities.txt @todo update


	// @todo must get file from local SINCE WILL NO LONGER BE HOSTED ON GOOGLE!!!

	$url = 'https://storage.googleapis.com/zodiacpress_cities/' . $datafile;
	$temp_dir = get_temp_dir();

	if ( ! $temp_dir ) {

		$error = __( 'Cannot find a writable temp directory.', 'zodiacpress' );

	} else {

		// Don't download if the file already exists
			
		if ( file_exists( $temp_dir . $datafile ) && filesize( $temp_dir . $datafile ) === $size ) {
			$out = true;
		} else {

			// File does not exist, or else it is of incomplete size, so download it.

			if ( ! copy( $url, $temp_dir . $datafile ) ) {
				
				$error = __( 'Cities data file could not be downloaded.', 'zodiacpress' );

			} else {

				$out = true;

			}

		} // end file_exists


	} // end temp dir


	if ( $out ) {

		// cities.txt datafile is ready

		$status = zp_string( 'inserting' );

		// Trigger the next async task: insert cities data into database table

		do_action( 'zp_atlas_insert_db' );

	} else {

		$status = $error;

		update_option( 'zp_atlas_db_notice', $error );

	}

	/**
	 * Save message to show on Atlas status field
	 */
	update_option( 'zp_atlas_db_pending', $status );

} );

/**
 * Task 2: Hooks onto the zp_atlas_insert_db async action to insert cities data into database
 *
 * Loads all cities data into the database table and adds the key and index.
 */
add_action( 'wp_async_zp_atlas_insert_db', function () {
	$out = false;
	$error = '';

	if ( ! ZP_Atlas_DB::table_exists() ) {
		$error = __( 'ERROR: zp_atlas table does not exist', 'zodiacpress' );
		update_option( 'zp_atlas_db_pending', $error );
		update_option( 'zp_atlas_db_notice', $error );// admin notice
		return $out;
	}

	if ( ZP_Atlas_DB::row_count() > 3000000 ) {
		
		// Cities data had already been inserted.
 
		// Make sure key and index were already created.
		$index = zp_atlas_table_create_keys();
		if ( true === $index ) {
			$out = true;
		} else {
			$error = sprintf( '%s %s.', zp_string( 'failed_keys' ), $index );
		}		
		

	} else {

		$insert = zp_atlas_load_data_infile();

		if ( true === $insert ) {
			
			update_option( 'zp_atlas_db_pending', zp_string( 'creating' ) );
			
			// create primary key and index
			$index = zp_atlas_table_create_keys();

			if ( true === $index ) {

				$out = true;

			} else {

				$error = sprintf( '%s %s.', zp_string( 'failed_keys' ), $index );

			}
		} else {
			// data was not loaded in to database
			$error = __( 'Failed to insert cities data into database table.', 'zodiacpress' );
		}
	}


	if ( $out ) {

		// The installation of cities data is complete, so clean up the pending stuff

		delete_option( 'zp_atlas_db_installing' );
		delete_option( 'zp_atlas_db_notice' );
		delete_option( 'zp_atlas_db_pending' );
		delete_option( 'zp_atlas_db_previous_notice' );
		
		// Set the db_version option to which serves as a flag that the database is ready

		update_option( 'zp_atlas_db_version', '1.8.1' );// @todo update ONLY upon changing database

		// Set transient flag to enable the Ready admin notice to appear

		set_transient( 'zp_atlas_ready_once', true, 60 );

	} else {

		// Installation failed so save error message

		update_option( 'zp_atlas_db_pending', $error );// status field
		update_option( 'zp_atlas_db_notice', $error );// admin notice 

	}

} );
