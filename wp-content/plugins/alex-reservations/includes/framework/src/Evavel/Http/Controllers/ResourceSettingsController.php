<?php

namespace Evavel\Http\Controllers;

use Evavel\Http\Controllers\Traits\ManageSettings;
use Evavel\Http\Request\SettingsRequest;

class ResourceSettingsController extends Controller
{
	use ManageSettings;

    public function handle(SettingsRequest $request, $resourceName, $tenantId)
    {
        if (!$this->validate(['resourceName' => $resourceName])) {
	        return $this->response(['response_code' => '404', 'error' => 'Invalid resource']);
        }

		// Only the tenant can have settings
	    // ex: /restaurants/settings -> is valid because restaurant is the tenant
	    // ex: /bookings/settings -> not valid because bookings is just a resource
		if ($resourceName != evavel_tenant_resource()) {
			return $this->response(['response_code' => '404', 'error' => 'Invalid resource tenant for settings']);
		}

		$data = $request->getSettings($tenantId);

	    return $this->response([
			'tenantId' => $tenantId,
			'label' => __eva('Settings'),
			'panels' => $data['panels'],
		    'settings' => $data['settings']
	    ]);

    }

	public function store(SettingsRequest $request, $resourceName, $tenantId)
	{
		$this->storeParamsSettings($request->params, $tenantId);

		return $this->response([
			'tenantId' => $tenantId,
			'resource' => $resourceName,
			'params' => $request->params
		]);

	}

}
