<?php

use Alexr\Models\Customer;

function alexr_remove_holded_bookings($restaurantId)
{
	//$limit_date = \Carbon\Carbon::now()->addMinutes(-10)->format('Y-m-d H:i:s');
	$limit_date = evavel_date_now()->addMinutes(-15)->format('Y-m-d H:i:s');

	// La reserva esta em modo Selected sin email
	// No se porque a veces falla

	// Antes de cambairles el estado voy a hacer unas comprobaciones extras
	// como ver si se han enviado notificaciones, no vaya a ser que la borre cuando ya
	// se han enviado notificaciones

	// Y si compruebo reservas deleted con notificaciones enviadas ?
	// Para eso tendría que registrar el ultimo estado de la reserva para poder recuperar el estado previo

	// En vez de hacerlo así voy a hacerlo una a una para que se quede registrado bien
	\Alexr\Models\Booking::where('status', \Alexr\Enums\BookingStatus::SELECTED)
		->where('restaurant_id', $restaurantId)
		->where('date_created', '<', $limit_date)
		->whereIsNull('email')
		->update(['status' => \Alexr\Enums\BookingStatus::DELETED]);
		//->delete();

}

function alexr_remove_pending_payment_bookings($restaurantId)
{
	// I give 2 hours to the user to make the payment
	//$limit_date = \Carbon\Carbon::now()->addMinutes(-240)->format('Y-m-d H:i:s');
	$limit_date = evavel_date_now()->addMinutes(-240)->format('Y-m-d H:i:s');

	\Alexr\Models\Booking::where('status', \Alexr\Enums\BookingStatus::PENDING_PAYMENT)
	                     ->where('restaurant_id', $restaurantId)
	                     ->where('date_created', '<', $limit_date)
	                     ->update(['status' => \Alexr\Enums\BookingStatus::DELETED]);
	                     //->delete();
}

function alexr_remove_permanently_deleted_bookings($restaurantId)
{
	// @TODO - por ahora no voy a borrar reservas por si acaso
	// mejor que se borren luego manualmente
	return;

	// 1 year old deleted bookings so can be debugged
	//$limit_date = \Carbon\Carbon::now()->addDays(-365)->format('Y-m-d H:i:s');
	$limit_date = evavel_date_now()->addDays(-365)->format('Y-m-d H:i:s');

	\Alexr\Models\Booking::where('status', \Alexr\Enums\BookingStatus::DELETED)
	                     ->where('restaurant_id', $restaurantId)
	                     ->where('date_created', '<', $limit_date)
	                     ->delete();
}

function alexr_delete_bookings_where_customer_null() {
	\Alexr\Models\Booking::whereIsNull('customer_id')->delete();
}

function alexr_create_new_customer(\Alexr\Models\Booking $booking)
{
	$customer = new Customer();

	$customer->restaurant_id = $booking->restaurant_id;
	$customer->email = $booking->email;
	$customer->first_name = $booking->first_name;
	$customer->last_name = $booking->last_name;
	$customer->name = $booking->first_name.' '.$booking->last_name;
	$customer->phone = $booking->phone;
	$customer->country_code = $booking->country_code;
	$customer->dial_code_country = $booking->dial_code_country;
	$customer->dial_code = $booking->dial_code;
	$customer->language = $booking->language;

	$customer->save();

	return $customer;
}

function alexr_transform_textarea_to_new_lines($text)
{
	$text = str_replace('<br>', "\r\n", $text);
	$text = str_replace('<br />', "\r\n", $text);
	if ($text == null || $text == 'null') return '';
	return $text;
}

function alexr_transform_new_lines_to_br($text)
{
	if ($text == null) return $text;
	return nl2br($text);
}

function alexr_convertArrayToBase64(&$arr) {
	foreach($arr as $key => $value) {
		if (is_array($value)) {
			$arr[$key] = alexr_convertArrayToBase64($value);
		} else if (is_string($value)) {
			$arr[$key] = base64_encode($value);
		}
	}
	return $arr;
}

function cplus_application_config($key, $default = false)
{
	return alexr_config($key, $default = false);
}

function alexr_config($key, $default = false)
{
	static $config_files = [];

	$keys = explode('.', $key);
	if (!is_array($keys)) return $default;

	// Cache the file
	$file_name = $keys[0].'.php';
	if (!isset($config_files[$file_name]))
	{
		$config_files[$file_name] = include ALEXR_DIR_CONFIG_FILES.$file_name;
	}

	$arr = $config_files[$file_name];

	for ($i = 1; $i < count($keys); $i++){
		$arr = $arr[$keys[$i]];
	}

	return $arr;
}


function alexr_config_tags($restaurant_id, $type = 'customers')
{
	$restaurant = \Alexr\Models\Restaurant::find($restaurant_id);

	$language = $restaurant ? $restaurant->language : 'en';

	$tags = alexr_config('tags.' . $language . '.' . $type);

	// Default to english
	if (!$tags) {
		$tags = alexr_config('tags.en.' . $type);
	}
	return $tags;
}

function alexr_view_booking_url($booking)
{
	return ALEXR_SITE_URL.'?'.ALEXR_GET_VIEW_BOOKING.'='.$booking->uuid;
}

function alexr_base64_decode($base64_string) {
	$buffer = '';
	$length = strlen($base64_string);
	$position = 0;
	$chunk_size = 4096; //8192; // Ajusta este valor según sea necesario

	$output = '';

	while ($position < $length) {
		$chunk = substr($base64_string, $position, $chunk_size);
		$buffer .= $chunk;
		$position += $chunk_size;

		// Asegurarse de que el buffer tenga un múltiplo de 4 caracteres
		$buffer_length = strlen($buffer);
		$remainder = $buffer_length % 4;
		if ($remainder > 0) {
			$padding = 4 - $remainder;
			$buffer .= str_repeat('=', $padding);
			$buffer_length += $padding;
		}

		// Decodificar el buffer
		$decoded = base64_decode($buffer, true);
		if ($decoded !== false) {
			$output .= $decoded;
			$buffer = '';
		} else {
			// Si la decodificación falla, retroceder un poco
			$buffer = substr($buffer, -3);
		}
	}

	// Decodificar cualquier residuo en el buffer
	if ($buffer !== '') {
		$decoded = base64_decode($buffer, true);
		if ($decoded !== false) {
			$output .= $decoded;
		}
	}

	return $output;
}

// Ejemplo de uso:
// $decodedContent = base64_decode_large($largeBase64String);
