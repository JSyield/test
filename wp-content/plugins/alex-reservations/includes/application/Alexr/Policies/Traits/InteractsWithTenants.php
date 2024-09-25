<?php

namespace Alexr\Policies\Traits;

use Alexr\Enums\UserRole;
use Alexr\Models\User;
use Evavel\Eva;

trait InteractsWithTenants
{
	protected $request;
	protected $tenantId;

	public function __construct()
	{
		$this->request = Eva::make('request');
		$this->tenantId = $this->request->tenantId();
	}

	// No lo estoy usando
	protected function belongsToTenant(User $user, $model = null)
	{
		if ($user->role === UserRole::ADMINISTRATOR) {
			return true;
		}

		// When editing or viewing
		if ($model != null) {
			switch ($user->role){
				case UserRole::OWNER:
					return in_array($model->restaurant_id, $user->restaurantsIds());
				case UserRole::EMPLOYE:
					return $user->restaurant->id == $model->restaurant_id;
				default:
					return false;
			}
		}

		// For viewAny or create
		else if ($this->tenantId > 0) {
			switch ($user->role){
				case UserRole::OWNER:
					return in_array($this->tenantId, $user->restaurantsIds());
				case UserRole::EMPLOYE:
					return $user->restaurant->id == $this->tenantId;
				default:
					return false;
			}
		}

		// No model, no tenant request
		// Lenses,Filters,Actions request will return true
		return true;
	}
}
