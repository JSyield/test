<?php

use Evavel\Database\DB;

class SRR_DB_Areas extends DB {

	public static $table_db = '';

	public static function init()
	{
		global $wpdb;
		self::$table_db = $wpdb->prefix.DB::$namespace.'_areas';
	}

	public function __construct() {
		parent::__construct();
		$this->table_name = $this->prefix.'_areas';
		$this->primary_key = 'id';
		$this->version     = '1.0';
	}

	public function sql_create() {

		return "CREATE TABLE {$this->table_name} (
		id bigint(20) NOT NULL AUTO_INCREMENT,
		restaurant_id bigint(20) DEFAULT NULL,
		floor_id bigint(20) DEFAULT NULL,
		active tinyint DEFAULT 1,
		ordering smallint DEFAULT 999,
		name varchar(100) DEFAULT 'Area',
		priority tinyint DEFAULT 5,
		bookable_staff tinyint(1) DEFAULT 1,
		bookable_online tinyint(1) DEFAULT 1,
		note_guests text DEFAULT NULL,
		note_internal text DEFAULT NULL,
		settings longtext DEFAULT NULL,
		date_created datetime NOT NULL,
		date_modified datetime NOT NULL,
		PRIMARY KEY  (id),
		KEY `areas_restaurant_id_foreign` (`restaurant_id`),
		CONSTRAINT `areas_restaurant_id_foreign` FOREIGN KEY (`restaurant_id`) REFERENCES `{$this->prefix}_restaurants` (`id`) ON DELETE CASCADE,
		KEY `areas_floor_id_foreign` (`floor_id`),
		CONSTRAINT `areas_floor_id_foreign` FOREIGN KEY (`floor_id`) REFERENCES `{$this->prefix}_floors` (`id`) ON DELETE CASCADE
		)";

	}

	public function sql_update_columns_releases() {
		$queries = [];

		// Release 45
		$queries['45'][] =  "ALTER TABLE {$this->table_name} ADD image_url varchar(255) DEFAULT NULL AFTER bookable_online";

		$this->run_queries_updates($queries);
	}

}

SRR_DB_Areas::init();
