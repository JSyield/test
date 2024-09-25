<?php

namespace Evavel\Http\Controllers;

use Evavel\Http\Request\IndexRequest;
use Evavel\Http\Request\LensIndexRequest;

class LensIndexController  extends Controller
{
	// List lenses for the resource
	public function index(LensIndexRequest $request)
	{
		return $this->response($request->availableLenses());
	}

	// Handle index lens page
	public function handle(LensIndexRequest $request, $resourceName, $lens)
	{
		if (!$this->validate(['resourceName' => $resourceName])) {
			return $this->response(['response_code' => '404', 'error' => 'Invalid resource']);
		}

		// Get the lens object, prevent conflict with ->lens() (request parameter)
		$lensObject = $request->lensObject();
		if ($lensObject == null) return evavel_response(__eva('Does not exist.'), 404);

		// Query
		$data = $request->searchIndex();

		// Response
		$response = [
			'label' => $lensObject->name(),
			'resources' => $data['resources'],
			'total' => $data['total'],
			'per_page' => $data['perPage'],
			'current_page' => $data['currentPage'],
			'total_pages' => $data['total_pages']
		];

		return $this->response($response);
	}
}
