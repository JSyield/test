<?php

use Evavel\Database\DB;

class SRR_DB_Btaggroups extends DB {

	public static $table_db = '';

	public static function init()
	{
		global $wpdb;
		self::$table_db = $wpdb->prefix.DB::$namespace.'_btaggroups';
	}

	public function __construct() {
		parent::__construct();
		$this->table_name = $this->prefix.'_btaggroups';
		$this->primary_key = 'id';
		$this->version     = '1.0';
	}

	public function sql_create()
	{
		return "CREATE TABLE {$this->table_name} (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			restaurant_id bigint(20) DEFAULT NULL,
			name varchar(100) DEFAULT 'group',
			ordering smallint DEFAULT 999,
			color varchar(10) DEFAULT NULL,
			backcolor varchar(10) DEFAULT NULL,
			is_deletable tinyint DEFAULT 1,
			is_vip tinyint DEFAULT 0,
			notes text DEFAULT NULL,
			date_created datetime NOT NULL,
			date_modified datetime NOT NULL,
			PRIMARY KEY  (id),
			KEY `btaggroups_restaurant_id_foreign` (`restaurant_id`),
			CONSTRAINT `btaggroups_restaurant_id_foreign` FOREIGN KEY (`restaurant_id`) REFERENCES `{$this->prefix}_restaurants` (`id`) ON DELETE CASCADE
			)";
	}

	public function sql_update_columns_releases() {
		$queries = [];

		// Release 87
		$queries['88'][] =  "ALTER TABLE {$this->table_name} ADD is_private tinyint DEFAULT 0 AFTER is_vip";

		$this->run_queries_updates($queries);
	}
}

SRR_DB_Btaggroups::init();
