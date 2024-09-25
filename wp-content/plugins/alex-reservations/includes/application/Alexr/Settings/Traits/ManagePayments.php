<?php

namespace Alexr\Settings\Traits;


use Alexr\Enums\PaymentStatus;

trait ManagePayments
{
	protected $rule_matched;

	public function isPaymentActive()
	{
		return $this->payment_active == 'yes' || $this->payment_active == 'yes_save';
	}

	public function isPaymentToCapture()
	{
		return $this->payment_active == 'yes_save';
	}

	public function getPaymentRules()
	{
		$rules = $this->payment_rules;
		if (is_array($rules)) {
			return $rules;
		}
		return [];
	}

	/**
	 * Check if the rule matches the date and time passed by
	 * If true means that this rule is the one to grab the price
	 *
	 * @param $rule
	 * @param $date (is included in the shift valid dates)
	 * @param $time
	 *
	 * @return bool
	 */
	public function isRuleMatch($rule, $date, $time)
	{
		// Check rule is active
		if (!isset($rule['active'])) return false;
		if (!$rule['active']) return false;

		// To get the week day
		$day = evavel_date_createFromFormat('Y-m-d', $date);
		$dayOfWeek = $day->dayOfWeek; // 1-mon , 7-sun

		// Check weekdays
		if (!isset($rule['weekdays'])) return false;
		$rule_weekdays = $rule['weekdays'];
		$weekdays = ['sun','mon','tue','wed','thu','fri','sat', 'sun'];
		if ($rule_weekdays[ $weekdays[$dayOfWeek] ] === false) return false;
		//ray('WEEKDAYS OK');

		// Check date ranges. Only apply if not empty
		if (!isset($rule['date_range'])) return false;
		$rule_dates = $rule['date_range'];
		$found_match = false;
		foreach($rule_dates as $range) {
			if(preg_match("#(.+) to (.+)#", $range, $matches)) {
				if ($matches[1] <= $date && $matches[2] >= $date) {
					$found_match = true;
				}
			}
		}
		if (!empty($rule_dates) && !$found_match) return false;
		//ray('DATES RANGE OK');


		// Check include specific dates. If not empty date should be in the list
		if (!isset($rule['include_days'])) return false;
		$include_days = $rule['include_days'];
		if (!empty($include_days)){
			if (!in_array($date, $include_days)){
				return false;
			}
		}
		//ray('INCLUDE DAYS OK');


		// Check exclude dates. If not empty check it is not included here
		if (!isset($rule['exclude_days'])) return false;
		$exclude_days = $rule['exclude_days'];
		if (!empty($exclude_days)){
			if (in_array($date, $exclude_days)){
				return false;
			}
		}
		//ray('EXCLUDE DAYS OK');

		// Reach this point means the date is accepted
		// Should not pass a date outside the shift available dates
		// Not checking here if the shift dates are valid,
		// it is supposed the date is included

		// Check time slot is accepted
		if (!isset($rule['slots'])) return false;
		$slots = $rule['slots'];
		foreach ($slots as $slot) {
			if ($slot['time'] == $time){
				// true means that the slots is blocked
				return !$slot['block'];
			}
		}

		return true;
	}

	public function getRulePrice($rule, $party)
	{
		if (!isset($rule['payment_type'])) return 0;

		$payment_type = $rule['payment_type'];
		if ($payment_type == 'fixed') {
			return isset($rule['price_fixed']) ? floatval($rule['price_fixed']) : 0;
		}

		if ($payment_type == 'per_seat') {
			return isset($rule['price_per_seat']) ? intval($party) * floatval($rule['price_per_seat']) : 0;
		}

		if ($payment_type == 'variable') {
			if (!isset($rule['price_variable'])) return 0;

			$prices = $rule['price_variable'];
			if (!is_array($prices)) return 0;

			$index = intval($party)-1;
			if ($index <= 0) return 0;

			if (isset($prices[$index])) {
				return floatval($prices[$index]);
			}

			return floatval($prices[count($prices)-1]);
		}


		return 0;
	}

	public function getPaymentAmountForBooking($booking)
	{
		return $this->getPaymentAmount($booking->date, $booking->time, $booking->party);
	}

	public function getPaymentRule($booking)
	{
		$this->getPaymentAmountForBooking($booking);
		return $this->rule_matched;
	}

	public function getPaymentMessage()
	{
		$message = '';
		if ($this->rule_matched && isset($this->rule_matched['description'])) {
			$message = $this->rule_matched['description'];
		}
		if (empty($message)) {
			$message = __eva('To secure your reservation, we kindly ask for a prepayment.').'<br>'
			           .('Your booking will be confirmed once the payment is successful.');
		}

		return $message;
	}

	/**
	 * Get the price for a date, time and party
	 * Retun 0 means no payment
	 *
	 * @param $date
	 * @param $time
	 * @param $party
	 *
	 * @return float|int
	 */
	public function getPaymentAmount($date, $time, $party)
	{
		/*
			Check payment is enabled
			Check every rule until matches
			Get the rule price based on party
		*/

		if (!defined('ALEXR_PRO_VERSION')) return 0;

		if (!$this->isPaymentActive()) return 0;

		foreach($this->getPaymentRules() as $rule)
		{
			if ($this->isRuleMatch($rule, $date, $time))
			{
				$this->rule_matched = $rule;
				return $this->getRulePrice($rule, $party);
			}
		}

		$this->rule_matched = null;
		return 0;
	}


}
