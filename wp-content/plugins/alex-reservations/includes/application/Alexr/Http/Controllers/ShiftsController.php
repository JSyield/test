<?php

namespace Alexr\Http\Controllers;

//use Alexr\Models\Shift;
use Alexr\Settings\ClosedSlot;
use Alexr\Settings\Event;
use Alexr\Settings\Shift;
//use Carbon\Carbon;
use Evavel\Http\Controllers\Controller;
use Evavel\Http\Request\Request;

class ShiftsController extends Controller
{
	/**
	 * Get all shifts and events and slots blocked for a date
	 * @param Request $request
	 *
	 * @return \WP_REST_Response
	 */
	public function forDate(Request $request)
	{
		$tenantId = $request->tenantId();
		if (!$tenantId) {
			return $this->response(['success' => false, 'error' => __eva('Tenant is not valid.')]);
		}

		$date = $request->date;

		$shifts = Shift::where(evavel_tenant_field(), $tenantId)->get()->toArray();
		$events = Event::where(evavel_tenant_field(), $tenantId)->get()->toArray();


		$services = [];
		foreach($shifts as $shift) {
			if ($shift->isDateBookableForShift($date)){
				$services[] = $shift;
			}
		}
		foreach($events as $event){
			if ($event->isDateBookableForEvent($date)){
				$services[] = $event;
			}
		}

		$selected = null;
		$closedSlots = ClosedSlot::where(evavel_tenant_field(), $tenantId)->first();
		if ($closedSlots) {
			$selected = $closedSlots->{$date};
		}
		if (!$selected){
			$selected = [];
		}

		//ray($services);

		return $this->response([
			'success' => true,
			'services' => $services,
			'selected' => $selected,
		]);
	}

	public function saveForDate(Request $request)
	{
		$tenantId = $request->tenantId();
		if (!$tenantId) {
			return $this->response(['success' => false, 'error' => __eva('Tenant is not valid.')]);
		}

		$date = $request->date;
		$selected = $request->selected;

		$closedSlots = ClosedSlot::where(evavel_tenant_field(), $tenantId)->first();
		if (!$closedSlots){
			$closedSlots = ClosedSlot::create([
				evavel_tenant_field() => $tenantId
			]);
		}

		$closedSlots->{$date} = json_decode($selected, true);

		$meta_value = $closedSlots->attributes['meta_value'];
		$closedSlots->attributes['meta_value'] = $this->cleanMetaValue($meta_value);

		$closedSlots->save();

		return $this->response(['success' => true]);
	}

	public function cleanMetaValue($meta_value) {
		$new_meta_value = [];
		foreach($meta_value as $date => $value) {
			if (!empty($value)){
				$new_meta_value[$date] = $value;
			}
		}
		return $new_meta_value;
	}

	public function getDatesWithSlotsClosed(Request $request)
	{
		$tenantId = $request->tenantId();
		if (!$tenantId) {
			return $this->response(['success' => false, 'error' => __eva('Tenant is not valid.')]);
		}

		$range = $request->range;
		$first_date = substr($range, 0, 10);
		$last_date = substr($range, 11, 10);

		// For each date check if it is in the range and if it has some slot
		$closedSlots = ClosedSlot::where(evavel_tenant_field(), $tenantId)->first();
		$dates_with_closed_slots = [];
		if ($closedSlots)
		{
			$meta_value = json_decode($closedSlots->original['meta_value'], true);
			foreach($meta_value as $date => $slots) {
				if (is_array($slots) && count($slots) > 0 && $date >= $first_date && $date <= $last_date) {
					$dates_with_closed_slots[] = $date;
				}
			}
		}

		return $this->response([
			'success' => true,
            'dates' => $dates_with_closed_slots,
			'dates_with_services' => $this->getDatesWithServicesAttached($tenantId, $first_date, $last_date)
		]);
	}

	public function getDatesWithServicesAttached($tenantId, $first_date, $last_date)
	{
		$list_dates = [];

		$stop_counting = false;
		//$day = Carbon::createFromFormat('Y-m-d H:i:s', $first_date.' 12:00:00');
		$day = evavel_date_createFromFormat('Y-m-d H:i:s', $first_date.' 12:00:00');
		$list_dates[] = $day->format('Y-m-d');

		$count = 1;
		while (!$stop_counting){
			$day = $day->addDays(1);
			$list_dates[] = $day->format('Y-m-d');
			if ($day->format('Y-m-d') >= $last_date || $count++ > 365) {
				$stop_counting = true;
			}
		}

		// Para cada servicio mirar si la fecha es permitida
		$shifts = Shift::where(evavel_tenant_field(), $tenantId)->get(); //->toArray();
		$events = Event::where(evavel_tenant_field(), $tenantId)->get(); //->toArray();

		$result = [];
		foreach($list_dates as $date){
			$result[$date] = [];
			foreach($shifts as $shift){
				if ($shift->isDateBookable($date)) {
					$result[$date][] = ['id' => $shift->id, 'color' => $shift->color];
				}
			}
			foreach($events as $event){
				if ($event->isDateBookable($date)) {
					$result[$date][] = ['id' => $event->id, 'color' => $event->color];
				}
			}
		}


		return $result;
	}

	/*public function index(Request $request)
	{
		$tenant = $request->tenantId();

		if (!$tenant) {
			return $this->response([
				'shifts' => []
			]);
		}

		return $this->response([
			'shifts' => Shift::where('restaurant_id', $tenant)->get()->toArray()
		]);
	}

	public function save(Request $request, $resourceId)
	{
		$tenant = $request->tenantId();
		$params = $request->body_params;

		$shift = Shift::find($resourceId);

		// @todo: que respuesta enviar
		//if (!$shift) { evavel_403(); }

		foreach($params as $key => $value) {
			$shift->{$key} = evavel_json_decode($value);
		}

		$shift->save();

		return $this->response([]);
	}

	public function create(Request $request)
	{
		$shift = new Shift;

		$shift->restaurant_id = $request->tenantId();

		$shift->setupDefaultValues()->save();

		return $this->response([
			'shift' => $shift
		]);
	}*/


}
