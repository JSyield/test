<?php

use Evavel\Database\DB;

class SRR_DB_Actions extends DB  {

	public static $table_db = '';

	public static function init()
	{
		global $wpdb;
		self::$table_db = $wpdb->prefix.DB::$namespace.'_actions';
	}

	public function __construct() {
		parent::__construct();
		$this->table_name = $this->prefix.'_actions';
		$this->primary_key = 'id';
		$this->version     = '1.0';
	}

	// Record actions taken by users like creating a new booking, updating, etc.
	public function sql_create() {

		// agent_type: user, customer
		// model_type: booking, customer, restaurant
		// name: Delete, Update, ..
		// event_type: EventBookingCreated, EventBookingStatusChangedByCustomer, ..

		return "CREATE TABLE {$this->table_name} (
		id bigint(20) NOT NULL AUTO_INCREMENT,
		restaurant_id bigint(20) DEFAULT NULL,
		
		agent_type varchar(255) DEFAULT NULL,
		agent_id bigint(20) DEFAULT NULL,
		
		model_type varchar(255) DEFAULT NULL,
		model_id bigint(20) DEFAULT NULL,
		
		original text DEFAULT NULL,
		changes text DEFAULT NULL,
		
		date_created datetime NOT NULL,
		date_modified datetime NOT NULL,
		PRIMARY KEY  (id),
		KEY `actions_restaurant_id_foreign` (`restaurant_id`),
		CONSTRAINT `actions_restaurant_id_foreign` FOREIGN KEY (`restaurant_id`) REFERENCES `{$this->prefix}_restaurants` (`id`) ON DELETE CASCADE
		)";
	}

	public function sql_update_columns_releases() {
		$queries = [];

		// Release 88
		$queries['88'][] =  "ALTER TABLE {$this->table_name} ADD name varchar(255) DEFAULT NULL AFTER restaurant_id";
		$queries['88'][] =  "ALTER TABLE {$this->table_name} ADD event_type varchar(255) DEFAULT NULL AFTER name";
		$queries['88'][] =  "ALTER TABLE {$this->table_name} ADD agent_name varchar(255) DEFAULT NULL AFTER agent_id";


		$this->run_queries_updates($queries);
	}
}

SRR_DB_Actions::init();
