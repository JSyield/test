<?php

use Evavel\Database\DB;

class SRR_DB_Customers extends DB
{
	public static $table_db = '';

	public static function init()
	{
		global $wpdb;
		self::$table_db = $wpdb->prefix.DB::$namespace.'_customers';
	}

	public function __construct() {
		parent::__construct();
		$this->table_name = $this->prefix.'_customers';
		$this->primary_key = 'id';
		$this->version     = '1.0';
	}

	public function sql_create()
	{
		return "CREATE TABLE {$this->table_name} (
		id bigint(20) NOT NULL AUTO_INCREMENT,
		uuid varchar(36) NOT NULL,
		restaurant_id bigint(20) DEFAULT NULL,
		email varchar(50) NOT NULL,
		name varchar(200) DEFAULT NULL,
		first_name varchar(100) DEFAULT NULL,
		last_name varchar(100) DEFAULT NULL,
		company varchar(200) DEFAULT NULL,
		country_code varchar(10) DEFAULT NULL, 
		dial_code varchar(10) DEFAULT NULL, 
		dial_code_country varchar(10) DEFAULT NULL, 
		phone varchar(50) DEFAULT NULL,
		language varchar(50) DEFAULT 'en',
		gender varchar(20) DEFAULT NULL,
		last_visit date DEFAULT NULL,
		visits int unsigned DEFAULT 0,
		covers int unsigned DEFAULT 0,
		cancels int unsigned DEFAULT 0,
		no_shows int unsigned DEFAULT 0,
		spend int unsigned DEFAULT 0,
		spend_per_visit int unsigned DEFAULT 0,
		spend_per_cover int unsigned DEFAULT 0,
		agree_conditions tinyint(1) DEFAULT 1,
		agree_receive_email_marketing tinyint(1) DEFAULT 0,
		agree_receive_sms tinyint(1) DEFAULT 0,
		notes text DEFAULT NULL,
		settings longtext DEFAULT NULL,
		birthday varchar(10) DEFAULT NULL,
		date_created datetime NOT NULL,
		date_modified datetime NOT NULL,
		PRIMARY KEY  (id),
		KEY `customers_restaurant_id_foreign` (`restaurant_id`),
		CONSTRAINT `customers_restaurant_id_foreign` FOREIGN KEY (`restaurant_id`) REFERENCES `{$this->prefix}_restaurants` (`id`) ON DELETE CASCADE
		)";
	}

	public function sql_update_columns_releases() {
		$queries = [];

		// Release 37
		$queries['37'][] =  "ALTER TABLE {$this->table_name} ADD agree_receive_email tinyint DEFAULT 1 NULL AFTER agree_receive_email_marketing";
		$queries['37'][] =  "ALTER TABLE {$this->table_name} ADD sms_status varchar(20) DEFAULT NULL AFTER agree_receive_sms";

		// Release 76
		$queries['76'][] =  "ALTER TABLE {$this->table_name} ADD visits_imported int unsigned DEFAULT 0 AFTER visits";
		$queries['76'][] =  "ALTER TABLE {$this->table_name} ADD spend_imported int unsigned DEFAULT 0 AFTER spend";

		$this->run_queries_updates($queries);
	}

}

SRR_DB_Customers::init();
