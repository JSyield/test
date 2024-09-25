<?php

use Evavel\Database\DB;

class SRR_DB_DailyNotifications extends DB {

	public static $table_db = '';

	public static function init()
	{
		global $wpdb;
		self::$table_db = $wpdb->prefix.DB::$namespace.'_daily_notifications';
	}

	public function __construct() {
		parent::__construct();
		$this->table_name = $this->prefix.'_daily_notifications';
		$this->primary_key = 'id';
		$this->version     = '1.0';
	}

	public function sql_create() {

		/*$sql = "CREATE TABLE {$this->table_name} (
		id bigint(20) NOT NULL AUTO_INCREMENT,
		uuid varchar(36) NOT NULL,
		restaurant_id bigint(20) DEFAULT NULL,
		user_id bigint(20) DEFAULT NULL,
		date date DEFAULT NULL,
		message text DEFAULT NULL,
		date_created datetime NOT NULL,
		date_modified datetime NOT NULL,
		PRIMARY KEY  (id),
		KEY `dailynotifications_restaurant_id_foreign` (`restaurant_id`),
		CONSTRAINT `dailynotifications_restaurant_id_foreign` FOREIGN KEY (`restaurant_id`) REFERENCES `{$this->prefix}_restaurants` (`id`) ON DELETE CASCADE
		)";*/

		// Puede dar error con el foreign key
		$sql = "CREATE TABLE {$this->table_name} (
		id bigint(20) NOT NULL AUTO_INCREMENT,
		uuid varchar(36) NOT NULL,
		restaurant_id bigint(20) DEFAULT NULL,
		user_id bigint(20) DEFAULT NULL,
		date date DEFAULT NULL,
		message text DEFAULT NULL,
		date_created datetime NOT NULL,
		date_modified datetime NOT NULL,
		PRIMARY KEY  (id)
		)";

		return $sql;
	}
}

SRR_DB_DailyNotifications::init();
