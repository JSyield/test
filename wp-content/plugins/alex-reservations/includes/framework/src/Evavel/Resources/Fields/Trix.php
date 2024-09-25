<?php

namespace Evavel\Resources\Fields;

class Trix extends Field
{
    public $component = 'trix-field';

    public $showOnIndex = false;

    public $withFiles = false;

    public $alwaysShow = false;

    public function toJsonSerialize()
    {
        return array_merge(parent::toJsonSerialize(),
            [
                'shouldShow' => $this->alwaysShow,
                'withFiles' => $this->withFiles,
            ]
        );
    }
}
