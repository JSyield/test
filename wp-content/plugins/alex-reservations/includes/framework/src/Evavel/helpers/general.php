<?php

// Exit if accessed directly
use Evavel\Interfaces\ToJsonSerialize;
use Evavel\Query\Query;


function EVAVEL()
{
	return \Evavel\Container\EvaContainer::singleton();
}


// @TODO particular de la aplicacion
function evavel_login()
{
	if (!defined('EVAVEL_PRO_VERSION')) {
		wp_redirect(wp_login_url());
		exit();
	}

	// @TODO add a setting for this custom login
	$enabled_custom_login = true;

	if ($enabled_custom_login) {
		evavel_login_dashboard();
	} else {
		wp_redirect(wp_login_url());
	}
	exit();
}

// @TODO particular de la aplicacion
function evavel_login_dashboard()
{
	if (!defined('EVAVEL_PRO_PLUGIN_DIR')) {
		wp_redirect(wp_login_url());
		return;
	}

	ob_start();
	require EVAVEL_PRO_PLUGIN_DIR . 'includes-pro/dashboard/page-login.php';
	echo ob_get_clean();
	exit();
}


if (!function_exists('evavel_logout')){
	function evavel_logout()
	{
		wp_logout();
	}
}

if (!function_exists('evavel_to_array')) {
    function evavel_to_array($object)
    {
        return json_decode(evavel_json_encode($object), true);
    }
}


if (!function_exists('evavel_collect')) {
    function evavel_collect($value)
    {
        return new \Evavel\Models\Collections\Collection($value);
    }
}


if (! function_exists('evavel_value')) {
    function evavel_value($value, ...$args)
    {
        return $value instanceof Closure ? $value(...$args) : $value;
    }
}

if (! function_exists('evavel_class_basename')) {
    function evavel_class_basename($class)
    {
        $class = is_object($class) ? get_class($class) : $class;

        return basename(str_replace('\\', '/', $class));
    }
}



if (! function_exists('evavel_json')) {
    function evavel_json($array)
    {
        $result = [];

        foreach($array as $key => $value)
        {
            if ( $value instanceof ToJsonSerialize)
            {
                $result[$key] = evavel_json($value->toJsonSerialize());
            }
            else if (is_array($value))
            {
                $result[$key] = evavel_json($value);
            }
            else
            {
                $result[$key] = $value;
            }
        }

        return $result;
    }
}


if (! function_exists('evavel_with')) {
    function evavel_with($value, callable $callback = null)
    {
        return is_null($callback) ? $value : $callback($value);
    }
}

if (! function_exists('evavel_make')) {
    function evavel_make($name)
    {
        return \Evavel\Container\EvaContainer::singleton()->resolve($name);
    }
}


if (! function_exists('evavel_listen')) {
	function evavel_listen( ...$args ) {
		return evavel_make('events')->listen(...$args);
	}
}

if (! function_exists('evavel_event')) {
	function evavel_event( ...$args ) {
		return evavel_make('events')->dispatch(...$args);
	}
}


if (! function_exists('evavel_send_json')) {
    function evavel_send_json($response, $code)
    {
        wp_send_json($response, $code);
    }
}

if (! function_exists('evavel_403')) {
	function evavel_403()
	{
		wp_send_json([], 403);
	}
}

if (! function_exists('evavel_404')) {
	function evavel_404()
	{
		wp_send_json([], 404);
	}
}


if (! function_exists('evavel_tenant_create_nonce')) {
	function evavel_tenant_create_nonce($tenant_id)
	{
		return evavel_create_nonce('booking-ajax-tenant-'.$tenant_id);
	}
}

if (! function_exists('evavel_tenant_verify_nonce')) {
	function evavel_tenant_verify_nonce($nonce, $tenant_id)
	{
		return evavel_verify_nonce($nonce, 'booking-ajax-tenant-'.$tenant_id);
	}
}

function evavel_create_nonce( $term = EVAVEL_NONCE) {
	return wp_create_nonce($term);
}

function evavel_verify_nonce($nonce, $term = EVAVEL_NONCE) {
	return wp_verify_nonce($nonce, $term);
}

function evavel_ajaxurl() {
	$url = admin_url('admin-ajax.php');
	if (!$url) {
		$url = '/wp-admin/admin-ajax.php';
	}
	return $url;
}

if (! function_exists('evavel_site_url')) {
	function evavel_site_url()
	{
		if (is_multisite()){
			return get_bloginfo('url');
		}
		return site_url();
	}
}

if (! function_exists('evavel_site_name')) {
	function evavel_site_name()
	{
		if (is_multisite()){
			return get_bloginfo('name');
		}
		return get_bloginfo('name');
	}
}

if (! function_exists('evavel_view_booking_url')) {
	function evavel_view_booking_url($uuid)
	{
		return add_query_arg([ EVAVEL_GET_VIEW_BOOKING => $uuid], evavel_site_url());
	}
}

function evavel_edit_booking_url($uuid, $status = false)
{
	$args = [
		EVAVEL_EDIT_VIEW_BOOKING => $uuid
	];

	if ($status) {
		$args['status'] = $status;
	}

	return add_query_arg($args, evavel_site_url());
}



if (! function_exists('evavel_create_wp_user')) {
	function evavel_create_wp_user($attrs)
	{
		$user_id = wp_insert_user($attrs);

		if (is_wp_error($user_id)){
			return null;
		}

		if (is_multisite()) {
			$blogs = get_sites();
			foreach( $blogs as $b ){
				$blog_id = $b->blog_id;
				add_user_to_blog( $b->blog_id, $user_id, 'subscriber' );
			}
		}

		return $user_id;
	}
}


if (! function_exists('evavel_delete_wp_user')) {
	function evavel_delete_wp_user($id)
	{
		require_once(ABSPATH.'wp-admin/includes/user.php');

		if (is_multisite()) {
			require_once ABSPATH . 'wp-admin/includes/ms.php';
			wpmu_delete_user(intval($id));
		} else {
			wp_delete_user(intval($id));
		}
	}
}

if (! function_exists('evavel_singular')) {
    function evavel_singular($name)
    {
        return \Evavel\Support\Str::singular($name);
    }
}

if (! function_exists('evavel_plural')) {
    function evavel_plural($name)
    {
        return \Evavel\Support\Str::plural($name);
    }
}


