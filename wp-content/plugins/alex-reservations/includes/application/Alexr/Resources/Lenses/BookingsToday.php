<?php

namespace Alexr\Resources\Lenses;

use Alexr\Actions\ChangeDate;
use Alexr\Actions\ChangeStatus;
use Alexr\Filters\BookingDateFilter;
use Alexr\Filters\BookingDateRangeFilter;
use Alexr\Filters\BookingStatusFilter;
use Evavel\Http\Request\LensIndexRequest;
use Evavel\Http\Request\Request;
use Evavel\Resources\LensResource;

class BookingsToday extends LensResource
{
	public function name()
	{
		return __eva('Today Bookings');
	}

	public function uriKey()
	{
		return 'bookings-today';
	}

	public static function query(LensIndexRequest $request, $query)
	{
		$query->where('date', 'like', '2022-04');
		return $query;
	}

	public function filters(Request $request) {
		return [
			new BookingStatusFilter(),
			new BookingDateFilter(),
			new BookingDateRangeFilter(),
		];
	}

	public function actions(Request $request) {
		return [
			new ChangeStatus(),
			new ChangeDate()
		];
	}
}
