<?php

namespace Alexr\Http\Controllers;

use Alexr\Models\CTag;
use Alexr\Models\CTagGroup;
use Evavel\Http\Controllers\Controller;
use Evavel\Http\Request\Request;

class CTagsController extends Controller
{
	public function index(Request $request) {
		$tenant = $request->tenantId();

		/*$tags = CTag::whereIsNull('restaurant_id')
		            ->orWhere('restaurant_id', $tenant)
		            ->orderBy('name', 'ASC')
		            ->get()->toArray();

		$groups = CTagGroup::whereIsNull('restaurant_id')
		                   ->orWhere('restaurant_id', $tenant)
		                   ->orderBy('name', 'ASC')
		                   ->get()->toArray();*/

		$tags = CTag::where('restaurant_id', $tenant)
		            ->orderBy('ordering', 'ASC')
		            ->get()->toArray();

		$groups = CTagGroup::where('restaurant_id', $tenant)
		                   ->orderBy('ordering', 'ASC')
		                   ->get()->toArray();

		return $this->response([
			'tags' => $tags,
			'groups' => $groups
		]);
	}
}
