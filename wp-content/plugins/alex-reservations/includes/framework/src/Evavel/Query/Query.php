<?php

namespace Evavel\Query;

// The QueryBuilder
use Evavel\Log\Log;
use Evavel\Models\Collections\Collection;
use Evavel\Query\Connections\Wordpress;
use Closure;

use Evavel\Query\Traits\WhereHasTrait;

/**
 * V.1.1
 */
class Query
{
	use WhereHasTrait;

	protected $debug_ray_all_sql = false;
	protected static $debug_ray_all_sql_static = false;
	protected $debug_ray_this_sql_only = false;

	protected static $use_cache = false;
	protected static $cached = [];

	public $connection;

	public $fromClass; // SRR_Booking, etc
	public $singular;

	public $table_name = '';
	public $keep_table_name = false;

	public $bindings = [
		'from' => [],
		'select' => [],
		'pivot' => [],
		'join' => [],
		'where' => [],
		'order' => []
	];
	public $columns;
	public $limit;
	public $offset;
	public $operators = [
		'=', '<', '>', '<=', '>=', '<>', '!=', '<=>',
		'like'
	];
	public $raw = [];
	public $withs = [];
	public $withMeta = false; // Extract meta values
	public $toArray = false;
	public $values = false;
	public $onlyCount = false;

	// For updating
	public $update = [];


	// For inserting
	public $insert = [];

	public $wheres = [];

	public $delete = false;

	public $pivotAccessor = 'pivot';

	public static function setDebug($option = true)
	{
		self::$debug_ray_all_sql_static = $option;
	}

	public static function setCache($option = true)
	{
		self::$use_cache = $option;
	}

	public static function tableWP($table_name, $from_class = null)
	{
		return static::table($table_name, $from_class, true);
	}

	// If $from_class is passed then the result will be transformed
	// each item to that Class object
	// Otherwise will return stdClass objects
	public static function table( $table_name, $from_class = null, $keep_table_name = false )
	{
		$table_name = sanitize_text_field($table_name);

		$instance = new static();

		$instance->keep_table_name = $keep_table_name;

		// Prepare the WP connection
		$instance->table_name = $table_name;
		$instance->connection = new Wordpress();

		$instance->bindings['from'] = $instance->validateTableName($table_name);

		// customers -> customer
		$singular_name = evavel_singular($table_name);
		$instance->singular = $singular_name;
		$instance->fromClass = $from_class;

		return $instance;
	}

	public function debug()
	{
		$this->debug_ray_this_sql_only = true;
		return $this;
	}

	public function fromTenant($id, $field = null)
	{
		// restaurant_id
		if ($field == null){
			$field = evavel_tenant_field();
		}

		return $this->where($field, '=', $id);
	}

	public function select($columns = ['*'])
	{
		$this->columns = [];
		$this->bindings['select'] = [];

		$columns = is_array($columns) ? $columns : func_get_args();

		$this->bindings['select'] = $columns;
		return $this;
	}

	public function pivot($columns = [], $accesor = 'pivot')
	{
		$this->bindings['pivot'] = $columns;
		$this->pivotAccessor = $accesor;
		return $this;
	}

	public function limit($limit)
	{
		$this->limit = intval($limit);
		return $this;
	}

	public function offset($offset)
	{
		$this->offset = intval($offset);
		return $this;
	}

	public function page($page, $per_page)
	{
		$this->limit = $per_page;
		$this->offset = $per_page * ($page -1);
		if ($this->offset < 0){
			$this->offset = 0;
		}

		return $this;
	}

	public function orderBy($column, $direction = 'asc')
	{
		$direction = strtolower($direction);

		$this->bindings['order'][] = [
			'column' => $this->parseAsTableField($column),
			'direction' => $direction
		];

		return $this;
	}

	public function orderByDesc($column)
	{
		return $this->orderBy($column, 'desc');
	}

	public function whereNested(Closure $callback, $boolean = 'and')
	{
		call_user_func($callback, $query = new Query());

		if (count($query->bindings['where'])) {
			$this->bindings['where'][] = [
				'type' => 'nested',
				'query' => str_replace('WHERE', '', $query->buildWhere()),
				'boolean' => $boolean
			];
		}

		return $this;
	}

	public function where($column, $operator = null, $value = null, $boolean = 'and')
	{
		// @todo: pending $column is array

		// Closure
		if ($column instanceof Closure){
			return $this->whereNested($column, $boolean);
		}

		// 2 values only means we are passing operator '='
		if (func_num_args() === 2) {
			$value = $operator;
			$operator = '=';
		}

		// Value is array. ->where('id', [1,2,4])
		if (is_array($value)){
			foreach($value as $v){
				$this->orWhere($column, $operator, $v);
			}
			return $this;
		}

		$this->bindings['where'][] = [
			'column' => sanitize_text_field($column),
			'operator' => $operator,
			'value' => sanitize_text_field($value),
			'boolean' => $boolean
		];

		return $this;
	}

	public function whereIn($column, $values, $boolean = 'and', $not = false)
	{
		$type = $not ? 'NotIn' : 'In';

		if ($type == 'NotIn') $type = 'NOT IN';
		if ($type == 'In') $type = 'IN';

		$this->wheres[] = [
			'type' => $type,
			'column' => $column,
			'values' => $values,
			'boolean' => $boolean
		];

		return $this;
	}

	public function orWhere($column, $operator = null, $value = null)
	{
		if ($value === null){
			return $this->where($column, '=', $operator, 'or');
		}
		return $this->where($column, $operator, $value, 'or');
	}

	public function orWhereIn($column, $values)
	{
		return $this->whereIn($column, $values, 'or');
	}

	public function whereNotIn($column, $values, $boolean = 'and')
	{
		return $this->whereIn($column, $values, $boolean, true);
	}

	public function orWhereNotIn($column, $values)
	{
		return $this->whereNotIn($column, $values, 'or');
	}

	public function whereIsNull($column)
	{
		return $this->where($column, 'IS NULL');
	}

	public function whereIsNotNull($column)
	{
		return $this->where($column, 'IS NOT NULL');
	}

	public function join($table, $first, $operator, $second)
	{
		$this->bindings['join'][] = [
			'table' => $table,
			'first' => $first,
			'operator' => $operator,
			'second' => $second
		];

		return $this;
	}


	public function raw($string) {
		$this->raw[] = sanitize_text_field($string);
		return $this;
	}

	public function rawSql($sql) {
		return $this->execute($sql);
	}

	public function getColumns()
	{
		$table_real = $this->bindings['from'];
		$sql = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '{$table_real}'";
		$columns =  $this->rawSql($sql);
		foreach($columns as &$column){
			$column = $table_real.'.'.$column;
		}
		return $columns;
	}

	public function with($model_name) {
		if (is_array($model_name)){
			foreach ($model_name as $name){
				$this->withs[] = sanitize_text_field($name);
			}
		} else if (is_string($model_name)) {
			$this->withs[] = sanitize_text_field($model_name);
		}

		return $this;
	}

	public function withMeta() {
		$this->withMeta = true;
		return $this;
	}

	public function toArray() {
		$this->toArray = true;
		return $this;
	}

	public function values() {
		$this->toArray = true;
		$this->values = true;
		return $this;
	}

	public function count( $per_page = 0 ) {
		$this->onlyCount = true;

		$result = $this->get();
		//ray('RESULT');
		//ray($result);

		if (is_object($result) && get_class($result) == Collection::class) {
			//ray($result->items);
			$total = $result->count();
		} else {
			$total = intval($result);
		}

		//$total = intval( $this->get());

		if ($per_page > 0){
			return [
				'count' => $total,
				'pages' =>  $this->numPages($total, $per_page)
			];
		}
		return $total;
	}

	public function countPages($per_page) {
		$total = $this->count();
		return $this->numPages($total, $per_page);
	}

	protected function numPages($total, $per_page) {
		if ($total == 0) return 0;
		//ray($total);
		//ray($per_page);
		return $total % $per_page == 0 ? intval($total/$per_page) : 1 + intval($total/$per_page);
	}

	public function onlyCount() {
		$this->onlyCount = true;
		return $this;
	}

	// FINAL STEP TO FETCH
	//-------------------------------------------
	public function get()
	{
		$list = $this->execute( $this->buildSql() );
		//ray('FromClass: ' . $this->fromClass);
		// @todo: Return as collection - only one result should not be a collection
		//if (is_array($list) && count($list) > 1 && $this->fromClass !== null)
		if ($this->fromClass !== null && !$this->onlyCount)
		{
			return evavel_collect($list);
		}
		return $list;
	}

	public function all()
	{
		return $this->get();
	}

	public function get_row()
	{
		$results = $this->get();
		if (is_object($results) && get_class($results) == Collection::class){
			//$results = -$results>toArray() // es recursivo, no lo puedo usar
			$results = $results->items;
		}
		if (is_array($results) && count($results) > 0){
			return $results[0];
		}
		return null;
	}

	public function first()
	{
		return $this->get_row();
	}

	public function last()
	{
		return $this->orderBy('id', 'DESC')->first();
	}

	public function find($id)
	{
		return $this->where('id', intval($id))->first();
	}


	// UPDATE / INSERT
	//-------------------------------------------


	// UPDATE table_name SET column1 = value, column2 = value2 WHERE id=X
	public function update(array $values)
	{
		$this->update = $values;

		$result = $this->get();

		// Prevent update again if using this same query object
		//$this->udpateValues = [];

		return $result;
	}

	/**
	 * Insert one or several records
	 * Each record should be an array of attributes
	 *
	 * @param array $rows
	 *
	 * @return array|\Evavel\Models\Collections\Collection|stdClass|stdClass[]|false|object|null
	 */
	public function insert(array $rows)
	{
		// Is only 1 record
		if (is_array($rows) && count($rows) > 0 && !is_array(array_values($rows)[0]))
		{
			$rows = [$rows];
		}

		$this->insert = $rows;

		$result = $this->get();

		if (is_object($result) && get_class($result) == Collection::class){
			//$result = $result->toArray();
			$result = $result->items;
		}

		if (is_array($result) && count($result) == 1){
			$result = $result[0];
		}

		$this->insert = [];

		return $result;
	}

	public function delete()
	{
		$this->delete = true;
		$this->get();
	}

	/*public function delete(array $ids)
	{
		$ins = implode(', ',$ids);
		$sql = "DELETE FROM {$this->bindings['from']} WHERE id in()";
		$this->execute($sql);
	}*/

	// BUILD SQL
	//-------------------------------------------
	/**
	 * Build the sql query
	 *
	 * @return string
	 */
	public function buildSql()
	{
		$sql  = $this->buildSelect();
		$sql .= $this->buildPivot();
		$sql .= $this->buildFrom();
		$sql .= $this->buildJoin();
		$sql .= $this->buildWhere();
		$sql .= $this->buildWheres();
		$sql .= $this->buildOrder();
		$sql .= $this->buildRaw();
		$sql .= $this->buildLimit();
		$sql .= $this->buildOffset();

		return $sql;
	}

	protected function buildSelect()
	{
		if ($this->onlyCount){
			return "SELECT COUNT(id)";
		}

		if (empty($this->bindings['select'])){
			$this->bindings['select'] = ['*'];
		}

		$selects = [];
		foreach($this->bindings['select'] as $select){
			$selects[] = $this->parseAsTableField($select);
		}

		if ($this->delete){
			return $this->buildDelete();
		}

		// Is un update
		if (!empty($this->update)){
			return $this->buildUpdate();
		}

		// Is for inserting new records
		if (!empty($this->insert)){
			return $this->buildInsert();
		}

		return "SELECT ".implode(',', $selects);
	}

	protected function buildPivot()
	{
		$sql = "";
		if (!empty($this->bindings['pivot']) && !empty($this->bindings['join']))
		{
			if (isset($this->bindings['join'][0]['table']))
			{
				$table = $this->validateTableName($this->bindings['join'][0]['table']);

				$sql_values = [];
				$accessor = $this->pivotAccessor;

				foreach($this->bindings['pivot'] as $column) {
					$sql_values[] = "`{$table}`.`{$column}` AS `{$accessor}_{$column}`";
				}
				$sql = ",".implode(",", $sql_values);
			}
		}

		return $sql;
	}

	protected function buildDelete()
	{
		return "DELETE `{$this->bindings['from']}`";
	}

	protected function buildUpdate()
	{
		$values = $this->update;
		if (empty($values)) return '';

		$sql = "";
		foreach($values as $key => $value)
		{
			if ($value == null) {
				$sql .= "$key = NULL";
			} else {
				$value = evavel_escape_especialChars($value);
				$sql .= " $key = '$value'";
			}

			$sql .= ", ";

			// Next does not work if the value is empty
			//if (next($values)){
			//$sql .= ", ";
			//}
		}

		$sql = rtrim($sql, ", ");

		return "UPDATE `{$this->bindings['from']}` SET {$sql}";
	}

	protected function buildInsert()
	{
		$rows = $this->insert;
		if (empty($rows)) return '';

		$keys = array_keys($rows[0]);
		$sql_keys = implode(', ',$keys);

		// Modify date_created and date_modified to correct recent values
		// so can be fetched the rows after the insert

		$sql_values = [];
		foreach ($rows as $row){

			if (isset($row['date_created']) && empty($row['date_created'])){
				$row['date_created'] = evavel_now();
			}
			if (isset($row['date_modified']) && empty($row['date_modified'])){
				$row['date_modified'] = evavel_now();
			}

			// NUll should be managed correctly
			$str = " (";
			foreach($row as $k => $v) {
				if ($v === null) {
					$str .= "NULL,";
				} else {
					$v = evavel_escape_especialChars($v);
					$str .= "'" . $v . "',";
				}
			}
			$str = substr($str, 0, -1) . " )";
			$sql_values[] = $str;

			//$sql_values[] = "( '" . implode( "','", array_values($row)) . "' )";
		}

		$sql_values = implode(', ', $sql_values);

		return "INSERT INTO `{$this->bindings['from']}` ({$sql_keys}) VALUES {$sql_values}";
		//INSERT INTO table_name (column1, column2, column3, ...)
		//VALUES (value1, value2, value3, ...);
	}


	protected function buildFrom()
	{
		if (!empty($this->update)) return '';
		if (!empty($this->insert)) return '';

		return " FROM `{$this->bindings['from']}`";
	}

	protected function buildJoin()
	{
		$sql = '';

		foreach($this->bindings['join'] as $join) {

			$table = $this->validateTableName($join['table']);

			$first = explode('.', $join['first']);
			$first_table = $this->validateTableName($first[0]);
			$first_field = $first[1];

			$second = explode('.', $join['second']);
			$second_table = $this->validateTableName($second[0]);
			$second_field = $second[1];

			$sql .= " inner join `{$table}` on `{$first_table}`.`{$first_field}` {$join['operator']} `{$second_table}`.`{$second_field}`";
		}

		return $sql;
	}

	protected function buildWhere()
	{
		$sql = '';
		$first = true;
		foreach($this->bindings['where'] as $where){

			if ($first) {
				$first = false;
				$next_boolean = false;
			} else {
				$next_boolean = $where['boolean'];
			}

			if (isset($where['type']))
			{
				if (empty($sql)){
					$sql = ' WHERE 1=1';
				}

				switch ($where['type']) {
					case 'nested':
						$sql .= " {$where['boolean']} ({$where['query']})";
						break;
					case 'raw':
						$sql .= " {$where['boolean']} ({$where['sql']})";
						break;
				}
			}

			/*if (isset($where['type']) && $where['type'] == 'nested')
			{
				if (empty($sql)){
					$sql = ' WHERE 1=1';
				}
				$sql .= " {$where['boolean']} ({$where['query']})";
			}*/


			else
			{
				$column = $this->parseAsTableField($where['column']);
				$operator = $where['operator'];
				$value = $where['value'];

				if ($operator == 'like') {
					if ( strpos($value, '%') === false ){
						$value = "%{$value}%";
					}
				}

				if (!$next_boolean){
					if ($value == 'IS NULL' || $value == 'IS NOT NULL'){
						$sql .= " WHERE {$column} {$value}";
					} else {
						$sql .= " WHERE {$column} {$operator} '{$value}'";
					}
				} else {
					if ($value == 'IS NULL' || $value == 'IS NOT NULL') {
						$sql .= " {$next_boolean} {$column} {$value}";
					} else {
						$sql .= " {$next_boolean} {$column} {$operator} '{$value}'";
					}
				}
			}

		}

		return $sql;
	}

	// where column in (x,x,x,x)
	protected function buildWheres()
	{
		$sql = '';

		$add_boolean = false;
		if (!empty($this->bindings['where'])) $add_boolean = true;

		$first = true;
		foreach($this->wheres as $where) {
			$values_string = "'".implode("','", $where['values'])."'";

			if ($first){
				if ($add_boolean) {
					$sql .= " {$where['boolean']} {$where['column']} {$where['type']} ({$values_string})";
				} else {
					$sql .= " WHERE {$where['column']} {$where['type']} ({$values_string})";
				}
				$first = false;
			} else {
				$sql .= " {$where['boolean']} {$where['column']} {$where['type']} ({$values_string})";
			}

		}
		return $sql;
	}

	protected function buildOrder()
	{
		$sql = '';
		foreach($this->bindings['order'] as $order){
			$sql .= " ORDER BY {$order['column']} {$order['direction']}";
		}
		return $sql;
	}

	protected function buildRaw()
	{
		$sql = '';
		foreach($this->raw as $raw){
			$sql .= " {$raw}";
		}
		return $sql;
	}

	protected function buildLimit()
	{
		$sql = '';
		if ($this->limit) {
			$sql = " LIMIT {$this->limit}";
		}
		return $sql;
	}

	protected function buildOffset()
	{
		$sql = '';
		if ($this->offset) {
			$sql .= " OFFSET {$this->offset}";
		}
		return $sql;
	}


	protected function parseAsTableField($select)
	{
		// id as cid => `id` as ``cid
		// customers.id as cid => `customers`.`id` as `cid`
		// customers.id => `customers`.``id

		$explode = explode('.', $select);
		if (is_array($explode) && count($explode) == 2)
		{
			$table = $this->validateTableName($explode[0]);
			$field = $explode[1];

			if (preg_match('#^(.+) as (.+)$#', $field, $matches)){
				$select = "`{$table}`.`$matches[1]` as `{$matches[2]}`";
			} else {
				$select = "`{$table}`.`$field`";
			}
		}
		else if (preg_match('#^(.+) as (.+)$#', $select, $matches))
		{
			$select = "`{$matches[1]}` as `{$matches[2]}`";
		}


		return $select;
	}

	public function validateTableName( $table )
	{
		if ($this->keep_table_name) return $table;
		return $this->connection->tableName($table);
	}

	/**
	 * @param $sql
	 * @return array|object|stdClass[]|null
	 */
	protected function execute($sql)
	{
		$result = $this->execute_cached($sql);
		if (self::$use_cache && !isset(self::$cached[$sql])){
			self::$cached[$sql] = $result;
		}

		return $result;
	}
	protected function execute_cached($sql)
	{
		if (self::$use_cache && isset(self::$cached[$sql])) {
			return self::$cached[$sql];
		}

		if ( $this->debug_ray_all_sql ||
		     $this->debug_ray_this_sql_only ||
		     self::$debug_ray_all_sql_static
		){
			$this->debug_ray_this_sql_only = false;
			ray($sql);
		}

		if (strpos($sql, 'DELETE') !== false)
		{
			evavel_log_to_file(Log::DB_DELETE, $sql);
		}
		else if (strpos($sql, 'UPDATE') !== false)
		{
			evavel_log_to_file(Log::DB_UPDATE, $sql);
		}
		else if (strpos($sql, 'INSERT') !== false)
		{
			evavel_log_to_file(Log::DB_CREATE, $sql);
		}

		$result = $this->connection->query($sql);
		//ray($result ? 'YES' : 'NO');

		// Update query
		if (!empty($this->update)){
			return $this->process_update_result($result);
		}

		// Insert query
		if (!empty($this->insert)){
			$result = $this->process_insert_result($result);
		}

		if ($result == null) {
			return $this->process_null_result($result);
		}

		// Result from WP -> ['COUNT[id]' => x]
		if ($this->onlyCount){
			$key = "COUNT(id)";

			if (is_array($result))
			{
				$item = $result[0];
				return (isset($item->$key)) ?  intval($item->$key) : $item;
			} else {
				return $result;
			}

		}

		// Transform to custom object Booking...
		if ($this->fromClass && is_countable($result)) {
			$class_name = $this->fromClass;
			for ($i = 0; $i < count($result); $i++) {
				$arr = evavel_to_array($result[$i]);
				$result[$i] = new $class_name($arr);
			}
		}

		return $this->process_result($result);
	}

	protected function process_update_result($result)
	{
		return $result;
	}

	/**
	 * Receive array of ids
	 *
	 * @param array $result
	 * @return array|stdClass[]|false|object|null
	 */
	protected function process_insert_result($result)
	{
		if (!$result) return $result;

		return static::table($this->table_name, $this->fromClass)
		             ->whereIn('id', $result)
		             ->first();

		/*
		// Fetch the last rows inserted
		$limit = count($this->insert);

		// uso el ID pero deberia buscar otra forma mas segura
		return static::table($this->table_name, $this->fromClass)
			->orderByDesc('id')
			->limit($limit)
			->get();
		*/
	}

	protected function process_null_result($result)
	{
		return $result;
	}

	protected function array_flatten($array) {
		if (!is_array($array)) {
			return false;
		}
		$result = array();
		foreach ($array as $key => $value) {
			if (is_array($value)) {
				$result = array_merge($result, $this->array_flatten($value));
			} else {
				$result = array_merge($result, array($key => $value));
			}
		}
		return $result;
	}

	protected function process_result(&$result)
	{
		$this->buildWithMeta($result);
		$this->buildWiths($result);

		if ($this->toArray){
			$array = evavel_to_array($result);
			return $this->values ? $this->array_flatten($array) : $array;
		}

		return $result;
	}

	protected function buildWithMeta(&$models)
	{
		if (!$this->withMeta) return;

		$class_name = evavel_model_prefix().ucfirst($this->singular);
		if (class_exists($class_name)){
			for($i = 0; $i < count($models); $i++){
				$models[$i]->metas = $class_name::metaValues($models[$i]->id);
			}
		}

		return $this;
	}

	protected function buildWiths(&$models)
	{
		if (empty($models)) return;

		foreach( $this->withs as $singular)
		{
			$plural = $singular.'s';
			for($i = 0; $i < count($models); $i++){

				$field_name = $singular.'_id';
				if ($models[$i]->$field_name !== null) {

					// Transform to class if parent is called from class
					$query_from_class = null;
					if ($this->fromClass){
						$query_from_class = evavel_model_prefix().$singular;
					}

					$query = Query::table($plural, $query_from_class);
					if ($this->withMeta){
						$query = $query->withMeta();
					}

					$models[$i]->$singular = $query->find($models[$i]->$field_name);
				}

			}
		}
	}

}
