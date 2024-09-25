<?php

namespace Evavel\Resources\Fields;

use Evavel\Interfaces\ToJsonSerialize;
use Evavel\Http\Request\Request;
use Evavel\Resources\Traits\Metable;

abstract class Field implements ToJsonSerialize
{
    use Metable;

    public $name;
    public $attribute;
    public $textAlign = 'left';
    public $panel = null;
    public $helpText;
    public $sortable = false;
	public $stacked = false;
	public $style = '';

    public $readonlyCallback;
    public $requiredCallback; // @todo: OJO -> si es function no funciona todavia con getRules

    public $showOnIndex = true;
    public $showOnDetail = true;
    public $showOnCreation = true;
    public $showOnUpdate = true;

	// If the field is hidden onUpdate but is sent
	// then will be updated.
	// Can happen when I do not want to show the field
	// in the update form but the data is still sent
	// through a direct update request
	public $saveOnUpdate = true;
	public $saveOnCreate = true;

    protected $computedCallback;

    public $model;
    public $context;
    public $value;

    protected $placeholder = '';

    public $rules = [];
    public $creationRules = [];
    public $updateRules = [];

	public $nullable = false;
	public $nullValues = [''];

    public function __construct($name, $attribute = null)
    {
        $this->name = $name;

        if ($attribute instanceof Closure || (is_callable($attribute) && is_object($attribute))) {
            $this->computedCallback = $attribute;
            $this->attribute = 'ComputedField';
        } else {
            $this->attribute = $attribute ? $attribute : str_replace(' ', '_', strtolower($name));
        }

    }

    public static function make(...$arguments)
    {
        return new static(...$arguments);
    }

    public function computed()
    {
        return (is_callable($this->attribute) && ! is_string($this->attribute)) ||
            $this->attribute == 'ComputedField';
    }

    public function sortable($value = true)
    {
        if (! $this->computed()) {
            $this->sortable = $value;
        }

        return $this;
    }

    public function showOnIndex($value = true)
    {
        $this->showOnIndex = $value;
        return $this;
    }

    public function showOnDetail($value = true)
    {
        $this->showOnDetail = $value;
        return $this;
    }

    public function showOnCreation($value = true)
    {
        $this->showOnCreation = $value;
        return $this;
    }

    public function showOnUpdate($value = true)
    {
        $this->showOnUpdate = $value;
        return $this;
    }

	public function saveOnUpdate()
	{
		$this->saveOnUpdate = true;
		return $this;
	}

	public function saveOnCreate()
	{
		$this->saveOnCreate = true;
		return $this;
	}

    public function textAlign($value = 'left')
    {
        $this->textAlign = $value;
        return $this;
    }

    public function help($helpText)
    {
        $this->helpText = $helpText;
        return $this;
    }

	public function stacked()
	{
		$this->stacked = true;
		return $this;
	}

	public function style($css)
	{
		$this->style = $css;
		return $this;
	}

	public function styleInline($width = '50%')
	{
		$this->style = 'display: inline-block; width: '.$width.';';
		return $this;
	}

    public function readonly($callback = true)
    {
        $this->readonlyCallback = $callback;
        return $this;
    }

    public function isReadonly(Request $request)
    {
        return evavel_with($this->readonlyCallback, function($callback) use ($request){
           if ($callback === true || (is_callable($callback) && call_user_func($callback, $request))) {
               //$this->setReadonlyAttribute();
               return true;
           }

           return false;
        });
    }

    public function required($callback = true)
    {
        $this->requiredCallback = $callback;
        return $this;
    }

    public function isRequired(Request $request)
    {
        return evavel_with($this->requiredCallback, function($callback) use ($request){
            if ($callback === true || (is_callable($callback) && call_user_func($callback, $request))) {
                return true;
            }

            if ($request->isCreate()){
                return in_array('required', $this->getCreationRules($request)[$this->attribute]);
            }

            if ($request->isUpdate()){
                return in_array('required', $this->getUpdateRules($request)[$this->attribute]);
            }

            return false;
        });
    }

    protected function setReadonlyAttribute()
    {
        $this->withMeta(['extraAttributes' => ['readonly' => true]]);

        return $this;
    }

    public function placeholder($text)
    {
        $this->placeholder = $text;
        $this->withMeta(['extraAttributes' => ['placeholder' => $text]]);

        return $this;
    }

    public function fill(Request $request, $model)
    {
	    $this->fillInto($request, $model, $this->attribute);
	    return $this;

        //$model->{$this->attribute} = $request->{$this->attribute};
        //return $this;
    }

	/**
	 * Hidrate the attribute based on the model of the request
	 *
	 * @param Request $request
	 * @param $model
	 * @param $attribute
	 * @param $requestAttribute
	 *
	 * @return mixed
	 */
	public function fillInto(Request $request, $model, $attribute, $requestAttribute = null)
	{
		return $this->fillAttribute($request, $requestAttribute != null ? $requestAttribute : $this->attribute, $model, $attribute);
	}

	protected function fillAttribute(Request $request, $requestAttribute, $model, $attribute)
	{
		if (isset($this->fillCallback)) {
			return call_user_func($this->fillCallback, $request, $model, $attribute, $requestAttribute);
		}

		return $this->fillAttributeFromRequest($request, $requestAttribute, $model, $attribute);
	}

	protected function fillAttributeFromRequest(Request $request, $requestAttribute, $model, $attribute)
	{
		//if ($request->exists($requestAttribute)) {
			//$value = $request[$requestAttribute];
			$value = $request->{$requestAttribute};
			$model->{$attribute} = $this->isNullValue($value) ? null : $value;
		//}
	}

	protected function isNullValue($value)
	{
		if (! $this->nullable) {
			return false;
		}

		if (is_callable($this->nullValues)) {
			$func = $this->nullValues;
			return $func($value);
		}

		return in_array($value, $this->nullValues);

		/*return is_callable($this->nullValues) ? ($this->nullValues)($value) : in_array(
			$value,
			(array) $this->nullValues
		);*/
	}

    public function resolveTableField()
    {
        return $this->attribute;
    }

    public function resolveValueFromModel($model)
    {
        if ($model == null) return null;

        if ($this->attribute == 'ComputedField'){
            $this->value = call_user_func($this->computedCallback, $model);
        } else {
            // WHen using hasMany or belongsToMany this causes an iteration,
            // so I have overwritten this method on those fields
            $this->value = $model->{$this->attribute};
        }

        return $this->value;
    }

    public function sortableUriKey()
    {
        // @todo: What happens with belongsTo field
        return $this->attribute;
    }

    /**
     * @param array|string $rules
     * @return $this
     */
    public function rules($rules)
    {
		$args = func_get_args();
        $this->rules = (is_string($rules)) ? $args : $rules;
		if (in_array('required', $args)){
			$this->required();
		}
        return $this;
    }

    public function getRules(Request $request)
    {
        if ($this->requiredCallback && !in_array('required', $this->rules)){
            $this->rules[] = 'required';
        }

        return [$this->attribute => $this->rules];
    }

    public function updateRules($rules)
    {
        $this->updateRules = (is_string($rules)) ? func_get_args() : $rules;
        return $this;
    }

    public function getUpdateRules(Request $request)
    {
        $rules = [$this->attribute => $this->updateRules];

        return array_merge_recursive($this->getRules($request), $rules);
    }

    public function creationRules($rules)
    {
        $this->creationRules = (is_string($rules)) ? func_get_args() : $rules;
        return $this;
    }

    public function getCreationRules(Request $request)
    {
        $rules = [$this->attribute => $this->creationRules];

        return array_merge_recursive($this->getRules($request), $rules);
    }

	public function fillForAction(Request $request, $model)
	{
		return isset($request->params[$this->attribute]) ? $request->params[$this->attribute] : '';
	}

    public function toJsonSerialize()
    {
        return evavel_with(evavel_make('request'), function($request){
            return array_merge([
                'attribute' => $this->attribute,
                'component' => $this->component,
                'name' => $this->name,
                'validationKey' => $this->attribute,
                'panel' => $this->panel,
                'helpText' => $this->helpText,
                'textAlign' => $this->textAlign,
                'stacked' => $this->stacked,
				'style' => $this->style,
                'readonly' => $this->isReadonly($request),
                'required' => $this->isRequired($request),
                'sortable' => $this->sortable,
                'sortableUriKey' => $this->sortableUriKey(),
                'value' => $this->value,
            ], $this->meta);

        });
    }
}
