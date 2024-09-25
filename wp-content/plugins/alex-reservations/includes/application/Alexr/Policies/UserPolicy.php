<?php

namespace Alexr\Policies;

use Alexr\Enums\UserRole;
use Alexr\Models\User;
use Alexr\Policies\Traits\InteractsWithTenants;
use Evavel\Eva;

class UserPolicy
{
	use InteractsWithTenants;

	public function viewAny(User $user)
	{
		if (!$this->belongsToTenant($user)) return false;

		// Other checks here

		return true;
	}

	public function view(User $user, User $model)
	{
		if (!$this->belongsToTenant($user, $model)) return false;

		// Other checks here

		return true;
	}

	public function create(User $user)
	{
		if (!$this->belongsToTenant($user)) return false;

		// Other checks here

		return true;
	}

	public function update(User $user, User $model)
	{
		if (!$this->belongsToTenant($user, $model)) return false;

		// Other checks here

		return true;
	}

	public function delete(User $user, User $model)
	{
		if (!$this->belongsToTenant($user, $model)) return false;

		// Other checks here

		return true;
	}
}
