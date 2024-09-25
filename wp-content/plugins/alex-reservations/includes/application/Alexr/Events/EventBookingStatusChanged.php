<?php
namespace Alexr\Events;

use Alexr\Models\Booking;
use Alexr\Models\User;

class EventBookingStatusChanged
{
	public $booking;
	public $old_status;
	public $new_status;
	public $user;

	// Cuando la accion se produce desde el email que recibe el administrador
	public $fromEmail;

	public function __construct(Booking $booking, $old_status, $new_status, User $user = null, $fromEmail = false)
	{
		//ray('Event Booking status changed');
		$this->booking = $booking;
		$this->old_status = $old_status;
		$this->new_status = $new_status;
		$this->user = $user;
		$this->fromEmail = $fromEmail;
	}
}
