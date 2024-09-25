<?php

namespace Alexr\Http\Traits;

use Alexr\Models\Booking;
use Alexr\Models\Restaurant;
use Evavel\Eva;
use Evavel\Http\Request\Request;

trait DownloadCsvController {

	public function downloadCSV(Request $request)
	{
		$date = $request->date;
		$tenantId = $request->tenantId();

		$user = Eva::make('user');
		if (!$user->canExport('bookings', $tenantId)){
			return $this->response([ 'success' => false, 'error' => __eva("You cannot export data.") ]);
		}

		$restaurant = Restaurant::find($tenantId);
		if (!$restaurant){
			return $this->response(['success' => false, 'error' => __eva('Invalid restaurant')]);
		}

		// Get the list of ids to export
		$list_ids = $request->list;
		if (empty($list_ids)) {
			return $this->response(['success' => false, 'error' => __eva('No data to download.')]);
		}
		$list_ids = explode(',', $list_ids);

		// Prepare the booking
		$bookings = Booking::whereIn('id', $list_ids)->get();

		$list = [];
		foreach($bookings as $booking){
			$list[] = $booking->toCsvArray();
		}

		if (empty($list)) {
			return $this->response(['success' => false, 'error' => __eva('No data to download.')]);
		}

		$list = $this->mapWithCustomColumns($tenantId, $list);

		return $this->response(['success' => 'true', 'data' => $this->convertToCsv($list)]);
	}

	protected function mapWithCustomColumns($tenantId, $list)
	{
		// Tener en cuenta las columnas visibles
		// Columns to use
		$key_columns = [
			'type' => 'type',
			'uuid' => 'uuid',
			'time' => 'time',
			'shift_event' => 'shift',
			'name' => 'name',
			'party' => 'party',
			'payment' => null,
			'area_table_selected' => null,
			'tables' => 'tables',
			'email' => 'email',
			'phone' => 'phone',
			'language' => 'language',
			'country' => 'country',
			'notes' => 'notes',
			'tags' => 'tags',
			'spend' => null,
			'reply' => null,
			'status' => 'status'
		];

		$columns = alexr_get_tenant_setting($tenantId, 'bookings_columns', false);

		usort($columns, function($a, $b) {
			return $b->order <= $a->order;
		});

		$new_list = [];

		foreach($list as $row)
		{
			$new_row = [];
			foreach($columns as $column)
			{
				if (!$column->enabled) continue;

				$column_name = $column->column;
				$key_field = $key_columns[$column_name];
				if (!$key_field) continue;

				if ($column_name == 'time') {
					$new_row['time_24h'] = $row['time_24h'];
					$new_row['time_12h'] = $row['time_12h'];
				}
				else if (isset($row[$key_field])) {
					$new_row[$column_name] = $row[$key_field];
				}
			}
			$new_list[] = $new_row;
		}

		usort($new_list, function($a, $b) {
			if (!isset($b['time_24h'])) return 0;
			return $b['time_24h'] <= $a['time_24h'];
		});

		return $new_list;
	}

	public function printPDF(Request $request)
	{
		$date = $request->date;
		$lang = $request->lang;
		$tenantId = $request->tenantId();

		$user = Eva::make('user');
		if (!$user->canExport('bookings', $tenantId)){
			return $this->response([ 'success' => false, 'error' => __eva("You cannot print data.") ]);
		}

		$restaurant = Restaurant::find($tenantId);
		if (!$restaurant){
			return $this->response(['success' => false, 'error' => __eva('Invalid restaurant')]);
		}

		// Get the list of ids to export
		$list_ids = $request->list;
		if (empty($list_ids)) {
			return $this->response(['success' => false, 'error' => __eva('No data to download.')]);
		}
		$list_ids = explode(',', $list_ids);

		// Prepare the booking
		$bookings = Booking::whereIn('id', $list_ids)->get();

		$list = [];
		foreach($bookings as $booking){
			$list[] = $booking->toCsvArray();
		}

		$list = $this->mapWithCustomColumns($tenantId, $list);

		$html = __eva("This is the FREE version, you need to upgrade to the PRO version");
		if (defined("ALEXR_PRO_PLUGIN_DIR"))
		{
			$bookings = $list;
			ob_start();
			include ALEXR_PRO_PLUGIN_DIR . 'includes-pro/dashboard/templates/pdf/bookings.php';
			$html = ob_get_clean();
		}

		return $this->response(['success' => true, 'html' => $html]);
	}

}
