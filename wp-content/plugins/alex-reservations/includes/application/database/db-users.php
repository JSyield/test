<?php

use Evavel\Database\DB;

class SRR_DB_Users extends DB
{
    public static $table_db = '';

    public static function init()
    {
        global $wpdb;
        self::$table_db = $wpdb->prefix.DB::$namespace.'_users';
    }

    public function __construct() {
        parent::__construct();
        $this->table_name = $this->prefix.'_users';
        $this->primary_key = 'id';
        $this->version     = '1.0';
    }

    public function sql_create()
    {
        return "CREATE TABLE {$this->table_name} (
		id bigint(20) NOT NULL AUTO_INCREMENT,
		uuid varchar(36) NOT NULL,
		restaurant_id bigint(20) DEFAULT NULL,
		wp_user_id bigint(20) DEFAULT NULL,
		active tinyint DEFAULT 1,
		name varchar(200) DEFAULT NULL,
		first_name varchar(100) DEFAULT NULL,
		last_name varchar(100) DEFAULT NULL,
		email varchar(50) DEFAULT NULL,
		role varchar(50) DEFAULT NULL,
		language varchar(50) DEFAULT 'en',
		country_code varchar(10) DEFAULT NULL,
		dial_code_country varchar(10) DEFAULT NULL,
		dial_code varchar(10) DEFAULT NULL, 
		phone varchar(50) DEFAULT NULL,
		magic_code varchar(10) DEFAULT NULL,
		notes text DEFAULT NULL,
		settings longtext DEFAULT NULL,
		date_created datetime NOT NULL,
		date_modified datetime NOT NULL,
		PRIMARY KEY  (id),
		KEY `users_restaurant_id_foreign` (`restaurant_id`),
		CONSTRAINT `users_restaurant_id_foreign` FOREIGN KEY (`restaurant_id`) REFERENCES `{$this->prefix}_restaurants` (`id`) ON DELETE CASCADE
		)";
    }

	public function sql_update_columns_releases() {
		$queries = [];

		// Release 55
		$queries['55'][] =  "ALTER TABLE {$this->table_name} ADD pin_code varchar(20) DEFAULT NULL AFTER magic_code";

		$this->run_queries_updates($queries);
	}
}

SRR_DB_Users::init();
