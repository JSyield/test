<?php

namespace Alexr\Policies;

use Alexr\Enums\UserRole;
use Alexr\Models\Booking;
use Alexr\Models\User;
use Alexr\Policies\Traits\InteractsWithTenants;
use Evavel\Eva;

class BookingPolicy
{
	use InteractsWithTenants;

	public function viewAny(User $user)
	{
		if (!$this->belongsToTenant($user)) return false;

		// Other checks here

		return true;
	}

	public function view(User $user, Booking $booking)
	{
		if (!$this->belongsToTenant($user, $booking)) return false;

		// Other checks here

		return true;
	}

	public function create(User $user)
	{
		if (!$this->belongsToTenant($user)) return false;

		// Other checks here

		return true;
	}

	public function update(User $user, Booking $booking)
	{
		if (!$this->belongsToTenant($user, $booking)) return false;

		// Other checks here

		return true;
	}

	public function delete(User $user, Booking $booking)
	{
		if (!$this->belongsToTenant($user, $booking)) return false;

		// Other checks here

		return true;
	}
}
