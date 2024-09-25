<?php

namespace Evavel\Models\Traits;

use Evavel\Query\Query;

trait HasMeta {

	protected $metaIsLoaded = false;

	/**
	 * Detect if meta has changed so needs to be saved
	 *
	 * @var bool
	 */
	protected $metaIsDirty = false;

	/**
	 * Store the meta fields
	 *
	 * @var null
	 */
	public $meta = [];

	/**
	 * Query the meta fields for resourceId
	 *
	 * @param $resourceId
	 *
	 * @return \stdClass
	 */
	public static function queryMetaToArray($resourceId)
	{
		$rows = self::queryMeta($resourceId);

		$list = new \stdClass();

		foreach($rows as $row){
			if (is_array($row)){
				$key = $row['key'];
				$value = $row['value'];
			} else {
				$key = $row->key;
				$value = $row->value;
			}
			$list->$key = $value;
		}

		return evavel_to_array($list);
	}

	/**
	 * Query to fetch the meta data from the model
	 * Values are json decoded after fetched
	 *
	 * @param $resourceId
	 *
	 * @return array|\Evavel\Query\stdClass|\Evavel\Query\stdClass[]|false|object|null
	 */
	public static function queryMeta($resourceId)
	{
		$resource_name = static::getResourceName();
		$table_name = static::$table_name; //$instance->table_name;
		$table_meta = static::$table_meta; //$instance->table_meta;

		$result = Query::table( $table_name )
		            ->select([
			            $table_meta.'.id as m_id',
			            $table_meta.'.meta_key as key',
			            $table_meta.'.meta_value as value'
		            ])
		            ->join($table_meta, $table_meta.'.'.$resource_name.'_id', '=', $table_name.'.id')
		            ->where($table_name.'.id', $resourceId)
		            ->get();

		foreach($result as $item) {
			$decoded = json_decode($item->value);
			if ($decoded){
				$item->value = $decoded;
			}
		}

		return $result;
	}

	public function metaDirty()
	{
		return $this->metaIsDirty;
	}

	public function loadMeta()
	{
		if (static::$table_meta){
			$this->getMetaFields();
		}
		return $this;
	}

	/**
	 * Query meta data
	 *
	 * @return void
	 */
	protected function getMetaFields()
	{
		$this->meta = $this::queryMetaToArray($this->id);
		$this->metaIsLoaded = true;
	}

	/**
	 * Check if this meta exists
	 *
	 * @param $key
	 *
	 * @return bool
	 */
	public function hasMeta($key)
	{
		if (!$this->metaIsLoaded){
			$this->loadMeta();
		}

		return array_key_exists($key, $this->meta);
	}

	/**
	 * Get the meta from key
	 *
	 * @param $key
	 *
	 * @return mixed|null
	 */
	public function getMeta($key = null)
	{
		if (!$this->metaIsLoaded){
			$this->loadMeta();
		}

		if (!$key) {
			return $this->meta;
		}

		return isset($this->meta[$key]) ? $this->meta[$key] : null;
	}

	/**
	 * Set some meta key in the array
	 *
	 * @param $key string|array
	 * @param $value mixed
	 *
	 * @return $this
	 */
	public function setMeta($key, $value = null)
	{
		// Receiving an array of pairs key->value in the first parameter
		if (is_array($key)){

			foreach ($key as $meta_key => $meta_value) {
				$this->meta[$meta_key] = $value;
			}
			$this->metaIsDirty = true;
		}
		else {
			$this->meta[$key] = $value;
			$this->metaIsDirty = true;
		}


		return $this;
	}

	public function deleteMeta($key)
	{
		if (is_array($key)){
			foreach ($key as $subkey){
				$this->deleteMeta($subkey);
			}
			return;
		}

		if (isset($this->meta[$key])) {
			unset($this->meta[$key]);
			$this->metaIsDirty = true;
			return;
		}
	}

	protected function toJson($value)
	{
		return evavel_json_encode($value);
	}

	/**
	 * Remove from the array using the key
	 *
	 * @param $key
	 *
	 * @return void
	 */
	public function removeMeta($key)
	{
		if (isset($this->meta[$key])){
			unset($this->meta[$key]);
			$this->metaIsDirty = true;
		}

		return $this;
	}

	/**
	 * Clear all meta fields
	 *
	 * @return $this
	 */
	public function clearMeta()
	{
		$this->meta = [];
		$this->metaIsDirty = true;

		return $this;
	}

	/**
	 * Save meta fields
	 * Values are transformed to json before saving
	 *
	 * @return void
	 */
	public function saveMeta()
	{
		if (!static::$table_meta) return;

		// Find all metas from the DB
		//$meta_saved = static::queryMetaToArray($this->id);
		$metas_saved = static::queryMeta($this->id);

		// Iterate fresh DB values and remove if the meta_key does not exists
		// now in the model meta
		foreach($metas_saved as $meta_saved)
		{
			// Remove meta from database
			if (!isset($this->meta[$meta_saved->key]))
			{
				Query::table(static::$table_meta)
				     ->where('id', $meta_saved->m_id)
				     ->delete();
			}
		}

		// Iterate model meta values and create/update DB records accordingly
		foreach($this->meta as $meta_key => $meta_value)
		{
			// Find the meta_saved object
			$meta_saved_found = false;
			foreach($metas_saved as $meta_saved){
				if ($meta_saved->key == $meta_key){
					$meta_saved_found = $meta_saved;
					break;
				}
			}

			// Create new record
			if (!$meta_saved_found)
			{
				$params = [
					'meta_key' => $meta_key,
					'meta_value' => evavel_json_encode($meta_value),
				];

				// Add tenant field
				if (static::$table_name != evavel_config('app.tenant')){
					$tenant_field = evavel_tenant_field();
					$params[$tenant_field] = $this->{$tenant_field};
				}

				// Add reference to model id
				$field_id = evavel_singular(static::$table_name).'_id';
				$params[$field_id] = $this->id;

				Query::table(static::$table_meta)->insert([$params]);
			}

			// Update record
			else if ($meta_saved_found->value != $meta_value)
			{
				Query::table(static::$table_meta)
					->where('id', $meta_saved_found->m_id)
					->update([
						'meta_value' => evavel_json_encode($meta_value),
					]);
			}
		}

	}
}
