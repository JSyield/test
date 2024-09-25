<?php

use Evavel\Database\DB;

class SRR_DB_Combination_Table extends DB
{
	public static $table_db = '';

	public static function init()
	{
		global $wpdb;
		self::$table_db = $wpdb->prefix.DB::$namespace.'_combination_table';
	}

	public function __construct() {
		parent::__construct();
		$this->table_name = $this->prefix.'_combination_table';
		$this->primary_key = 'id';
		$this->version     = '1.0';
	}

	public function sql_create()
	{

		return "CREATE TABLE {$this->table_name} (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			combination_id bigint(20) DEFAULT NULL,
			table_id bigint(20) NOT NULL,
			PRIMARY KEY  (id),
			KEY `combinationtable_combination_id_foreign` (`combination_id`),
			CONSTRAINT `combinationtable_combination_id_foreign` FOREIGN KEY (`combination_id`) REFERENCES `{$this->prefix}_combinations` (`id`) ON DELETE CASCADE,
			KEY `combinationtable_table_id_foreign` (`table_id`),
			CONSTRAINT `combinationtable_table_id_foreign` FOREIGN KEY (`table_id`) REFERENCES `{$this->prefix}_tables` (`id`) ON DELETE CASCADE
			
			)";

	}

}

SRR_DB_Combination_Table::init();
