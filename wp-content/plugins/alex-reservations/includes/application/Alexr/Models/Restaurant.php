<?php

namespace Alexr\Models;

use Alexr\Enums\BookingStatus;
use Alexr\Models\Traits\CalculateBlockedSlots;
use Alexr\Models\Traits\CalculateBlockedTables;
use Alexr\Models\Traits\CalculateBookingFormDates;
use Alexr\Models\Traits\CalculateBookingMetrics;
use Alexr\Settings\Event;
use Alexr\Settings\General;
use Alexr\Settings\Shift;
use Evavel\Models\Model;
use Evavel\Notifications\Notification;
use Evavel\Notifications\Notifications;
use Evavel\Query\Query;
use Evavel\Support\Str;

class Restaurant extends Model
{
	use CalculateBookingFormDates;
	use CalculateBlockedTables;
	use CalculateBlockedSlots;
	use CalculateBookingMetrics;

	public static $table_name = 'restaurants';
	public static $table_meta = 'restaurant_meta';

	public $appends = [
		'tablesList', 'currency',
		'countUsers', 'countCustomers', 'countBookings'
		//'vipColorCustomer', 'vipColorBooking'
	];

	public $casts = [
		'settings' => 'array'
	];

	public function bookings()
	{
		return $this->hasMany(Booking::class);
	}

	public function users()
	{
		$belongsToMany = $this->belongsToMany(User::class, 'restaurant_user', 'restaurant_id', 'user_id');

		$belongsToMany->addRelationColumns(['role as user_role', 'settings as user_settings']);

		return $belongsToMany;
	}

	public static function booted()
	{
		static::creating(function($model) {
			$model->uuid = Str::uuid();
		});
	}

	public function getVipColorCustomerAttribute() {
		$groups = CTagGroup::where('restaurant_id', $this->id)
		                   ->get();

		foreach($groups as $group){
			if ($group->is_vip === 1) {
				return $group->backcolor;
			}
		}

		return '#000000';
	}

	public function getVipColorBookingAttribute() {
		$groups = BTagGroup::where('restaurant_id', $this->id)
			->get();

		foreach($groups as $group){
			if ($group->is_vip === 1) {
				return $group->backcolor;
			}
		}

		return '#000000';
	}

	public function getCountUsersAttribute() {
		return Query::table('restaurant_user')
		            ->where('restaurant_id', $this->id)
		            ->count();
	}

	public function getCountCustomersAttribute() {
		return Query::table('customers')
            ->where('restaurant_id', $this->id)
			->count();
	}

	public function getCountBookingsAttribute() {
		return Query::table('bookings')
		            ->where('restaurant_id', $this->id)
		            ->count();
	}

	public function getSoundUrlAttribute() {
		return ALEXR_PLUGIN_URL. 'assets/sounds/coin.mp3';
	}

	// SETTINGS

	/**
	 * Get the image logo from the table restaurant_settings mete_key=global (image is base64)
	 * @return null
	 */
	public function getLogoImgAttribute()
	{
		$general = General::where('restaurant_id', $this->id)->first();

		if ($general) {
			return $general->logo_img;
		}

		return null;
	}

	public function getMapIframeAttribute()
	{
		$latitude = $this->latitude;
		$longitude = $this->longitude;

		if (!$latitude || empty($latitude) || !$longitude || empty($longitude)) {
			return null;
		}

		//return null;
		$query_string = sprintf("q=%s,%s&z=15&output=embed&maptype=satellite",
			// latitude, lontitude
			$latitude, $longitude
			//'40.43224895813295', '-3.692393302917481',
		);

		return "//maps.google.com/maps?" . $query_string;
	}

	/**
	 * Get from the settings field
	 * @return mixed|string
	 */
	public function getNoteFromUsAttribute() {
		$settings = $this->settings;
		if (isset($settings['note_from_us'])) {
			return $settings['note_from_us'];
		}
		return '';
	}

	/**
	 * Get from the settings field
	 * @return mixed|string
	 */
	public function setNoteFromUsAttribute($value) {
		$this->saveSetting('note_from_us', $value);
	}

	public function getReservationPolicyAttribute() {
		$settings = $this->settings;
		if (isset($settings['reservation_policy'])) {
			return $settings['reservation_policy'];
		}
		return '';
	}

	public function setReservationPolicyAttribute($value) {
		$this->saveSetting('reservation_policy', $value);
	}

	public function getLatitudeAttribute() {
		$settings = $this->settings;
		if (isset($settings['latitude'])) {
			return $settings['latitude'];
		}
		return null;
	}

	public function setLatitudeAttribute($value) {
		$this->saveSetting('latitude', $value);
	}

	public function getLongitudeAttribute() {
		$settings = $this->settings;
		if (isset($settings['longitude'])) {
			return $settings['longitude'];
		}
		return null;
	}

	public function setLongitudeAttribute($value) {
		$this->saveSetting('longitude', $value);
	}

	public function getTimelineStartAttribute() {
		return alexr_get_dashboard_setting($this->id, 'timeline_start');
	}

	public function setTimelineStartAttribute($value) {
		alexr_save_dashboard_setting($this->id, 'timeline_start', $value);
	}

	public function getTimelineEndAttribute() {
		return alexr_get_dashboard_setting($this->id, 'timeline_end');
	}

	public function setTimelineEndAttribute($value) {
		alexr_save_dashboard_setting($this->id, 'timeline_end', $value);
	}

	protected function saveSetting($setting_name, $value) {
		$settings = $this->settings;
		if (!is_array($settings)){
			$settings = [];
		}
		$settings[$setting_name] = $value;
		$this->settings = $settings;
	}

	/**
	 * Send dashboard notification
	 *
	 * @param Notification $notification
	 *
	 * @return void
	 */
	public function notify( Notification $notification )
	{
		$users = $this->users;

		if ($users) {
			// Skip users administrators, could be it was a user and has been transformed later as administrator
			// but remains a record inside restaurant_user table
			$only_users = $users->filter(function($user) { return $user->role != 'administrator'; });
			Notifications::send($only_users, $notification);
		}

		// Also all administrators should be notified
		// This generates duplicate users
		$admin_users = User::where('role', 'administrator')->get();
		if ($users) {
			Notifications::send($admin_users, $notification);
		}

	}

	public function notifications()
	{
		return $this->hasMany(\Alexr\Models\Notification::class);
	}

	// Enable / disable online bookings for a shift and date y-MM-dd
	//--------------------------------------------------------------------

	/**
	 * Get the meta key for shift id and date
	 * @param $shift_id
	 * @param $date_ymd
	 *
	 * @return string
	 */
	public function metaKeyOnlineBookings($shift_id, $date_ymd)
	{
		return 'online-'.$shift_id.'-'.$date_ymd;
	}

	/**
	 * Check if online bookings are enabled
	 *
	 * @param $shift_id
	 * @param $date_ymd
	 *
	 * @return bool
	 */
	public function isOnlineBookingsEnabled($shift_id, $date_ymd)
	{
		$this->loadMeta();
		return $this->getMeta( $this->metaKeyOnlineBookings($shift_id, $date_ymd) ) != 'no';
	}

	/**
	 * Enabled online bookings
	 *
	 * @param $shift_id
	 * @param $date_ymd
	 *
	 * @return void
	 */
	public function enableOnlineBookings($shift_id, $date_ymd)
	{
		$this->deleteMeta( $this->metaKeyOnlineBookings($shift_id, $date_ymd)  );
		$this->saveMeta();
	}

	/**
	 * Disable online bookings
	 *
	 * @param $shift_id
	 * @param $date_ymd
	 *
	 * @return void
	 */
	public function disableOnlineBookings($shift_id, $date_ymd)
	{
		$this->setMeta( $this->metaKeyOnlineBookings($shift_id, $date_ymd) , 'no' );
		$this->saveMeta();
	}

	// Calculate seatings and tables for a shift and date y-MM-dd
	//--------------------------------------------------------------------

	public function getSeatsOccupied($shift_id, $date)
	{
		$bookings = Booking::where('restaurant_id', $this->id)
			->where('date', $date)
			->where('shift_event_id', $shift_id)
			->get();

		$party_total = 0;
		foreach($bookings as $booking){
			if (!in_array($booking->status, [
				BookingStatus::SELECTED,
				BookingStatus::CANCELLED,
				BookingStatus::DENIED,
				BookingStatus::NO_SHOW
			]))
			{
				$party_total += $booking->party;
			}

		}

		return $party_total;
	}

	public function getSeatsTotal($shift_id, $date)
	{
		$item = Shift::where('id', $shift_id)->first();
		if (!$item) {
			$item = Event::where('id', $shift_id)->first();
		}
		if (!$item){
			return null;
		}

		return $item->totalCovers();
	}

	public function getTablesOccupied($shift_id, $date)
	{
		$bookings = Booking::where('restaurant_id', $this->id)
		                   ->where('date', $date)
		                   ->where('shift_event_id', $shift_id)
		                   ->get();

		$tables_occupied = 0;

		foreach($bookings as $booking){
			if (!in_array($booking->status, [
				BookingStatus::SELECTED,
				BookingStatus::CANCELLED,
				BookingStatus::DENIED,
				BookingStatus::NO_SHOW
			]))
			{
				$tables_occupied += count($booking->tables);
			}
		}

		return $tables_occupied;
	}

	public function getTablesTotal($shift_id, $date)
	{
		$item = Shift::where('id', $shift_id)->first();
		if (!$item) {
			$item = Event::where('id', $shift_id)->first();
		}
		if (!$item){
			return null;
		}

		return $item->totalTables();
	}
}
