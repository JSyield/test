<?php

namespace Evavel\Notifications;

class Notifications {

	public static function send ( $notifiables, $notification )
	{
		foreach($notifiables as $notifiable) {
			$notification->handle($notifiable);
		}
	}
}
