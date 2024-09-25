<?php

use Evavel\Database\DB;

class SRR_DB_Payments extends DB {

	public static $table_db = '';

	public static function init()
	{
		global $wpdb;
		self::$table_db = $wpdb->prefix.DB::$namespace.'_payments';
	}

	public function __construct() {
		parent::__construct();
		$this->table_name = $this->prefix.'_payments';
		$this->primary_key = 'id';
		$this->version     = '1.0';
	}

	public function sql_create() {

		return "CREATE TABLE {$this->table_name} (
		id bigint(20) NOT NULL AUTO_INCREMENT,
		uuid varchar(36) NOT NULL,
		restaurant_id bigint(20) DEFAULT NULL,
		booking_id bigint(20) DEFAULT NULL,
		order_hash varchar(256) DEFAULT NULL,
		amount int DEFAULT NULL,
		currency varchar(25) DEFAULT NULL,
		payment_type varchar(25) DEFAULT NULL,
		order_date timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
		order_status varchar(25) DEFAULT NULL,
		notes text DEFAULT NULL,
		name varchar(25) DEFAULT NULL,
		email varchar(100) DEFAULT NULL,
		address varchar(255) DEFAULT NULL,
		country varchar(25) DEFAULT NULL,
		postal_code varchar(25) DEFAULT NULL,
		stripe_payment_intent_id varchar(255) DEFAULT NULL,
		status varchar(25) DEFAULT NULL,
		stripe_payment_status varchar(25) DEFAULT NULL,
		stripe_payment_method varchar(255) DEFAULT NULL,
		stripe_payment_response text DEFAULT NULL,
		date_created datetime NOT NULL,
		date_modified datetime NOT NULL,
		PRIMARY KEY  (id),
		KEY payments_restaurant_id_foreign (restaurant_id),
		CONSTRAINT payments_restaurant_id_foreign FOREIGN KEY (restaurant_id) REFERENCES {$this->prefix}_restaurants (id) ON DELETE CASCADE,
		KEY payments_booking_id_foreign (booking_id),
		CONSTRAINT payments_booking_id_foreign FOREIGN KEY (booking_id) REFERENCES {$this->prefix}_bookings (id) ON DELETE CASCADE
		)";
	}

	public function sql_update_columns_releases() {
		$queries = [];

		// Release 56
		$queries['56'][] =  "ALTER TABLE {$this->table_name} ADD settings longtext DEFAULT NULL AFTER stripe_payment_response";

		$this->run_queries_updates($queries);
	}

}

SRR_DB_Payments::init();
