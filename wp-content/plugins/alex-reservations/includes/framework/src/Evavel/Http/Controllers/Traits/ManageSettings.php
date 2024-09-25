<?php

namespace Evavel\Http\Controllers\Traits;

trait ManageSettings
{
	/**
	 * Save settings for the Application and for each Tenant
	 * When tenantId = 0 then is for the Application
	 *
	 * @param $name_config
	 * @param $params
	 * @param $tenantId
	 *
	 * @return void
	 */
	protected function storeParamsSettings($params, $tenantId = 0)
	{
		$name_config = $tenantId == 0 ? 'settings-main' : 'settings-tenant';

		$config = evavel_config($name_config);
		$settings = $config['settings'];

		foreach($settings as $key => $list_fields){
			foreach($list_fields as $field) {
				if (!in_array($field['type'], ['help', 'header'])) {

					$value = isset($params[$field['key']]) ? $params[$field['key']] : null;
					$value = $this->setDefaultValue($field, $value);

					// Application setting
					if ($tenantId == 0) {
						evavel_save_setting( $field['key'], $value );
					}
					// Tenant setting
					else {
						evavel_tenant_save_setting( $tenantId, $field['key'], $value );
					}
				}
			}
		}
	}

	/**
	 * Set default value
	 * (ex: toogle false is saved as 0)
	 *
	 * @param $field
	 * @param $value
	 *
	 * @return int|mixed
	 */
	protected function setDefaultValue($field, $value)
	{
		if ($field['type'] == 'toggle' && empty($value)){
			$value = 0;
		}
		return $value;
	}
}
