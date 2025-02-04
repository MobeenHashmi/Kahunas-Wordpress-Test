<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Plugin_Test_Update_User_Orders extends WP_Background_Process {
	/**
	 * Set the unique action name.
	 *
	 * @var string
	 */
	protected $action = 'plugin_test_update_user_orders';

	/**
	 * Process a single user ID.
	 *
	 * @param int $user_id The user ID.
	 * @return false Return false when the task is complete.
	 */
	protected function task( $user_id ) {
		$total = plugin_test_calculate_total_order_value( $user_id );
		update_user_meta( $user_id, 'total_order_value', $total );
		// Returning false indicates that the task is complete.
		return false;
	}
}
