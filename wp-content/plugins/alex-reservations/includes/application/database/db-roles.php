<?php

use Evavel\Database\DB;

class SRR_DB_Roles extends DB
{
	public static $table_db = '';

	public static function init()
	{
		global $wpdb;
		self::$table_db = $wpdb->prefix.DB::$namespace.'_roles';
	}

	public function __construct() {
		parent::__construct();
		$this->table_name = $this->prefix.'_roles';
		$this->primary_key = 'id';
		$this->version     = '1.0';
	}

	public function sql_create()
	{
		return "CREATE TABLE {$this->table_name} (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			restaurant_id bigint(20) DEFAULT NULL,
			name varchar(100) DEFAULT NULL,
			role varchar(50) DEFAULT NULL,
			settings text DEFAULT NULL,
			date_created datetime NOT NULL,
			date_modified datetime NOT NULL,
			PRIMARY KEY  (id),
			KEY `role_restaurant_id_foreign` (`restaurant_id`),
			CONSTRAINT `role_restaurant_id_foreign` FOREIGN KEY (`restaurant_id`) REFERENCES `{$this->prefix}_restaurants` (`id`) ON DELETE CASCADE
			)";
	}

}

SRR_DB_Roles::init();
