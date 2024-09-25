<?php

use Evavel\Database\DB;

class SRR_DB_Floors extends DB {

	public static $table_db = '';

	public static function init()
	{
		global $wpdb;
		self::$table_db = $wpdb->prefix.DB::$namespace.'_floors';
	}

	public function __construct() {
		parent::__construct();
		$this->table_name = $this->prefix.'_floors';
		$this->primary_key = 'id';
		$this->version     = '1.0';
	}

	public function sql_create() {

		return "CREATE TABLE {$this->table_name} (
		id bigint(20) NOT NULL AUTO_INCREMENT,
		restaurant_id bigint(20) DEFAULT NULL,
		active tinyint DEFAULT 1,
		ordering smallint DEFAULT 999,
		name varchar(100) DEFAULT 'Floor',
		priority tinyint DEFAULT 5,
		note_guests mediumtext DEFAULT NULL,
		note_internal mediumtext DEFAULT NULL,
		bookable_staff tinyint(1) DEFAULT 1,
		bookable_online tinyint(1) DEFAULT 1,
		notes text DEFAULT NULL,
		settings longtext DEFAULT NULL,
		date_created datetime NOT NULL,
		date_modified datetime NOT NULL,
		PRIMARY KEY  (id),
		KEY `floors_restaurant_id_foreign` (`restaurant_id`),
		CONSTRAINT `floors_restaurant_id_foreign` FOREIGN KEY (`restaurant_id`) REFERENCES `{$this->prefix}_restaurants` (`id`) ON DELETE CASCADE
		)";

	}

}

SRR_DB_Floors::init();
