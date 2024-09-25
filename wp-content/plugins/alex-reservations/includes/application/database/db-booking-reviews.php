<?php

use Evavel\Database\DB;

class SRR_DB_Booking_Reviews extends DB
{
	public static $table_db = '';

	public static function init()
	{
		global $wpdb;
		self::$table_db = $wpdb->prefix.DB::$namespace.'_booking_reviews';
	}

	public function __construct() {
		parent::__construct();
		$this->table_name = $this->prefix.'_booking_reviews';
		$this->primary_key = 'id';
		$this->version     = '1.0';
	}

	public function sql_create()
	{
		return "CREATE TABLE {$this->table_name} (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			restaurant_id bigint(20) DEFAULT NULL,
			booking_id bigint(20) DEFAULT NULL,
			score1 tinyint DEFAULT 0,
			score2 tinyint DEFAULT 0,
			score3 tinyint DEFAULT 0,
			score4 tinyint DEFAULT 0,
			score5 tinyint DEFAULT 0,
			feedback text DEFAULT NULL,
			date_created datetime NOT NULL,
			date_modified datetime NOT NULL,
			PRIMARY KEY  (id),
			KEY `bookingnreview_restaurant_id_foreign` (`restaurant_id`),
			CONSTRAINT `bookingnreview_restaurant_id_foreign` FOREIGN KEY (`restaurant_id`) REFERENCES `{$this->prefix}_restaurants` (`id`) ON DELETE CASCADE,
			KEY `bookingnreview_booking_id_foreign` (`booking_id`),
			CONSTRAINT `bookingnreview_booking_id_foreign` FOREIGN KEY (`booking_id`) REFERENCES `{$this->prefix}_bookings` (`id`) ON DELETE CASCADE
			)";
	}

}

SRR_DB_Booking_Reviews::init();
