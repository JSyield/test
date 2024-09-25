<?php

namespace Evavel\Resources\Fields;

use Evavel\Http\Request\Request;
use Evavel\Models\Collections\Collection;

class FieldCollection extends Collection
{
    public function whereInstanceOf($type)
    {
        return $this->filter(function($value) use($type){
            if (is_array($type)) {
                foreach ($type as $classType){
                    if ($value instanceof $classType){
                        return true;
                    }
                }
                return false;
            }

            return $value instanceof $type;
        });
    }

    public function findFieldByAttribute($attribute, $default = null)
    {
        return $this->first(function($field) use($attribute){
            return isset($field->attribute) && $field->attribute == $attribute;
        }, $default);
    }

    public function withoutReadonly(Request $request)
    {
        return $this->reject(function ($field) use ($request) {
            return $field->isReadonly($request);
        });
    }

	public function resolve($resource)
	{

	}
}
