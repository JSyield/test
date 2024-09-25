<?php

namespace Alexr\Models\Traits;

use Alexr\Settings\ClosedSlot;

trait CalculateBlockedSlots
{
	function hasDateBlockedSlots($date_string)
	{
		$closedSlots = ClosedSlot::where(evavel_tenant_field(), $this->id)->first();
		if (!$closedSlots) return false;

		$selected = $closedSlots->{$date_string};
		if (!$selected) return false;

		return true;
	}
}
