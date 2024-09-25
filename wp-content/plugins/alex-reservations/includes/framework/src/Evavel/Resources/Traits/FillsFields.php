<?php

namespace Evavel\Resources\Traits;

use Evavel\Http\Request\Request;

trait FillsFields
{
    public static function fill(Request $request, $model)
    {
        return static::fillFields($request, $model,
            (new static($model))->creationFieldsWithoutReadonly($request)->all()
        );
    }

    public static function fillForUpdate(Request $request, $model)
    {
        return static::fillFields($request, $model,
            // Creates a new resource with the original model
            (new static($model))->updateFieldsWithoutReadonly($request)->all()
        );
    }

    // And fill the new values inside the model for the resource fields passed
    protected static function fillFields(Request $request, $model, $fields)
    {
        foreach($fields as $field){
            $field->fill($request, $model);
        }
        return $model;
    }
}
