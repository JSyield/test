<?php

use Evavel\Database\DB;

class SRR_DB_UserMeta extends DB
{
    public static $table_db = '';

    public static function init()
    {
        global $wpdb;
        self::$table_db = $wpdb->prefix.DB::$namespace.'_user_meta';
    }

    public function __construct() {
        parent::__construct();
        $this->table_name = $this->prefix.'_user_meta';
        $this->primary_key = 'id';
        $this->version     = '1.0';
    }

    public function sql_create()
    {

        return "CREATE TABLE {$this->table_name} (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			restaurant_id bigint(20) DEFAULT NULL,
			user_id bigint(20) NOT NULL,
			meta_key varchar(255) DEFAULT NULL,
			meta_value longtext DEFAULT NULL,
			PRIMARY KEY  (id),
			KEY user_id (user_id),
			KEY meta_key (meta_key),
			KEY `usersmeta_restaurant_id_foreign` (`restaurant_id`),
			CONSTRAINT `usersmeta_restaurant_id_foreign` FOREIGN KEY (`restaurant_id`) REFERENCES `{$this->prefix}_restaurants` (`id`) ON DELETE CASCADE,
			KEY `usersmeta_user_id_foreign` (`user_id`),
			CONSTRAINT `usersmeta_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `{$this->prefix}_users` (`id`) ON DELETE CASCADE
			)";

    }

}

SRR_DB_UserMeta::init();
