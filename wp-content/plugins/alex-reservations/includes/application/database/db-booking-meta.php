<?php

use Evavel\Database\DB;

class SRR_DB_BookingMeta extends DB
{
    public static $table_db = '';

    public static function init()
    {
        global $wpdb;
        self::$table_db = $wpdb->prefix.DB::$namespace.'_booking_meta';
    }

    public function __construct() {
        parent::__construct();
        $this->table_name = $this->prefix.'_booking_meta';
        $this->primary_key = 'id';
        $this->version     = '1.0';
    }

    public function sql_create()
    {

        return "CREATE TABLE {$this->table_name} (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			restaurant_id bigint(20) DEFAULT NULL,
			booking_id bigint(20) NOT NULL,
			meta_key varchar(255) DEFAULT NULL,
			meta_value longtext DEFAULT NULL,
			PRIMARY KEY  (id),
			KEY booking_id (booking_id),
			KEY meta_key (meta_key),
			KEY `bookingsmeta_restaurant_id_foreign` (`restaurant_id`),
			CONSTRAINT `bookingsmeta_restaurant_id_foreign` FOREIGN KEY (`restaurant_id`) REFERENCES `{$this->prefix}_restaurants` (`id`) ON DELETE CASCADE,
			KEY `bookingsmeta_booking_id_foreign` (`booking_id`),
			CONSTRAINT `bookingsmeta_booking_id_foreign` FOREIGN KEY (`booking_id`) REFERENCES `{$this->prefix}_bookings` (`id`) ON DELETE CASCADE
			)";

    }

}

SRR_DB_BookingMeta::init();
