<?php

// @TODO: depends on Wordpress
if (! function_exists('evavel_response')) {
	function evavel_response($response, $code)
	{
		return new \WP_REST_Response( $response, $code );
	}
}

if (! function_exists('evavel_wp_table_users')) {
	function evavel_wp_table_users()
	{
		global $wpdb;
		return $wpdb->base_prefix.'users';
	}
}

if (! function_exists('evavel_wp_set_password')) {
	function evavel_wp_set_password( $password, $user_id )
	{
		wp_set_password($password, $user_id);
	}
}

if (! function_exists('evavel_wp_login_url')) {
	function evavel_wp_login_url()
	{
		return wp_login_url();
	}
}

if (! function_exists('evavel_wp_auto_login')) {
	function evavel_wp_auto_login($user_id)
	{
		$user = get_user_by( 'id', $user_id );
		if( $user ) {
			wp_set_current_user( $user_id, $user->user_login );
			wp_set_auth_cookie( $user_id );
			do_action( 'wp_login', $user->user_login );
		}
	}
}

