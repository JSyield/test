<?php

namespace Evavel\Resources\Fields;

use Evavel\Http\Request\Request;
use Evavel\Resources\Interfaces\RelatableField;
use Evavel\Resources\Traits\ResolvesReverseRelation;
use Evavel\Resources\Traits\Searchable;

class BelongsTo extends Field implements RelatableField
{
    use Searchable;
    use ResolvesReverseRelation;

    public $component = 'belongs-to-field';
    public $belongsToId;
    public $singularLabel; // Customer
    public $resourceClass; // SRR_Customer::class
    public $resourceName; // customers
    public $belongsToRelationship; // customer
    public $viewable = true;

    public function __construct($name, $attribute, $resource)
    {
        parent::__construct($name, $attribute);

        $this->resourceClass = $resource;
        $this->resourceName = $resource::uriKey();
        $this->belongsToRelationship = $this->attribute;
        $this->singularLabel = $name;
    }

    public function sortable($value = true)
    {
        // @todo: This is not sortable for now
        return $this;
    }

    public function buildAssociatableQuery(Request $request)
    {
        //$model = forward_static_call([$resourceClass = $this->resourceClass, 'newModel']);
        // @todo: Pending

        $resourceClass = $this->resourceClass; // SRR_Resource_Customer -> SRR_Customer
        $modelClass = $resourceClass::$modelClass;

        $query = $modelClass::db();

        $this->addTenantQuery($request, $query);
        $this->addSearchQuery($request, $query);

        return $query;
    }

    // Get tenant from resourceId
    public function addTenantQuery(Request $request, $query)
    {
        $field_tenant = evavel_config('app.tenant');

	    // Skip tenant 'where' if tenant is like resourceName
	    // Because I'm trying to create from inside some tenant
	    // for example: restaurant view -> create booking
		if ($this->resourceName == $field_tenant) {
			return $this;
		}

        $field_tenant = evavel_singular($field_tenant).'_id';

        if ($request->editMode == 'update') {
            // Extract tenant from resourceId
            $modelClass = $request->modelClass();
            $model = $modelClass::withId($request->resourceId());
            $query->where($field_tenant, $model->$field_tenant);
        }

        else if ($request->editMode == 'create') {
            if ($tenant_id = $request->tenantId()){
                $query->where($field_tenant, $tenant_id);
            }
        }

        return $this;
    }

    public function addSearchQuery(Request $request, $query)
    {
        if ($search_value = $request->search()) {
            $resourceClass = $this->resourceClass;
            $where_fields = $resourceClass::$search;
            $request->querySearch($query, $where_fields, $search_value);
        }
    }

    public function fill(Request $request, $model)
    {
        $attr = $this->resolveTableField();
        $model->{$attr} = $request->{$this->attribute};
        return $this;
    }

    public function formatAssociatableResource(Request $request, $resource)
    {
        return [
            'label' =>  $resource->{$resource::$title},
            'value' => $resource->{$resource->field_id}
        ];
    }

    public function resolveTableField()
    {
        return $this->attribute.'_id';
    }

    public function resolveValueFromModel($model)
    {
        // Model is the booking
        // Belongs to Customer
        // Extract customer id
        // Extract attribute for the customer resource title
        // And assign to value

        if ($model == null) return null;

        // consumer_id
        $attr = $this->resolveTableField();
        $this->belongsToId = $model->$attr;

        //SRR_Resource_Customer -> SRR_Customer
        $rClass = $this->resourceClass;
        $title = $rClass::$title;
        $modelClass = evavel_resource_to_model_class($rClass);

		if ($this->belongsToId != null) {
			$belongsModel = $modelClass::withId($this->belongsToId);
			$this->value = $belongsModel->$title;
		} else {
			$this->value = null;
		}
    }

    public function toJsonSerialize()
    {
        return array_merge(
            [
                'belongsToId' => $this->belongsToId,
                'belongsToRelationship' => $this->belongsToRelationship,
                'singularLabel' => $this->singularLabel,
                'label' => forward_static_call([$this->resourceClass, 'label']),
                'resourceName' => $this->resourceName,
                'viewable' => $this->viewable,
                'showCreateRelationButton' => true,
                // @todo: Create using parent relation
                'reverse' => $this->isReverseRelation(evavel_make('request')),
                'searchable' => $this->searchable,
            ],
            parent::toJsonSerialize()
        );
    }

}
