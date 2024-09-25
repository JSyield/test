<?php

namespace Evavel\Resources\Fields;

class HasMany extends Field
{
    public $component = 'has-many-field';

    public $singularLabel; // Booking
    public $resourceClass; // SRR_Booking::class
    public $resourceName; // bookings
    public $hasManyRelationship; // bookings

    public function __construct($name, $attribute, $resource)
    {
        parent::__construct($name, $attribute);

        $this->resourceClass = $resource;
        $this->resourceName = $resource::uriKey();
        $this->hasManyRelationship = $this->attribute;
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

    public function resolveValueFromModel($model)
    {
        return null;
    }

    public function toJsonSerialize()
    {
        return array_merge(
            [
                'hasManyRelationship' => $this->hasManyRelationship,
                'listable' => true,
                'perPage' => $this->perPage(),
                'resourceName' => $this->resourceName,
                'singularLabel' => $this->singularLabel
            ],
            parent::toJsonSerialize()
        );
    }

}
