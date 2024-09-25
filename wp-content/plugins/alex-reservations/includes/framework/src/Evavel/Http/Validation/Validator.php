<?php

/*
 $validator->passes()
 $validator->fails()
 $validator->messages() , $validator->errors()
*/

namespace Evavel\Http\Validation;

use Evavel\Http\Validation\Traits\ValidatorMessages;
use Evavel\Http\Validation\Traits\ValidatorRules;

class Validator
{
    use ValidatorMessages;
    use ValidatorRules;

    public $initialRules;
    public $data;
    public $rules;

    public $currentRule;
    public $messages;

    protected $numericRules = ['Numeric', 'Integer'];

    public function __construct(array $data, array $rules)
    {
        $this->initialRules = $rules;
        $this->data = $data;

        $this->setRules($rules);
    }

    public static function make(...$params)
    {
        return new static(...$params);
    }

    public function setRules(array $rules)
    {
        // @todo: Map with keys

        $this->rules = [];

        $this->addRules($rules);

        return $this;
    }

    public function addRules($rules)
    {
        $list = [];
        foreach($rules as $attribute => $attr_rules){
            if ($the_rules = $this->parseRules($attr_rules)){
                $list[$attribute] = $the_rules;
            }
        }

        $this->rules = array_merge_recursive($this->rules, $list);
    }

    protected function parseRules($value)
    {
        if (is_string($value)){
            return explode('|', $value);
        } else if (is_array($value)){
            if (count($value) > 0){
                return $value;
            }
        }
        return null;
    }

    public function errors()
    {
        return $this->messages();
    }

    public function messages()
    {
        if (! $this->messages) {
            $this->passes();
        }

        return $this->messages;
    }

    public function passes()
    {
        $this->messages = [];

        foreach($this->rules as $attribute => $rules) {
            foreach($rules as $rule){
                $this->validateAttribute($attribute, $rule);
            }
        }

        return empty($this->messages);
    }

    public function fails()
    {
        return ! $this->passes();
    }

    public function getValue($attribute)
    {
        return key_exists($attribute, $this->data)
            ? $this->data[$attribute]
            : null;
    }

    public function validateAttribute($attribute, $rule)
    {
        $result = $this->parseRule($rule);
        $rule = $result['rule'];
        $parameters = $result['parameters'];

        $value = $this->getValue($attribute);

        $method = "validate".ucfirst($rule);

        if (!$this->$method($attribute, $value, $parameters, $this)) {
          $this->addFailure($attribute, $rule, $parameters);
        }
    }

    public function addFailure($attribute, $rule, $parameters)
    {
        $this->messages[$attribute][] = $this->getErrorMessage($attribute, $rule, $parameters);
    }

    public function parseRule($rule)
    {
        $args = explode(':', $rule);

        $rule = array_shift($args);

        return [
            'rule' => $rule,
            'parameters' => $args
        ];
    }

    protected function getSize($attribute, $value)
    {
        $hasNumeric = $this->hasRule($attribute, ['integer','numeric']);

        if ($hasNumeric && is_numeric($value)) {
            return $value;
        } elseif (is_array($value)) {
            return count($value);
        }

        return mb_strlen($value ? $value : '');
    }

    public function hasRule($attribute, $rules)
    {
        return ! is_null($this->getRule($attribute, $rules));
    }

    // @todo: test
    public function getRule($attribute, $rules)
    {
        if (! array_key_exists($attribute, $this->rules)) {
            return;
        }

        $rules = (array) $rules;

        foreach ($this->rules[$attribute] as $rule) {
            if (in_array($rule, $rules)){
                return $rule;
            }
        }
    }

}
