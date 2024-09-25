<?php

use Evavel\Query\Query;

/**
 * Get application setting
 *
 * @param $meta_key
 *
 * @return null
 */
function alexr_get_setting($meta_key, $default = null)
{
	$data = Query::table('settings')->where('meta_key', $meta_key)->first();

	if ($data){
		return $data->meta_value;
	}

	return $default;
}

/**
 * Get all application settings
 *
 * @return
 */
function alexr_get_all_settings()
{
	$data = Query::table('settings')->get();
	return $data;
}

/**
 * Save application setting
 *
 * @param $meta_key
 * @param $meta_value
 *
 * @return bool
 */
function alexr_save_setting($meta_key, $meta_value)
{
	$data = Query::table('settings')->where('meta_key', $meta_key)->first();

	if ($data) {
		Query::table('settings')
		     ->where('id', $data->id)
		     ->update([
				'meta_key' => $meta_key,
				'meta_value' => $meta_value
			]);
	} else {
		Query::table('settings')->insert([
			'meta_key' => $meta_key,
			'meta_value' => $meta_value
		]);
	}

	return true;
}

/**
 * Delete application setting
 *
 * @param $meta_key
 *
 * @return bool
 */
function alexr_delete_setting($meta_key)
{
	$data = Query::table('settings')->where('meta_key', $meta_key)->first();

	if ($data) {
		Query::table('settings')->where('id', $data->id)->delete();
	}

	return true;
}


function alexr_get_tenant_setting($tenant_id, $meta_key, $as_array = true)
{
	$data = Query::table('restaurant_setting')
	             ->where(evavel_tenant_field(), $tenant_id)
	             ->where('meta_key', $meta_key)
	             ->first();

	if ($data) {
		return json_decode($data->meta_value, $as_array);
	}

	return null;
}

function alexr_save_tenant_setting($tenant_id, $meta_key, $meta_value)
{
	$data = Query::table('restaurant_setting')
				->where(evavel_tenant_field(), $tenant_id)
				->where('meta_key', $meta_key)
				->first();

	if ($data) {
		Query::table('restaurant_setting')->where('id', $data->id)->update([
			'meta_key' => $meta_key,
			'meta_value' => json_encode($meta_value)
		]);
	} else {
		Query::table('restaurant_setting')->insert([
			evavel_tenant_field() => $tenant_id,
			'meta_key' => $meta_key,
			'meta_value' => json_encode($meta_value)
		]);
	}

	return true;
}


// Caso particular de alexr_get_tenant_setting donde los valores simples
// se almacenan en el setting 'dashboard'
function alexr_get_dashboard_setting($tenant_id, $meta_key, $default = null)
{
	$dashboard = alexr_get_tenant_setting($tenant_id, 'dashboard');
	if (is_object($dashboard)) {
		$dashboard = (array) $dashboard;
	}
	if (!is_array($dashboard)){
		$dashboard = [];
	}
	return isset($dashboard[$meta_key]) ? $dashboard[$meta_key] : $default;
}

function alexr_get_all_dashboard_settings($tenant_id)
{
	$dashboard = alexr_get_tenant_setting($tenant_id, 'dashboard');
	if (is_object($dashboard)) {
		$dashboard = (array) $dashboard;
	}
	if (!is_array($dashboard)){
		$dashboard = [];
	}
	return $dashboard;
}


function alexr_save_dashboard_setting($tenant_id, $meta_key, $meta_value)
{
	$dashboard = alexr_get_tenant_setting($tenant_id, 'dashboard');
	if (is_object($dashboard)) {
		$dashboard = (array) $dashboard;
	}
	if (!is_array($dashboard)){
		$dashboard = [];
	}
	$dashboard[$meta_key] = $meta_value;
	alexr_save_tenant_setting($tenant_id, 'dashboard', $dashboard);
}

function alexr_save_all_dashboard_settings($tenant_id, $list)
{
	$dashboard = alexr_get_tenant_setting($tenant_id, 'dashboard');
	if (is_object($dashboard)) {
		$dashboard = (array) $dashboard;
	}
	if (!is_array($dashboard)){
		$dashboard = [];
	}

	foreach($list as $meta_key => $meta_value) {
		$dashboard[$meta_key] = $meta_value;
		alexr_save_tenant_setting($tenant_id, 'dashboard', $dashboard);
	}
}


function alexr_get_active_languages()
{
	$languages = evavel_languages_all();

	$active = alexr_get_setting('active_languages');
	$current_active = $active;

	if ($active != null) {
		$active = json_decode($active, true);
	}


	// Default list
	if (! $active || !is_array($active)) {
		$active = [];
		foreach($languages as $key => $label){
			$active[$key] = true;
		}
	}

	// Be sure I have all the languages
	foreach($languages as $key => $label){
		if (!isset($active[$key])){
			$active[$key] = true;
		}
	}

	// Save only if has changed
	$new_active = json_encode($active);
	if ($new_active != $current_active) {
		alexr_save_setting('active_languages', $new_active);
	}

	return $active;
}

function alexr_set_only_active_language($lang)
{
	$list = alexr_get_active_languages();
	foreach($list as $key => $value) {
		if ($key == $lang) {
			$list[$key] = true;
		} else {
			$list[$key] = false;
		}
	}
	alexr_save_setting('active_languages', json_encode($list));
}
