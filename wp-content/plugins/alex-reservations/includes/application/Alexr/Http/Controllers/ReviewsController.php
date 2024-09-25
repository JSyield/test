<?php

namespace Alexr\Http\Controllers;

use Alexr\Models\BookingReview;
use Evavel\Database\DB;
use Evavel\Http\Controllers\Controller;
use Evavel\Http\Request\Request;
use Evavel\Query\Query;

class ReviewsController extends Controller {

	/**
	 * Return list of reviews
	 * Can return the booking attached if parameter with_bookings=yes is passed through
	 *
	 * @param Request $request
	 *
	 * @return \WP_REST_Response
	 */
	public function index(Request $request)
	{
		$date = $request->date;
		$tenantId = $request->tenantId();

		// if yes then will return also the booking attached to each review
		$withBookings = $request->with_bookings;

		$reviews = [];

		// One day
		if (preg_match('#^\d{4}-\d{2}-\d{2}$#', $date))
		{
			$reviews = BookingReview::where('restaurant_id', $tenantId)
						->where('date_created', 'like', $date.'%');
		}

		// One month
		else if (preg_match('#^\d{4}-\d{2}$#', $date))
		{
			$reviews = BookingReview::where('restaurant_id', $tenantId)
                        ->where('date_created', 'LIKE', $date.'%');
		}

		// Range dates
		else if (preg_match('#^(\d{4}-\d{2}-\d{2})-(\d{4}-\d{2}-\d{2})$#', $date, $matches)) {
			$date1 = $matches[1];
			$date2 = $matches[2];

			$reviews = BookingReview::where('restaurant_id', $tenantId)
	                    ->where('date_created', '>=', $date1.' 00:00:00')
						->where('date_created', '<=', $date2.' 23:59:59');
		}

		//Query::setDebug(true);
		Query::setCache(true);

		$reviews = $reviews->orderBy('date_created', 'ASC')->get()->toArray();

		// Add booking to every review
		if ($withBookings == 'yes')
		{
			$list = [];
			foreach($reviews as $review)
			{
				$item = $review->toArray();
				$item['booking'] = $review->booking;
				$list[] = $item;
			}
			$reviews = $list;
		}

		return $this->response([
			'reviews' => $reviews
		]);
	}
}
