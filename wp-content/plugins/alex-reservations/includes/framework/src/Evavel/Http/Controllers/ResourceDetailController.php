<?php

namespace Evavel\Http\Controllers;

use Evavel\Http\Request\DetailRequest;

class ResourceDetailController extends Controller
{
    public function handle(DetailRequest $request, $resourceName, $resourceId)
    {
        if (!$this->validate(['resourceName' => $resourceName]) || !$resourceId) {
            return $this->response(['response_code' => '404', 'error' => 'Invalid resource']);
        }

        $resource = $request->queryResource();

        if ($resource == null){
            return $this->response(['response_code' => '404', 'error' => 'Not found']);
        }

        $resourceClass = $request->resourceClass();

        $response = array(
            'panels' => $resource->availablePanels($request),
            'title' => $resourceId,
            'resource' => array_merge(
                [
                    'label' => $resourceClass::label(),
                    'labelSingular' => $resourceClass::labelSingular(),
                    'actions' => $resource->actions($request)
                ],
                $resource->serializeForDetail($request)
            )
        );

        return $this->response($response);
    }

    protected function extractPanelNames($fields)
    {
        $panels = [];
        foreach ($fields as $field){
            if (!isset($panels[$field->panel])){
                $panels[$field->panel] = [
                    'name' => $field->panel,
                    'component' => 'panel',
                    'showToolbar' => true,
                    'helpText' => 'SOME HELP HERE'
                ];
            }
        }

        return array_values($panels);
    }

}
