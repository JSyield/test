<?php

function alexr_generateRestaurantForNewUser($email)
{
	$name = explode('@', $email);
	$name = $name[0];

	// Create the restaurant
	$restaurant = \Alexr\Models\Restaurant::create( [
		'name' => 'My restaurant '.$name,
		'timezone' => 'Europe/Madrid',
		'currency' => 'EUR'
	]);
	$restaurant->save();

	// Create USER
	$user = new \Alexr\Models\User([
		'name' => $name,
		'first_name' => $name,
		'email' => $email,
		'role' => 'user'
	]);

	// Attach WP user
	$controller = new \Alexr\Http\Controllers\UsersController();
	$wp_user_id = $controller->createWpUserIfNotExist($name.' '.$name, $email);
	$user->wp_user_id = $wp_user_id;
	$user->save();

	// SYnc user with restaurant
	$user->restaurants()->sync([$restaurant->id]);

	$row = \Evavel\Query\Query::table('restaurant_user')
	                       ->where('restaurant_id', $restaurant->id)
	                       ->where('user_id', $user->id)
	                       ->first();

	if ($row) {
		\Evavel\Query\Query::table('restaurant_user')
		                ->where('id', $row->id)
		                ->update( [
			                'role' => \Alexr\Enums\UserRole::SUPER_MANAGER
		                ]);
	}

	// Generate at least one Shift to be able to create reservations
	$shift = new \Alexr\Settings\Shift();
	$shift->restaurant_id = $restaurant->id;
	$shift->setupDefaultValues();

	$shift->name = 'Lunch';
	$shift->start_date = evavel_date_now()->addDays(-2)->format('Y-m-d');
	$shift->end_date = evavel_date_now()->addDays(365)->format('Y-m-d');
	$shift->first_seating = 39600;
	$shift->last_seating = 39600 + 6 * 3600;
	$shift->min_covers_reservation = 2;
	$shift->max_covers_reservation = 8;

	$shift->days_of_week = [
		'sun' => true,
		'mon' => true,
		'tue' => true,
		'wed' => true,
		'thu' => true,
		'fri' => true,
		'sat' => true
	];

	$shift->save();

	return $user;
}
