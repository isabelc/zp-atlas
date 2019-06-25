<?php
if ( ! defined( 'ABSPATH' ) ) exit;
/**
 * Instantiates the async tasks so they will run when their actions are fired.
 */
function zpatlas_async_tasks() {
	new ZPAtlas_Download();
	new ZPAtlas_Unzip();
	new ZPAtlas_Pluck();
	new ZPAtlas_Mapcodes();
	new ZPAtlas_Insert_DB();
}
add_action( 'plugins_loaded', 'zpatlas_async_tasks' );

/**
 * Get size of a file on GeoNames download server
 * @param string $filename Just the name of the file
 */
function zpa_gn_filesize($filename) {
	$head = array_change_key_case(get_headers("https://download.geonames.org/export/dump/$filename", TRUE));
	return $head['content-length'];
}

/**
 * Task 1: Hooks onto the zp_atlas_download async action
 *
 * Download the allCountries.zip data file from GeoNames to local temp folder.
 * This will not run if the Atlas db is already in use.
 */
add_action( 'wp_async_zp_atlas_download', function () {
	delete_option('zp_atlas_db_try_again');
	$out = false;
	$error = '';
	$filename = 'allCountries.zip';
	$url = 'https://download.geonames.org/export/dump/'.$filename;
	$temp_dir = get_temp_dir();

	if ( ! $temp_dir ) {

		$error = __( 'Cannot find a writable temp directory.', 'zp-atlas' );

	} else {

		// Don't download if the file already exists
			
		if ( file_exists( $temp_dir . $filename ) && filesize( $temp_dir . $filename ) >= zpa_gn_filesize($filename) ) {
			$out = true;
		} else {

			// File does not exist, or else it is of incomplete size, so download it.

			if ( ! copy( $url, $temp_dir . $filename ) ) {
				
				$error = __( 'allCountries.zip file could not be downloaded.', 'zp-atlas' );

			} else {

				$out = true;

			}

		} // end file_exists


	} // end temp dir


	if ( $out ) {
		$status = zpa_string( 'unzip' );
		// Trigger the next async task: unzip allCountries.zip
		do_action('zp_atlas_unzip', $temp_dir);

	} else {

		$status = $error;
		update_option( 'zp_atlas_db_notice', $error );
		update_option('zp_atlas_db_try_again', true);

	}

	/**
	 * Save message to show on Atlas status field
	 */
	update_option( 'zp_atlas_db_pending', $status );

} );


/**
 * Task 2: Unzip the allCountries.zip file
 */
add_action( 'wp_async_zp_atlas_unzip', function () {
	delete_option('zp_atlas_db_try_again');
	
	$out = false;
	$error = '';

	$zip_file = $_POST['dir'] . 'allCountries.zip';
	$text_file = $_POST['dir'] . 'allCountries.txt';

	if (file_exists($text_file)) {
		$out = true;
	} else {
		$des = $_POST['dir'];
		ob_start();
		system("unzip -d $des $zip_file",$ret);
		$result = ob_get_clean();
		if (0 === $ret || 1 === $ret) {
			$out = true;
		} else {
			$unzip_errors = array(
				// 1 => 'one or more warning errors were encountered. Some files may have been skipped.',
				2 => 'a generic error in the zipfile format was detected.',
				3 => 'a severe error in the zipfile format was detected.',
				4 => 'unzip was unable to allocate memory for one or more buffers during program initialization.',
				5 => 'unzip was unable to allocate memory or unable to obtain a tty to read the decryption password(s).',
				6 => 'unzip was unable to allocate memory during decompression to disk.',
				7 => 'unzip was unable to allocate memory during in-memory decompression.',
				8 => '[currently not used]',
				9 => 'the specified zipfiles were not found.',
				10 => 'invalid options were specified on the command line.',
				11 => 'no matching files were found.',
				50 => 'the disk is (or was) full during extraction.',
				51 => 'the end of the ZIP archive was encountered prematurely.',
				80 => 'the user aborted unzip prematurely with control-C (or similar)',
				81 => 'testing or extraction of one or more files failed due to unsupported compression methods or unsupported decryption.',
				82 => 'no files were found due to bad decryption password(s).'
			);
			
			$error = "unzip failed because: $unzip_errors[$ret]";
		}
	}

	if ( $out ) {
		$status = 'Plucking cities from datafile...';

		// Trigger the next async task: pluck unique cities from allCountries.txt
		do_action('zp_atlas_pluck', $_POST['dir']);
	} else {
		$status = $error;
		update_option( 'zp_atlas_db_notice', $error );
		update_option('zp_atlas_db_try_again', true);
	}
	/**
	 * Save message to show on Atlas status field
	 */
	update_option( 'zp_atlas_db_pending', $status );	

});


/**
 * Task 3: pluck unique cities from allCountries.txt
 * 
 * Steps include:
 * 
 * 1. Extract only cities (& towns, villages...) from GeoNames datafile allCountries.txt,
 * 2. and parse them to pluck only unique cities by city|admin1|country,
 * 3. and place these in a new file, /tmp/cities_tmp.txt
 *
 * Only the needed fields will be extracted:
 *
 * [0]	geonameid
 * [1]	name
 * [4]	latitude
 * [5]	longitude
 * [8]	country_code
 * [10]	admin1_code
 * [17]	timezone
 * [18]	modification_date
 *
 *
 * The new fields will be:
 *
 * [0]	geonameid
 * [1]	name
 * [2]	latitude
 * [3]	longitude 
 * [4]	country_code
 * [5]	admin1_code
 * [6]	timezone
 * [7]	modification_date
 *
 */
add_action( 'wp_async_zp_atlas_pluck', function () {
	delete_option('zp_atlas_db_try_again');
	$out = false;
	$error = '';
	$cities_tmp_file = $_POST['dir'] . 'cities_tmp.txt';
	$countries_file = $_POST['dir'] . 'allCountries.txt';

	if (file_exists($cities_tmp_file)) {
		$out = true;
	} else {

		$tmp = array();
		$handle = @fopen($countries_file,'r');

		if (false === $handle) {
			$error = "missing file ($countries_file)";
		} else {
			set_time_limit(360);
			$default = ini_get('memory_limit');
			ini_set('memory_limit', '-1');

			while(($v = fgets($handle, 4096))!==false) {

				$v = str_replace('"',' ',$v);
				$e = explode("\t", $v);

				// validate that mandatory fields are not empty
				if(!empty($e[0]) && !empty($e[1]) && !empty($e[17]) && isset($e[18]) && !empty($e[6]) && !empty($e[7])) {

					// Limit to only cities, towns, villages...(feature class P)
					$feature_codes = array('PPL','PPLA','PPLA2','PPLA3','PPLA4','PPLC');
					if('P' == $e[6] && in_array($e[7],$feature_codes)) {

						/* Remove unwanted fields. Keep only these:
						 * [0]	geonameid
						 * [1]	name
						 * [4]	latitude
	 					 * [5]	longitude
						 * [8]	country_code
						 * [10]	admin1_code
						 * [17]	timezone
						 * [18]	modification_date
						 */
						unset($e[16],$e[15],$e[14],$e[13],$e[12],$e[11],$e[9],$e[7],$e[6],$e[3],$e[2]);

						/****************************************************
						*
						* Only keep rows with a unique combination of these 3 for a city:
						*
						*	name, admin1_code, country_code
						*
						* This will be a unique index (key) in the database.
						* 
						****************************************************/
						// unique city name|admin1_code|country_code
						$id = $e[1] . "|" . $e[10] . "|" . $e[8];
						isset($tmp[$id]) or $tmp[$id] = implode("\t", $e);
					}
				}
			}
			fclose($handle);
			$unique_array = array_values($tmp);
			$s = implode(PHP_EOL, $unique_array);
			file_put_contents($cities_tmp_file, $s);
			$out = true;
			set_time_limit(30);// restore to default
			ini_set('memory_limit',$default);
		}
	}

	if ( $out ) {
		$status = 'Mapping country codes to names...';

		// Trigger the next async task:
		do_action('zp_atlas_mapcodes', $_POST['dir']);

	} else {
		$status = $error;
		
		update_option( 'zp_atlas_db_notice', $error );
		update_option('zp_atlas_db_try_again', true);
	}
	/**
	 * Save message to show on Atlas status field
	 */
	update_option( 'zp_atlas_db_pending', $status );

});



/**
 * Task 4: replace country codes and admin1 codes to the real name.
 * Creates the final cities.txt.
 *
 * Codes are from files from the GeoNames download server: https://download.geonames.org/export/dump/
 * 
 * Admin1 codes are from 'admin1CodesASCII.txt'
 * Country codes are from 'countryInfo.txt'
 *
 */
add_action( 'wp_async_zp_atlas_mapcodes', function () {
	delete_option('zp_atlas_db_try_again');
	
	$out = false;
	$error = '';

	$cities_tmp = $_POST['dir'] . 'cities_tmp.txt';

	$cities_file = $_POST['dir'] . 'cities.txt';
	if (file_exists($cities_file)) {
		$out = true;
	} else {
		set_time_limit(360);
		$default = ini_get('memory_limit');
		ini_set('memory_limit', '-1');

		// prep codes
		$files = array('countryInfo.txt','admin1CodesASCII.txt');
		foreach($files as $k => $f) {
			$file = 'https://download.geonames.org/export/dump/'.$f;
			$y = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
			$x = array();
			foreach($y as $line) {
				$e = explode("\t", $line);
				$x[] = $e;
			}
			unset($y);
		    if ('countryInfo.txt' == $f) {
			    $keys = array_column($x, 0);// ISO code
			    $values = array_column($x, 4);//country name
		    } else {
		        $keys = array_column($x, 0);//admin1 code
		        $values = array_column($x, 2);//admin1 name ascii
		    }
		    unset($x);
		    global ${"codes".$k};
		    ${"codes".$k} = array_combine($keys, $values);
		}

		$cities = file($cities_tmp, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

		if (false === $cities) {
			$error = "missing file ($cities_tmp)";
		} else {
			$data = array();
			foreach($cities as $c) {
				$e = explode("\t", $c);
			    $e[5] = isset($codes1["$e[4].$e[5]"]) ? trim($codes1["$e[4].$e[5]"]) : '';// replace admin1 code with name
			    $e[4] = isset($codes0["$e[4]"]) ? trim($codes0["$e[4]"]) : '';// replace country code with country name
			    $data[] = implode("\t", $e);
			}
			$s = implode(PHP_EOL, $data);
			file_put_contents($cities_file, $s);

			$out = true;
		}

		set_time_limit(30);// restore to default
		ini_set('memory_limit',$default);		
	}

	if ( $out ) {

		// cities.txt datafile is ready

		$status = zpa_string( 'inserting' );

		// Trigger the next async task: insert cities data into database table

		do_action('zp_atlas_insert_db', $_POST['dir']);

	} else {
		$status = $error;
		
		update_option( 'zp_atlas_db_notice', $error );
		update_option('zp_atlas_db_try_again', true);
	}
	/**
	 * Save message to show on Atlas status field
	 */
	update_option( 'zp_atlas_db_pending', $status );	
});

/**
 * Task 5: Hooks onto the zp_atlas_insert_db async action to insert cities data into database
 *
 * Loads all cities data into the database table and adds the key and index.
 */
add_action( 'wp_async_zp_atlas_insert_db', function () {
	delete_option('zp_atlas_db_try_again');

	$out = false;
	$error = '';

	if ( ! ZPAtlas_DB::table_exists() ) {
		$error = __( 'ERROR: zp_atlas table does not exist', 'zp-atlas' );
		update_option( 'zp_atlas_db_pending', $error );
		update_option( 'zp_atlas_db_notice', $error );// admin notice
		return $out;
	}

	if ( ZPAtlas_DB::row_count() > 3000000 ) {
		
		// Cities data had already been inserted.
 
		// Make sure key and index were already created.
		$index = zpatlas_table_create_keys();
		if ( true === $index ) {
			$out = true;
		} else {
			$error = sprintf( '%s %s.', zpa_string( 'failed_keys' ), $index );
		}		
		

	} else {

		$insert = zpatlas_load_data_infile();

		if ( true === $insert ) {
			
			update_option( 'zp_atlas_db_pending', zpa_string( 'creating' ) );
			
			// create primary key and index
			$index = zpatlas_table_create_keys();

			if ( true === $index ) {

				$out = true;

			} else {

				$error = sprintf( '%s %s.', zpa_string( 'failed_keys' ), $index );

			}
		} else {
			// data was not loaded in to database
			$error = __( 'Failed to insert cities data into database table.', 'zp-atlas' );
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
		update_option('zp_atlas_db_try_again', true);
	}

} );
