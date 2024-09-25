<?php

namespace Alexr\Models;

use Alexr\Enums\UserRole;
use Evavel\Models\Model;
use Evavel\Query\Query;

class Role extends Model
{
	public static $table_name = 'roles';
	public static $table_meta = false;
	public static $pivot_tenant_field = 'restaurant_id';

	public $appends = [
		'permissions'
	];

	protected $casts = [
		'id' => 'int',
		'settings' => 'json',
	];

	public function restaurant()
	{
		return $this->belongsTo(Restaurant::class);
	}

	public function getPermissionsAttribute()
	{
		$settings = $this->settings;

		$default_permissions = self::defaultPermissions($this->role);

		// Should merge with default variables
		if (is_array($settings) && isset($settings['permissions'])) {
			// Remove possible previous permissions saved
			$list_merged = array_merge($default_permissions, $settings['permissions']);
			$list = [];
			foreach ($list_merged as $key => $value){
				if (isset($default_permissions[$key])) {
					$list[$key] = $list_merged[$key];
				}
			}

			return $list;
		}


		return $default_permissions;
	}

	public function setPermissionsAttribute($permissions)
	{
		$settings = $this->settings;

		if (!is_array($settings)) {
			$settings = [];
		}

		$settings['permissions'] = $permissions;

		$this->settings = $settings;
	}

	public static function permissionsLabels()
	{
		return [
			'vertical_menu' => ['label' => __eva('Vertical Menu'), 'description' => ''],
			'wp_back_button' => ['label' => __eva('WP back button'), 'description' => ''],
			'bookings' => [ 'label' => __eva('Bookings'), 'description' => '' ],
			'customers' => ['label' => __eva('Customers'), 'description' => '' ],
			'floor_plan' => ['label' => __eva('Floor Plan'), 'description' => '' ],
			'reporting' => ['label' => __eva('Statistics'), 'description' => '' ],
			'close_days' => ['label' => __eva('Close days'), 'description' => '' ],
			'close_slots' => ['label' => __eva('Close slots'), 'description' => '' ],
			'restaurant' => ['label' => __eva('Restaurant settings'), 'description' => '' ],
			'shifts' => ['label' => __eva('Shifts'), 'description' => '' ],
			'events' => ['label' => __eva('Events'), 'description' => '' ],
			'widgets' => ['label' => __eva('Widgets'), 'description' => '' ],
			'widgetmessage' => ['label' => __eva('Widget Messages'), 'description' => '' ],
			'email_config' => ['label' => __eva('Email Config'), 'description' => '' ],
			'email_templates' => ['label' => __eva('Email Templates'), 'description' => '' ],
			'email_custom' => ['label' => __eva('Custom emails'), 'description' => '' ],
			'email_reminders' => ['label' => __eva('Email Reminders'), 'description' => '' ],
			'sms_templates' => ['label' => __eva('SMS Templates'), 'description' => '' ],
			'sms_reminders' => ['label' => __eva('SMS Reminders'), 'description' => '' ],
			'booking_tags' => ['label' => __eva('Tags for bookings'), 'description' => '' ],
			'customer_tags' => ['label' => __eva('Tags for customers'), 'description' => '' ],
		];
	}

	public static function actions()
	{
		return [
			'enable' => __eva('Visible'),
			'view' => __eva('View'),
			'edit' => __eva('Edit'),
			'create' => __eva('Create'),
			'delete' => __eva('Delete'),
			'export' => __eva('Export'),
			'import' => __eva('Import'),
			'columns' => __eva('Columns'),
			'view_list' => __eva('List'),
			'view_floor_plan' => __eva('Floor Plan'),
			'view_calendar' => __eva('Calendar'),
			'view_customers' => __eva('Customers'),
			'view_metrics' => __eva('Statistics'),
			'view_editor' => __eva('Editor'),
			'view_settings' => __eva('Settings')
		];
	}

	public static function defaultPermissions($role)
	{
		$list = [
			'super_manager' => [
				'vertical_menu' => ['view_list' => true, 'view_floor_plan' => true, 'view_calendar' => true, 'view_customers' => true, 'view_metrics' => true, 'view_editor' => true, 'view_settings' => true],
				'wp_back_button' => ['enable' => false],
				'bookings' => ['edit' => true, 'create' => true, 'export' => true, 'columns' => true],
				'customers' => ['edit' => true, 'create' => true, 'delete' => true, 'export' => true, 'import' => true, 'columns' => true],
				'close_days' => ['edit' => true],
				'close_slots' => ['edit' => true],
				'floor_plan' => ['edit' => true],
				'reporting' => ['view' => true],
				'restaurant' => ['edit' => true],
				'shifts' => ['edit' => true, 'create' => true, 'delete' => true],
				'events' => ['edit' => true, 'create' => true, 'delete' => true],
				'widgets' => ['edit' => true, 'create' => true, 'delete' => true],
				'widgetmessage' => ['edit' => true],
				'email_config' => ['edit' => true],
				'email_templates' => ['edit' => true],
				'email_custom' => ['edit' => true, 'create' => true, 'delete' => true ],
				'email_reminders' => ['edit' => true, 'create' => true, 'delete' => true ],
				'sms_templates' => ['edit' => true],
				'sms_reminders' => ['edit' => true, 'create' => true, 'delete' => true ],
				'booking_tags' => ['edit' => true],
				'customer_tags' => ['edit' => true],
			],
			'manager' => [
				'vertical_menu' => ['view_list' => true, 'view_floor_plan' => true, 'view_calendar' => true, 'view_customers' => true, 'view_metrics' => true, 'view_editor' => true, 'view_settings' => true],
				'wp_back_button' => ['enable' => false],
				'bookings' => ['edit' => true, 'create' => true, 'export' => true, 'columns' => false],
				'customers' => ['edit' => true, 'create' => true, 'delete' => false, 'export' => true, 'import' => true, 'columns' => false],
				'close_days' => ['edit' => false],
				'close_slots' => ['edit' => false],
				'floor_plan' => ['edit' => false],
				'reporting' => ['view' => true],
				'restaurant' => ['edit' => false],
				'shifts' => ['edit' => true, 'create' => true, 'delete' => true],
				'events' => ['edit' => true, 'create' => true, 'delete' => true],
				'widgets' => ['edit' => true, 'create' => true, 'delete' => true],
				'widgetmessage' => ['edit' => true],
				'email_config' => ['edit' => false],
				'email_templates' => ['edit' => false],
				'email_custom' => ['edit' => false, 'create' => false, 'delete' => false ],
				'email_reminders' => ['edit' => false, 'create' => false, 'delete' => false ],
				'sms_templates' => ['edit' => false],
				'sms_reminders' => ['edit' => false, 'create' => false, 'delete' => false],
				'booking_tags' => ['edit' => false],
				'customer_tags' => ['edit' => false],
			],
			'sub_manager' => [
				'vertical_menu' => ['view_list' => true, 'view_floor_plan' => true, 'view_calendar' => true, 'view_customers' => true, 'view_metrics' => true, 'view_editor' => true, 'view_settings' => true],
				'wp_back_button' => ['enable' => false],
				'bookings' => ['edit' => false, 'create' => false, 'export' => false, 'columns' => false],
				'customers' => ['edit' => false, 'create' => false, 'delete' => false, 'export' => false, 'import' => false, 'columns' => false],
				'close_days' => ['edit' => false],
				'close_slots' => ['edit' => false],
				'floor_plan' => ['edit' => false],
				'reporting' => ['view' => false],
				'restaurant' => ['edit' => false],
				'shifts' => ['edit' => false, 'create' => false, 'delete' => false],
				'events' => ['edit' => false, 'create' => false, 'delete' => false],
				'widgets' => ['edit' => false, 'create' => false, 'delete' => false],
				'widgetmessage' => ['edit' => false],
				'email_config' => ['edit' => false],
				'email_templates' => ['edit' => false,],
				'email_custom' => ['edit' => false, 'create' => false, 'delete' => false ],
				'email_reminders' => ['edit' => false, 'create' => false, 'delete' => false],
				'sms_templates' => ['edit' => false],
				'sms_reminders' => ['edit' => false, 'create' => false, 'delete' => false],
				'booking_tags' => ['edit' => false],
				'customer_tags' => ['edit' => false],
			]
		];

		return isset($list[$role]) ? $list[$role] : $list['sub_manager'];
	}

	public static function completeMissedPermissions($permissions_role, $role_manager)
	{
		$permissions_default = self::defaultPermissions($role_manager);

		foreach($permissions_default as $resource => $list) {
			foreach($list as $key => $value) {
				if (isset($permissions_role[$resource][$key])){
					$permissions_default[$resource][$key] = $permissions_role[$resource][$key];
				}
			}
		}

		return $permissions_default;
	}

	/**
	 * Create default roles in the DB
	 *
	 * @param $restaurant_id
	 *
	 * @return void
	 */
	public static function createDefaultRoles($restaurant_id = null)
	{
		$list = UserRole::listing();

		foreach($list as $key => $label) {

			if ($restaurant_id == null) {
				$role = Role::whereIsNull('restaurant_id')->where('role', $key)->first();
			} else {
				$role = Role::where('restaurant_id', $restaurant_id)->where('role', $key)->first();
			}

			if (!$role) {
				$role = Role::create([
					'restaurant_id' => $restaurant_id,
					'name' => $label,
					'role' => $key,
					'settings' => ['permissions' => self::defaultPermissions($key)]
				]);

				$role->save();
			}
		}
	}
}
