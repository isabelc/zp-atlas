<?php
/*
Plugin Name: ZodiacPress Atlas
Plugin URI: https://isabelcastillo.com/free-plugins/zp-atlas@todoLIVE
Description: Your own atlas database for ZodiacPress instead of using GeoNames.org
Version: 1.0.alpha-1
Author: Isabel Castillo
Author URI: https://isabelcastillo.com
License: GNU GPLv2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: zp-atlas@todo
Domain Path: /languages@todo

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
	define( 'ZPATLAS_VERSION', '0.9.alpha-1' );// @todo update
}
if ( ! defined( 'ZPATLAS_URL' ) ) {
	define( 'ZPATLAS_URL', plugin_dir_url( __FILE__ ) );
}
if ( ! defined( 'ZPATLAS_PATH' ) ) {
	define( 'ZPATLAS_PATH', plugin_dir_path( __FILE__ ) );
}

include_once ZPATLAS_PATH . 'includes/settings.php';


if (!class_exists('ZP_Atlas_DB', false)) {
	include_once ZPATLAS_PATH . 'includes/class-zp-atlas-db.php';
}

include_once ZPATLAS_PATH . 'includes/atlas-functions.php';


if ( is_admin() || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
	include_once ZPATLAS_PATH . 'includes/admin/admin-functions.php';
}


/**
 * Load admin-specific scripts and styles.
 */
function zpa_admin_scripts() {
	$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
	wp_register_style( 'zpatlas', ZPATLAS_URL . 'assets/css/zpatlass' . $suffix . '.css', array(), ZPATLAS_VERSION );
	wp_enqueue_style( 'zpatlas' );

	wp_register_script( 'zp-atlas-install', ZPATLAS_URL . '/assets/admin-atlas-install' . $suffix . '.js', array( 'jquery' ), ZPATLAS_VERSION, true );

	wp_localize_script( 'zp-atlas-install', 'zpAtlasStrings',
		array(
			'adminurl'		=> admin_url(),
			'checkStatus'	=> __( 'Check the status.', 'zodiacpress' ),
			'creatingKeys'	=> zp_string( 'creating' ),
			'dismiss'		=> __( 'Dismiss this notice.', 'zodiacpress' ),
			'inserting'		=> zp_string( 'inserting' ),
			'installing'	=> zp_string( 'installing' ),
   			'installingNotice'	=> zp_string( 'installing_notice' ),
   			'installingNow' => get_option( 'zp_atlas_db_installing' ),
			'nonce'			=> wp_create_nonce( 'zp_atlas_install' ),
			'statusHeading'	=> __( 'ZodiacPress Status Message', 'zodiacpress' )
		)
	);
	
	// add install script only if atlas has not been installed and a custom db is not being used.
	if ( ! ZP_Atlas_DB::is_installed() && ! ZP_Atlas_DB::is_separate_db() ) {
		wp_enqueue_script( 'zp-atlas-install' );
	}

	if ( zp_is_admin_page() ) {
		wp_register_script( 'zpa-admin', ZPATLAS_URL . 'assets/zpa-admin' . $suffix . '.js', array(), ZPATLAS_VERSION, true );
		wp_enqueue_script( 'zpa-admin' );
	}
}
add_action( 'admin_enqueue_scripts', 'zpa_admin_scripts', 100 );
