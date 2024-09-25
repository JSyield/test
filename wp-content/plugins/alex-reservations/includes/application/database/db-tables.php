<?php

use Evavel\Database\DB;

class SRR_DB_Tables extends DB {

	public static $table_db = '';

	public static function init()
	{
		global $wpdb;
		self::$table_db = $wpdb->prefix.DB::$namespace.'_tables';
	}

	public function __construct() {
		parent::__construct();
		$this->table_name = $this->prefix.'_tables';
		$this->primary_key = 'id';
		$this->version     = '1.0';
	}

	public function sql_create() {

		return "CREATE TABLE {$this->table_name} (
		id bigint(20) NOT NULL AUTO_INCREMENT,
		restaurant_id bigint(20) DEFAULT NULL,
		area_id bigint(20) DEFAULT NULL,
		active tinyint DEFAULT 1,
		ordering smallint DEFAULT 999,
		name varchar(20) NOT NULL,
		min_seats tinyint DEFAULT 1,
		max_seats tinyint DEFAULT 2,
		shape varchar(20) DEFAULT '',
		type varchar(20) DEFAULT 'regular',
		priority tinyint DEFAULT 5,
		bookable_staff tinyint(1) DEFAULT 1,
		bookable_online tinyint(1) DEFAULT 1,
		note_guests text DEFAULT NULL,
		note_internal text DEFAULT NULL,
		settings longtext DEFAULT NULL,
		date_created datetime NOT NULL,
		date_modified datetime NOT NULL,
		PRIMARY KEY  (id),
		KEY `tables_restaurant_id_foreign` (`restaurant_id`),
		CONSTRAINT `tables_restaurant_id_foreign` FOREIGN KEY (`restaurant_id`) REFERENCES `{$this->prefix}_restaurants` (`id`) ON DELETE CASCADE,
		KEY `tables_area_id_foreign` (`area_id`),
		CONSTRAINT `tables_area_id_foreign` FOREIGN KEY (`area_id`) REFERENCES `{$this->prefix}_areas` (`id`) ON DELETE CASCADE
		)";

	}

	public function sql_update_columns_releases() {
		$queries = [];

		// Release 19
		$queries['19'][] =  "ALTER TABLE {$this->table_name} ADD amount_1 varchar(20) DEFAULT NULL AFTER bookable_online";
		$queries['19'][] =  "ALTER TABLE {$this->table_name} ADD amount_2 varchar(20) DEFAULT NULL AFTER amount_1";
		$queries['19'][] =  "ALTER TABLE {$this->table_name} ADD amount_3 varchar(20) DEFAULT NULL AFTER amount_2";
		$queries['19'][] =  "ALTER TABLE {$this->table_name} ADD amount_4 varchar(20) DEFAULT NULL AFTER amount_3";
		$queries['19'][] =  "ALTER TABLE {$this->table_name} ADD amount_5 varchar(20) DEFAULT NULL AFTER amount_4";
		$queries['19'][] =  "ALTER TABLE {$this->table_name} ADD amount_6 varchar(20) DEFAULT NULL AFTER amount_5";

		// Release 84
		$queries['84'][] =  "ALTER TABLE {$this->table_name} ADD shareable tinyint DEFAULT 0 AFTER max_seats";

		$this->run_queries_updates($queries);
	}

}

SRR_DB_Tables::init();
