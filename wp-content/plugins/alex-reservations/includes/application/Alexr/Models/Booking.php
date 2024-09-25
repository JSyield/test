<?php

namespace Alexr\Models;

use Alexr\Enums\BookingStatus;
use Alexr\Enums\Countries;
use Alexr\Models\Traits\HasSettings;
use Alexr\Models\Traits\ManageAddToCalendar;
use Alexr\Models\Traits\ReturnBookingMessages;
use Alexr\Models\Traits\SendBookingEmailReminders;
use Alexr\Models\Traits\SendBookingEmails;
use Alexr\Models\Traits\SendBookingSms;
use Alexr\Models\Traits\SendBookingSmsReminders;
use Alexr\Models\Traits\StoreEmailSent;
use Alexr\Models\Traits\StoreSmsSent;
use Alexr\Models\Traits\BookingUsePayments;
use Alexr\Settings\Event;
use Alexr\Settings\Shift;
use Alexr\Settings\Traits\HasTimeOptions;
use Alexr\Settings\WidgetForm;
use Evavel\Database\DB;
use Evavel\Models\Model;
use Evavel\Query\Query;
use Evavel\Support\Str;

class Booking extends Model
{
	use HasSettings;
	use HasTimeOptions;
	use SendBookingEmails;
	use SendBookingSms;
	use StoreEmailSent;
	use StoreSmsSent;
	use ReturnBookingMessages;
	use ManageAddToCalendar;
	use SendBookingEmailReminders;
	use SendBookingSmsReminders;
	use BookingUsePayments;

	public static $table_name = 'bookings';
	public static $table_meta = 'booking_meta';
	public static $pivot_tenant_field = 'restaurant_id';

	public $attributes = [
		'party' => 2
	];

	public $appends = [
		'name',
		'tagsList',
		'tablesList', 'tablesSeatsList',
		'isVip', 'isCustomerVip', 'shiftName',
		'isPaid', 'isCardCaptured', 'isForCapturingCard',
		'isPreauthConfirmed',
		'paidAmountFormatted', 'customValues'
	];

	protected $casts = [
		'id' => 'int',
		'party' => 'int',
		'time' => 'int',
		'duration' => 'int',
		'shift' => 'int',
		'custom_fields' => 'array',
		'agree_receive_email' => 'int',
		'agree_receive_sms' => 'int',
	];

	public function restaurant()
	{
		return $this->belongsTo(Restaurant::class);
	}

	public function customer()
	{
		return $this->belongsTo(Customer::class);
	}

	public static function booted()
	{
		//ray('Calling Booking booted');
		static::creating(function($booking){
			//ray('Booted creating booking');
			$booking->uuid = Str::uuid('bo');
			$booking->token = Str::upper(Str::random(8));
		});

		static::saved(function($booking){
			if ($booking->customer) {
				$booking->customer->calculateVisits();
			}
		});
	}

	public function tags()
	{
		return $this->belongsToMany(BTag::class, 'booking_btag', 'booking_id', 'btag_id', 'id', 'id', 'btags');
	}

	public function tables()
	{
		//return $this->belongsToMany(Table::class, 'booking_table', 'booking_id', 'table_id', 'id', 'id', 'tables');

		// ->pivot
		return $this->belongsToMany(Table::class, 'booking_table', 'booking_id', 'table_id', 'id', 'id', 'tables')
		            ->withPivot(['seats']);
	}

	public function tablesWithSeats()
	{
		$tables_seats = [];

		foreach($this->tables as $table) {
			$tables_seats[$table->id] = ['seats' => $table->pivot_seats];
		}

		return $tables_seats;
	}

	// Sincronizar las mesas pero mantener las mesas que no se hayan borrado con sus sillas
	public function syncTablesAndKeepCurrentSeats($tablesIds)
	{
		$current_tables_seats = $this->tablesWithSeats();

		$new_sync = [];
		foreach($tablesIds as $tableId) {
			if (isset($current_tables_seats[$tableId])){
				$new_sync[$tableId] = $current_tables_seats[$tableId];
			} else {
				$new_sync[$tableId] = ['seats' => null];
			}
		}

		$this->tables()->sync($new_sync);
	}

	public function notifications()
	{
		return $this->hasMany(BookingNotification::class);
	}

	public function payments()
	{
		return $this->hasMany(Payment::class);
	}

	public function review()
	{
		return $this->hasOne( BookingReview::class);
	}

	public function getActionsAttribute()
	{
		$list = Action::where('restaurant_id', $this->restaurant_id)
			 ->where('model_type', 'LIKE', '%Booking')
			 //->where('model_type', Booking::class) // no funciona
             ->where('model_id', intval($this->id))
			 ->orderBy('date_created', 'ASC')
			 ->get();

		return $list;
	}

	public function getNameAttribute()
	{
		return $this->first_name . ' ' . $this->last_name;
	}

	public function getTagsListAttribute()
	{
		return $this->tags->map( function($tag) {
			return intval($tag->id);
		})->toArray();
	}

	public function getTagsListNamesAttribute()
	{
		$names =  $this->tagsListNamesArray;
		return implode(', ', $names);
	}

	public function getTagsListNamesArrayAttribute()
	{
		$names =  $this->tags->map( function($tag) { return $tag->name; })->toArray();
		return $names;
	}

	public function getTablesListAttribute()
	{
		return $this->tables->map( function($table) {
			return intval($table->id);
		})->toArray();
	}

	// result: {'306': ["1", "2", "3"], '307': ["4", "5", "6"]}
	// En javascript llevan las keys como texto, aunque use numeros
	public function getTablesSeatsListAttribute()
	{
		$list = [];
		foreach($this->tables as $table) {
			$list[intval($table->id)] = $table->pivot_seats ? explode(',', $table->pivot_seats) : [];
		}
		return $list;

		//return $this->tables->map( function($table) {
		//	return intval($table->id) . '[' . $table->pivot_seats . ']';
		//})->toArray();
	}

	public static function tablesNamesWithAreaArray($list)
	{
		return Table::whereIn('id', $list)
            ->get()
            ->map(function($table) {
	            return $table->name.'('.$table->area->name.')';
            })
            ->toArray();
	}

	public function getTablesListNamesAttribute()
	{
		$names = $this->tables->map( function($table) { return $table->name; })->toArray();
		return implode(', ', $names);
	}

	public function getIsVipAttribute() {
		$list = $this->tags->filter(function($tag){
			return $tag->group->is_vip;
		})->toArray();

		return count($list) > 0;
	}

	public function getIsCustomerVipAttribute() {
		$customer = $this->customer;
		if (!$customer) return false;

		return $customer->isVip;
	}

	public function getShiftNameAttribute() {
		$id = $this->shift_event_id;
		if ($id > 0){
			$shift = Shift::where('id', $id)->first();
			if ($shift) return $shift->name;
			$event = Event::where('id', $id)->first();
			if ($event) return $event->name;
		}
		return '-';
	}

	/**
	 * Configuration for the view booking page
	 * depends on the widget where this booking was managed
	 * @return false[]
	 */
	public function getViewLayoutAttribute()
	{
		$widget_id = $this->widget_id;
		if (!$widget_id) {
			return [ 'show_services_duration' => false ];
		}

		$widget_form = WidgetForm::where('id', $widget_id)->first();
		if (!$widget_form) {
			return [ 'show_services_duration' => false ];
		}

		$show_services_duration = isset($widget_form->form_config['show_services_duration']) ? $widget_form->form_config['show_services_duration'] : 'yes';
		$show_services_duration = $show_services_duration == 'no' ? false : true;

		return [ 'show_services_duration' => $show_services_duration ];
	}

	public function getAreaSelectedNameAttribute()
	{
		if ($this->area_selected_id == null) return '';
		$area = Area::find($this->area_selected_id);
		return $area != null ? $area->name : '';
	}

	public function getTableSelectedNameAttribute()
	{
		if ($this->table_selected_id == null) return '';
		$table = Table::find($this->table_selected_id);
		return $table != null ? $table->name : '';
	}

	/**
	 * Get custom fields formatted in array
	 * Has the full field used and the value submitted
	 * @return void
	 */
	public function getCustomValuesAttribute()
	{
		$fields = $this->custom_fields;

		if ($fields == null) {
			return null;
		}

		try {
			if (is_string($fields)){
				$fields = json_decode($fields, true);
			}

			if (!is_array($fields)) {
				return null;
			}

			// Decode custom fields of type: options, select
			foreach($fields as $key => $field)
			{
				if ($field['field']['type'] == 'select' || $field['field']['type'] == 'options')
				{
					$value = $fields[$key]['value'];
					if (is_string($value))
					{
						$value = str_replace('\"', '"', $value);
						$value = json_decode($value, true);
						$fields[$key]['value'] = $value;
					}
				}
			}

		} catch (\Exception $e) {
			$fields = null;
		}

		return $fields;
	}

	public function customFieldsEmailHtml()
	{
		$values = $this->customValues;
		if ($values == null) return '';
		if (!is_array($values)) return '';

		$html = '';

		foreach($values as $key => $item)
		{
			if (!isset($item['field']) || !isset($item['value'])) {
				continue;
			}
			if ($item['field']['type'] == 'text'){
				$html .= "<div>{$item['value']}</div>";
			}
			else if ($item['field']['type'] == 'textarea'){
				$html .= "<div>{$item['value']}</div>";
			}
			else if ($item['field']['type'] == 'checkbox'){
				$val = ($item['value'] === true || $item['value'] == 'true') ? '[X]' : '[-]';
				$html .= "<div>{$val} {$item['field']['name']}</div>";
			}
			else if ($item['field']['type'] == 'select') {
				$html .= "<div>{$item['value']['value']}</div>";
			}
			else if ($item['field']['type'] == 'options') {
				foreach($item['value'] as $key => $val){
					if ($val === true || $val == 'true'){
						$html .= "<div>[X] {$key}</div>";
					}
				}
			}
		}

		return $html;
	}

	public function customFieldsCsvFormat()
	{
		$html = $this->customFieldsEmailHtml();
		$html = str_replace('<div>', '', $html);
		$html = str_replace('</div>', "\r\n", $html);
		return $html;
	}

	public function toCustomArray() {
		return self::toDataArray($this);
	}

	/**
	 * Used for mybooking view
	 * @param $booking
	 *
	 * @return array
	 */
	public static function toDataArray($booking) {
		$review = $booking->review;
		if ($review){
			$review = $review->toArray();
		}
		return [
			'restaurant' => $booking->restaurant->name,
			'restaurant_id' => $booking->restaurant_id,
			//'date_format' => $booking->restaurant->date_format,
			//'time_format' => $booking->restaurant->time_format,
			'timezone' => $booking->restaurant->timezone,
			'uuid' => $booking->uuid,
			'id' => $booking->id,
			'name' => $booking->name,
			'email' => $booking->email,
			'status' => $booking->status,
			'status_label' => \Alexr\Enums\BookingStatus::label($booking->status),
			'date' => $booking->date,
			'party' => $booking->party,
			'time' => $booking->time,
			'duration' => $booking->duration,
			'language' => $booking->language,
			'review' => $review,
			'notes' => alexr_transform_new_lines_to_br($booking->notes),
			'tags' => $booking->getTagsNames(),
			'custom_fields' => $booking->customValues,
			'gateway' => $booking->gateway,
			'area_selected_id' => $booking->area_selected_id,
			'table_selected_id' => $booking->table_selected_id,
			'area_selected_name' => $booking->areaSelectedName,
			'table_selected_name' => $booking->tableSelectedName
		];
	}

	/**
	 * Data for CSV exporting
	 * @return array
	 */
	public function toCsvArray($lang = 'en') {
		$data =  [
			'id' => $this->id,
			'uuid' => $this->uuid,
			'type' => $this->type,
			'status' => __eva_x( BookingStatus::label($this->status), $lang ),
			'date' => $this->date,
			'name' => $this->name,
			'email' => $this->email,
			'language' => $this->language,
			'party' => $this->party,
			'shift' => $this->shiftName,
			'time_24h' => evavel_seconds_to_Hm($this->time),
			'time_12h' => evavel_seconds_to_Hm12($this->time),
			'duration' => evavel_seconds_to_duration($this->duration),
			'phone' => '('.$this->dial_code.')'.$this->phone,
			'country' => Countries::country($this->country_code),
			'tables' => $this->tablesListNames,
			'tags' => $this->tagsListNames,
			'notes' => $this->notes . "\n" . $this->private_notes,
			'custom_fields' => $this->customFieldsCsvFormat()
		];



		return $data;
	}

	// STRIPE
}
