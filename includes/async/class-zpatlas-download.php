<?php
if ( ! defined( 'ABSPATH' ) ) exit;
if ( ! class_exists( 'WP_Async_Task', false ) ) {
	include_once ZPATLAS_PATH . 'includes/libraries/wp-async-task.php';
}
/**
 * Class that extends WP_Async_Task to download allCountries.zip from GeoNames in the background
 */
class ZPAtlas_Download extends WP_Async_Task {
	protected $action = 'zp_atlas_download';
	/**
	 * Prepare data for the asynchronous request
	 *
	 * @throws Exception If atlas is already in use so the task will not run
	 *
	 * @param array $data An array of data sent to the hook
	 *
	 * @return array
	 */
	protected function prepare_data( $data ) {
		if ( ZPAtlas_DB::is_installed() ) {
			throw new Exception( 'Atlas is already installed so do not get the data file again.' );
		}		
		return $data;
	}
	/**
	 * Run the async task action
	 */
	protected function run_action() {
		do_action( "wp_async_$this->action" );
	}
}
