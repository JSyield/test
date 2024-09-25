<?php

namespace Evavel\Models\Traits;

use Evavel\Interfaces\Arrayable;
use Evavel\Models\Collections\Arr;
use Evavel\Support\Str;


/**
 * V.1.0
 */
trait HasAttributes
{
	public $attributes = [];
	public $original = [];
	public $changes = [];

	/**
	 * Accessors to append to the models array
	 * @var array
	 */
	protected $appends = [];

	protected $casts = [];

	public function getAttributes()
	{
		return $this->attributes;
	}

	public function getAttribute($key)
	{
		if (! $key) {
			return;
		}

		// Check if has an attribute,
		// otherwise try meta field or relationship
		if (array_key_exists($key, $this->attributes) ||
			$this->hasGetMutator($key)
		)
		{
			return $this->getAttributeValue($key);
		}

		// Try with meta field
		if (array_key_exists($key, $this->meta))
		{
			return $this->getMetaValue($key);
		}

		// Base model methods are intented as helpers, not as relations
		if (method_exists(self::class, $key)) {
			return;
		}

		if (method_exists($this, $key)){
			return $this->getRelationValue($key);
		}

		return null;
	}

	public function getAttributeValue($key)
	{
		return $this->transformModelValue($key, $this->getAttributeFromArray($key));
	}

	public function getAttributeFromArray($key)
	{
		$attributes = $this->getAttributes();
		if (isset($attributes[$key])) return $attributes[$key];

		return null;
	}

	protected function transformModelValue($key, $value)
	{
		if ($this->hasGetMutator($key)) {
			return $this->mutateAttribute($key, $value);
		}

		// Check has cast
		if ($this->hasCast($key)) {
			return $this->castAttribute($key, $value);
		}

		return $value;
	}

	public function hasGetMutator($key)
	{
		return method_exists($this, 'get'.Str::studly($key).'Attribute');
	}

	protected function mutateAttribute($key, $value)
	{
		return $this->{'get' . Str::studly($key) . 'Attribute'}($value);
	}

	public function hasCast($key)
	{
		if (array_key_exists($key, $this->getCasts())) {
			return true;
		}

		return false;
	}

	public function getCasts()
	{
		return array_merge([
			//'date_created' => 'datetime', // no lo uso
			//'date_modified' => 'datetime', // no lo uso
			// For the Setting.php
			'meta_value' => 'array'
		], $this->casts);
	}

	protected function castAttribute($key, $value)
	{
		$castType = $this->getCasts()[$key];

		// @todo: add more cast types
		switch ($castType) {
			case 'int':
			case 'integer':
				return (int) $value;
			case 'null_or_integer':
				if ($value == null) return $value;
				return (int) $value;
			case 'bool':
			case 'boolean':
				return (bool) $value;
			case 'object':
				return $this->fromJson($value, true);
			case 'array':
			case 'json':
				return $this->fromJson($value, false);
			case 'array_of_integers':
				return $this->fromJsonInteger($value, false);
			case 'date':
				return $this->asDate($value);
			case 'datetime':
			case 'custom_datetime':
				return $this->asDateTime($value);
		}

		return $value;
	}

	protected function asDate($value)
	{
		return $this->asDateTime($value)->startOfDay();
	}

	protected function asDateTime($value)
	{
		// @todo: pending create a custom Date facade
		return evavel_new_date($value);
	}

	public function getMetaValue($key)
	{
		return $this->meta[$key];
	}

	public function getRelationValue($key)
	{
		$relation = $this->$key();

		return $relation->getResults();
	}

	/*public function setAttribute($key, $value)
	{
		if (key_exists($key, $this->attributes)){
			$this->attributes[$key] = $value;
		}

		// For new model
		if ($this->id == null){
			$this->original[$key] = null;
			$this->attributes[$key] = $value;
		}
	}*/

	public function setAttribute($key, $value)
	{
		if ($this->hasSetMutator($key)) {
			return $this->setMutatedAttributeValue($key, $value);
		}

		$this->attributes[$key] = $value;

		return $this;
	}

	public function hasSetMutator($key)
	{
		return method_exists($this, 'set'.Str::studly($key).'Attribute');
	}

	protected function setMutatedAttributeValue($key, $value)
	{
		return $this->{'set'.Str::studly($key).'Attribute'}($value);
	}

	public function getDirty()
	{
		$dirty = [];

		foreach($this->getAttributes() as $key => $value)
		{
			if (! $this->originalIsEquivalent($key)) {
				$dirty[$key] = $value;
			}
		}

		return $dirty;
	}

	// @todo: pending casting different types
	public function originalIsEquivalent($key)
	{
		if (! array_key_exists($key, $this->original)) {
			return false;
		}

		$attribute = Arr::get($this->attributes, $key);
		$original = Arr::get($this->original, $key);

		if ($attribute == $original) {
			return true;
		} elseif (is_null($attribute)) {
			return false;
		} else if (is_null($original)) {
			return false;
		}
	}

	public function syncOriginal()
	{
		$this->original = $this->getAttributes();

		return $this;
	}

	public function syncChanges()
	{
		$this->changes = $this->getDirty();

		return $this;
	}

	protected function getAttributesForInsert()
	{
		return $this->getAttributes();
	}

	public function fromJson($value, $asObject = true)
	{
		if (is_string($value)){
			return json_decode($value, ! $asObject);
		}
		return $value;
	}

	public function fromJsonInteger($value, $asObject = true)
	{
		$data = $this->fromJson($value, $asObject);
		if (is_array($data)) {
			return array_map('intval', $data);
		}
		return $data;
	}

	protected function getArrayableItems( array $values ) {

		if ( count( $this->getVisible() ) > 0 ) {
			$values = array_intersect_key( $values, array_flip( $this->getVisible() ) );
		}

		if ( count( $this->getHidden() ) > 0 ) {
			$values = array_diff_key( $values, array_flip( $this->getHidden() ) );
		}

		return $values;
	}

	public function attributesToArray()
	{
		$attributes = $this->getArrayableAttributes();

		$attributes = $this->addCastAttributesToArray($attributes);

		foreach ($this->getArrayableAppends() as $key) {
			$attributes[$key] = $this->{$key};
		}

		// Convertir tambien las colecciones
		/*foreach ($this->getArrayableAppends() as $key) {
			// Convert also if the appends is a collection
			$value = $this->{$key};
			if ($value instanceof Arrayable) {
				$value = $value->toArray();
			}
			$attributes[$key] = $value;
		}*/

		return $attributes;
	}

	protected function addCastAttributesToArray(array $attributes)
	{
		foreach($attributes as $key => $value){
			if ($this->hasCast($key)){
				$attributes[$key] = $this->castAttribute($key, $value);
			}
		}

		return $attributes;
	}

	protected function getArrayableAppends()
	{
		//ray('getArrayableAppends');
		//ray($this->appends);
		//ray(get_class($this));
		if (! count($this->appends)) {
			return [];
		}

		return $this->appends;
	}

	public function relationsToArray()
	{
		// @pending
		return [];
	}

	protected function getArrayableAttributes()
	{
		return $this->getArrayableItems($this->getAttributes());
	}
}


