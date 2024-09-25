<?php

use Evavel\Database\DB;

class SRR_DB_Bookings extends DB {

    public static $table_db = '';

    public static function init()
    {
        global $wpdb;
        self::$table_db = $wpdb->prefix.DB::$namespace.'_bookings';
    }

    public function __construct() {
        parent::__construct();
        $this->table_name = $this->prefix.'_bookings';
        $this->primary_key = 'id';
        $this->version     = '1.1';
    }

    public function sql_create() {

        // ID, restaurant_id, Date, Status, Party, Duration, Name, Email, Phone, consumer_id, customer_id, notes, date_created, date_modified

        return "CREATE TABLE {$this->table_name} (
		id bigint(20) NOT NULL AUTO_INCREMENT,
		uuid varchar(36) NOT NULL,
		token varchar(255) NOT NULL,
		source varchar(50) DEFAULT NULL,
		type varchar(50) DEFAULT NULL,
		restaurant_id bigint(20) DEFAULT NULL,
		customer_id bigint(20) DEFAULT NULL,
		date date DEFAULT NULL,
		time int unsigned DEFAULT NULL,
		party int unsigned DEFAULT 2,
		duration int unsigned DEFAULT 3600,
		shift_event_id int unsigned DEFAULT NULL,
		shift_event_name varchar(100) DEFAULT NULL,
		first_name varchar(100) DEFAULT NULL,
		last_name varchar(100) DEFAULT NULL,
		language varchar(50) DEFAULT 'en',
		status varchar(50) DEFAULT NULL,
		email varchar(50) DEFAULT NULL,
		country_code varchar(10) DEFAULT NULL, 
		dial_code varchar(10) DEFAULT NULL, 
		dial_code_country varchar(10) DEFAULT NULL, 
		phone varchar(50) DEFAULT NULL,
		birthday varchar(10) DEFAULT NULL,
		spend int unsigned DEFAULT 0,
		notes text DEFAULT NULL,
		settings longtext DEFAULT NULL,
		date_created datetime NOT NULL,
		date_modified datetime NOT NULL,
		PRIMARY KEY  (id),
		KEY `bookings_restaurant_id_foreign` (`restaurant_id`),
		CONSTRAINT `bookings_restaurant_id_foreign` FOREIGN KEY (`restaurant_id`) REFERENCES `{$this->prefix}_restaurants` (`id`) ON DELETE CASCADE,
		KEY `bookings_customer_id_foreign` (`customer_id`),
		CONSTRAINT `bookings_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `{$this->prefix}_customers` (`id`) ON DELETE CASCADE
		)";

    }

	public function sql_update_columns_releases() {
		$queries = [];

		// Release 19
		$queries['19'][] =  "ALTER TABLE {$this->table_name} ADD widget_id int unsigned DEFAULT NULL AFTER duration";

		// Release 36
		$queries['36'][] =  "ALTER TABLE {$this->table_name} ADD custom_fields text DEFAULT NULL AFTER notes";

		// Release 37
		$queries['37'][] =  "ALTER TABLE {$this->table_name} ADD agree_receive_email tinyint DEFAULT 1 NULL AFTER duration";
		$queries['37'][] =  "ALTER TABLE {$this->table_name} ADD agree_receive_sms tinyint DEFAULT 1 NULL AFTER agree_receive_email";
		$queries['37'][] =  "ALTER TABLE {$this->table_name} ADD sms_status varchar(20) DEFAULT NULL AFTER agree_receive_sms";

		// Release 44
		$queries['44'][] =  "ALTER TABLE {$this->table_name} ADD table_selected_id int unsigned DEFAULT NULL AFTER custom_fields";
		$queries['44'][] =  "ALTER TABLE {$this->table_name} ADD area_selected_id int unsigned DEFAULT NULL AFTER custom_fields";

		// Release 56
		$queries['56'][] =  "ALTER TABLE {$this->table_name} ADD gateway varchar(100) DEFAULT NULL AFTER status";
		$queries['56'][] =  "ALTER TABLE {$this->table_name} ADD gateway_token varchar(100) DEFAULT NULL AFTER gateway";

		// Release 57
		$queries['57'][] =  "ALTER TABLE {$this->table_name} ADD amount int unsigned DEFAULT NULL AFTER gateway_token";

		// Release 68
		$queries['68'][] =  "ALTER TABLE {$this->table_name} ADD private_notes text DEFAULT NULL AFTER notes";

		// Release 76
		$queries['76'][] =  "ALTER TABLE {$this->table_name} ADD blocked_table tinyint DEFAULT 0 AFTER status";

		// Release 87
		$queries['88'][] =  "ALTER TABLE {$this->table_name} ADD parent_booking_id bigint(20) DEFAULT NULL AFTER status";

		$this->run_queries_updates($queries);
	}

}

SRR_DB_Bookings::init();
