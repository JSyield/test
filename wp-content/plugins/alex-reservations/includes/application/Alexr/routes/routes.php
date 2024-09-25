<?php

return [
	// The FLOOR plan builder
	evavel_route_get('/app/floorplan', 'Alexr\Http\Controllers\FloorPlanController@index' ),

	evavel_route_get('/app/floorplan/areas', 'Alexr\Http\Controllers\FloorPlanController@areas' ),

	// Save area decoration
	evavel_route_post('/app/floorplan/area/:area/decoration', 'Alexr\Http\Controllers\FloorPlanController@decoration', \Evavel\Enums\Context::UPDATE),

	// Save area canvas position
	evavel_route_post('/app/floorplan/area/:area', 'Alexr\Http\Controllers\FloorPlanController@canvas', \Evavel\Enums\Context::UPDATE),


	// LOGOUT ---------------------------------------------------------------
	evavel_route_post('/app/logout', 'Alexr\Http\Controllers\LogoutController@logout', \Evavel\Enums\Context::UPDATE),


	// BOOKINGS ---------------------------------------------------------------

	// Get the list of Booking Tags and Groups
	evavel_route_get('/app/btags', 'Alexr\Http\Controllers\BtagsController@index'),

	// Get covers occupied for shift
	evavel_route_get('/app/bookings/covers/:shiftId/:date', 'Alexr\Http\Controllers\BookingsController@covers', \Evavel\Enums\Context::INDEX),

	// Get tables reserved based on shift, date, slot and duration selected -> return array of ids
	evavel_route_get('/app/bookings/tables/:shiftId/:date', 'Alexr\Http\Controllers\BookingsController@tables', \Evavel\Enums\Context::INDEX),

	// Get booking
	evavel_route_get('/app/bookings/:bookingId', 'Alexr\Http\Controllers\BookingsController@booking', \Evavel\Enums\Context::INDEX),


	// Update booking tables id only - No lo captura bien, lo envio a BookingsController@update
	evavel_route_post('/app/bookings/:bookingId/tables', 'Alexr\Http\Controllers\BookingsController@updateTables', \Evavel\Enums\Context::UPDATE),

	// Update booking status only
	evavel_route_post('/app/bookings/:bookingId/status/:status', 'Alexr\Http\Controllers\BookingsController@updateStatus', \Evavel\Enums\Context::UPDATE),

	// Update booking data
	evavel_route_post('/app/bookings/:bookingId', 'Alexr\Http\Controllers\BookingsController@update', \Evavel\Enums\Context::UPDATE),


	// Create a booking
	evavel_route_post('/app/bookings/create', 'Alexr\Http\Controllers\BookingsController@create', \Evavel\Enums\Context::CREATE),

	// Send email
	evavel_route_post('/app/bookings/email/:status/:bookingId', 'Alexr\Http\Controllers\BookingsController@emailStatus', \Evavel\Enums\Context::UPDATE),
	evavel_route_post('/app/bookings/email/:bookingId', 'Alexr\Http\Controllers\BookingsController@emailCustom', \Evavel\Enums\Context::UPDATE),

	// Send SMS
	evavel_route_post('/app/bookings/sms/:status/:bookingId', 'Alexr\Http\Controllers\BookingsController@smsStatus', \Evavel\Enums\Context::UPDATE),
	evavel_route_post('/app/bookings/sms/:bookingId', 'Alexr\Http\Controllers\BookingsController@smsCustom', \Evavel\Enums\Context::UPDATE),

	// Download CSV bookings. Print document
	evavel_route_post('/app/bookings/csv/:date', 'Alexr\Http\Controllers\BookingsController@downloadCSV', \Evavel\Enums\Context::INDEX),
	evavel_route_post('/app/bookings/pdf/:date', 'Alexr\Http\Controllers\BookingsController@printPDF', \Evavel\Enums\Context::INDEX),

	evavel_route_get('/app/bookings/status', 'Alexr\Http\Controllers\BookingsController@indexStatus', \Evavel\Enums\Context::INDEX),

	// Get list of bookings for one day, one month, several-days
	// Get list of bookings for one day, one month, several-days
	// 2022-09-01 , 2022-09, 2022-09-01-2022-09-30
	evavel_route_get('/app/bookings/:date', 'Alexr\Http\Controllers\BookingsController@index', \Evavel\Enums\Context::INDEX),



	// Mark notification as read/unread and load more
	evavel_route_post('/app/notifications/read/:notificationId', 'Alexr\Http\Controllers\NotificationsController@read', \Evavel\Enums\Context::UPDATE),
	evavel_route_post('/app/notifications/unread/:notificationId', 'Alexr\Http\Controllers\NotificationsController@unread', \Evavel\Enums\Context::UPDATE),
	evavel_route_get('/app/notifications/more/:notificationId', 'Alexr\Http\Controllers\NotificationsController@loadMore', \Evavel\Enums\Context::UPDATE),
	evavel_route_post('/app/notifications/allread', 'Alexr\Http\Controllers\NotificationsController@markAllRead', \Evavel\Enums\Context::UPDATE),

	// Payments
	evavel_route_get('/app/payments/data/:bookingId', 'Alexr\Http\Controllers\BookingsController@getPaymentData', \Evavel\Enums\Context::INDEX),
	evavel_route_get('/app/payments/receipt/:bookingId', 'Alexr\Http\Controllers\BookingsController@getReceipt', \Evavel\Enums\Context::INDEX),
	evavel_route_post('/app/payments/charge-card/:bookingId', 'Alexr\Http\Controllers\BookingsController@chargeCard', \Evavel\Enums\Context::INDEX),
	evavel_route_post('/app/payments/charge-preauth/:bookingId', 'Alexr\Http\Controllers\BookingsController@chargePreauth', \Evavel\Enums\Context::INDEX),

	// SHIFTS ---------------------------------------------------------------

	// Shifts metrics and enable/disable online bookings
	evavel_route_get('/app/shift-metrics', 'Alexr\Http\Controllers\BookingsController@shiftMetrics', \Evavel\Enums\Context::INDEX),
	evavel_route_post('/app/shift-metrics', 'Alexr\Http\Controllers\BookingsController@updateShiftMetrics', \Evavel\Enums\Context::UPDATE),

	// Get dates with some slot closed in the range of dates
	evavel_route_get('/app/shifts/:range/slots-closed', 'Alexr\Http\Controllers\ShiftsController@getDatesWithSlotsClosed', \Evavel\Enums\Context::INDEX),
	// Get blocked slots for shifts/events for specific date
	evavel_route_get('/app/shifts/:date', 'Alexr\Http\Controllers\ShiftsController@forDate', \Evavel\Enums\Context::INDEX),
	evavel_route_post('/app/shifts/:date', 'Alexr\Http\Controllers\ShiftsController@saveForDate', \Evavel\Enums\Context::INDEX),


	// CLOSE TABLES ---------------------------------------------------------------
	evavel_route_get('/app/closed-tables-range/:range', 'Alexr\Http\Controllers\ClosedTablesController@getDatesWithTablesClosed', \Evavel\Enums\Context::INDEX),
	evavel_route_get('/app/closed-tables/:date', 'Alexr\Http\Controllers\ClosedTablesController@forDate', \Evavel\Enums\Context::INDEX),
	evavel_route_post('/app/closed-tables/:date', 'Alexr\Http\Controllers\ClosedTablesController@saveForDate', \Evavel\Enums\Context::INDEX),
	// parameters are in the query (tableId, date)
	evavel_route_get('/app/closed-table-times', 'Alexr\Http\Controllers\ClosedTablesController@getTableTimesClosed', \Evavel\Enums\Context::INDEX),
	// Topbar. Check for a date if has slots and tables blocked
	evavel_route_get('/app/closed-statuses/:date', 'Alexr\Http\Controllers\ClosedTablesController@getStatuses', \Evavel\Enums\Context::INDEX),

	// CUSTOMERS ---------------------------------------------------------------

	// Get the list of Customers Tags and Groups
	evavel_route_get('/app/ctags', 'Alexr\Http\Controllers\CtagsController@index'),

	// Get a list of customers (used for the metrics)
	evavel_route_get('/app/customers-list/:customers', 'Alexr\Http\Controllers\CustomersController@customersList', \Evavel\Enums\Context::UPDATE),

	// Get customer
	evavel_route_get('/app/customers/:customerId', 'Alexr\Http\Controllers\CustomersController@customer', \Evavel\Enums\Context::UPDATE),

	// Update Customer data
	evavel_route_post('/app/customers/:customerId', 'Alexr\Http\Controllers\CustomersController@update', \Evavel\Enums\Context::UPDATE),

	// Create a customer
	evavel_route_post('/app/customers/create', 'Alexr\Http\Controllers\CustomersController@create', \Evavel\Enums\Context::CREATE),

	// Delete customers
	evavel_route_post('/app/customers/delete', 'Alexr\Http\Controllers\CustomersController@delete', \Evavel\Enums\Context::DELETE),

	// Merge customers
	evavel_route_post('/app/customers/merge', 'Alexr\Http\Controllers\CustomersController@merge', \Evavel\Enums\Context::UPDATE),

	// CSV export
	evavel_route_post('/app/customers/csv', 'Alexr\Http\Controllers\CustomersController@downloadCSV', \Evavel\Enums\Context::UPDATE),
	evavel_route_post('/app/customers/pdf', 'Alexr\Http\Controllers\CustomersController@printPDF', \Evavel\Enums\Context::UPDATE),

	// Get list of Customers paginated
	evavel_route_get('/app/customers', 'Alexr\Http\Controllers\CustomersController@index', \Evavel\Enums\Context::INDEX),

	// Import customers
	evavel_route_post('/app/customers/import', 'Alexr\Http\Controllers\CustomersController@import', \Evavel\Enums\Context::UPDATE),


	// PAYMENTS

	evavel_route_get('/app/payments', 'Alexr\Http\Controllers\PaymentsController@index', \Evavel\Enums\Context::INDEX),


	// RESTAURANTS ---------------------------------------------------------------

	// Get list of Restaurants
	evavel_route_get('/app/restaurants', 'Alexr\Http\Controllers\RestaurantsController@index', \Evavel\Enums\Context::INDEX),

	// Get list of Restaurants Metrics
	evavel_route_get('/app/restaurants/metrics', 'Alexr\Http\Controllers\RestaurantsController@metrics', \Evavel\Enums\Context::INDEX),

	// Get restaurant
	evavel_route_get('/app/restaurants/:restaurantId', 'Alexr\Http\Controllers\RestaurantsController@restaurant', \Evavel\Enums\Context::INDEX),

	// Update restaurant data
	evavel_route_post('/app/restaurants/:restaurantId', 'Alexr\Http\Controllers\RestaurantsController@update', \Evavel\Enums\Context::UPDATE),

	// Delete restaurants
	evavel_route_post('/app/restaurants/delete', 'Alexr\Http\Controllers\RestaurantsController@delete', \Evavel\Enums\Context::DELETE),

	// Create a restaurant
	evavel_route_post('/app/restaurants/create', 'Alexr\Http\Controllers\RestaurantsController@create', \Evavel\Enums\Context::CREATE),


	// USERS ---------------------------------------------------------------

	// Get list of Users
	evavel_route_get('/app/users', 'Alexr\Http\Controllers\UsersController@index', \Evavel\Enums\Context::INDEX),

	// Get user
	evavel_route_get('/app/users/:userId', 'Alexr\Http\Controllers\UsersController@user', \Evavel\Enums\Context::INDEX),

	// Update user data
	evavel_route_post('/app/users/:userId', 'Alexr\Http\Controllers\UsersController@update', \Evavel\Enums\Context::UPDATE),

	// Create a user
	evavel_route_post('/app/users/create', 'Alexr\Http\Controllers\UsersController@create', \Evavel\Enums\Context::CREATE),

	// Delete users
	evavel_route_post('/app/users/delete', 'Alexr\Http\Controllers\UsersController@delete', \Evavel\Enums\Context::DELETE),

	// Get tokens issues
	evavel_route_get('/app/users-tokens', 'Alexr\Http\Controllers\UsersController@listTokens', \Evavel\Enums\Context::INDEX),

	// User clear tokens
	evavel_route_post('/app/user-clear-tokens/:userId', 'Alexr\Http\Controllers\UsersController@clearUserTokens', \Evavel\Enums\Context::DELETE),
	evavel_route_post('/app/user-clear-tokens', 'Alexr\Http\Controllers\UsersController@clearAllTokens', \Evavel\Enums\Context::DELETE),

	// Get user associated restaurants
	evavel_route_get('/app/user-restaurants/:userId', 'Alexr\Http\Controllers\UsersController@restaurants', \Evavel\Enums\Context::INDEX),



	// REVIEWS ---------------------------------------------------------------

	// Get list of reviews for one day, one month, several-days
	// 2022-09-01 , 2022-09, 2022-09-01-2022-09-30
	evavel_route_get('/app/reviews/:date', 'Alexr\Http\Controllers\ReviewsController@index', \Evavel\Enums\Context::INDEX),


	// PROFILE ---------------------------------------------------------------

	evavel_route_get('/app/profile', 'Alexr\Http\Controllers\ProfileController@index', \Evavel\Enums\Context::INDEX),

	evavel_route_post('/app/profile', 'Alexr\Http\Controllers\ProfileController@update', \Evavel\Enums\Context::INDEX),

	// SUPPORT ---------------------------------------------------------------

	evavel_route_post('/app/support', 'Alexr\Http\Controllers\SupportController@send', \Evavel\Enums\Context::INDEX),

	// LICENSE ---------------------------------------------------------------

	evavel_route_get('/app/license-check', 'Alexr\Http\Controllers\LicenseController@index', \Evavel\Enums\Context::INDEX),
	evavel_route_post('/app/license-check', 'Alexr\Http\Controllers\LicenseController@update', \Evavel\Enums\Context::INDEX),

	evavel_route_get('/app/license-restaurants', 'Alexr\Http\Controllers\LicenseController@getRestaurants', \Evavel\Enums\Context::INDEX),

	evavel_route_get('/app/license-errors', 'Alexr\Http\Controllers\LicenseController@getErrors', \Evavel\Enums\Context::INDEX),
	evavel_route_post('/app/license-errors', 'Alexr\Http\Controllers\LicenseController@saveErrors', \Evavel\Enums\Context::INDEX),


	//evavel_route_post('/app/license/activate', 'Alexr\Http\Controllers\LicenseController@activate', \Evavel\Enums\Context::INDEX),
	//evavel_route_post('/app/license/deactivate', 'Alexr\Http\Controllers\LicenseController@deactivate', \Evavel\Enums\Context::INDEX),


	// WP USERS ---------------------------------------------------------------

	// Get list of WP-Users
	evavel_route_get('/app/wp-users', 'Alexr\Http\Controllers\WpUsersController@index', \Evavel\Enums\Context::INDEX),

	// Get WP-User
	evavel_route_get('/app/wp-users/:userId', 'Alexr\Http\Controllers\WpUsersController@user', \Evavel\Enums\Context::INDEX),


	// ROLES ---------------------------------------------------------------

	// Get list of roles
	evavel_route_get('/app/roles', 'Alexr\Http\Controllers\RolesController@index', \Evavel\Enums\Context::INDEX),

	// Update roles data
	evavel_route_post('/app/roles', 'Alexr\Http\Controllers\RolesController@update', \Evavel\Enums\Context::UPDATE),


	// BOOKINGS ---------------------------------------------------------------

	evavel_route_get('/app/search-bookings', 'Alexr\Http\Controllers\SearchBookingsController@search', \Evavel\Enums\Context::INDEX),
	evavel_route_get('/app/fields-booking', 'Alexr\Http\Controllers\SearchBookingsController@fields', \Evavel\Enums\Context::INDEX),
	evavel_route_post('/app/reserve-booking', 'Alexr\Http\Controllers\SearchBookingsController@reserve', \Evavel\Enums\Context::INDEX),

	evavel_route_post('/app/test-email', 'Alexr\Http\Controllers\SendTestEmailController@index', \Evavel\Enums\Context::INDEX),
	evavel_route_post('/app/test-sms', 'Alexr\Http\Controllers\SendTestSmsController@sendSms', \Evavel\Enums\Context::INDEX),
	evavel_route_post('/app/test-whatsapp', 'Alexr\Http\Controllers\SendTestSmsController@sendWhatsapp', \Evavel\Enums\Context::INDEX),

	// WIZARD ---------------------------------------------------------------
	evavel_route_get('/app/wizard', 'Alexr\Http\Controllers\WizardController@index', \Evavel\Enums\Context::INDEX),
	evavel_route_post('/app/wizard', 'Alexr\Http\Controllers\WizardController@save', \Evavel\Enums\Context::INDEX),


	// TRANSLATE ---------------------------------------------------------------
	evavel_route_get('/app/translate/languages', 'Alexr\Http\Controllers\TranslateController@activeLanguages', \Evavel\Enums\Context::INDEX),
	evavel_route_post('/app/translate/languages', 'Alexr\Http\Controllers\TranslateController@saveActiveLanguages', \Evavel\Enums\Context::INDEX),
	evavel_route_get('/app/translate/load/:lang', 'Alexr\Http\Controllers\TranslateController@index', \Evavel\Enums\Context::INDEX),
	evavel_route_post('/app/translate/load/:lang', 'Alexr\Http\Controllers\TranslateController@save', \Evavel\Enums\Context::INDEX),
	evavel_route_post('/app/translate/sync/:lang', 'Alexr\Http\Controllers\TranslateController@sync', \Evavel\Enums\Context::INDEX),
	evavel_route_get('/app/translate/download/:lang', 'Alexr\Http\Controllers\TranslateController@download', \Evavel\Enums\Context::INDEX),

	// DASHBOARD CONFIGURATION ---------------------------------------------------------------
	evavel_route_get('/app/dashboard/columns', 'Alexr\Http\Controllers\DashboardController@getColumns', \Evavel\Enums\Context::INDEX),
	evavel_route_post('/app/dashboard/columns', 'Alexr\Http\Controllers\DashboardController@saveColumns', \Evavel\Enums\Context::INDEX),


	// GENERAL METRICS ---------------------------------------------------------------
	evavel_route_get('/app/general-metrics', 'Alexr\Http\Controllers\BookingsController@generalMetrics', \Evavel\Enums\Context::INDEX),


	// File uploads
	evavel_route_post('/app/upload/file', 'Alexr\Http\Controllers\UploadFileController@upload', \Evavel\Enums\Context::INDEX),
	evavel_route_post('/app/delete/file', 'Alexr\Http\Controllers\UploadFileController@delete', \Evavel\Enums\Context::INDEX),


	// UI SETTINGS
	evavel_route_get('/app/dashboard/settings', 'Alexr\Http\Controllers\DashboardController@getSettings', \Evavel\Enums\Context::INDEX),
	evavel_route_post('/app/dashboard/settings', 'Alexr\Http\Controllers\DashboardController@saveSettings', \Evavel\Enums\Context::INDEX),


	// DAILY NOTIFICATIONS
	evavel_route_get('/app/daily-notifications/:date', 'Alexr\Http\Controllers\DailyNotificationsController@index', \Evavel\Enums\Context::INDEX),
	evavel_route_post('/app/daily-notifications/delete/:uuid', 'Alexr\Http\Controllers\DailyNotificationsController@delete', \Evavel\Enums\Context::INDEX),
	evavel_route_post('/app/daily-notifications/update/:uuid', 'Alexr\Http\Controllers\DailyNotificationsController@update', \Evavel\Enums\Context::INDEX),
	evavel_route_post('/app/daily-notifications/save/:date', 'Alexr\Http\Controllers\DailyNotificationsController@save', \Evavel\Enums\Context::INDEX),
	evavel_route_post('/app/daily-notifications/read-all/:date', 'Alexr\Http\Controllers\DailyNotificationsController@readAll', \Evavel\Enums\Context::INDEX),


	// MOBILE - hacerlos mas rapido
	evavel_route_get('/app/mobile/get_all_data', 'Alexr\Http\MobileControllers\MobileController@getAllData', \Evavel\Enums\Context::INDEX),

	// NOW SETTINGS OF THE APPLICATION -> done from the framework

	// THIS IS THE FIRST EXAMPLE I MADE FOR TESTING: /t/1/availability
	// Get shifts
	//evavel_route_get('/app/shifts', 'Alexr\Http\Controllers\ShiftsController@index' ),

	// Save shift
	//evavel_route_post('/app/shifts/:resourceId', 'Alexr\Http\Controllers\ShiftsController@save', \Evavel\Enums\Context::UPDATE ),

	// New shift
	//evavel_route_get('/app/shifts/new', 'Alexr\Http\Controllers\ShiftsController@create', \Evavel\Enums\Context::CREATE ),
];
