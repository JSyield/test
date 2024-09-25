<?php

use Evavel\Database\DB;

class SRR_DB_Booking_Table extends DB
{
	public static $table_db = '';

	public static function init()
	{
		global $wpdb;
		self::$table_db = $wpdb->prefix.DB::$namespace.'_booking_table';
	}

	public function __construct() {
		parent::__construct();
		$this->table_name = $this->prefix.'_booking_table';
		$this->primary_key = 'id';
		$this->version     = '1.0';
	}

	public function sql_create()
	{
		return "CREATE TABLE {$this->table_name} (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			booking_id bigint(20) DEFAULT NULL,
			table_id bigint(20) NOT NULL,
			PRIMARY KEY  (id),
			KEY `bookingtable_booking_id_foreign` (`booking_id`),
			CONSTRAINT `bookingtable_booking_id_foreign` FOREIGN KEY (`booking_id`) REFERENCES `{$this->prefix}_bookings` (`id`) ON DELETE CASCADE,
			KEY `bookingtable_table_id_foreign` (`table_id`),
			CONSTRAINT `bookingtable_table_id_foreign` FOREIGN KEY (`table_id`) REFERENCES `{$this->prefix}_tables` (`id`) ON DELETE CASCADE
			)";
	}

	public function sql_update_columns_releases()
	{
		$queries = [];

		// Release 84
		$queries['84'][] =  "ALTER TABLE {$this->table_name} ADD seats text DEFAULT NULL AFTER table_id";

		$this->run_queries_updates($queries);
	}

}

SRR_DB_Booking_Table::init();
