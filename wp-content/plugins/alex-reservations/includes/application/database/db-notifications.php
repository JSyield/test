<?php

use Evavel\Database\DB;

class SRR_DB_Notifications extends DB {

	public static $table_db = '';

	public static function init()
	{
		global $wpdb;
		self::$table_db = $wpdb->prefix.DB::$namespace.'_notifications';
	}

	public function __construct() {
		parent::__construct();
		$this->table_name = $this->prefix.'_notifications';
		$this->primary_key = 'id';
		$this->version     = '1.0';
	}

	public function sql_create() {

		return "CREATE TABLE {$this->table_name} (
		id bigint(20) NOT NULL AUTO_INCREMENT,
		uuid varchar(36) NOT NULL,
		restaurant_id bigint(20) DEFAULT NULL,
		type varchar(255) NOT NULL,
		notifiable_type varchar(255) NOT NULL,
		notifiable_id bigint(20) NOT NULL,
		data text NOT NULL,
		read_at datetime DEFAULT NULL,
		date_created datetime NOT NULL,
		date_modified datetime NOT NULL,
		PRIMARY KEY  (id),
		KEY `notifications_restaurant_id_foreign` (`restaurant_id`),
		CONSTRAINT `notifications_restaurant_id_foreign` FOREIGN KEY (`restaurant_id`) REFERENCES `{$this->prefix}_restaurants` (`id`) ON DELETE CASCADE
		)";
	}

}

SRR_DB_Notifications::init();
