<?php

namespace Alexr\Http\Controllers;

use Alexr\Models\BTagGroup;
use Alexr\Models\CTagGroup;
use Alexr\Models\Restaurant;
use Alexr\Models\Role;
use Alexr\Settings\EmailConfig;
use Alexr\Settings\EmailTemplate;
use Alexr\Settings\Shift;
use Alexr\Settings\WidgetForm;
use Alexr\Settings\WidgetMessage;
use Evavel\Http\Controllers\Controller;
use Evavel\Http\Request\Request;

class WizardController extends Controller {

	protected $restaurantId;

	public function index(Request $request)
	{
		return $this->response([
			'success' => true,
			'tags' => alexr_config('tags'),
		]);
	}

	public function save(Request $request)
	{
		$values = $request->values;
		$values = json_decode($values);

		$this->storeRestaurant($values->Restaurant)
			->storeShift($values->Shift)
			->storeBookings($values->Bookings)
			->storeCustomers($values->Customers)
			->storeOtherSettings();

		return $this->response(['success' => true]);
	}

	protected function storeRestaurant( $data )
	{
		$restaurant = Restaurant::where('name', 'MyRestaurantName')->first();

		if ($restaurant)
		{
			$this->restaurantId = $restaurant->id;

			$restaurant->name = $data->name;
			$restaurant->timezone = $data->timezone;
			$restaurant->language = $data->language;
			$restaurant->save();

			alexr_set_only_active_language($data->language);
		}
		return $this;
	}

	protected function storeShift( $data )
	{
		if (!$this->restaurantId) return;

		$shifts_count = Shift::where('restaurant_id', $this->restaurantId)->get()->count();
		if ($shifts_count > 0) return;

		$shift = new Shift;
		$shift->restaurant_id = $this->restaurantId;
		$shift->setupDefaultValues();

		$fields = [
			'name', 'start_date', 'end_date',
			'min_covers_reservation', 'max_covers_reservation',
			'first_seating', 'last_seating', 'availability_total'
		];

		foreach($fields as $field) {
			$shift->{$field} = $data->{$field};
		}

		// Append all week days to the shift
		$shift->days_of_week = [
			'sun' => true,
			'mon' => true,
			'tue' => true,
			'wed' => true,
			'thu' => true,
			'fri' => true,
			'sat' => true
		];

		$shift->save();

		return $this;
	}

	protected function storeBookings( $data )
	{
		if (!$this->restaurantId) return;

		// Attach new Booking groups with tags for restaurant
		foreach($data as $group_name) {
			BTagGroup::addPredefinedGroup($group_name, $this->restaurantId);
		}

		return $this;
	}

	protected function storeCustomers( $data )
	{
		if (!$this->restaurantId) return;

		// Attach new Booking groups with tags for restaurant
		foreach($data as $group_name) {
			CTagGroup::addPredefinedGroup($group_name, $this->restaurantId);
		}

		return $this;
	}

	protected function storeOtherSettings()
	{
		// Store any other settings needed for running the restaurant

		// Widget config
		$w_form_count = WidgetForm::where('restaurant_id', $this->restaurantId)->get()->count();
		if ($w_form_count == 0)
		{
			$w_form = new WidgetForm;
			$w_form->restaurant_id = $this->restaurantId;
			$w_form->setupDefaultValues();
			$w_form->save();
		}


		// Widget messages
		$w_messages_count = WidgetMessage::where('restaurant_id', $this->restaurantId)->get()->count();
		if ($w_messages_count == 0)
		{
			$w_messages = new WidgetMessage;
			$w_messages->restaurant_id = $this->restaurantId;
			$w_messages->setupDefaultValues();
			$w_messages->save();
		}


		// Email config
		$email_config_count = EmailConfig::where('restaurant_id', $this->restaurantId)->get()->count();
		if ($email_config_count == 0)
		{
			$email_config = new EmailConfig;
			$email_config->restaurant_id = $this->restaurantId;
			$email_config->setupDefaultValues();
			$email_config->save();
		}


		// Email templates
		$email_template_count = EmailTemplate::where('restaurant_id', $this->restaurantId)->get()->count();
		if ($email_template_count == 0)
		{
			$email_template = new EmailTemplate;
			$email_template->restaurant_id = $this->restaurantId;
			$email_template->setupDefaultValues();
			$email_template->save();
		}

		// Create default roles
		Role::createDefaultRoles($this->restaurantId);

		return $this;
	}

}
