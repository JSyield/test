<?php

namespace Evavel\Notifications;

class Notification {

	public function via($notifiable)
	{
		return ['database'];
	}

	public function handle($user)
	{
		$vias = $this->via($user);

		foreach($vias as $via) {
			if ($via == 'database') {
				$this->toDatabase($user);
			}
		}
	}

	public function toDatabase($user)
	{
		$calls = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);
		$class_name = isset($calls[1]['class']) ? $calls[1]['class'] : get_class($this);

		$tenantField = evavel_tenant_field();

		return [
			$tenantField => $this->booking->{$tenantField},
			'type' => evavel_escape_className($class_name),
			'notifiable_type' => evavel_escape_className(get_class($user)),
			'notifiable_id' => $user->id,
		];
	}
}
