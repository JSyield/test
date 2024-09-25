<?php

use Evavel\Database\DB;

class SRR_DB_Combinations extends DB {

	public static $table_db = '';

	public static function init()
	{
		global $wpdb;
		self::$table_db = $wpdb->prefix.DB::$namespace.'_combinations';
	}

	public function __construct() {
		parent::__construct();
		$this->table_name = $this->prefix.'_combinations';
		$this->primary_key = 'id';
		$this->version     = '1.0';
	}

	public function sql_create() {

		return "CREATE TABLE {$this->table_name} (
		id bigint(20) NOT NULL AUTO_INCREMENT,
		restaurant_id bigint(20) DEFAULT NULL,
		floor_id bigint(20) DEFAULT NULL,
		area_id bigint(20) DEFAULT NULL,
		active tinyint DEFAULT 1,
		ordering smallint DEFAULT 999,
		name varchar(100) DEFAULT NULL,
		min_seats tinyint DEFAULT 1,
		max_seats tinyint DEFAULT 2,
		priority tinyint DEFAULT 5,
		bookable_staff tinyint(1) DEFAULT 1,
		bookable_online tinyint(1) DEFAULT 1,
		note_internal text DEFAULT NULL,
		settings longtext DEFAULT NULL,
		date_created datetime NOT NULL,
		date_modified datetime NOT NULL,
		PRIMARY KEY  (id),
		KEY `combinations_restaurant_id_foreign` (`restaurant_id`),
		CONSTRAINT `combinations_restaurant_id_foreign` FOREIGN KEY (`restaurant_id`) REFERENCES `{$this->prefix}_restaurants` (`id`) ON DELETE CASCADE,
		KEY `combinations_floor_id_foreign` (`floor_id`),
		CONSTRAINT `combinations_floor_id_foreign` FOREIGN KEY (`floor_id`) REFERENCES `{$this->prefix}_floors` (`id`) ON DELETE CASCADE,
		KEY `combinations_area_id_foreign` (`area_id`),
		CONSTRAINT `combinations_area_id_foreign` FOREIGN KEY (`area_id`) REFERENCES `{$this->prefix}_areas` (`id`) ON DELETE CASCADE
		)";

	}

}

SRR_DB_Combinations::init();
