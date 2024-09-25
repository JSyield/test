<?php


// Call ajax action from the dashboard to check if PRO plugin need to be updated

class ALEXR_AutoUpdate {

	public static $action = "update_alexr_pro";

	public $seconds_to_check_update = 24*3600; // Every 24 hours

	public function __construct()
	{
		$action = self::$action;
		add_action( 'wp_ajax_'.$action, array($this, 'check_version_pro_ajax'));
		add_action( 'wp_ajax_nopriv_'.$action, array($this, 'check_version_pro_ajax'));
	}

	public static function create_nonce()
	{
		return wp_create_nonce(self::$action);
	}

	/**
	 * Call this funcion to update the PRO plugin
	 * @param $check_nonce
	 *
	 * @return void
	 */
	public function check_version_pro_ajax( $check_nonce = true )
	{
		if ($check_nonce && wp_doing_ajax())
		{
			if ($this->is_pro_active()) {
				wp_send_json_error();
			}

			if (!isset($_GET['nonce'])) {
				wp_send_json_error();
			}

			$nonce = sanitize_text_field($_GET['nonce']);
			if (!wp_verify_nonce($nonce, self::$action)) {
				wp_send_json_error();
			}
		}

		// Check only once a day
		if ($this->is_pro_active() && $this->is_time_to_check()){
			$this->check_version_pro();
		}

		if (wp_doing_ajax()){
			wp_send_json_success();
		}
	}

	public function is_pro_active()
	{
		return defined('ALEXR_PRO_VERSION');
	}

	public function is_time_to_check()
	{
		$key = '_srr_alexr_pro_check_update';
		$update_check_checked = get_option($key, false);
		if (current_time( 'timestamp' ) > $update_check_checked )
		{
			// Do not check until 1 day again
			update_option( $key, ( current_time( 'timestamp' ) + $this->seconds_to_check_update ) );
			return true;
		}

		return false;
	}

	public function check_version_pro()
	{
		if (!defined('ALEXR_VERSION')) {
			return;
		}

		if (!defined('ALEXR_PRO_VERSION')) {
			return;
		}

		// La PRO debe ir por delante de la FREE
		if ( version_compare(ALEXR_VERSION, ALEXR_PRO_VERSION, '<') ) {
			return;
		}

		$current_pro_version = ALEXR_PRO_VERSION;

		$data = $this->check_last_version_pro();
		if (!$data) {
			return;
		}
		$last_pro_version = $data['version'];

		// La nueva version debe ser mayor que la actual instalada
		if ( version_compare($current_pro_version, $last_pro_version, '<') ) {
			$this->update_pro_now($data);
		} else {
			//ray('No hace falta actualizar');
		}

	}

	public function check_last_version_pro()
	{
		$server_url = 'https://alexreservations.com';
		$remote = wp_remote_get( $server_url.'/plugin/info',
			array(
				'timeout' => 10,
				'headers' => array(
					'Accept' => 'application/json'
				)
			)
		);

		if(
			is_wp_error( $remote )
			|| 200 !== wp_remote_retrieve_response_code( $remote )
			|| empty( wp_remote_retrieve_body( $remote ) )
		) {
			return false;
		}

		$remote = json_decode( wp_remote_retrieve_body( $remote ), true );
		return $remote;
	}


	public function update_pro_now($data)
	{
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
		require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

		$plugin = "alex-reservations-pro/alex-reservations-pro.php";
		$slug = 'alex-reservations-pro';

		$plugin_data          = get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin );
		$status['plugin']     = $plugin;
		$status['pluginName'] = $plugin_data['Name'];

		if ( $plugin_data['Version'] ) {
			/* translators: %s: Plugin version. */
			$status['oldVersion'] = sprintf( __( 'Version %s' ), $plugin_data['Version'] );
		}

		wp_update_plugins();

		$skin     = new WP_Ajax_Upgrader_Skin();
		$upgrader = new Plugin_Upgrader( $skin );
		$result   = $upgrader->bulk_upgrade( array( $plugin ) );
	}
}

$alexr_auto_update = new ALEXR_AutoUpdate();

// Do not want to run while managing the dashboard. Will run at the backend or front-end
// I can also call it using the ajax hook update_alexr_pro but not using it yet
if (!wp_doing_ajax())
{
	if (defined('ALEXR_PRO_DISABLE_AUTOUPDATE') && ALEXR_PRO_DISABLE_AUTOUPDATE) return;
	$alexr_auto_update->check_version_pro_ajax(false);
}

