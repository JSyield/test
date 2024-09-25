<?php

namespace Evavel\Models\Collections;

use Evavel\Interfaces\Arrayable;

trait EnumeratesValues
{
    public function mapInto($class)
    {
        return $this->map(function($value, $key) use($class) {
            return new $class($value, $key);
        });
    }

    protected function valueRetriever($value)
    {
        if ($this->useAsCallable($value)) {
            return $value;
        }

        // Assume $item will be a direct array
	    return function($item) use($value) {
		    if (is_array($item)) {
			    return $item[$value];
		    }
		    return $item->{$value};
	    };
        //@todo: pending as -dot- notation for array with subarrays -> get_data()
    }

    public function reject($callback = true)
    {
        $useAsCallable = $this->useAsCallable($callback);

        return $this->filter(function ($value, $key) use ($callback, $useAsCallable) {
            return $useAsCallable
                ? ! $callback($value, $key)
                : $value != $callback;
        });
    }

    protected function useAsCallable($value)
    {
        return ! is_string($value) && is_callable($value);
    }

    public function toArray()
    {
        return $this->map(function ($value) {
            return $value instanceof Arrayable ? $value->toArray() : $value;
        })->all();
    }
}
