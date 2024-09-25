<?php

namespace Evavel\Models\Traits;

trait HasTimestamps
{
    public $timestamps = true;

    public function usesTimestamps()
    {
        return $this->timestamps;
    }

    public function touch()
    {
        if (! $this->usesTimestamps()) {
            return false;
        }

        $this->updateTimestamps();

        return $this->save();
    }

    public function updateTimestamps()
    {
        $this->attributes[static::UPDATED_AT] = evavel_now();

		if (!$this->exists) {
			$this->attributes[static::CREATED_AT] = evavel_now();
		}
    }
}
