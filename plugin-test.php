<?php
/**
 * Plugin Name: Plugin Test Options
 * Description: A plugin to optimize DB queries, cache API requests, process background updates, and display a Gutenberg block.
 * Version: 1.0
 * Author: Your Name
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Define plugin constants.
define( 'PLUGIN_TEST_PATH', plugin_dir_path( __FILE__ ) );
define( 'PLUGIN_TEST_URL', plugin_dir_url( __FILE__ ) );

// Include required files.
require_once PLUGIN_TEST_PATH . 'includes/admin-options.php';
require_once PLUGIN_TEST_PATH . 'includes/functions.php';
require_once PLUGIN_TEST_PATH . 'includes/rest-endpoints.php';
require_once PLUGIN_TEST_PATH . 'includes/class-background-process.php';
require_once PLUGIN_TEST_PATH . 'includes/class-update-user-orders.php';

// Initialize the Admin Options page.
new Plugin_Test_Admin_Options();

// Activation hook: Add an index on the orders table for optimization.
register_activation_hook( __FILE__, 'plugin_test_activate' );
function plugin_test_activate() {
	global $wpdb;
	$orders_table = $wpdb->prefix . 'orders';
	// Add an index on user_id if it does not exist.
	$index_exists = $wpdb->get_results( "SHOW INDEX FROM $orders_table WHERE Key_name = 'user_id_index'" );
	if ( empty( $index_exists ) ) {
		$wpdb->query( "ALTER TABLE $orders_table ADD INDEX user_id_index (user_id)" );
	}
}

// Register Gutenberg block assets.
add_action( 'init', 'plugin_test_register_block' );
function plugin_test_register_block() {
	// Register editor script.
	wp_register_script(
		'plugin-test-block',
		PLUGIN_TEST_URL . 'assets/js/block.js',
		array( 'wp-blocks', 'wp-element', 'wp-editor', 'wp-api-fetch' ),
		'1.0',
		true
	);

	// Register editor styles.
	wp_register_style(
		'plugin-test-block-editor',
		PLUGIN_TEST_URL . 'assets/css/editor.css',
		array( 'wp-edit-blocks' ),
		'1.0'
	);

	// Register front-end styles.
	wp_register_style(
		'plugin-test-block-style',
		PLUGIN_TEST_URL . 'assets/css/style.css',
		array(),
		'1.0'
	);

	// Register the block.
	register_block_type( 'plugin-test/top-users', array(
		'editor_script'   => 'plugin-test-block',
		'editor_style'    => 'plugin-test-block-editor',
		'style'           => 'plugin-test-block-style',
		'render_callback' => 'plugin_test_render_top_users_block', // Container for dynamic rendering.
	) );
}

// Render callback for the Gutenberg block.
// (This outputs a container; the JavaScript will load data via our REST endpoint.)
function plugin_test_render_top_users_block( $attributes ) {
	$order = isset( $attributes['order'] ) ? esc_attr( $attributes['order'] ) : 'desc';
	return '<div id="plugin-test-top-users-block" data-order="' . $order . '"></div>';
}

// Handle admin-post actions (background processing and cache clearing).
add_action( 'admin_post_plugin_test_actions', 'plugin_test_handle_admin_actions' );
function plugin_test_handle_admin_actions() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Unauthorized user' );
	}
	if ( isset( $_POST['plugin_test_action'] ) ) {
		// Update total order values in the background.
		if ( 'process_orders' === $_POST['plugin_test_action'] ) {
			check_admin_referer( 'plugin_test_process', 'plugin_test_process_nonce' );
			// Instantiate our background process.
			$processor = new Plugin_Test_Update_User_Orders();
			$user_ids = get_users( array( 'fields' => 'ID' ) );
			foreach ( $user_ids as $user_id ) {
				$processor->push_to_queue( $user_id );
			}
			$processor->save()->dispatch();
			wp_redirect( admin_url( 'options-general.php?page=plugin-test-options&updated=1' ) );
			exit;
		}
		// Clear the API cache.
		elseif ( 'clear_api_cache' === $_POST['plugin_test_action'] ) {
			check_admin_referer( 'plugin_test_clear_cache', 'plugin_test_clear_cache_nonce' );
			$keyword = get_option( 'plugin_test_api_keyword', 'default' );
			$transient_key = 'plugin_test_api_' . md5( $keyword );
			delete_transient( $transient_key );
			wp_redirect( admin_url( 'options-general.php?page=plugin-test-options&cache_cleared=1' ) );
			exit;
		}
	}
}
