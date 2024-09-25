<?php

namespace Alexr\Hooks;

use Evavel\Http\Controllers\ResourceCreateController;
use Evavel\Http\Controllers\ResourceDestroyController;
use Evavel\Http\Controllers\ResourceUpdateBulkController;
use Evavel\Http\Controllers\ResourceUpdateController;

class HooksResourceController
{
	public function __construct()
	{
		ResourceCreateController::$authorizations[] = array($this, 'authorize_resource_create');
		ResourceUpdateController::$authorizations[] = array($this, 'authorize_resource_update');
		ResourceDestroyController::$authorizations[] = array($this, 'authorize_resource_destroy');
		ResourceUpdateBulkController::$authorizations[] = array($this, 'authorize_resource_bulk_update');
	}

	/**
	 * Filter the resource name to add the corresponding permissions that should be managed
	 *
	 * @param $resource
	 * @param $data
	 *
	 * @return mixed
	 */
	public function authorize_resource_create($resource, $data)
	{
		$permission_name = $data['permission_name'];
		$permission_message = $data['permission_message'];

		if (str_contains($resource, 'CTag')) {
			$permission_name = 'customer_tags';
			$permission_message = __eva('You cannot create customers tags.');
		} else if (str_contains($resource, 'BTag')) {
			$permission_name = 'booking_tags';
			$permission_message = __eva('You cannot create bookings tags.');
		}

		$data['permission_name'] = $permission_name;
		$data['permission_message'] = $permission_message;

		return $data;
	}

	/**
	 * Filter the resource name to add the corresponding permissions that should be managed
	 *
	 * @param $resource
	 * @param $data
	 *
	 * @return mixed
	 */
	public function authorize_resource_update($resource, $data)
	{
		$permission_name = $data['permission_name'];
		$permission_message = $data['permission_message'];

		if (str_contains($resource, 'CTag')) {
			$permission_name = 'customer_tags';
			$permission_message = __eva('You cannot edit customers tags.');
		} else if (str_contains($resource, 'BTag')) {
			$permission_name = 'booking_tags';
			$permission_message = __eva('You cannot edit bookings tags.');
		}

		$data['permission_name'] = $permission_name;
		$data['permission_message'] = $permission_message;

		return $data;
	}

	/**
	 * Filter the resource name to add the corresponding permissions that should be managed
	 *
	 * @param $resource
	 * @param $data
	 *
	 * @return mixed
	 */
	public function authorize_resource_destroy($resource, $data)
	{
		$permission_name = $data['permission_name'];
		$permission_message = $data['permission_message'];

		if (str_contains($resource, 'CTag')) {
			$permission_name = 'customer_tags';
			$permission_message = __eva('You cannot delete customers tags.');
		} else if (str_contains($resource, 'BTag')) {
			$permission_name = 'booking_tags';
			$permission_message = __eva('You cannot delete bookings tags.');
		}

		$data['permission_name'] = $permission_name;
		$data['permission_message'] = $permission_message;

		return $data;
	}

	/**
	 * Filter the resource name to add the corresponding permissions that should be managed
	 *
	 * @param $resource
	 * @param $data
	 *
	 * @return mixed
	 */
	public function authorize_resource_bulk_update($resource, $data)
	{
		$permission_name = $data['permission_name'];
		$permission_message = $data['permission_message'];

		if (str_contains($resource, 'CTagGroup')) {
			$permission_name = 'customer_tags';
			$permission_message = __eva('You cannot edit customers tags.');
		} else if (str_contains($resource, 'BTagGroup')) {
			$permission_name = 'booking_tags';
			$permission_message = __eva('You cannot edit bookings tags.');
		}

		$data['permission_name'] = $permission_name;
		$data['permission_message'] = $permission_message;

		return $data;
	}
}

new HooksResourceController;
