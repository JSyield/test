<?php

namespace Alexr\Hooks;

use Evavel\Http\Controllers\AppSettingsController;

class HooksAppSettings
{
	public function __construct()
	{
		AppSettingsController::$authorize_create[] = array($this, 'authorize_create');
		AppSettingsController::$authorize_update[] = array($this, 'authorize_update');
		AppSettingsController::$authorize_delete[] = array($this, 'authorize_delete');
	}

	public function authorize_create($settingClass, $data)
	{
		$permission_name = $data['permission_name'];
		$permission_message = $data['permission_message'];

		if (str_contains($settingClass, 'Shift')) {
			$permission_name = 'shifts';
			$permission_message = __eva('You cannot create new Shifts.');
		} else if (str_contains($settingClass, 'Event')) {
			$permission_name = 'events';
			$permission_message = __eva('You cannot create new Events.');
		} else if (str_contains($settingClass, 'Widgetform')) {
			$permission_name = 'widgets';
			$permission_message = __eva('You cannot create new Widgets.');
		}

		$data['permission_name'] = $permission_name;
		$data['permission_message'] = $permission_message;

		return $data;
	}

	public function authorize_update($settingClass, $data)
	{
		$permission_name = $data['permission_name'];
		$permission_message = $data['permission_message'];

		if (str_contains($settingClass, 'General')) {
			$permission_name = 'restaurant';
			$permission_message = __eva('You cannot edit these settings.');
		} else if (str_contains($settingClass, 'Shift')) {
			$permission_name = 'shifts';
			$permission_message = __eva('You cannot edit Shifts.');
		} else if (str_contains($settingClass, 'Event')) {
			$permission_name = 'events';
			$permission_message = __eva('You cannot edit Events.');
		} else if (str_contains($settingClass, 'Widgetform')) {
			$permission_name = 'widgets';
			$permission_message = __eva('You cannot edit Widgets.');
		} else if (str_contains($settingClass, 'Widgetmessage')) {
			$permission_name = 'widgetmessage';
			$permission_message = __eva('You cannot edit messages.');
		} else if (str_contains($settingClass, 'EmailConfig')) {
			$permission_name = 'email_config';
			$permission_message = __eva('You cannot edit email configuration.');
		} else if (str_contains($settingClass, 'EmailTemplate')) {
			$permission_name = 'email_templates';
			$permission_message = __eva('You cannot edit email templates.');
		}

		$data['permission_name'] = $permission_name;
		$data['permission_message'] = $permission_message;

		return $data;
	}

	public function authorize_delete($settingClass, $data)
	{
		$permission_name = $data['permission_name'];
		$permission_message = $data['permission_message'];

		if (str_contains($settingClass, 'Shift')) {
			$permission_name = 'shifts';
			$permission_message = __eva('You cannot delete Shifts.');
		} else if (str_contains($settingClass, 'Event')) {
			$permission_name = 'events';
			$permission_message = __eva('You cannot delete Events.');
		} else if (str_contains($settingClass, 'Widgetform')) {
			$permission_name = 'widgets';
			$permission_message = __eva('You cannot delete Widgets.');
		}

		$data['permission_name'] = $permission_name;
		$data['permission_message'] = $permission_message;

		return $data;
	}

}

new HooksAppSettings;
