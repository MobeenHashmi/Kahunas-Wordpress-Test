<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Fetch API data for a given keyword.
 * Uses the WordPress Transients API to cache responses for 10 minutes.
 *
 * @param string $keyword The API keyword.
 * @return mixed The API data (decoded JSON) or false on failure.
 */
function plugin_test_get_api_data( $keyword ) {
	$transient_key = 'plugin_test_api_' . md5( $keyword );
	$data          = get_transient( $transient_key );
	if ( false === $data ) {
		// Example API call to JSONPlaceholder. (Note: JSONPlaceholder does not filter by keyword,
		// so in a real scenario you might use a different API or filter the response.)
		$response = wp_remote_get( 'https://jsonplaceholder.typicode.com/posts?keyword=' . urlencode( $keyword ) );
		if ( is_wp_error( $response ) ) {
			return false;
		}
		$data = json_decode( wp_remote_retrieve_body( $response ), true );
		set_transient( $transient_key, $data, 10 * MINUTE_IN_SECONDS );
	}
	return $data;
}

/**
 * Calculate the total order value for a given user.
 *
 * @param int $user_id The user ID.
 * @return float The total order value.
 */
function plugin_test_calculate_total_order_value( $user_id ) {
	global $wpdb;
	$orders_table = $wpdb->prefix . 'orders'; // Assumes the orders table exists.
	$total = $wpdb->get_var( $wpdb->prepare(
		"SELECT SUM(order_total) FROM $orders_table WHERE user_id = %d",
		$user_id
	) );
	return $total ? floatval( $total ) : 0;
}
