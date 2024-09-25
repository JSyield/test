<?php

namespace Evavel\Database;

use Evavel\Query\Query;

abstract class DB {

    public static $namespace = EVAVEL_DB_NAMESPACE;

    public $prefix;
    public $table_name;
    public $version;
    public $primary_key;

    public function __construct() {
        global $wpdb;
        $this->prefix = $wpdb->prefix.static::$namespace;
    }

    public function table_exists($table_name) {
        global $wpdb;
        $table_name = sanitize_text_field($table_name);
        return $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE '%s'", $table_name ) ) === $table_name;
    }

    public function installed() {
        return $this->table_exists($this->table_name);
    }

    public function sql_create() {
        return "";
    }

    public function create_table() {
        global $wpdb;
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		$command = $this->sql_create() . " CHARACTER SET utf8 COLLATE utf8_general_ci;";
        dbDelta( $command);

        update_option( $this->table_name . '_db_version', $this->version );
    }

    public function delete_table() {
        global $wpdb;
        $wpdb->query( "DROP TABLE IF EXISTS " . $this->table_name );
    }


	public function run_queries_updates($queries) {
		global $wpdb;

		$force_update = false; // TESTING

		foreach($queries as $release_number => $list_sql)
		{
			if ($force_update || !$this->has_updated_columns($release_number))
			{
				//ray('update '.$this->table_name.' '.$release_number);
				foreach($list_sql as $sql)
				{
					// Antes de hacer la query compruebo si existe ya la columna
					// en caso de que se trate de ADD column
					if ($this->can_run_update_query($sql)) {
						$wpdb->query($sql);
					}
				}

				$this->save_updated_columns($release_number);
			}
		}
	}

	// Comprobar si la columna esta ya añadida para que no lance un mensaje de warning
	public function can_run_update_query($sql) {

		// SQL para añadir una columna
		if (preg_match('#ALTER TABLE (.+) ADD ([\S]+)#', $sql, $matches))
		{
			$table_name = $matches[1];
			$column_name = $matches[2];
			//ray('ADD COLUMN ' . $matches[2] .' to ' . $matches[1]);

			// Comprobar si ya existe la columna
			$columns = Query::table($table_name)->getColumns();
			if(in_array("{$table_name}.{$column_name}", $columns)) {
				//ray('YA EXISTE ' . $matches[2]);
				return false;
			}
			//ray('********* NO EXISTE **************+ ' . $matches[2]);
			return true;
		}

		// Otro SQL pasa
		return true;
	}

	// Comprobar si se han actualizado las ultimas columnas añadidas
	// 19,34,36...
	public function has_updated_columns($release_number) {
		$meta = '_srr_table_columns_releases_'.$this->table_name;
		$post_meta = get_option($meta, '');
		$post_meta = explode(',', $post_meta);
		return in_array(intval($release_number), $post_meta);
	}

	// Guardar que si se han actualizado
	public function save_updated_columns($release_number) {
		$meta = '_srr_table_columns_releases_'.$this->table_name;
		$post_meta = get_option($meta, '');

		if ($post_meta == '') {
			$post_meta = [];
		} else {
			$post_meta = explode(',', $post_meta);
		}

		$post_meta[] = intval($release_number);
		$post_meta = array_unique($post_meta);
		$post_meta = implode(',', $post_meta);
		update_option($meta, $post_meta);
	}
}
