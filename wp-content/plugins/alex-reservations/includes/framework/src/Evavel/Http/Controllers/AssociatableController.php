<?php

namespace Evavel\Http\Controllers;

use Evavel\Http\Request\Request;
use Evavel\Models\Collections\Collection;
use Evavel\Resources\Fields\FieldCollection;
use Evavel\Resources\Interfaces\RelatableField;

class AssociatableController extends Controller
{
    public function index(Request $request)
    {
        // @todo: Should return directly the FieldCollection from availableFields
        $collection = new FieldCollection($request->newResource()
            ->availableFields($request));

        $field = $collection->whereInstanceOf(RelatableField::class)
            ->findFieldByAttribute($request->field(), function(){
                evavel_send_json('error', 404);
            });

        $query = $field->buildAssociatableQuery($request);

	    $resources = $query->get();
		if (is_array($resources)) {
			$resources = new Collection($resources);
		}

        $resources = $resources
            ->mapInto($field->resourceClass)
            ->map(function($resource) use($request, $field){
                return $field->formatAssociatableResource($request, $resource);
            })
            //->sortBy(function($item){return $item['display'];})
            ->sortBy('label')
            ->values()
            ->toArray();

        return $this->response(['resources' => $resources]);
    }

}
