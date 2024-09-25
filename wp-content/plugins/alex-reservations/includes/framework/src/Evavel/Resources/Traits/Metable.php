<?php

namespace Evavel\Resources\Traits;

trait Metable
{
    public $meta = [];

    public function meta()
    {
        return $this->meta;
    }

    public function withMeta(array $meta)
    {
        $this->meta = array_merge($this->meta, $meta);

        return $this;
    }
}
