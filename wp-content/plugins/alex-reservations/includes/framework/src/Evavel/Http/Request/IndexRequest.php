<?php

namespace Evavel\Http\Request;

use Evavel\Facades\Gate;
use Evavel\Http\Request\Traits\CountsResources;
use Evavel\Http\Request\Traits\QueriesResources;
use Evavel\Query\Query;

class IndexRequest extends Request
{
    use CountsResources, QueriesResources;

	public function authorize()
	{
		if (Gate::denies('viewAny', [$this->modelClass()])) {
			evavel_403();
		}
	}

    public function searchIndex()
    {
        $perPage = $this->perPage();
        $currentPage = $this->currentPage();

        // Request count with tenant
        $query_count = Query::table($this->resolveTable());

        // Request resources with tenant
        $table = $this->resolveTable();
        $query = Query::table($table)
            ->orderBy("{$table}.{$this->orderBy()}", $this->orderByDirection())
            ->page($currentPage, $perPage)
            ->toArray();

        // Add restriction via resource id
        $this->applyViaResource($query_count);
        $this->applyViaResource($query);

        // Scope user role
        $this->applyModelScope($query_count);
        $this->applyModelScope($query);

        // Filter using resource indexQuery
        $this->applyIndexQuery($query_count);
        $this->applyIndexQuery($query);

		// Used only for lenses
		if (method_exists($this, 'applyLensQuery')){
			$this->applyLensQuery($query_count);
			$this->applyLensQuery($query);
		}

        $this->applySearch($query_count);
        $this->applySearch($query);

        $this->applyFilters($query_count);
        $this->applyFilters($query);


        $total_arr = $query_count->count($perPage);
        $rows = $query->get();

        return [
          'total' => $total_arr['count'],
          'perPage' => $perPage,
          'currentPage' => $currentPage,
          'total_pages' => $total_arr['pages'],
          'resources' => $this->toResources( $rows ),
        ];
    }

    public function toResources($rows)
    {
        $modelClass = $this->modelClass();
        $resourceClass = $this->resourceClass();

        $resources = [];

        foreach ($rows as $row) {
            $resource = new $resourceClass(new $modelClass($row));
	        $resources[] = $resource->serializeForIndex($this);
        }

        return $resources;
    }

    public function applySearch(Query $query)
    {
        if ($search_value = $this->search())
        {
            $resourceClass = $this->resourceClass();
            $where_fields = $resourceClass::$search;
            $this->querySearch($query, $where_fields, $search_value);
        }

        return $this;
    }

    public function applyFilters(Query $query)
    {
        if (!empty($filters = $this->filters())) {
            $filters->map(function($filter) use($query) {
                $filter->__invoke($this, $query);
            });
        }

        return $this;
    }

    public function resolveModel()
    {
        static $models = [];

        $resourceId = $this->viaResourceId();

        if (!isset($models[$resourceId])){
            $parentModelClass = evavel_model_prefix().ucfirst(evavel_singular($this->viaResource()));
            $models[$resourceId] = $parentModelClass::make($resourceId);
        }

        return $models[$resourceId];
    }

    public function applyViaResource($query)
    {
        // Use the relation from the model relation method
        if ($field = $this->viaResource) {

            if (in_array($this->relationshipType(), ['hasMany', 'belongsToMany'] )) {

                $method = $this->resourceName();
                $this->resolveModel()->$method()->buildQuery($query);
            }

        }

        return $this;
    }

    public function applyModelScope($query)
    {
        $modelClass = $this->modelClass();
        $modelClass::scopedGlobal($query);
    }

    public function applyIndexQuery($query)
    {
        // When hasMany via restaurant should not apply tenat filter
        // I want all bookings, customers, ... listed for the restaurant
        if ($this->viaResource() !== evavel_config('app.tenant'))
        {
            $resourceClass = $this->resourceClass();
            $resourceClass::indexQuery($this, $query);
        }

        return $this;
    }

}

