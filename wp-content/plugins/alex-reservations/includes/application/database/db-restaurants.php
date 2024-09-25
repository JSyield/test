<?php

use Evavel\Database\DB;

class SRR_DB_Restaurants extends DB {

    public static $table_db = '';

    public static function init()
    {
        global $wpdb;
        self::$table_db = $wpdb->prefix.DB::$namespace.'_restaurants';
    }

    public function __construct() {
        parent::__construct();
        $this->table_name = $this->prefix.'_restaurants';
        $this->primary_key = 'id';
        $this->version     = '1.1';
    }

    public function sql_create() {

        $sql = "CREATE TABLE {$this->table_name} (
		id bigint(20) NOT NULL AUTO_INCREMENT,
		uuid varchar(36) NOT NULL,
		active tinyint DEFAULT 1,
		timezone varchar(30) DEFAULT 'Europe/Madrid',
		name varchar(200) DEFAULT NULL,
		email varchar(50) NOT NULL,
		language varchar(50) DEFAULT 'en',
		
		address varchar(250) DEFAULT NULL,
		city varchar(50) DEFAULT NULL,
		country varchar(50) DEFAULT NULL,
		country_code varchar(10) DEFAULT NULL, 
		postal_code varchar(50) DEFAULT NULL,
		business_code varchar(50) DEFAULT NULL,
		
		phone varchar(50) DEFAULT NULL,
		dial_code varchar(10) DEFAULT NULL, 
		dial_code_country varchar(10) DEFAULT NULL,
		
		coord_x varchar(20) DEFAULT NULL,
		coord_y varchar(20) DEFAULT NULL,
		
		currency varchar(5) DEFAULT 'USD',
		first_day_of_week tinyint DEFAULT 7,
		
		notes text DEFAULT NULL,
		settings longtext DEFAULT NULL,
		
		link_web varchar(250) DEFAULT NULL,
		link_facebook varchar(250) DEFAULT NULL,
		link_instagram varchar(250) DEFAULT NULL,
		link_social1 varchar(250) DEFAULT NULL,
		link_social2 varchar(250) DEFAULT NULL,
		
		date_created datetime NOT NULL,
		date_modified datetime NOT NULL,
		PRIMARY KEY  (id)
		)";

		return $sql;
    }

	public function sql_update_columns_releases() {
		$queries = [];

		// Release 13
		$queries['13'][] =  "ALTER TABLE {$this->table_name} ADD date_format varchar(10) NULL DEFAULT 'dmy' AFTER currency";
		$queries['13'][] =  "ALTER TABLE {$this->table_name} ADD time_format varchar(10) NULL DEFAULT '24h' AFTER date_format";

		$this->run_queries_updates($queries);
	}

}

SRR_DB_Restaurants::init();
