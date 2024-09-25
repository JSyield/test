<?php

namespace Alexr\Settings;

use Evavel\Models\SettingCustomized;

class ClosedTable extends SettingCustomized
{
	public static $table_name = 'restaurant_setting';
	public static $meta_key = 'closed_tables';
	public static $pivot_tenant_field = 'restaurant_id';

	public static $custom_component = null;
}
