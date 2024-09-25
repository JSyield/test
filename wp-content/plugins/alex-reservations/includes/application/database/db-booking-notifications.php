<?php

use Evavel\Database\DB;

class SRR_DB_Booking_Notifications extends DB
{
	public static $table_db = '';

	public static function init()
	{
		global $wpdb;
		self::$table_db = $wpdb->prefix.DB::$namespace.'_booking_notifications';
	}

	public function __construct() {
		parent::__construct();
		$this->table_name = $this->prefix.'_booking_notifications';
		$this->primary_key = 'id';
		$this->version     = '1.1';
	}

	public function sql_create()
	{
		return "CREATE TABLE {$this->table_name} (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			restaurant_id bigint(20) DEFAULT NULL,
			booking_id bigint(20) DEFAULT NULL,
			type varchar(255) DEFAULT NULL,
			payload longtext DEFAULT NULL,
			date_created datetime NOT NULL,
			date_modified datetime NOT NULL,
			PRIMARY KEY  (id),
			KEY `bookingnotification_restaurant_id_foreign` (`restaurant_id`),
			CONSTRAINT `bookingnotification_restaurant_id_foreign` FOREIGN KEY (`restaurant_id`) REFERENCES `{$this->prefix}_restaurants` (`id`) ON DELETE CASCADE,
			KEY `bookingnotification_booking_id_foreign` (`booking_id`),
			CONSTRAINT `bookingnotification_booking_id_foreign` FOREIGN KEY (`booking_id`) REFERENCES `{$this->prefix}_bookings` (`id`) ON DELETE CASCADE
			)";
	}

	public function sql_update_columns_releases() {
		$queries = [];

		// Release 27
		$queries['27'][] =  "ALTER TABLE {$this->table_name} ADD type_id int unsigned DEFAULT NULL AFTER type";

		$this->run_queries_updates($queries);
	}

}

SRR_DB_Booking_Notifications::init();
