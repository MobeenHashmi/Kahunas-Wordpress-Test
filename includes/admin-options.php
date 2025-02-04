<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Plugin_Test_Admin_Options {

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	/**
	 * Add a submenu page under Settings.
	 */
	public function add_admin_menu() {
		add_options_page(
			'Plugin Test Options',
			'Plugin Test Options',
			'manage_options',
			'plugin-test-options',
			array( $this, 'settings_page_html' )
		);
	}

	/**
	 * Register the API keyword setting.
	 */
	public function register_settings() {
		register_setting( 'plugin_test_options', 'plugin_test_api_keyword' );
	}

	/**
	 * Output the HTML for the settings page.
	 */
	public function settings_page_html() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<div class="wrap">
			<h1>Plugin Test Options</h1>
			<form method="post" action="options.php">
				<?php
				settings_fields( 'plugin_test_options' );
				do_settings_sections( 'plugin_test_options' );
				?>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><label for="plugin_test_api_keyword">API Keyword</label></th>
						<td>
							<input name="plugin_test_api_keyword" type="text" id="plugin_test_api_keyword" value="<?php echo esc_attr( get_option( 'plugin_test_api_keyword', 'default' ) ); ?>" class="regular-text">
						</td>
					</tr>
				</table>
				<?php submit_button(); ?>
			</form>
			<hr>
			<h2>Actions</h2>
			<!-- Button to trigger background processing. -->
			<form method="post" action="<?php echo admin_url( 'admin-post.php' ); ?>">
				<?php wp_nonce_field( 'plugin_test_process', 'plugin_test_process_nonce' ); ?>
				<input type="hidden" name="action" value="plugin_test_actions">
				<input type="hidden" name="plugin_test_action" value="process_orders">
				<?php submit_button( 'Update Total Order Values (Background Process)' ); ?>
			</form>
			<!-- Button to clear the API cache. -->
			<form method="post" action="<?php echo admin_url( 'admin-post.php' ); ?>">
				<?php wp_nonce_field( 'plugin_test_clear_cache', 'plugin_test_clear_cache_nonce' ); ?>
				<input type="hidden" name="action" value="plugin_test_actions">
				<input type="hidden" name="plugin_test_action" value="clear_api_cache">
				<?php submit_button( 'Clear API Cache' ); ?>
			</form>
			<hr>
			<h2>Latest API Response</h2>
			<?php
			$keyword  = get_option( 'plugin_test_api_keyword', 'default' );
			$api_data = plugin_test_get_api_data( $keyword );
			if ( $api_data && is_array( $api_data ) && ! empty( $api_data ) ) {
				// For demonstration, we simply show the title and body from the first returned post.
				$first_post = $api_data[0];
				echo '<h3>Title: </h3>';
				echo '<h3>' . esc_html( $first_post['title']),' -----Coming from API' . '</h3>';
				echo '<h3>Body: </h3>';
				echo '<p>' . esc_html( $first_post['body'] ), ' -----Coming from API' . '</p>';
			} else {
				echo '<p>No API data available.</p>';
			}
			?>
		</div>
		<?php
	}
}
