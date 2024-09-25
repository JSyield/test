<?php

use Evavel\Database\DB;

class SRR_DB_RestaurantSetting extends DB
{
	public static $table_db = '';

	public static function init()
	{
		global $wpdb;
		self::$table_db = $wpdb->prefix.DB::$namespace.'_restaurant_setting';
	}

	public function __construct() {
		parent::__construct();
		$this->table_name = $this->prefix.'_restaurant_setting';
		$this->primary_key = 'id';
		$this->version     = '1.0';
	}

	public function sql_create()
	{
		return  "CREATE TABLE {$this->table_name} (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			restaurant_id bigint(20) NOT NULL,
			meta_key varchar(255) DEFAULT NULL,
			meta_value longtext DEFAULT NULL,
			date_created datetime NOT NULL,
			date_modified datetime NOT NULL,
			PRIMARY KEY  (id),
			KEY restaurant_id (restaurant_id),
			KEY meta_key (meta_key),
			KEY `restaurantssetting_restaurant_id_foreign` (`restaurant_id`),
			CONSTRAINT `restaurantssetting_restaurant_id_foreign` FOREIGN KEY (`restaurant_id`) REFERENCES `{$this->prefix}_restaurants` (`id`) ON DELETE CASCADE
			)";
	}

	public function sql_update_columns_releases() {
		$queries = [];

		// Release63
		$queries['63'][] =  "ALTER TABLE {$this->table_name} ADD ordering smallint DEFAULT 999 AFTER meta_value";

		$this->run_queries_updates($queries);
	}

}

SRR_DB_RestaurantSetting::init();
