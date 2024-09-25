<?php

namespace Evavel\Models;

use Evavel\Http\Request\AppSettingsRequest;
use Evavel\Query\Query;

class Setting extends Model
{
	public static $table_name;
	public static $meta_key;
	public static $pivot_tenant_field;

	public static function description() {
		return null;
	}

	public static function scopedGlobal( Query $query ) {
		return $query->where('meta_key', static::$meta_key);
	}

	// Es un campo dentro de meta value
	public function __get($key) {

		if ($key == static::$pivot_tenant_field) {
			return $this->getAttribute($key);
		}

		if ($key == 'meta_key') {
			return static::$meta_key;
		}

		if ($key == 'ordering') {
			return $this->getAttribute($key);
		}

		if (method_exists($this, $key)) {
			return $this->getAttribute($key);
		}

		$meta_value = $this->getAttribute('meta_value');
		if (!is_array($meta_value)) return null;

		$value = isset($meta_value[$key]) ? $meta_value[$key] : null;

		if ($this->hasCast($key)) {
			$value = $this->castAttribute($key, $value);
		}

		return $value;
	}

	public function __set($key, $value) {

		if ($key == static::$pivot_tenant_field){
			$this->setAttribute($key, $value);
			return;
		}

		if ($key == 'meta_key') {
			$this->setAttribute('meta_key', $value);
			return;
		}

		if ($key == 'ordering') {
			$this->setAttribute('ordering', $value);
			return;
		}

		$meta_value = $this->getAttribute('meta_value');
		if (!is_array($meta_value)) {
			$meta_value = [];
		}

		$meta_value[$key] = $value;
		$this->setAttribute('meta_value', $meta_value);
	}

	public function save( array $options = [] ) {
		$this->meta_key = static::$meta_key;
		return parent::save($options);
	}

	//protected function defaultValue() {
		//return [];
	//}

	/**
	 * Convert meta_value all keys to array of attributes like any other model
	 * @return array
	 */
	public function toArray() {

		$data = [
			'id' => intval($this->id),
			'ordering' => intval($this->ordering),
		];

		// Move all keys to the array
		foreach( $this->defaultValue() as $key => $value ){
			$data[$key] = evavel_json_decode($this->{$key});
		}

		return $data;
	}

	/**
	 * Check any personal rule that should be checked with other
	 * items in the list like dates overlapping for example
	 * @return void
	 */
	public function validate() {

		return [];

		//return [ 'error' => 'This is overlapping with item xx' ];
	}

	/** Validate all items to find overlaps between several items */
	public static function validateAll() {
		return [];
	}

	public function setupDefaultValues()
	{
		foreach($this->defaultValue() as $key => $value){
			$this->{$key} = $value;
		}

		return $this;
	}

	public function defaultvalue() {
		return [];
	}

	public function fields() {
		return [];
	}

	public static function configuration(AppSettingsRequest $request)
	{
		return [ 'mode' => static::class];
	}
}
