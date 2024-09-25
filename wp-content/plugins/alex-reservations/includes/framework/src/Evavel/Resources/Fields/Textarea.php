<?php

namespace Evavel\Resources\Fields;

class Textarea extends Field
{
    public $component = 'textarea-field';

    public $showOnIndex = false;

    public $alwaysShow = false;

    public $rows = 5;

    public function rows($rows)
    {
        $this->rows = $rows;
        return $this;
    }

    public function alwaysShow()
    {
        $this->alwaysShow = true;
        return $this;
    }

    public function resolveValueFromModel($model)
    {
        parent::resolveValueFromModel($model);

        $this->value = evavel_encode($this->value);
    }

    public function toJsonSerialize()
    {
        return array_merge(parent::toJsonSerialize(),
            [
                'rows' => $this->rows,
                'shouldShow' => $this->alwaysShow
            ]
        );
    }
}
