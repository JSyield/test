<?php

namespace Evavel\Resources\Traits;

use Evavel\Http\Request\Request;
use Evavel\Resources\Fields\FieldCollection;
use Evavel\Resources\Fields\HasMany;
use Evavel\Resources\Fields\Panel;

trait ResolvesFields
{
    public function indexFields(Request $request)
    {
        $fields = $this->resolveFields($request, function($fields) use ($request) {
            return $this->removeNonIndexFields($request, $fields);
        });

        return $fields;

        /*return $this->assignToPanels(
            Panel::defaultNameForIndex($request, $this->model),
            $fields
        );*/
    }

    public function detailFields(Request $request)
    {
        return $this->resolveFields($request, function($fields) use ($request) {
            return $this->removeNonDetailFields($request, $fields);
        });
    }

    public function updateFields(Request $request)
    {
        return $this->resolveFields($request, function($fields) use ($request) {
            return $this->removeNonUpdateFields($request, $fields);
        });

        //return $fields;

        /*return $this->assignToPanels(
            Panel::defaultNameForUpdate($request, $this->model),
            $fields
        );*/
    }

    public function creationFieldsWithoutReadonly(Request $request)
    {
        $fields = new FieldCollection($this->creationFields($request));

        return $fields->withoutReadonly($request);
    }

    public function updateFieldsWithoutReadonly(Request $request)
    {
        $fields = new FieldCollection($this->updateFields($request));

        return $fields->withoutReadonly($request);
    }


    public function creationFields(Request $request)
    {
        return $this->resolveFields($request, function($fields) use ($request) {
            return $this->removeNonCreationFields($request, $fields);
        });
    }

    public function idField(Request $request)
    {
        $fields = $this->resolveValues($this->availableFields($request));

        foreach($fields as $field) {
            if ($field->attribute == 'id'){

                return $field;

                /*return $this->assignToPanels(
                    Panel::defaultNameForIndex($request, $this->model),
                    $field
                );*/

            }
        }
        return null;
    }

    public function availablePanels(Request $request)
    {
        $panels = [];

        $panels['default'] = Panel::makeDefault($this);

        foreach ($this->groupedFields($request) as $group) {
            if ($group instanceof Panel){
                $panels[$group->name] = $group;
            }
        }

        return array_values($panels);
    }

    /*protected function assignToPanels($label, $fields)
    {
        if (is_array($fields)){
            foreach ($fields as &$field) {
                if (!$field->panel){
                    $field->panel = $label;
                }
            }
        } else {
            if (!$fields->panel){
                $fields->panel = $label;
            }
        }

        return $fields;
    }*/

    public function removeNonIndexFields($request, $fields)
    {
        $filter_fields = [];

        foreach ($fields as $field) {
            if ($field->showOnIndex) {
                $filter_fields[] = $field;
            }
        }

        return $filter_fields;
    }

    public function removeNonDetailFields($request, $fields)
    {
        $filter_fields = [];

        foreach ($fields as $field) {
            if ($field->showOnDetail) {
                $filter_fields[] = $field;
            }
        }

        return $filter_fields;
    }

    public function removeNonUpdateFields($request, $fields)
    {
		$is_request_to_save = count($request->body_params) > 0;

        $filter_fields = [];

        foreach ($fields as $field) {
            if ( ($field->showOnUpdate || ($field->saveOnUpdate && $is_request_to_save) ) &&
                $field->attribute !== 'id' &&
                $field->attribute !== 'ComputedField' &&
                !$field instanceof HasMany)
            {
                $filter_fields[] = $field;
            }
        }

        return $filter_fields;
    }

    public function removeNonCreationFields($request, $fields)
    {
	    $is_request_to_save = count($request->body_params) > 0;

        $filter_fields = [];

        foreach ($fields as $field) {
            if ( ($field->showOnCreation || ($field->saveOnCreate && $is_request_to_save) ) &&
                $field->attribute !== 'id' &&
                $field->attribute !== 'ComputedField' &&
                !$field instanceof HasMany)
            {
                $filter_fields[] = $field;
            }
        }

        return $filter_fields;
    }

    /**
     * Return fields with values
     *
     * @param Request $request
     * @param Closure|null $filter
     * @return mixed
     */
    protected function resolveFields(Request $request, \Closure $filter = null)
    {
        $fields = $this->resolveValues($this->availableFields($request));

        if (! is_null($filter)){
            $fields = $filter($fields);
        }

        return $fields;
    }

    /**
     * Only need to resolve values for updating, not when creating
     *
     * @param $fields
     * @return mixed
     */
    public function resolveValues($fields)
    {
        if ($this->model !== null){
            foreach ($fields as $field){
                $field->resolveValueFromModel($this->model);
            }
        }

        return $fields;
    }

    public function availableFields(Request $request)
    {
        $method = $this->fieldsMethod($request);

        $fields = $this->{$method}($request);
        return array_values($this->unGroupPanels($fields));
    }

    public function groupedFields(Request $request)
    {
        $method = $this->fieldsMethod($request);
        return $this->{$method}($request);
    }

    public function unGroupPanels($fields)
    {
        $list_fields = [];
        foreach($fields as $field){
            if ($field instanceof Panel){
                foreach ($field->items as $panel_field) {
                    $list_fields[] = $panel_field;
                }
            } else {
                $list_fields[] = $field;
            }
        }
        return $list_fields;
    }

    protected function fieldsMethod(Request $request)
    {
        if ($request->isIndex() && method_exists($this, 'fieldsForIndex')) {
            return 'fieldsForIndex';
        }

        if ($request->isDetail() && method_exists($this, 'fieldsForDetail')) {
            return 'fieldsForDetail';
        }

        if ($request->isCreate() && method_exists($this, 'fieldsForCreate')) {
            return 'fieldsForCreate';
        }

        if ($request->isUpdate() && method_exists($this, 'fieldsForUpdate')) {
            return 'fieldsForUpdate';
        }

        return 'fields';
    }

    public function updateRules($request)
    {
        $fields = $this->updateFields($request);
        $rules = [];
        foreach($fields as $field){
            $rules = array_merge($field->getUpdateRules($request), $rules);
        }
        return $rules;
    }

    public function creationRules($request)
    {
        $fields = $this->creationFields($request);
        $rules = [];
        foreach($fields as $field){
            $rules = array_merge($field->getCreationRules($request), $rules);
        }
        return $rules;
    }
}
