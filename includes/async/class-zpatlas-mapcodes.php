<?php
if ( ! defined( 'ABSPATH' ) ) exit;
if ( ! class_exists( 'WP_Async_Task', false ) ) {
	include_once ZPATLAS_PATH . 'includes/libraries/wp-async-task.php';
}
/**
 * Class that extends WP_Async_Task to unzip the allCountries.zip file in the background
 */
class ZPAtlas_Mapcodes extends WP_Async_Task {
	protected $action = 'zp_atlas_mapcodes';
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
		return array('dir' => $data[0]);
	}
	/**
	 * Run the async task action
	 */
	protected function run_action() {
		do_action( "wp_async_$this->action" );
	}
}
