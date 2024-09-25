<?php

namespace Alexr\Http\Traits;

use Evavel\Http\Request\Request;
use Evavel\Query\Query;

trait GeneralMetricsController {

	public function generalMetrics(Request $request)
	{
		$restaurants = Query::table('restaurants')
		                    ->select(['id','name'])
		                    ->get();

		$list = [];
		foreach($restaurants as $restaurant)
		{
			$list['restaurant-'.$restaurant->id] = [
				'name' => $restaurant->name,
				'bookings' => []
			];

			for ($month = 0; $month < 6; $month++){
				$y_month = evavel_date_now()->addMonth(-$month)->format('Y-m');
				$total = Query::table('bookings')
					->where('restaurant_id', $restaurant->id)
					->where('date', 'like', $y_month.'%')
					->count();
				$list['restaurant-'.$restaurant->id]['bookings'][$y_month] = $total;
			}
		}

		$data = $list;
		return $this->response(['success' => true, 'data' => $data]);
	}
}
