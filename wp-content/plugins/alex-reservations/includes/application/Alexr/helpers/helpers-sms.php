<?php

function alexr_clean_phone_number($phone)
{
	if (!$phone) return '';
	$phone = preg_replace('#[+\-\(\)\.\: ]#', '', $phone);
	return '+'.$phone;
}


function alexr_is_valid_phone($phone)
{
	$phone_number_validation_regex = "/^\\+?\\d{1,4}?[-.\\s]?\\(?\\d{1,3}?\\)?[-.\\s]?\\d{1,4}[-.\\s]?\\d{1,4}[-.\\s]?\\d{1,9}$/";
	return strlen($phone) >= 7 && preg_match($phone_number_validation_regex, $phone) == 1;
}
