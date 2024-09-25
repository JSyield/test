<?php

namespace Alexr\Http\Controllers;

use Alexr\Models\BTag;
use Alexr\Models\BTagGroup;
use Alexr\Models\Table;
use Evavel\Http\Controllers\Controller;
use Evavel\Http\Request\Request;

class BTagsController extends Controller
{
	public function index(Request $request) {

		$tenant = $request->tenantId();

		/*$tags = BTag::whereIsNull('restaurant_id')
		            ->orWhere('restaurant_id', $tenant)
		            ->orderBy('name', 'ASC')
		            ->get()->toArray();

		$groups = BTagGroup::whereIsNull('restaurant_id')
		                    ->orWhere('restaurant_id', $tenant)
							->orderBy('name', 'ASC')
		                    ->get()->toArray();*/

		$tags = BTag::where('restaurant_id', $tenant)
		            ->orderBy('ordering', 'ASC')
		            ->get()->toArray();

		$groups = BTagGroup::where('restaurant_id', $tenant)
		                   ->orderBy('ordering', 'ASC')
		                   ->get()->toArray();

		return $this->response([
			'tags' => $tags,
			'groups' => $groups
		]);
	}
}
