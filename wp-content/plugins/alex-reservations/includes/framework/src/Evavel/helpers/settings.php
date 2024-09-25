<?php

use Evavel\Query\Query;

if (! function_exists('evavel_encode')) {

	/**
	 * Encode helper
	 *
	 * @param $value
	 * @param $doubleEncode
	 *
	 * @return string
	 */
	function evavel_encode($value, $doubleEncode = true)
	{
		return htmlspecialchars($value ? $value : '', ENT_QUOTES, 'UTF-8', $doubleEncode);
	}
}

if (! function_exists('evavel_json_decode'))
{
	/**
	 * json decode helper
	 *
	 * @param $value
	 *
	 * @return mixed
	 */
	function evavel_json_decode($value, $associative = true)
	{
		if (is_array($value)) return $value;

		if (is_string($value)) {
			$decoded = json_decode($value, $associative);
			return $decoded ? $decoded : $value;
		}

		return $value;
	}

	function evavel_json_encode($value)
	{
		if (is_string($value)) return $value;
		if (is_numeric($value)) return (string) $value;

		//return json_encode($value, 448);
		return json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
	}

	function evavel_escape_className($name)
	{
		return str_replace('\\', '\\\\', $name);
	}

	function evavel_escape_especialChars($value)
	{
		$escapers = array("\\", "/", "\"", "\n", "\r", "\t", "\x08", "\x0c", "'");
		$replacements = array("\\\\", "\\/", "\\\"", "\\n", "\\r", "\\t", "\\f", "\\b", "\'");
		$result = str_replace($escapers, $replacements, $value);
		return $result;
	}
}

/**
 * Tenant settings helpers
 */

if (! function_exists('evavel_tenant_field')) {
	/**
	 * Get tenant field (ex: restaurant_id)
	 *
	 * @return string
	 */
	function evavel_tenant_field()
	{
		return evavel_singular(evavel_tenant_resource()).'_id';
	}

	function evavel_tenant_setting_table()
	{
		return evavel_singular(evavel_tenant_resource()).'_setting';
	}
}

if (! function_exists('evavel_tenant_resource')) {
	/**
	 * Get the tenant resource (ex: restaurants)
	 *
	 * @return false|mixed
	 */
	function evavel_tenant_resource()
	{
		return evavel_config('app.tenant');
	}
}


if (! function_exists('evavel_tenant_settings_table')) {
	/**
	 * Get the tenant table name without the prefix
	 * (ex: restaurant_meta)
	 *
	 * @return string
	 */
	function evavel_tenant_settings_table()
	{
		return evavel_singular(evavel_tenant_resource()).'_meta';
	}
}

if (! function_exists('evavel_settings_table')) {
	function evavel_settings_table()
	{
		return 'settings';
	}
}


if (! function_exists( 'evavel_tenant_get_setting' )) {

	/**
	 * Get specific tenant key setting
	 *
	 * @param $tenantId
	 * @param $key
	 * @param $default
	 *
	 * @return mixed|null
	 */
	function evavel_tenant_get_setting($tenantId, $key, $default = null)
	{
		$result = Query::table(evavel_tenant_settings_table())
		               ->where(evavel_tenant_field(), $tenantId)
		               ->where('meta_key', $key)
		               ->first();

		return $result != null ? evavel_json_decode($result->meta_value) : $default;
	}
}

if (! function_exists( 'evavel_tenant_save_setting' ))
{
	function evavel_tenant_save_setting($tenantId, $key, $value)
	{
		$table = evavel_tenant_settings_table();
		$tenantField = evavel_tenant_field();
		$value = evavel_json_encode($value);

		$result = Query::table($table)
		               ->where($tenantField, $tenantId)
		               ->where('meta_key', $key)
		               ->first();

		// Update setting
		if ($result)
		{
			Query::table($table)
			     ->where('id', $result->id)
				 ->update([
					'meta_value' => $value
				 ]);
		}

		// Insert new setting
		else
		{
			$params = [
				$tenantField => $tenantId,
				'meta_key' => $key,
				'meta_value' => $value
			];

			Query::table($table)->insert([$params]);
		}
	}
}





/**
 * General settings of the Platform
 */

if (! function_exists( 'evavel_get_setting' ))
{
	function evavel_get_setting($key, $default = null)
	{
		$result = Query::table(evavel_settings_table())
			->where('meta_key', $key)
			->first();

		return $result != null ? evavel_json_decode($result->meta_value) : $default;
	}
}

if (! function_exists( 'evavel_save_setting' ))
{
	function evavel_save_setting($key, $value)
	{
		$table = evavel_settings_table();
		$value = evavel_json_encode($value);

		$result = Query::table($table)
		               ->where('meta_key', $key)
		               ->first();

		// Update setting
		if ($result)
		{
			Query::table($table)
			     ->where('id', $result->id)
			     ->update([
				     'meta_value' => $value
			     ]);
		}

		// Insert new setting
		else
		{
			$params = [
				'meta_key' => $key,
				'meta_value' => $value
			];

			Query::table($table)->insert([$params]);
		}
	}
}

if (! function_exists( 'evavel_tenant_id' ))
{
	function evavel_tenant_id()
	{
		$request = \Evavel\Eva::make('request');
		return $request->tenant;
	}
}

if (! function_exists( 'evavel_tenant' ))
{
	function evavel_tenant()
	{
		$tenant_id = evavel_tenant_id();
		if (!$tenant_id) return null;

		//$model_class = evavel_tenant_model_class();
		//return $model_class::where('id', $tenant_id)->first();

		$resource = evavel_tenant_resource();
		return Query::table($resource)->where('id', $tenant_id)->first();
	}
}




