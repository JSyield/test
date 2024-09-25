<?php

use Evavel\Database\DB;

class SRR_DB_Settings extends DB
{
	public static $table_db = '';

	public static function init()
	{
		global $wpdb;
		self::$table_db = $wpdb->prefix.DB::$namespace.'_settings';
	}

	public function __construct() {
		parent::__construct();
		$this->table_name = $this->prefix.'_settings';
		$this->primary_key = 'id';
		$this->version     = '1.0';
	}

	public function sql_create()
	{
		return "CREATE TABLE {$this->table_name} (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			meta_key varchar(255) DEFAULT NULL,
			meta_value longtext DEFAULT NULL,
			PRIMARY KEY  (id),
			KEY meta_key (meta_key)
			)";

	}
}

SRR_DB_Settings::init();
