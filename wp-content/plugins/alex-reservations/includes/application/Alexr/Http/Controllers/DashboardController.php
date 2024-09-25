<?php

namespace Alexr\Http\Controllers;

use Alexr\Http\Traits\UISettingsController;
use Evavel\Http\Controllers\Controller;
use Evavel\Http\Request\Request;

class DashboardController extends Controller
{
	protected $meta_key_bookings_columns = 'bookings_columns';
	protected $meta_key_customers_columns = 'customers_columns';

	protected $meta_key_booking_editor = 'booking_editor';
	protected $meta_key_customer_editor = 'customer_editor';

	use UISettingsController;

	public function getColumns(Request $request)
	{
		$tenantId = $request->tenant;
		$resource = $request->resource;

		// This is for restoring to default options
		$useDefault = $request->useDefault;

		$columns = [];

		if ($resource == 'bookings') {

			if ($useDefault == 'yes') {
				$columns = $this->getDefaultColumnsBookings();
				alexr_save_tenant_setting($tenantId, $this->meta_key_bookings_columns, $columns);
			} else {
				$columns = $this->getColumnsBookings($tenantId);
			}
		}

		else if ($resource == 'customers') {

			if ($useDefault == 'yes') {
				$columns = $this->getDefaultColumnsCustomers();
				alexr_save_tenant_setting($tenantId, $this->meta_key_customers_columns, $columns);
			} else {
				$columns = $this->getColumnsCustomers($tenantId);
			}
		}

		else if ($resource == 'booking_editor') {

			if ($useDefault == 'yes') {
				$columns = $this->getDefaultColumnsBookingEditor();
				alexr_save_tenant_setting($tenantId, $this->meta_key_booking_editor, $columns);
			} else {
				$columns = $this->getColumnsBookingEditor($tenantId);
			}
		}

		else if ($resource == 'customer_editor') {

			if ($useDefault == 'yes') {
				$columns = $this->getDefaultColumnsCustomerEditor();
				alexr_save_tenant_setting($tenantId, $this->meta_key_customer_editor, $columns);
			} else {
				$columns = $this->getColumnsCustomerEditor($tenantId);
			}
		}


		return $this->response(['success' => true, 'columns' => $columns]);

	}

	public function saveColumns(Request $request)
	{
		$tenantId = $request->tenant;
		$resource = $request->resource;
		$columns = $request->columns;

		$columns = evavel_json_decode($columns, true);

		if ($resource == 'bookings') {
			alexr_save_tenant_setting($tenantId, $this->meta_key_bookings_columns, $columns);
		}
		else if ($resource == 'customers') {
			alexr_save_tenant_setting($tenantId, $this->meta_key_customers_columns, $columns);
		}
		else if ($resource == 'booking_editor') {
			alexr_save_tenant_setting($tenantId, $this->meta_key_booking_editor, $columns);
		}
		else if ($resource == 'customer_editor') {
			alexr_save_tenant_setting($tenantId, $this->meta_key_customer_editor, $columns);
		}

		return $this->response(['success' => true]);
	}

	/**
	 * Default columns for list of bookings
	 * @return array
	 */
	protected function getDefaultColumnsBookings()
	{
		$key_columns = [
			'type', 'source', 'uuid', 'time',
			'shift_event', 'name',
			'party', 'payment', 'area_table_selected', 'tables',
			'email', 'phone', 'language',
			'country', 'notes',
			'tags', 'spend',
			'reply', 'status'
		];

		$columns = [];
		$order = 1;
		foreach ($key_columns as $key) {
			$columns[] = [
				'column' => $key,
				'enabled' => $key != 'uuid' ? true : false,
				'order' => $order++
			];
		}

		return $columns;
	}

	protected function getColumnsBookings($tenant_id)
	{
		$columns = alexr_get_tenant_setting($tenant_id, $this->meta_key_bookings_columns);

		// Set default columns
		if (!$columns) {
			$columns = $this->getDefaultColumnsBookings();
			alexr_save_tenant_setting($tenant_id, $this->meta_key_bookings_columns, $columns);
		}

		return $columns;
	}

	/**
	 * Default columns for list of customer
	 * @return array
	 */
	protected function getDefaultColumnsCustomers()
	{
		$key_columns = [
			'uuid',
			'name', 'company', 'email', 'phone', 'language', 'country_code',
			'tags', 'birthday', 'last_visit', 'visits',
			'spend', 'spend_per_visit', 'notes'
		];

		$columns = [];
		$order = 1;
		foreach ($key_columns as $key) {
			$columns[] = [
				'column' => $key,
				'enabled' => $key != 'uuid' ? true : false,
				'order' => $order++
			];
		}

		return $columns;
	}

	protected function getColumnsCustomers($tenant_id)
	{
		$columns = alexr_get_tenant_setting($tenant_id, $this->meta_key_customers_columns);

		// Set default columns
		if (!$columns) {
			$columns = $this->getDefaultColumnsCustomers();
			alexr_save_tenant_setting($tenant_id, $this->meta_key_customers_columns, $columns);
		}

		return $columns;
	}

	/**
	 * Field that can be hidden in the booking editor/creator
	 *
	 * @return array
	 */
	protected function getDefaultColumnsBookingEditor()
	{
		$key_columns = [
			'tables', 'notifications', 'country_code', 'spend',
			'edit_booking_with_multiple_tabs',
			'new_booking_with_multiple_tabs',
			'request_phone_new_booking'
		];

		$columns = [];
		foreach ($key_columns as $key) {
			$columns[] = [
				'column' => $key,
				'enabled' => false, // true means to hide the field
			];
		}

		return $columns;
	}

	protected function getColumnsBookingEditor($tenant_id)
	{
		$columns = alexr_get_tenant_setting($tenant_id, $this->meta_key_booking_editor);

		// Set default columns
		if (!$columns) {
			$columns = $this->getDefaultColumnsBookingEditor();
			alexr_save_tenant_setting($tenant_id, $this->meta_key_booking_editor, $columns);
		}

		// Add default columns not exists yet
		$default_columns = $this->getDefaultColumnsBookingEditor();
		foreach($default_columns as $default_column) {
			$found = array_filter($columns, function($column) use($default_column) {
				return $column['column'] == $default_column['column'];
			});
			if (count($found) == 0) {
				$columns[] = $default_column;
			}
		}

		return $columns;
	}

	/**
	 * Field that can be hidden in the customer editor/creator
	 *
	 * @return array
	 */
	protected function getDefaultColumnsCustomerEditor()
	{
		$key_columns = [
			'company', 'country_code'
		];

		$columns = [];
		foreach ($key_columns as $key) {
			$columns[] = [
				'column' => $key,
				'enabled' => false, // true means to hide the field
			];
		}

		return $columns;
	}

	protected function getColumnsCustomerEditor($tenant_id)
	{
		$columns = alexr_get_tenant_setting($tenant_id, $this->meta_key_customer_editor);

		// Set default columns
		if (!$columns) {
			$columns = $this->getDefaultColumnsCustomerEditor();
			alexr_save_tenant_setting($tenant_id, $this->meta_key_customer_editor, $columns);
		}

		return $columns;
	}

	/*protected function refillWithAllColumns($existing_columns, $all_columns)
	{
		// Prepare array with keys, it is easier to search
		$columns = [];
		foreach ($existing_columns as $column){
			$columns[$column['column']] = $column;
		}

		$result = [];
		foreach($all_columns as $item){
			$result[] = isset($columns[$item['column']]) ? $columns[$item['column']] : $item;
		}
		return $result;
	}*/
}
