<?php

namespace Alexr\Policies;

use Alexr\Enums\UserRole;
use Alexr\Models\Restaurant;
use Alexr\Models\User;
use Alexr\Policies\Traits\InteractsWithTenants;
use Evavel\Eva;
use Evavel\Http\Request\Request;

class RestaurantPolicy
{
	use InteractsWithTenants;

	public function viewAny(User $user)
	{
		if (!$this->belongsToTenant($user)) return false;

		return true;
	}

	public function view(User $user, Restaurant $restaurant)
	{
		if (!$this->belongsToTenant($user)) return false;

		return true;
	}

	public function create(User $user)
	{
		return $user->role == UserRole::ADMINISTRATOR;
	}

	public function update(User $user, Restaurant $restaurant)
	{
		return $user->role == UserRole::ADMINISTRATOR;
	}

	public function delete(User $user, Restaurant $restaurant)
	{
		return $user->role == UserRole::ADMINISTRATOR;
	}

}
