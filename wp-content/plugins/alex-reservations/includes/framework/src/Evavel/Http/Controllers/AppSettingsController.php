<?php

namespace Evavel\Http\Controllers;

//use Alexr\Settings\Event;
//use Alexr\Settings\Shift;

use Alexr\Models\Booking;
use Evavel\Eva;
use Evavel\Http\Controllers\Controller;
use Evavel\Http\Request\AppSettingsRequest;
use Evavel\Http\Request\Request;
use Evavel\Models\SettingCustomized;
use Evavel\Models\SettingListing;
use Evavel\Models\SettingSimple;
use Evavel\Models\SettingSimpleGrouped;
use Evavel\Query\Query;


class AppSettingsController extends Controller
{
	public static $authorize_create = [];
	public static $authorize_update = [];
	public static $authorize_delete = [];

	/**
	 * Fetch the initial configuration of the setting to define the layout
	 * @param Request $request
	 * @param $settingName
	 *
	 * @return void
	 */
	public function config(AppSettingsRequest $request, $settingName)
	{
		$settingClass = $request->settingClass();
		$tenantId = $request->tenantId();

		return $this->response([
			'config' => $settingClass::configuration($request),
			'tenantId' => $request->tenantId(),
		]);
	}



	public function items(AppSettingsRequest $request, $settingName = null)
	{
		$settingClass = $request->settingClass();
		$tenantId = $request->tenantId();

		$parents_class = class_parents($settingClass);

		// SINGLE SETTING required -> for a simple settings I only have one item so return directly the fields
		// GROUP OF SINGLE SETTINGS required --> adds a level of grouping the fields
		if (in_array(SettingSimple::class, $parents_class) ||
		    in_array(SettingSimpleGrouped::class, $parents_class))
		{
			$tenantField = evavel_tenant_field();
			$setting = $settingClass::where($tenantField, $tenantId)->first();

			// Create on the fly if it does not exist yet
			if (!$setting)
			{
				$setting = new $settingClass;
				$field_tenant_id = evavel_tenant_field();
				$setting->{$field_tenant_id} = $request->tenantId();
				$setting->setupDefaultValues()->save();
			}

			$response = [
				'id' => $setting->id,
				'name' => $setting->settingName(),
			];

			if (in_array(SettingSimple::class, $parents_class)) {
				$response['fields'] = $setting->fields();
			}

			if (in_array(SettingSimpleGrouped::class, $parents_class)) {
				$response['items'] = $setting->listItems();
				$response['componentListItem'] = $settingClass::$component_list_item;
			}

			$response['success'] = true;

			return $this->response($response);
		}


		// LISTING OF SETTINGS THAT CAN BE CREATED AND DELETED
		else if (in_array(SettingListing::class, $parents_class))
		{
			$tenantField = evavel_tenant_field();

			$items = $settingClass::where($tenantField, $tenantId)
			                      ->orderBy('date_created', 'ASC')
			                      ->get()
			                      ->toArray();

			return $this->response([
				'success' => true,
				'componentListItem' => $settingClass::$component_list_item,
				'items' =>  $settingClass::where($tenantField, $tenantId)->orderBy('date_created', 'ASC')->get()->toArray(),
				'description' => $settingClass::description()
				//'errors' => $settingClass::validateAll()
			]);
		}

		// CUSTOM SETTING with custom component
		else if (in_array(SettingCustomized::class, $parents_class))
		{
			return $this->response([
				'success' => true,
				'settingId' => $settingClass::getId($tenantId),
				'items' => $settingClass::getItems($request, $tenantId),
				'label' => $settingClass::label()
			]);
		}

	}

	public function listing(AppSettingsRequest $request, $settingName) {
		$settingClass = $request->settingClass();
		$tenantId = $request->tenantId();

		if (method_exists($settingClass, 'getListing')){
			return $this->response(['items' => $settingClass::getListing($tenantId)]);
		}

		return $this->response(['items' => []]);
	}

	public function create(AppSettingsRequest $request, $settingName)
	{
		$settingClass = $request->settingClass();

		// Auhorization
		$permission_name = false;
		$permission_message = false;

		// Extended function to check permissions
		$data = ['permission_name' => $permission_name, 'permission_message' => $permission_message];
		foreach(self::$authorize_create as $func) {
			$data = $func[0]->{$func[1]}($settingClass, $data);
		}
		$permission_name = $data['permission_name'];
		$permission_message = $data['permission_message'];


		// Check the appropiate permission
		if ($permission_name){
			$user = Eva::make('user');
			if (!$user->canCreate($permission_name, $request->tenantId())) {
				return $this->response([ 'success' => false, 'error' => $permission_message]);
			}
		}

		// Create setting
		$setting = new $settingClass;
		$field_tenant_id = evavel_tenant_field();
		$setting->{$field_tenant_id} = $request->tenantId();
		$setting->setupDefaultValues()->save();

		return $this->response(['success' => true, 'item' => $setting]);
	}

	/**
	 * Get the fields with values for an item
	 *
	 * @param AppSettingsRequest $request
	 * @param $settingName
	 * @param $settingId
	 *
	 * @return \WP_REST_Response
	 */
	public function get(AppSettingsRequest $request, $settingName, $settingId)
	{
		$settingClass = $request->settingClass();
		$setting = $settingClass::find($settingId);

		$response = [
			'fields' => $setting->fields(),
			'error' => $setting->validate(),
		];

		return $this->response($response);
	}

	public function update(AppSettingsRequest $request, $settingName, $settingId)
	{
		$settingClass = $request->settingClass();

		// Auhorization
		$permission_name = false;
		$permission_message = false;

		// Extended function to check permissions
		$data = ['permission_name' => $permission_name, 'permission_message' => $permission_message];
		foreach(self::$authorize_update as $func) {
			$data = $func[0]->{$func[1]}($settingClass, $data);
		}
		$permission_name = $data['permission_name'];
		$permission_message = $data['permission_message'];

		// Check the appropiate permission
		if ($permission_name){
			$user = Eva::make('user');
			if (!$user->canEdit($permission_name, $request->tenantId())) {
				return $this->response([ 'success' => false, 'error' => $permission_message]);
			}
		}


		$setting = $settingClass::find($settingId);

		$params = $request->body_params;

		foreach($params as $key => $value) {

			//$value = str_replace("\\", "\", $value);
			//ray($key. ' => ' . $value);

			// Is textarea ?
			if (preg_match("#\n#", $value))
			{
				$value = preg_replace('/\r/u', '', $value);
				$value = preg_replace('/\n/u', '<br>', $value);
				$setting->{$key} = $value;
			}

			// String with \n inside is a json string coming from the email templates
			// where 1 field is actually many fields inside for the different languages
			else if (strpos($value, '\n') !== false)
			{
				$value = str_replace('\n', '<br>', $value);
				$new_value = evavel_json_decode($value);
				$setting->{$key} = $new_value;
			}
			else
			{
				$new_value = evavel_json_decode($value);
				$setting->{$key} = $new_value;
			}

		}

		// Check rules before saving
		$errors = $setting->validate();

		// I save even if errors because for shifts
		// I need to overlap same dates but different areas
		$setting->save();

		$response = ['success' => true, 'item' => $setting->toArray()];

		if (!empty($errors)){
			//return $this->response(['success' => false, 'error' => $errors]);
			$response['warning'] = $errors;
		}

		// Add something else?
		if (method_exists($setting, 'addToUpdateResponse')){
			$response = $setting->addToUpdateResponse($response);
		}

		return $this->response($response);
	}

	public function delete(AppSettingsRequest $request, $settingName, $settingId)
	{
		$settingClass = $request->settingClass();

		// Auhorization
		$permission_name = false;
		$permission_message = false;

		// Extended function to check permissions
		$data = ['permission_name' => $permission_name, 'permission_message' => $permission_message];
		foreach(self::$authorize_delete as $func) {
			$data = $func[0]->{$func[1]}($settingClass, $data);
		}
		$permission_name = $data['permission_name'];
		$permission_message = $data['permission_message'];

		// Check the appropiate permission
		if ($permission_name){
			$user = Eva::make('user');
			if (!$user->canDelete($permission_name, $request->tenantId())) {
				return $this->response([ 'success' => false, 'error' => $permission_message]);
			}
		}

		$table = $settingClass::$table_name;

		Query::table($table)
			->where('id', $settingId)
			->delete();

		return $this->response(['success' => true, 'settingName' => $settingName, 'settingId' => $settingId]);
	}

	public function duplicate(AppSettingsRequest $request, $settingName, $settingId)
	{
		$settingClass = $request->settingClass();

		$service = null;

		// @TODO Esto es especifico de la Aplicacion
		if (str_contains($settingClass, 'Shift'))
		{
			$service = evavel_shift_class()::find($settingId);
		}
		else if (str_contains($settingClass, 'Event'))
		{
			$service = evavel_event_class()::find($settingId);
		}

		if ($service)
		{
			$table_name = $settingClass::$table_name;

			$field = evavel_tenant_field();

			Query::table($table_name)->insert([
				$field => $service->{$field},
				'meta_key' => $service->meta_key,
				'meta_value' => $service->original['meta_value']
			]);
		}

		return $this->response(['success' => true, 'settingName' => $settingName, 'settingId' => $settingId]);
	}

	public function ordering(AppSettingsRequest $request, $settingName)
	{
		$ordering = json_decode($request->ordering);

		$table = evavel_tenant_setting_table();

		foreach($ordering as $key => $value){
			Query::table($table)
			     ->where('id', $key)
			     ->update(['ordering' => $value]);
		}



		return $this->response(['success' => true]);
	}

	// Preview email HTML for the setting Email Templates
	public function preview(AppSettingsRequest $request, $settingName, $settingId)
	{
		if ($settingName == 'email_templates') {
			return $this->previewEmail($request, $settingName, $settingId, evavel_app_email_template_class()::find($settingId));
		}
		else if ($settingName == 'email_reminders') {
			return $this->previewEmail($request, $settingName, $settingId, evavel_app_email_reminder_class()::find($settingId));
		}
		else if ($settingName == 'email_custom') {
			return $this->previewEmail($request, $settingName, $settingId, evavel_app_email_custom_class()::find($settingId));
		}
		else {
			return $this->response([
				'success' => false,
				'error' => __eva('Invalid setting name')
			]);
		}
	}

	public function previewEmail(AppSettingsRequest $request, $settingName, $settingId, $emailTemplate)
	{
		if (!$emailTemplate) {
			return $this->response([
				'success' => false,
				'error' => __eva('Invalid template')
			]);
		}

		$lang = $request->lang;
		$attribute = $request->attribute;

		$content_base64 = $emailTemplate->{$attribute}['content_'.$lang];
		$content = base64_decode($content_base64);

		$tenantField = evavel_tenant_field();
		$class_mailManager = evavel_app_email_manager_class();
		$mm = new $class_mailManager($emailTemplate->{$tenantField});
		$message = $mm->messageFormatted($content);

		// Fill with some booking
		$booking = Booking::first();
		if ($booking) {
			$message = $booking->parseEmailTags($message, $lang);
		}

		$message .= '<style>p { margin-bottom: 20px; }</style>';

		return $this->response([
			'success' => true,
			'html' => $message
		]);
	}

}
