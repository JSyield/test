<?php

if ( ! defined( 'ABSPATH' ) ) exit;

function ALEXR_after_install_check_DB( $force_update = false ) {

    if (!is_admin()) return;

	$key = '_srr_tables_checked_release_'.ALEXR_RELEASE;
    $srr_tables_checked = get_option($key, false); // Last time it was checked

	$force_update = false; // TESTING

    if ( $force_update || false === $srr_tables_checked || current_time( 'timestamp' ) > $srr_tables_checked  ) {

		//ray('Forcing update');

	    $tables = [
		    'restaurants', 'restaurant_meta', 'restaurant_setting',
		    'users', 'user_meta',
		    'customers', 'customer_meta', 'ctaggroups', 'ctags', 'customer_ctag',
		    'bookings', 'booking_meta', 'booking_notifications', 'booking_reviews', 'btaggroups', 'btags', 'booking_btag',
		    'restaurant_user',
		    'settings', 'notifications', 'payments',
		    'floors', 'areas', 'tables',
		    'combinations', 'combination_table',
		    'booking_table',
		    'roles', 'actions', 'daily_notifications', 'tokens'
	    ];

		foreach($tables as $table)
		{
			// Install the table if does not exists
			if (!WP_ALEXR()->{$table}->installed()) {
				WP_ALEXR()->{$table}->create_table();
			}
		}

		// Update columns
	    foreach($tables as $table)
	    {
		    // Update for new columns, check if method exists
		    // means that need to add columns
		    if (method_exists(WP_ALEXR()->{$table}, 'sql_update_columns_releases')) {
				//ray('Checking columns: ' . $table);
			    WP_ALEXR()->{$table}->sql_update_columns_releases();
		    }
	    }


        // Do not check until 1 day again
	    update_option( $key, ( current_time( 'timestamp' ) + 24 * 60 * 60 ) );
        //update_option( $key, ( current_time( 'timestamp' ) + WEEK_IN_SECONDS ) );

		// Test 20 seconds
	    //update_option( '_srr_tables_checked', ( current_time( 'timestamp' ) + 20 ) );
    }

}

function ALEXR_after_install_update_translations()
{
	$key = '_srr_translations_checked_release_'.ALEXR_RELEASE;
	$srr_translations_checked = get_option($key, false);

	if (false === $srr_translations_checked) {
		\Alexr\Http\Controllers\TranslateController::syncAllFiles();
		update_option( $key, current_time( 'timestamp' ));
	}

}

add_action( 'admin_init', 'ALEXR_after_install_check_DB' );
add_action( 'admin_init', 'ALEXR_after_install_update_translations' );
