<?php

namespace Evavel\Models\Collections;

use Evavel\Interfaces\Arrayable;
use Traversable;
use ArrayAccess;

/**
 * V.1.0
 */
class Collection implements ArrayAccess, Enumerable, Arrayable
{
	use EnumeratesValues;

	public $items = [];

	public function __construct($items = [])
	{
		if (!is_array($items)) {
			$items = [$items];
		}

		$this->items = $this->getArrayableItems($items);
	}

	protected function getArrayableItems($items)
	{
		if (is_array($items)) {
			return $items;
		}

		return (array) $items;
	}

	public function all()
	{
		return $this->items;
	}

	/**
	 * Get the keys of the items
	 * @return $this
	 */
	public function keys()
	{
		return new static(array_keys($this->items));
	}

	public function first(callable $callback = null, $default = null)
	{
		return Arr::first($this->items, $callback, $default);
	}

	public function map(callable $callback)
	{
		$keys = array_keys($this->items);

		$items = array_map($callback, $this->items, $keys);

		return new static(array_combine($keys, $items));
	}

	public function mapWithKeys(callable $callback)
	{
		$result = [];

		foreach ($this->items as $key => $value) {
			$assoc = $callback($value, $key);

			foreach ($assoc as $mapKey => $mapValue) {
				$result[$mapKey] = $mapValue;
			}
		}

		return new static($result);
	}


	public function filter(callable $callback = null)
	{
		if ($callback) {
			return new static(Arr::where($this->items, $callback));
		}

		return new static(array_filter($this->items));
	}

	public function sortBy($callback, $options = SORT_REGULAR, $descending = false)
	{
		$results = [];

		$callback = $this->valueRetriever($callback);

		foreach ($this->items as $key => $value) {
			$results[$key] = $callback($value, $key);
		}

		$descending ? arsort($results, $options)
			: asort($results, $options);

		foreach (array_keys($results) as $key) {
			$results[$key] = $this->items[$key];
		}

		return new static($results);
	}

	public function values()
	{
		return new static(array_values($this->items));
	}



	public function flatten($depth = INF)
	{
		return new static(Arr::flatten($this->items, $depth));
	}

	public function pluck($value)
	{
		$list = [];
		foreach ($this->items as $item){
			if (is_array($item)){
				$list[] = $item[$value];
			} else if (is_object($item)) {
				$list[] = $item->{$value};
			}
		}
		return $list;
	}

	public function each(callable $callback)
	{
		foreach ($this as $key => $item) {
			if ($callback($item, $key) === false) {
				break;
			}
		}

		return $this;
	}

	/**
	 * Group an associative array by a field or using a callback.
	 *
	 * @param  (callable(TValue, TKey): array-key)|array|string  $groupBy
	 * @param  bool  $preserveKeys
	 * @return static<array-key, static<array-key, TValue>>
	 */
	public function groupBy($groupBy, $preserveKeys = false)
	{
		if (! $this->useAsCallable($groupBy) && is_array($groupBy)) {
			$nextGroups = $groupBy;

			$groupBy = array_shift($nextGroups);
		}

		$groupBy = $this->valueRetriever($groupBy);

		$results = [];

		foreach ($this->items as $key => $value) {
			$groupKeys = $groupBy($value, $key);

			if (! is_array($groupKeys)) {
				$groupKeys = [$groupKeys];
			}

			foreach ($groupKeys as $groupKey) {
				// PHP 8.0
				/*$groupKey = match (true) {
					is_bool($groupKey) => (int) $groupKey,
					$groupKey instanceof \Stringable => (string) $groupKey,
					default => $groupKey,
				};*/
				if (is_bool($groupKey)) {
					$groupKey = (int) $groupKey;
				} elseif ($groupKey instanceof \Stringable) {
					$groupKey = (string) $groupKey;
				}

				if (! array_key_exists($groupKey, $results)) {
					$results[$groupKey] = new static;
				}

				$results[$groupKey]->offsetSet($preserveKeys ? $key : null, $value);
			}
		}

		$result = new static($results);

		if (! empty($nextGroups)) {
			return $result->map->groupBy($nextGroups, $preserveKeys);
		}

		return $result;
	}

	/**
	 * Key an associative array by a field or using a callback.
	 *
	 * @param  (callable(TValue, TKey): array-key)|array|string  $keyBy
	 * @return static<array-key, TValue>
	 */
	public function keyBy($keyBy)
	{
		$keyBy = $this->valueRetriever($keyBy);

		$results = [];

		foreach ($this->items as $key => $item) {
			$resolvedKey = $keyBy($item, $key);

			if (is_object($resolvedKey)) {
				$resolvedKey = (string) $resolvedKey;
			}

			$results[$resolvedKey] = $item;
		}

		return new static($results);
	}

	public function getIterator(): Traversable {
		return new \ArrayIterator($this->items);
	}

	/**
	 * @return int
	 */
	#[\ReturnTypeWillChange]
	public function count()
	{
		return count($this->items);
	}

	/**
	 * @param mixed $offset
	 *
	 * @return bool
	 */
	#[\ReturnTypeWillChange]
	public function offsetExists( $offset ) {
		return isset($this->items[$offset]);
	}

	/**
	 * @param mixed $offset
	 *
	 * @return mixed
	 */
	#[\ReturnTypeWillChange]
	public function offsetGet( $offset ){
		return $this->items[$offset];
	}

	/**
	 * @param mixed $offset
	 * @param mixed $value
	 *
	 * @return void
	 */
	#[\ReturnTypeWillChange]
	public function offsetSet( $offset, $value ){
		if (is_null($offset)) {
			$this->items[] = $value;
		} else {
			$this->items[$offset] = $value;
		}
	}

	/**
	 * @param mixed $offset
	 *
	 * @return void
	 */
	#[\ReturnTypeWillChange]
	public function offsetUnset( $offset ){
		unset($this->items[$offset]);
	}

	// Operar cada model
	public function toArrayEachModel() {
		$list = [];

		foreach($this->items as $item)
		{
			if (is_string($item) || is_int($item) || is_float($item) || is_array($item)){
				$list[] = $item;
			}
			else if (is_object($item)) {
				$list[] = $item->toArray();
			}
			else {
				$list[] = $item;
			}
		}
		return $list;
	}

	// Obtener el array de IDs solamente
	public function toArrayIds(){
		return $this->map(function($item){ return $item->id; })->toArray();
	}

	// Recursivo, no usar, necesito los objetos como classes, no como array
	/*public function toArray() {
		$list = [];

		foreach($this->items as $item)
		{
			if (is_string($item) || is_int($item) || is_float($item) || is_array($item)){
				$list[] = $item;
			}
			else if (is_object($item)) {
				$list[] = $item->toArray();
			}
			else {
				$list[] = $item;
			}
		}
		return $list;
	}*/
}
