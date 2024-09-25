<?php

namespace Alexr\Listeners;

use Alexr\Events\EventBookingSeatsChanged;
use Alexr\Events\EventBookingStatusChangedByCustomer;
use Alexr\Events\EventBookingCreated;
use Alexr\Events\EventBookingModified;
use Alexr\Events\EventBookingStatusChanged;
use Alexr\Events\EventBookingTablesChanged;
use Alexr\Models\Action;
use Alexr\Models\Booking;
use Alexr\Models\Customer;
use Alexr\Models\Table;
use Alexr\Models\User;

class ListenBookingEvents
{
	protected $event;

	public function __construct() {}

	public function handle($event)
	{
		$this->event = $event;

		switch (get_class($event)) {

			// Si lo crea el customer user = null
			case EventBookingCreated::class:
				//ray('CLASS EventBookingCreated');
				if ($event->user) {
					$this->actionUserCreatedBooking($event->booking, $event->user);
				} else {
					$this->actionCustomerCreatedBooking($event->booking);
				}
				break;

			// Si lo hace el customer user = null
			case EventBookingModified::class:
				//ray('CLASS EventBookingModified');
				if ($event->user) {
					$this->actionUserModifiedBooking($event->booking, $event->user, $event->old_attributes);
				} else {
					$this->actionCustomerModifiedBooking($event->booking, $event->old_attributes);
				}
				break;

			// Puede cambiar status desde el dashboard -> user existe, fromEmail = false
			// Puede cambiar status desde el email -> user no existe, fromEmail = true
			case EventBookingStatusChanged::class:
				//ray('CLASS EventBookingStatusChanged');
				if ($event->fromEmail) {
					$this->actionUserChangedStatusFromEmail($event->booking, $event->old_status, $event->new_status);
				} else {
					$this->actionUserChangedStatus($event->booking, $event->user, $event->old_status, $event->new_status);
				}
				break;

			// Solo lo puede hacer desde el floor plan
			case EventBookingTablesChanged::class:
				//ray('CLASS EventBookingTablesChanged'); ray($event->old_tables); ray($event->new_tables);
				$this->actionUserChangedTables($event->booking, $event->user, $event->old_tables, $event->new_tables);
				break;

			// Solo lo puede hacer desde el floor plan
			case EventBookingSeatsChanged::class:
				$this->actionUserChangedSeats($event->booking, $event->user, $event->old_seats, $event->new_seats);
				break;

			// El customer desde la booking view
			case EventBookingStatusChangedByCustomer::class:
				//ray('CLASS EventBookingStatusChangedByCustomer');
				$this->actionCustomerChangedStatus($event->booking, $event->old_status, $event->new_status);
				break;
		}

		//ray($event->booking);
		//ray($event->user);
	}

	/*
		Customer crea reserva
		Customer modifica reserva
		Customer cancela reserva
		Customer modifica reserva
		User crea reserva
		User modifica reserva
		User actualiza status
		User cambia mesas
		User cambia status en el frontal
	*/

	protected function actionCustomerCreatedBooking(Booking $booking)
	{
		Action::create([
			'name' => 'Created',
			'event_type' => EventBookingCreated::class,
			'restaurant_id' => $booking->restaurant_id,
			'agent_type' => Customer::class,
			'agent_id' => $booking->customer_id,
			'agent_name' => $this->getCustomerName($booking),
			'model_type' => Booking::class,
			'model_id' => $booking->id,
			'original' => [],
			'changes' => $this->getAttributes($booking)
		]);
	}

	protected function actionUserCreatedBooking(Booking $booking, User $user)
	{
		Action::create([
			'name' => 'Created',
			'event_type' => EventBookingCreated::class,
			'restaurant_id' => $booking->restaurant_id,
			'agent_type' => User::class,
			'agent_id' => $user->id,
			'agent_name' => $this->getUserName($user),
			'model_type' => Booking::class,
			'model_id' => $booking->id,
			'original' => [],
			'changes' => $this->getAttributes($booking)
		]);
	}

	protected function actionCustomerModifiedBooking(Booking $booking, $old_attributes)
	{
		Action::create([
			'name' => 'Modified',
			'event_type' => EventBookingModified::class,
			'restaurant_id' => $booking->restaurant_id,
			'agent_type' => Customer::class,
			'agent_id' => $booking->customer_id,
			'agent_name' => $this->getCustomerName($booking),
			'model_type' => Booking::class,
			'model_id' => $booking->id,
			'original' => $old_attributes,
			'changes' => $this->getAttributes($booking)
		]);
	}

	protected function actionUserModifiedBooking(Booking $booking, User $user, $old_attributes)
	{
		Action::create([
			'name' => 'Modified',
			'event_type' => EventBookingModified::class,
			'restaurant_id' => $booking->restaurant_id,
			'agent_type' => User::class,
			'agent_id' => $user->id,
			'agent_name' => $this->getUserName($user),
			'model_type' => Booking::class,
			'model_id' => $booking->id,
			'original' => $old_attributes,
			'changes' => $this->getAttributes($booking)
		]);
	}

	protected function actionUserChangedStatus(Booking $booking, User $user, $old_status, $new_status)
	{
		Action::create([
			'name' => 'Changed status',
			'event_type' => EventBookingStatusChanged::class,
			'restaurant_id' => $booking->restaurant_id,
			'agent_type' => User::class,
			'agent_id' => $user->id,
			'agent_name' => $this->getUserName($user),
			'model_type' => Booking::class,
			'model_id' => $booking->id,
			'original' => ['status' => $old_status],
			'changes' => ['status' => $new_status]
		]);
	}

	protected function actionUserChangedStatusFromEmail(Booking $booking, $old_status, $new_status)
	{
		Action::create([
			'name' => 'Changed status',
			'event_type' => EventBookingStatusChanged::class,
			'restaurant_id' => $booking->restaurant_id,
			'agent_type' => User::class,
			'agent_id' => null,
			'agent_name' => 'Admin user',
			'model_type' => Booking::class,
			'model_id' => $booking->id,
			'original' => ['status' => $old_status],
			'changes' => ['status' => $new_status]
		]);
	}

	protected function actionUserChangedTables(Booking $booking, User $user, $old_tables, $new_tables)
	{
		$old_tables_names = [];
		$new_tables_names = [];

		if (is_array($old_tables)) {
			$old_tables_names = Booking::tablesNamesWithAreaArray($old_tables);
			//$old_tables_names = $this->getTablesNames($old_tables);
		}

		if (is_array($new_tables)) {
			$new_tables_names = Booking::tablesNamesWithAreaArray($new_tables);
			//$new_tables_names = $this->getTablesNames($new_tables);
		}

		Action::create([
			'name' => 'Changed tables',
			'event_type' => EventBookingTablesChanged::class,
			'restaurant_id' => $booking->restaurant_id,
			'agent_type' => User::class,
			'agent_id' => $user->id,
			'agent_name' => $this->getUserName($user),
			'model_type' => Booking::class,
			'model_id' => $booking->id,
			'original' => ['tables_id' => $old_tables, 'tables_names' => $old_tables_names],
			'changes' => ['tables_id' => $new_tables, 'tables_names' => $new_tables_names]
		]);
	}

	protected function getTableNameWithAreaName(Booking $booking, $tableId) {

		static $results = [];

		if (isset($results[$booking->restaurant_id])) {
			$tables = $results[$booking->restaurant_id];
		} else {
			$tables = Table::where('restaurant_id', $booking->restaurant_id)->get();
			$results[$booking->restaurant_id] = $tables;
		}

		foreach ($tables as $table) {
			if ($table->id == $tableId) {
				return $table->name.'('.$table->area->name.')';
			}
		}
		return 'NoName';
	}

	protected function actionUserChangedSeats(Booking $booking, User $user, $old_seats, $new_seats)
	{
		//ray('GRABAR ACCION Changed Seats');
		//ray($old_seats);
		//ray($new_seats);

		$old_tables_names = [];
		$new_tables_names = [];

		foreach($old_seats as $tableId => $seats) {
			$table_name = $this->getTableNameWithAreaName($booking, $tableId);
			$old_tables_names[$table_name] = $seats;
		}

		foreach($new_seats as $tableId => $seats) {
			$table_name = $this->getTableNameWithAreaName($booking, $tableId);
			$new_tables_names[$table_name] = $seats;
		}

		//ray($old_tables_names);
		//ray($new_tables_names);

		Action::create([
			'name' => 'Changed tables seats',
			'event_type' => EventBookingSeatsChanged::class,
			'restaurant_id' => $booking->restaurant_id,
			'agent_type' => User::class,
			'agent_id' => $user->id,
			'agent_name' => $this->getUserName($user),
			'model_type' => Booking::class,
			'model_id' => $booking->id,
			'original' => ['tables_seats_id' => $old_seats, 'tables_seats_names' => $old_tables_names],
			'changes' => ['tables_seats_id' => $new_seats, 'tables_seats_names' => $new_tables_names]
		]);
	}

	protected function actionCustomerChangedStatus(Booking $booking, $old_status, $new_status)
	{
		Action::create([
			'name' => 'Changed tables customer',
			'event_type' => EventBookingStatusChangedByCustomer::class,
			'restaurant_id' => $booking->restaurant_id,
			'agent_type' => Customer::class,
			'agent_id' => $booking->customer_id,
			'agent_name' => $this->getCustomerName($booking),
			'model_type' => Booking::class,
			'model_id' => $booking->id,
			'original' => ['status' => $old_status],
			'changes' => ['status' => $new_status]
		]);
	}

	protected function getUserName(User $user) {
		$name = $user->first_name;
		if ($user->last_name) {
			$name .= (' ' . $user->last_name);
		}
		if (strlen($name) < 2) {
			if ($user->name && strlen($user->name) > 2) {
				$name = $user->name;
			}
		}
		return $name;
	}

	protected function getCustomerName(Booking $booking) {
		$name = $booking->first_name;
		if ($booking->last_name) {
			$name .= (' ' . $booking->last_name);
		}
		return $name;
	}

	protected function getTablesNames($list) {
		$list = array_map(function($id){
			return intval($id);
		}, $list);

		return Table::whereIn('id', $list)
             ->get()
             ->map(function($table) {
				 return $table->name.'('.$table->area->name.')';
			 })
             ->toArray();
	}

	protected function getAttributes($booking) {
		$attributes = $booking->attributes;
		$tables_id = $booking->tablesList;
		$attributes['tables_id'] = $tables_id;
		$attributes['tables_names'] = Booking::tablesNamesWithAreaArray($tables_id);
		$attributes['tags_names'] = $booking->tagsListNamesArray;
		return $attributes;
	}

	public static function getOriginal($booking) {
		$attributes = $booking->original;
		$tables_id = $booking->tablesList;
		$attributes['tables_id'] = $tables_id;
		$attributes['tables_names'] = Booking::tablesNamesWithAreaArray($tables_id);
		$attributes['tags_names'] = $booking->tagsListNamesArray;
		return $attributes;
	}
}
