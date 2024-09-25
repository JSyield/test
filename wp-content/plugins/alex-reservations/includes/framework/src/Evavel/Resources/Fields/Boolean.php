<?php

namespace Evavel\Resources\Fields;

use Evavel\Http\Request\Request;

class Boolean extends Field
{
    public $component = 'boolean-field';

    public $trueValue = true;

    public $falseValue = false;

    public $textAlign = 'center';

	public function __construct($name, $attribute = null)
	{
		parent::__construct($name, $attribute);

		$this->withMeta(['type' => 'checkbox']);
	}

    public function values($trueValue, $falseValue)
    {
        return $this->trueValue($trueValue)->falseValue($falseValue);
    }

    public function trueValue($value)
    {
        $this->trueValue = $value;

        return $this;
    }

    public function resolveValueFromModel($model)
    {
        $value = parent::resolveValueFromModel($model);

        $this->value = $value == $this->trueValue;

        return $this->value;
    }

    public function falseValue($value)
    {
        $this->falseValue = $value;

        return $this;
    }

	public function typeSwitch()
	{
		$this->withMeta(['type' => 'switch']);

		return $this;
	}

	protected function fillAttributeFromRequest(Request $request, $requestAttribute, $model, $attribute)
	{
		$value = $request->{$requestAttribute};

		if ($value == 'true') $value = 1;
		if ($value == 'false') $value = 0;

		$model->{$attribute} = $this->isNullValue($value) ? null : $value;
	}
}
