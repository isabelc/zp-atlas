<?php
/*
Plugin Name: ZodiacPress Atlas
Plugin URI: https://isabelcastillo.com/free-plugins/zpatlas
Description: Your own atlas database for ZodiacPress instead of using GeoNames.org
Version: 1.1.alpha-1
Author: Isabel Castillo
Author URI: https://isabelcastillo.com
License: GNU GPLv2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: zp-atlas
Domain Path: /languages

Copyright 2019 Isabel Castillo

This file is part of ZodiacPress Atlas.

ZodiacPress Atlas is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.

ZodiacPress Atlas is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with ZodiacPress Atlas. If not, see <http://www.gnu.org/licenses/>.
*/
if ( ! defined( 'ABSPATH' ) ) exit;
if ( ! defined( 'ZPATLAS_VERSION' ) ) {
	define( 'ZPATLAS_VERSION', '1.1' );// @todo update
}
if ( ! defined( 'ZPATLAS_URL' ) ) {
	define( 'ZPATLAS_URL', plugin_dir_url( __FILE__ ) );
}
if ( ! defined( 'ZPATLAS_PATH' ) ) {
	define( 'ZPATLAS_PATH', plugin_dir_path( __FILE__ ) );
}
include_once(ZPATLAS_PATH . 'includes/updater.php');
$updater = new ZPAtlas_Updater(__FILE__ , 'isabelc', 'zp-atlas');
add_action('plugins_loaded', function() {
	include_once ZPATLAS_PATH . 'includes/settings.php';
}, 999);
include_once ZPATLAS_PATH . 'includes/class-zpatlas-db.php';
include_once ZPATLAS_PATH . 'includes/atlas-functions.php';
include_once ZPATLAS_PATH . 'includes/async/async-tasks.php';
include_once ZPATLAS_PATH . 'includes/async/class-zpatlas-download.php';
include_once ZPATLAS_PATH . 'includes/async/class-zpatlas-unzip.php';
include_once ZPATLAS_PATH . 'includes/async/class-zpatlas-pluck.php';
include_once ZPATLAS_PATH . 'includes/async/class-zpatlas-mapcodes.php';
include_once ZPATLAS_PATH . 'includes/async/class-zpatlas-insert-db.php';
if ( is_admin() || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
	include_once ZPATLAS_PATH . 'includes/admin/admin-functions.php';
}
/**
 * Returns a message string
 */
function zpa_string( $id = '' ) {
	$strings = array(
		'active'		=> __( 'Active', 'zp-atlas' ),
		'unzip' => __( 'unzipping allCountries.zip...', 'zp-atlas' ),
		'creating'		=> __( 'Creating table keys...', 'zp-atlas' ),
		'failed_keys'	=> __( 'Failed to create table key(s):', 'zp-atlas' ),
		'inserting'		=> __( 'Inserting cities data into database...', 'zp-atlas' ),
		'installing'	=> __( 'installing...', 'zp-atlas' ),
		'installing_notice' => __( 'The atlas is being installed in the background. This will take a few minutes.', 'zp-atlas' )
	);
	return $strings[ $id ];
}
/**
 * Load admin-specific scripts and styles.
 */
function zpa_admin_scripts() {
	$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
	wp_register_style( 'zpatlas', ZPATLAS_URL . 'assets/zpatlas' . $suffix . '.css', array(), ZPATLAS_VERSION );
	wp_enqueue_style( 'zpatlas' );

	wp_register_script( 'zp-atlas-install', ZPATLAS_URL . '/assets/admin-atlas-install' . $suffix . '.js', array( 'jquery' ), ZPATLAS_VERSION, true );

	wp_localize_script( 'zp-atlas-install', 'zpAtlasStrings',
		array(
			'adminurl'		=> admin_url(),
			'checkStatus'	=> __( 'Check the status.', 'zp-atlas' ),
			'unzip'			=> zpa_string('unzip'),
			'creatingKeys'	=> zpa_string( 'creating' ),
			'dismiss'		=> __( 'Dismiss this notice.', 'zp-atlas' ),
			'inserting'		=> zpa_string( 'inserting' ),
			'installing'	=> zpa_string( 'installing' ),
   			'installingNotice'	=> zpa_string( 'installing_notice' ),
   			'installingNow' => get_option( 'zp_atlas_db_installing' ),
			'nonce'			=> wp_create_nonce( 'zp_atlas_install' ),
			'statusHeading'	=> __( 'ZodiacPress Status Message', 'zp-atlas' )
		)
	);
	
	// add install script only if atlas has not been installed and a custom db is not being used.
	if ( ! ZPAtlas_DB::is_installed() && ! ZPAtlas_DB::is_separate_db() ) {
		wp_enqueue_script( 'zp-atlas-install' );
	}

	if ( zp_is_admin_page() ) {
		wp_register_script( 'zpa-admin', ZPATLAS_URL . 'assets/zpa-admin' . $suffix . '.js', array(), ZPATLAS_VERSION, true );
		wp_enqueue_script( 'zpa-admin' );
	}
}
add_action( 'admin_enqueue_scripts', 'zpa_admin_scripts', 100 );
/**
 * Register fron end styles and scripts
 */
function zpa_register_scripts() {
	$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
	
	
	// @deprecated @todo remove this in next update after 1.1, leave only the new one below
	/* If atlas db option is selected and if the atlas is installed, use autocomplete-db.js instead of the regular autocomplete.js. */
	wp_register_script( 'zp-autocomplete-db', ZPATLAS_URL . 'assets/zp-autocomplete-db' . $suffix . '.js', array( 'jquery-ui-autocomplete', 'jquery' ), ZPATLAS_VERSION );
	wp_localize_script( 'zp-autocomplete-db', 'zp_js_strings', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );

	// new 
	wp_register_script( 'zp-atlas', ZPATLAS_URL . 'assets/zp-atlas' . $suffix . '.js', array(), ZPATLAS_VERSION );
	$str = zp_script_localization_data();
	unset($str['lang'],$str['u']);
	wp_localize_script( 'zp-atlas', 'zpastr', $str );
}
add_action( 'wp_enqueue_scripts', 'zpa_register_scripts' );
/**
 * Load the zp-autocomplete-db js instead of the core zp js
 */
function zpa_swap_scripts( $report_atts ) {
	/* If atlas db option is selected and if the atlas is installed, use autocomplete-db.js instead of the regular autocomplete.js. */
	if ( ZPAtlas_DB::use_db() ) {

		// @deprecated @todo remove legacy stuff and version_compare in next update after 1.1
		if ( defined('ZODIACPRESS_VERSION') && version_compare(ZODIACPRESS_VERSION, '1.9.1', '>') ) {

			// for new ZP > 2.0
			wp_dequeue_script( 'zp' );
			wp_enqueue_script( 'zp-atlas' );

		} else {

			// for legacy ZP < 2.0 
			wp_dequeue_script( 'zp-autocomplete' );
			wp_enqueue_script( 'zp-autocomplete-db' );
		}
	}
}
add_action( 'zp_report_shortcode_before', 'zpa_swap_scripts' );
/**
 * Handles ajax request to get cities from atlas database for autocomplete birth place field.
 */
function zpatlas_get_cities() {
	if ( empty( $_GET['c'] ) ) {
		return;	
	}
	global $zpdb;
	$a_json = array();
	$term = sanitize_text_field( $_GET['c'] );
	$term = $zpdb->esc_like( $term ) . '%';
	$sql = $zpdb->prepare( 'SELECT name,admin1,country,latitude,longitude,timezone FROM ' . $zpdb->prefix . 'zp_atlas WHERE name LIKE %s ORDER BY country DESC, name', $term );
	if ( $results = $zpdb->get_results( $sql ) ) {
		foreach ( $results as $row ) {
			$a_json[] = array(
				'value'	=> ( $row->name . ( $row->admin1 ? ', ' . $row->admin1 : '' ) .', '.$row->country ),
				'lat'	=> $row->latitude,
				'long'	=> $row->longitude,
				'tz'	=> $row->timezone
			);
		}
	}
	echo json_encode( $a_json );
	wp_die();
}
add_action( 'wp_ajax_zp_atlas_get_cities', 'zpatlas_get_cities' );
add_action( 'wp_ajax_nopriv_zp_atlas_get_cities', 'zpatlas_get_cities' );
function zp_atlas_lang() {
	load_plugin_textdomain( 'zp-atlas', false, plugin_basename( dirname( __FILE__ ) ) . '/languages' );
}
add_action('init', 'zp_atlas_lang');
