<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'rest_api_init', function() {
	register_rest_route( 'plugin-test/v1', '/top-users', array(
		'methods'             => 'GET',
		'callback'            => 'plugin_test_get_top_users',
		'permission_callback' => '__return_true',
		'args'                => array(
			'order' => array(
				'default'          => 'desc',
				'sanitize_callback'=> 'sanitize_text_field',
			),
		),
	) );
} );

/**
 * Callback for the top users endpoint.
 *
 * @param WP_REST_Request $request The request object.
 * @return WP_REST_Response The response containing top users.
 */
function plugin_test_get_top_users( WP_REST_Request $request ) {
	$order = $request->get_param( 'order' );
	$order = ( 'asc' === strtolower( $order ) ) ? 'ASC' : 'DESC';

	$args = array(
		'meta_key' => 'total_order_value',
		'orderby'  => 'meta_value_num',
		'order'    => $order,
		'number'   => 5,
		'fields'   => array( 'ID', 'display_name' ),
	);
	$users  = get_users( $args );
	$result = array();
	foreach ( $users as $user ) {
		$total         = get_user_meta( $user->ID, 'total_order_value', true );
		$result[] = array(
			'ID'                => $user->ID,
			'display_name'      => $user->display_name,
			'total_order_value' => $total ? floatval( $total ) : 0,
		);
	}
	return rest_ensure_response( $result );
}
