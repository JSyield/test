<?php

namespace Evavel\Http\Controllers;

use Evavel\Http\Controllers\Traits\ManageSettings;
use Evavel\Http\Request\SettingsRequest;

// To manage settings in general that are stored in the meta_table
class SettingsController extends Controller
{
	use ManageSettings;

	public function handle(SettingsRequest $request)
	{
		$data = $request->getSettings();

		return $this->response([
			'label' => __eva('Settings'),
			'panels' => $data['panels'],
			'settings' => $data['settings']
		]);
	}

	public function store(SettingsRequest $request)
	{
		$this->storeParamsSettings($request->params);

		return $this->response([
			'params' => $request->params
		]);
	}

}
