<?php

namespace Alexr\Http\Controllers;

use Alexr\Models\Restaurant;
use Evavel\Eva;
use Evavel\Http\Controllers\Controller;
use Evavel\Http\Request\Request;
use Evavel\Query\Query;

class LicenseController extends Controller
{
	public function index(Request $request)
	{
		// License is for the plugin, not related to any user or restaurant

		return $this->response(['success' => true, 'license' => [
			'key' => alexr_get_setting('license_key'),
			//'activated' => alexr_get_setting('license_activated'),
			'activation' => alexr_get_setting('license_activation'),
			'addons' => alexr_get_setting('license_addons', ''),
			'addons_tenants' => alexr_get_setting('license_addons_tenants', ''),
			'license_code_check' => alexr_get_setting('license_code_check', ''),
		]]);
	}

	public function getRestaurants(Request $request)
	{
		return $this->response([
			'success' => true,
            'restaurants_id' => Restaurant::get()->map( function($restaurant) {
                return intval($restaurant->id);
            })->toArray()
		]);
	}

	public function update(Request $request)
	{
		$license_key = $request->license_key;
		//ray('##'.$license_key.'##');
		//if ($license_key == null) { ray('License key is null');}
		$license_activation = $request->license_activation;
		$license_addons = $request->license_addons;
		$license_addons_tenants = $request->license_addons_tenants;

		if ($license_key && is_string($license_key) && strlen($license_key) >= 9) {
			//ray('License key exists');
			alexr_save_setting('license_key', $license_key);
		} else {
			// No borro la licencia por si ha habido un fallo en el cliente
			// y ha puesto la licencia a NULL y por eso se desactiva sola
			//ray('License key does not exists');
			//alexr_save_setting('license_key', '');
		}

		alexr_save_setting('license_code_check', $request->license_code_check);

		if ($license_activation) {
			alexr_save_setting('license_activation', $license_activation);
		} else {
			alexr_delete_setting('license_activation');
		}

		if ($license_addons) {
			alexr_save_setting('license_addons', $license_addons);
		} else {
			alexr_delete_setting('license_addons');
		}

		if ($license_addons_tenants) {
			alexr_save_setting('license_addons_tenants', $license_addons_tenants);
		} else {
			alexr_delete_setting('license_addons_tenants');
		}

		return $this->response(['success' => true, 'message' => __eva('Saved!')]);
	}

	public function getErrors(Request $request)
	{
		return $this->response([ 'success' => true, 'list' => alexr_get_setting('license_list')]);
	}

	public function saveErrors(Request $request)
	{
		$list = $request->license_list;
		alexr_save_setting('license_list', $list);

		return $this->response(['success' => true]);
	}

}
