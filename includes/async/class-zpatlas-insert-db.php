<?php
if ( ! defined( 'ABSPATH' ) ) exit;
if ( ! class_exists( 'WP_Async_Task', false ) ) {
	include_once ZPATLAS_PATH . 'includes/libraries/wp-async-task.php';
}
/**
 * Class that extends WP_Async_Task to insert cities data into database in the background.
 */
class ZPAtlas_Insert_DB extends WP_Async_Task {
	protected $action = 'zp_atlas_insert_db';
	/**
	 * Prepare data for the asynchronous request
	 *
	 * @throws Exception If for any reason the request should not happen
	 *
	 * @param array $data An array of data sent to the hook
	 *
	 * @return array
	 */
	protected function prepare_data($data) {
		return $data;
	}
	/**
	 * Run the async task action
	 */
	protected function run_action() {
		do_action( "wp_async_$this->action" );
	}
}
