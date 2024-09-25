<?php

namespace Evavel\Log;

class Log {

	const DB_CREATE = "DB create";
	const DB_UPDATE = "DB update";
	const DB_DELETE = "DB delete";
	const AJAX_SAVE_BOOKING_VALUES = "AJAX updateBookingValuesFromRequest";
	const AJAX_ERROR_SAVE_BOKING = "AJAX error saving booking";

	// GET param to recognize the download action
	const DOWNLOAD_PARAM = EVAVEL_LOG_DOWNLOAD_PARAM;
	const NONCE_TEXT = EVAVEL_LOG_NONCE_TEXT;
	const WP_OPTION = EVAVEL_LOG_WP_OPTION;

	public function __construct()
	{
		add_action('init', array($this, 'checkDownload'));
	}

	// Called from evavel_log_to_file()
	public static function save($base_dir, $action, $message)
	{
		// Check if it is enabled
		$enabled = get_option(self::WP_OPTION, 'no');
		if ($enabled == 'no') return;

		// Full message
		//$date_full = date("Y-m-d H:i:s");
		$date_full = gmdate("Y-m-d H:i:s").' UTC';

		$is_cron = false;
		$name_full = self::getUserName();
		if (!$name_full) {
			$is_cron = true;
			$name_full = '-';
		}
		$full_message = '['.$date_full.'] '. $action.' | '.$name_full.' | '.$message;

		// Create the first time
		$date = evavel_date_now()->format('Y_m');
		//$file_name = $date.'_'.($is_cron ? 'NO_USER_' : '').$action.'.txt';

		$file_name = $date.'.txt';

		$file_path = $base_dir.'/'.$file_name;
		if (!file_exists($file_path)) {
			file_put_contents($file_path, $full_message."\n");
			return;
		}

		// Update next time
		$fp = fopen($file_path, 'a');
		fwrite($fp, $full_message);
		fwrite($fp, "\n\n");
		fclose($fp);
	}

	public static function getUserName()
	{
		// If no user logged in then it is a cron or some other plugin
		if (!is_user_logged_in()) return false;

		$user_wp = wp_get_current_user();
		$email = $user_wp->data->user_email;

		$userClass = evavel_app_user_class();
		$user = $userClass::where('email', $email)
		                  ->where('wp_user_id', $user_wp->ID)
		                  ->first();

		return $user->name.' ('.$user->email.') ' . get_current_user().' ('.$user_wp->data->ID.')';
	}

	public static function createDownloadLink($filename)
	{
		return '/?'.self::DOWNLOAD_PARAM.'='.$filename.'&token='.evavel_create_nonce(self::NONCE_TEXT);
	}

	// Download the file
	public function checkDownload()
	{
		if (isset($_GET[self::DOWNLOAD_PARAM]))
		{
			$file = sanitize_text_field($_GET[self::DOWNLOAD_PARAM]);
			$token = sanitize_text_field($_GET['token']);

			if (!evavel_verify_nonce($token, self::NONCE_TEXT)) return;

			$filename = evavel_path_to_log_files().'/'.$file;
			if (file_exists($filename)) {
				header('Content-Description: File Transfer');
				header('Content-Type: application/octet-stream');
				header('Content-Disposition: attachment; filename="' . basename($filename) . '"');
				header('Expires: 0');
				header('Cache-Control: must-revalidate');
				header('Pragma: public');
				header('Content-Length: ' . filesize($filename));
				readfile($filename);
				exit;
			}
		}
	}
}

new Log();
