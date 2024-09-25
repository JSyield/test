<?php

use Evavel\Database\DB;

class SRR_DB_Booking_Btag extends DB
{
	public static $table_db = '';

	public static function init()
	{
		global $wpdb;
		self::$table_db = $wpdb->prefix.DB::$namespace.'_booking_btag';
	}

	public function __construct() {
		parent::__construct();
		$this->table_name = $this->prefix.'_booking_btag';
		$this->primary_key = 'id';
		$this->version     = '1.0';
	}

	public function sql_create()
	{

		return "CREATE TABLE {$this->table_name} (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			booking_id bigint(20) DEFAULT NULL,
			btag_id bigint(20) NOT NULL,
			PRIMARY KEY  (id),
			KEY `bookingbtag_booking_id_foreign` (`booking_id`),
			CONSTRAINT `bookingbtag_booking_id_foreign` FOREIGN KEY (`booking_id`) REFERENCES `{$this->prefix}_bookings` (`id`) ON DELETE CASCADE,
			KEY `bookingbtag_btag_id_foreign` (`btag_id`),
			CONSTRAINT `bookingbtag_btag_id_foreign` FOREIGN KEY (`btag_id`) REFERENCES `{$this->prefix}_btags` (`id`) ON DELETE CASCADE
			)";

	}

}

SRR_DB_Booking_Btag::init();
