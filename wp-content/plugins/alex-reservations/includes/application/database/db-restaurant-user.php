<?php

use Evavel\Database\DB;

class SRR_DB_Restaurant_User extends DB
{
    public static $table_db = '';

    public static function init()
    {
        global $wpdb;
        self::$table_db = $wpdb->prefix.DB::$namespace.'_restaurant_user';
    }

    public function __construct() {
        parent::__construct();
        $this->table_name = $this->prefix.'_restaurant_user';
        $this->primary_key = 'id';
        $this->version     = '1.0';
    }

    public function sql_create()
    {
        return "CREATE TABLE {$this->table_name} (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			restaurant_id bigint(20) DEFAULT NULL,
			user_id bigint(20) NOT NULL,
			role varchar(50) DEFAULT NULL,
			settings text DEFAULT NULL,
			PRIMARY KEY  (id),
			KEY `restaurantuser_restaurant_id_foreign` (`restaurant_id`),
			CONSTRAINT `restaurantuser_restaurant_id_foreign` FOREIGN KEY (`restaurant_id`) REFERENCES `{$this->prefix}_restaurants` (`id`) ON DELETE CASCADE,
			KEY `restaurantuser_user_id_foreign` (`user_id`),
			CONSTRAINT `restaurantuser_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `{$this->prefix}_users` (`id`) ON DELETE CASCADE
			
			)";

    }

}

SRR_DB_Restaurant_User::init();
