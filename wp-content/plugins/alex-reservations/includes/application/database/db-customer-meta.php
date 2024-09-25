<?php

use Evavel\Database\DB;

class SRR_DB_CustomerMeta extends DB
{
	public static $table_db = '';

	public static function init()
	{
		global $wpdb;
		self::$table_db = $wpdb->prefix.DB::$namespace.'_customer_meta';
	}

	public function __construct() {
		parent::__construct();
		$this->table_name = $this->prefix.'_customer_meta';
		$this->primary_key = 'id';
		$this->version     = '1.0';
	}

	public function sql_create()
	{

		return "CREATE TABLE {$this->table_name} (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			restaurant_id bigint(20) DEFAULT NULL,
			customer_id bigint(20) NOT NULL,
			meta_key varchar(255) DEFAULT NULL,
			meta_value longtext DEFAULT NULL,
			PRIMARY KEY  (id),
			KEY customer_id (customer_id),
			KEY meta_key (meta_key),
			KEY `customersmeta_restaurant_id_foreign` (`restaurant_id`),
			CONSTRAINT `customersmeta_restaurant_id_foreign` FOREIGN KEY (`restaurant_id`) REFERENCES `{$this->prefix}_restaurants` (`id`) ON DELETE CASCADE,
			KEY `customersmeta_customer_id_foreign` (`customer_id`),
			CONSTRAINT `customersmeta_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `{$this->prefix}_customers` (`id`) ON DELETE CASCADE
			)";

	}

}

SRR_DB_CustomerMeta::init();
