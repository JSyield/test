<?php
namespace Alexr\Settings\Traits;

trait HasTimeOptions {

	protected function listOfHours( $expand_hours = 0, $interval = 900) {
		$list = [];
		for ($seconds = 0; $seconds <= (86400 + 3600 * $expand_hours); $seconds += $interval) {
			$list[] = [
				'label' => $this->toHour($seconds),
				'value' => $seconds
			];
		}
		return $list;
	}

	protected function listOfHoursStartEnd($start_seconds, $end_seconds, $interval = 900) {
		$list = [];
		for ($seconds = $start_seconds; $seconds <= $end_seconds; $seconds += $interval) {
			$list[] = [
				'label' => $this->toHour($seconds),
				'value' => $seconds
			];
		}
		return $list;
	}

	protected function toHour($seconds, $format = '12h')
	{
		$seconds = intval($seconds);
		$hour = intval($seconds / 3600);
		$minutes = intval( ($seconds / 60) - 60 * $hour );

		if ($format == '24h') {
			return $hour.':'.str_pad(''.$minutes, 2, '0', STR_PAD_LEFT);
		}

		$zone = ' AM';
		if ($hour >= 12) {
			$zone = ' PM';
			$hour = $hour - 12;
		}
		if ($seconds == 86400) {
			$zone = ' AM';
		}
		if ($seconds > 86400) {
			$zone = __eva(' AM (next day)');
			$hour = $hour - 12;
		}
		if ($hour == 0) {
			$hour = 12;
		}

		return $hour.':'.str_pad(''.$minutes, 2, '0', STR_PAD_LEFT).$zone;
	}

	protected function toDuration($seconds)
	{
		$hours = intval($seconds / 3600);
		$minutes = intval( ($seconds / 60) - 60 * $hours );

		if ($hours == 0) {
			return $minutes . '' . __eva('m');
		}

		$duration = $hours . '' . __eva('h');

		if ($minutes > 0) {
			$duration .= ' ' . $minutes . '' . __eva('m');
		}

		return $duration;
	}

	protected function toListDurations($durations) {

		$list = [];
		for ($i = 0; $i < count($durations); $i++) {

			$minutes = 0;
			if (preg_match('~(\d+)min~', $durations[$i], $matches)) {
				$minutes = intval($matches[1]);
			}
			$hours = 0;
			if (preg_match('~(\d+)h~', $durations[$i], $matches)) {
				$hours = intval($matches[1]);
			}
			$days = 0;
			if (preg_match('~(\d+) days~', $durations[$i], $matches)) {
				$days = intval($matches[1]);
			}

			$label = __eva($durations[$i]);
			$value = $minutes * 60 + $hours * 3600 + $days * 86400;
			if ($value == 0) {
				$label = strtoupper($label);
				$value = $durations[$i];
			}

			$list[] = [
				'label' => $label,
				'value' => $value
			];
		}

		return $list;
	}

	protected function toDaysDurations($durations) {

		$list = [];
		for ($i = 0; $i < count($durations); $i++) {

			$days = 0;
			if (preg_match('~(\d+) days~', $durations[$i], $matches)) {
				$days = intval($matches[1]);
			}

			$label = __eva($durations[$i]);

			$list[] = [
				'label' => $label,
				'value' => $days
			];
		}

		return $list;

	}

}
