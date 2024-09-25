<?php

namespace Evavel\Query\Traits;

use Closure;
use Evavel\Database\DB;

trait WhereHasTrait
{
	public function whereHas($relation, Closure $callback = null, $operator = '>=', $count = 1)
	{
		// Determinar la tabla relacionada y las columnas de unión
		$relatedTable = $this->getRelatedTable($relation);
		$foreignKey = $this->getForeignKey($this->table_name);
		$localKey = 'id';
		$table_name = $this->validateTableName($this->table_name);


		// Crear una subquery
		//$subQuery = new static();
		// Crear una subquery usando el método estático table
		$subQuery = static::table($relatedTable);
		$subQuery->select([$foreignKey]);

		// Si se proporciona un callback, aplicarlo a la subquery
		if ($callback !== null) {
			call_user_func($callback, $subQuery);
		}

		// Construir la cláusula WHERE EXISTS
		$subSql = $subQuery->buildSql();
		$existsClause = "EXISTS ({$subSql} AND {$relatedTable}.{$foreignKey} = {$table_name}.{$localKey})";
		//ray($existsClause);

		// Agregar la cláusula a las condiciones WHERE
		$this->whereRaw($existsClause);

		return $this;
	}

	protected function getRelatedTable($relation)
	{
		// Asumiendo que el prefijo 'wp_alexa_' ya está manejado por validateTableName
		return $this->validateTableName($relation);
	}

	protected function getForeignKey($table)
	{
		// Simplemente usar 'id' como sufijo sin el prefijo de la tabla
		//return rtrim($table, 's') . '_id';
		global $wpdb;
		$prefix = $wpdb->base_prefix.DB::$namespace.'_';

		$singularTable = rtrim($table, 's');
		$key = $singularTable . '_id';
		return str_replace($prefix, '', $key);
	}

	public function whereRaw($sql, $bindings = [])
	{
		$this->bindings['where'][] = [
			'type' => 'raw',
			'sql' => $sql,
			'bindings' => $bindings,
			'boolean' => 'and'
		];

		return $this;
	}

	// Estos métodos deben estar implementados en la clase que use este trait
	abstract protected function validateTableName($tableName);
	abstract public function buildSql();
	abstract public static function table($tableName, $fromClass = null, $keepTableName = false);
	abstract public function select($columns);
}
