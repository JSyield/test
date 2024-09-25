<?php

namespace Alexr\Models\Traits;

trait CustomerPaymentUtils {

	// STRIPE

	public function getStripeIdAttribute()
	{
		return $this->get_setting('stripe_id');
	}

	public function setStripeIdAttribute($value)
	{
		$this->set_setting('stripe_id', $value);
	}
}
