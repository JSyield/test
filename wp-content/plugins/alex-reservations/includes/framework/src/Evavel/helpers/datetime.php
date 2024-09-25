<?php

/**
 * UTC date string
 */


if (! function_exists('evavel_now')) {
	function evavel_now()
	{
		return evavel_date_now()->format('Y-m-d H:i:s');
	}
}

/**
 * Carbon now with timezone
 */
if (! function_exists('evavel_now_timezone')) {
	function evavel_now_timezone($timezone) {
		return evavel_date_now()->setTimezone($timezone);
		//return \Carbon\Carbon::now()->setTimezone($timezone);
	}
	function evavel_now_timezone_formatted($timezone, $date_format){
		return evavel_now_timezone($timezone)->format($date_format);
	}
}

/**
 * Return specific date with the current hour
 */
if (! function_exists('evavel_date_timezone')) {
	function evavel_date_timezone( $date_string, $timezone ) {
		$Hmi = evavel_now_timezone($timezone)->format( 'H:i:s' );
		return evavel_date_createFromFormat( "Y-m-d H:i:s", $date_string . ' ' . $Hmi, $timezone );
	}
}

/**
 * Create Carbon from total seconds of the day
 */
if (!function_exists('evavel_carbon')) {
	function evavel_carbon( $date_string, $total_seconds, $time_zone ) {
		$date_hour = $date_string . " " . evavel_seconds_to_Hmi( $total_seconds );
		return evavel_date_createFromFormat( "Y-m-d H:i:s", $date_hour, $time_zone );
	}
}

if (!function_exists('evavel_carbon_now')) {
	function evavel_carbon_now( $total_seconds, $time_zone ) {
		$date_string = evavel_date_now()->setTimezone($time_zone)->toDateString();
		return evavel_carbon($date_string, $total_seconds, $time_zone);
	}
}


if (!function_exists('evavel_carbon_2000')) {
	function evavel_carbon_2000() {
		return evavel_date_createFromFormat("Y-m-d H:i:s", "2000-01-01 00:00:00", "UTC");
	}
}

if (!function_exists('evavel_now_timestamp')) {
	function evavel_now_timestamp()
	{
		return evavel_date_now()->timestamp;
	}
}

if (!function_exists('evavel_date_timestamp')) {
	function evavel_date_timestamp($Ymd_Hsi)
	{
		return evavel_date_createFromFormat('Y-m-d H:i:s', $Ymd_Hsi)->timestamp;
	}
}

/**
 * Transform total seconds to H:m:i so can be used by Carbon
 */
if (!function_exists('evavel_seconds_to_Hmi')) {
	function evavel_seconds_to_Hmi($total_seconds)
	{
		$hours = intval($total_seconds / 3600);
		$minutes = intval( ($total_seconds - 3600 * $hours) / 60 );
		$seconds = $total_seconds - 3600 * $hours - 60 * $minutes;

		return str_pad($hours, 2, "0", STR_PAD_LEFT) .":". str_pad($minutes, 2, "0", STR_PAD_LEFT) . ":" . str_pad($seconds, 2, "0", STR_PAD_LEFT);
	}
}

if (!function_exists('evavel_seconds_to_Hm')) {
	function evavel_seconds_to_Hm($total_seconds)
	{
		$hours = intval($total_seconds / 3600);
		$minutes = intval( ($total_seconds - 3600 * $hours) / 60 );

		return str_pad($hours, 2, "0", STR_PAD_LEFT) .":". str_pad($minutes, 2, "0", STR_PAD_LEFT);
	}
	function evavel_seconds_to_Hm12($total_seconds)
	{
		$hours = intval($total_seconds / 3600);
		$minutes = intval( ($total_seconds - 3600 * $hours) / 60 );

		$am = 'am';
		if ($hours >= 12) {
			$am = 'pm';
			if ($hours > 12){
				$hours = $hours - 12;
			}

		}

		return str_pad($hours, 2, "0", STR_PAD_LEFT) .":". str_pad($minutes, 2, "0", STR_PAD_LEFT).$am;
	}
}

function evavel_Hm_to_seconds($hm)
{
	if (preg_match('#(\d+):(\d+)#', $hm, $matches)) {
		$h = intval($matches[1]) * 3600;
		$m = intval($matches[2]) * 60;
		return $h + $m;
	}

	return 0;
}

if (!function_exists('evavel_seconds_to_duration')) {
	function evavel_seconds_to_duration($total_seconds)
	{
		$hours = intval($total_seconds / 3600);
		$minutes = intval( ($total_seconds - 3600 * $hours) / 60 );

		if ($hours == 0) {
			return $minutes . '' . __eva('m');
		}

		$duration = $hours . '' . __eva('h');
        if ($minutes >0 ){
            $duration .= ' ' . $minutes . '' . __eva('m');
		}

        return $duration;
	}
}


/**
 * Calculate the number of seconds from the start of the day
 */
if (!function_exists('evavel_seconds')) {
	function evavel_seconds(\Cake\Chronos\Chronos $date)
	{
		return $date->hour * 3600 + $date->minute * 60 + $date->second;
	}
}


// Replace with CHRONOS library
function evavel_date_createFromFormatTranslate($format, $date_string, $locale, $translated_format){

	$string_not_translated = \Cake\Chronos\Chronos::createFromFormat($format, $date_string)
	       ->format($translated_format);

	return evavel_datetranslations_translate($string_not_translated, $locale);

	/*return \Carbon\Carbon::createFromFormat('Y-m-d', $date_string)
	      ->locale($locale)
	      ->translatedFormat($translated_format);*/
}

function evavel_date_translate($string_not_translated, $locale)
{
	return evavel_datetranslations_translate($string_not_translated, $locale);
}

function evavel_date_createFromFormat($format, $date_string, $timezone = null)
{
	if ($timezone == null){
		return \Cake\Chronos\Chronos::createFromFormat($format, $date_string);
	} else {
		return \Cake\Chronos\Chronos::createFromFormat($format, $date_string, $timezone);
	}
}

function evavel_new_date($date_string)
{
	return new Cake\Chronos\Chronos($date_string);
}

function evavel_date_now()
{
	return Cake\Chronos\Chronos::now();
}
