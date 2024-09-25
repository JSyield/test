<?php

use Evavel\Database\DB;

class SRR_DB_Customer_Ctag extends DB
{
	public static $table_db = '';

	public static function init()
	{
		global $wpdb;
		self::$table_db = $wpdb->prefix.DB::$namespace.'_customer_ctag';
	}

	public function __construct() {
		parent::__construct();
		$this->table_name = $this->prefix.'_customer_ctag';
		$this->primary_key = 'id';
		$this->version     = '1.0';
	}

	public function sql_create()
	{

		return "CREATE TABLE {$this->table_name} (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			customer_id bigint(20) DEFAULT NULL,
			ctag_id bigint(20) NOT NULL,
			PRIMARY KEY  (id),
			KEY `customerctag_customer_id_foreign` (`customer_id`),
			CONSTRAINT `customerctag_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `{$this->prefix}_customers` (`id`) ON DELETE CASCADE,
			KEY `customerctag_ctag_id_foreign` (`ctag_id`),
			CONSTRAINT `customerctag_ctag_id_foreign` FOREIGN KEY (`ctag_id`) REFERENCES `{$this->prefix}_ctags` (`id`) ON DELETE CASCADE
			)";

	}

}

SRR_DB_Customer_Ctag::init();
