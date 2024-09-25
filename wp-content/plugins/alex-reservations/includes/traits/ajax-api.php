<?php

use Alexr\Models\Area;
use Alexr\Models\Combination;
use Alexr\Models\Floor;
use Alexr\Models\Table;

// https://wp_alexreservations.test/wp-admin/admin-ajax.php
{
	function message() {
		wp_send_json_success( [
			'available' => ['12:30', '13:30', '14:00'],
			'message' => $_REQUEST['message'] ?? 'NO MESSAGE'
		] );
	}

	function api_get_options_for_date() {

		$token = $_REQUEST['token'] ?? '';
		$name = $_REQUEST['name'] ?? '';
		$phone = $_REQUEST['phone'] ?? '';
		$date = $_REQUEST['date'] ?? '';
		$time = $_REQUEST['time'] ?? '';

		wp_send_json_success([
			'available' => ['12:30', '13:30', '14:00'],
			'token' => $token,
			'name' => $name,
			'phone' => $phone,
			'date' => $date,
			'time' => $time
		]);
	}
}
