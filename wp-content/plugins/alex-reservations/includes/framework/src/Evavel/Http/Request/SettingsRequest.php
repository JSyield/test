<?php

namespace Evavel\Http\Request;

use Evavel\Eva;
use Evavel\Facades\Gate;
use Evavel\Models\User;

class SettingsRequest extends Request
{
	/**
	 * Store the content of the configuration file read
	 *
	 * @var
	 */
	protected $config;

	/**
	 * Is managing tenant settings
	 *
	 * @var bool
	 */
	protected $isTenant = false;

	/**
	 * Is managing application settings
	 *
	 * @var bool
	 */
	protected $isMain = false;

	/**
	 * Authorize the request
	 *
	 * @return bool|void
	 */
	public function authorize() {

		$tenantId = $this->tenantId();

		if ($tenantId > 0){
			if (Gate::denies('settings-tenant', [$tenantId])) {
				evavel_403();
			}
		} else {
			if (Gate::denies('settings-main')) {
				evavel_403();
			}
		}
	}

	/**
	 * Fetch application settings
	 * or tenant settings
	 *
	 * @return array
	 */
	public function getSettings($tenantId = 0)
	{
		if ($tenantId == 0){
			$config = evavel_config('settings-main');
		} else {
			$config = evavel_config('settings-tenant');
		}

		$this->config = $config;
		$this->isMain = ($tenantId == 0);
		$this->isTenant = !$this->isMain;

		$user = Eva::make('user');


		$settings = $config['settings'];

		foreach($settings as &$list_settings)
		{
			foreach($list_settings as &$item)
			{
				// Add some default properties
				$item['stacked'] = isset($item['stacked']) ? $item['stacked'] : false;

				if ($this->isMain) {
					$item['value'] = evavel_get_setting($item['key']);
				}
				else {
					$item['value'] = evavel_tenant_get_setting($tenantId, $item['key']);
				}
			}
		}

		$panels = [];

		foreach($config['panels'] as $key => $value) {
			if ($this->isTenant){
				if ($this->authorizeToManageSettings($user, $key)) {
					$panels[] = [ 'key' => $key, 'label' => $value['label'] ];
				}
			} else {
				$panels[] = ['key' => $key, 'label' => $value ];
			}
		}

		return [
			'panels' => $panels,
			'settings' => $settings
		];
	}

	/**
	 * AUthorize tenant group settings based on role
	 *
	 * @param User $user
	 * @param $key
	 *
	 * @return bool
	 */
	protected function authorizeToManageSettings(User $user, $key)
	{
		$role_number = isset($this->config['user_roles'][$user->role])
			? $this->config['user_roles'][$user->role]
			: false;

		if (!$role_number) return false;

		return in_array($role_number, $this->config['panels'][$key]['roles_can_edit']);
	}
}

