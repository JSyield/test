<?php

namespace Alexr\Http\Controllers;

use Evavel\Eva;
use Evavel\Http\Controllers\Controller;
use Evavel\Http\Request\Request;

class ProfileController extends Controller
{
	public function index(Request $request)
	{
		$user = Eva::make('user');

		return $this->response(['success' => true, 'user' => $user]);
	}

	public function update(Request $request)
	{
		$user = Eva::make('user');
		$params = $request->params;

		$user->first_name = $params['first_name'];
		$user->last_name = $params['last_name'];
		$user->name = $user->first_name . ' ' . $user->last_name;
		$user->dial_code_country = $params['dial_code_country'];
		$user->dial_code = $params['dial_code'];
		$user->phone = $params['phone'];

		$user->save();

		return $this->response(['success' => true, 'message' => __eva('Done!')]);
	}
}
