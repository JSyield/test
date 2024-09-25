<?php

namespace Alexr\Http\Controllers;

use Evavel\Http\Controllers\Controller;
use Evavel\Http\Request\Request;
use Evavel\Query\Query;

class WpUsersController extends Controller
{
	public function index(Request $request)
	{
		// @TODO AUTHORIZATION user

		$tableName = evavel_wp_table_users();

		$perPage = isset($request->params['per_page']) ? $request->params['per_page'] : 25;
		$currentPage = isset($request->params['page']) ? $request->params['page'] : 1;
		$search = isset($request->params['search']) ? $request->params['search'] : null;

		$orderBy = isset($request->params['order']) ? $request->params['order'] : 'display_name';
		$orderBy = empty($orderBy) ? 'display_name' : $orderBy;

		$orderByDirection = isset($request->params['direction']) ? $request->params['direction'] : 'asc';
		$orderByDirection = empty($orderByDirection) ? 'asc' : $orderByDirection;

		$query_count = Query::table($tableName, null, true);

		$query = Query::table($tableName,null,true)
			->select(['ID', 'display_name', 'user_email'])
			->orderBy("{$tableName}.{$orderBy}", $orderByDirection)
			->page($currentPage, $perPage);

		// Filter search
		$this->applySearch($query_count, $search);
		$this->applySearch($query, $search);

		// Do the query
		$total_arr = $query_count->count($perPage);
		$rows = $query->get();

		foreach($rows as $row){
			$row->first_name = get_user_meta($row->ID, 'first_name', true);
			$row->last_name = get_user_meta($row->ID, 'last_name', true);
		}

		return $this->response([
			'total' => $total_arr['count'],
			'resources' => $rows,
			'per_page' => $perPage,
			'page' => $currentPage,
			'total_pages' => $total_arr['pages'],
		]);
	}

	public function user(Request $request)
	{
		$tableName = evavel_wp_table_users();

		$userId = intval($request->userId);

		$wpuser = Query::table($tableName,null,true)
		               ->where('ID', $userId)
		               ->select(['ID', 'display_name', 'user_email'])
		               ->first();

		return $this->response(['success' => true, 'wpuser' => $wpuser]);
	}


	// QUERY FILTERS
	//---------------------------------------------------------

	public function applySearch(Query $query, $search_value)
	{
		if (!$search_value) return $query;

		$this->querySearch($query, ['display_name', 'user_email'], $search_value);
	}

	public function querySearch($query, $where_fields, $search_value)
	{
		$closure = function($query) use($where_fields, $search_value) {
			for ($i = 0; $i < count($where_fields); $i++)
			{
				$w_field = $where_fields[$i];
				if ($i == 0){
					$query->where($w_field, 'like', $search_value);
				} else {
					$query->orWhere($w_field, 'like', $search_value);
				}
			}
			return $query;
		};

		$query->where($closure);
	}

}
