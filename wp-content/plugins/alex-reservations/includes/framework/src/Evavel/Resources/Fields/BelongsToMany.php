<?php

namespace Evavel\Resources\Fields;

class BelongsToMany extends Field
{
    public $component = 'belongs-to-many-field';

    public $singularLabel; // Customer
    public $resourceClass; // SRR_Customer::class
    public $resourceName; // customers
    public $manyToManyRelationship; // customer

    public function __construct($name, $attribute, $resource)
    {
        parent::__construct($name, $attribute);

        $this->resourceClass = $resource;
        $this->resourceName = $resource::uriKey();
        $this->manyToManyRelationship = $this->attribute;
        $this->singularLabel = $name;

        $this->showOnIndex = false;
        $this->showOnCreation = false;
        $this->showOnUpdate = false;
    }

    public function perPage()
    {
        $resourceClass = $this->resourceClass;
        return $resourceClass::$perPageViaRelationship;
    }

    public function sortable($value = true)
    {
        // @todo: This is not sortable for now
        return $this;
    }

    public function resolveValueFromModel($model)
    {
        return null;
    }

    public function toJsonSerialize()
    {
        return array_merge(
            [
                'belongsToManyRelationship' => $this->manyToManyRelationship,
                'listable' => true,
                'perPage' => $this->perPage(),
                'label' => forward_static_call([$this->resourceClass, 'label']),
                'resourceName' => $this->resourceName,
                'singularLabel' => $this->singularLabel,
            ],
            parent::toJsonSerialize()
        );
    }
}
