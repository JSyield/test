<?php

use Evavel\Database\DB;

class SRR_DB_Tokens extends DB {

	public static $table_db = '';

	public static function init()
	{
		global $wpdb;
		self::$table_db = $wpdb->prefix.DB::$namespace.'_tokens';
	}

	public function __construct() {
		parent::__construct();
		$this->table_name = $this->prefix.'_tokens';
		$this->primary_key = 'id';
		$this->version     = '1.0';
	}

	public function sql_create() {

		return "CREATE TABLE {$this->table_name} (
		id bigint(20) NOT NULL AUTO_INCREMENT,
		uuid varchar(36) NOT NULL,
		user_id bigint(20) DEFAULT NULL,
		token varchar(255) DEFAULT NULL,
		expire_date datetime DEFAULT NULL,
		date_created datetime NOT NULL,
		date_modified datetime NOT NULL,
		PRIMARY KEY  (id),
		KEY user_id (user_id),
		KEY `tokens_user_id_foreign` (`user_id`),
		CONSTRAINT `tokens_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `{$this->prefix}_users` (`id`) ON DELETE CASCADE
		)";

		return $sql;
	}

}

SRR_DB_Tokens::init();
