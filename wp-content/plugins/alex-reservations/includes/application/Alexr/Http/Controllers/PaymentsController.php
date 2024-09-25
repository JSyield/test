<?php

namespace Alexr\Http\Controllers;

use Alexr\Models\Booking;
use Alexr\Models\Customer;
use Alexr\Models\Payment;
use Evavel\Eva;
use Evavel\Http\Controllers\Controller;
use Evavel\Http\Request\Request;
use Evavel\Query\Query;

class PaymentsController extends Controller
{
	public function index(Request $request)
	{
		$user = Eva::make('user');
		if (!$user->canManage($request->tenant)){
			return $this->response([ 'success' => false, 'error' => __eva("You cannot access.") ]);
		}

		$tableName = 'payments';
		$tenantId = $request->tenantId();

		$perPage = isset($request->params['per_page']) ? $request->params['per_page'] : 25;
		$currentPage = isset($request->params['page']) ? $request->params['page'] : 1;
		$orderBy = 'order_date';
		$orderByDirection = 'DESC';

		// When using Query does not have access to $appends parameter of the model
		$query_count = Query::table($tableName)->where('restaurant_id', $tenantId);

		$query = Payment::where('restaurant_id', $tenantId)
		                 ->with('booking')
		                 ->orderBy("{$tableName}.{$orderBy}", $orderByDirection)
		                 ->page($currentPage, $perPage);

		// Do the query
		$total_arr = $query_count->count($perPage);
		$rows = $query->get()->toArray();

		return $this->response([
			'total' => $total_arr['count'],
			'resources' => $rows,
			'per_page' => $perPage,
			'page' => $currentPage,
			'total_pages' => $total_arr['pages'],
		]);
	}
}
