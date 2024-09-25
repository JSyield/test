<?php

namespace Alexr\Http\Controllers;

use Alexr\Models\Token;
use Evavel\Http\Controllers\Controller;
use Evavel\Http\Request\Request;

class LogoutController extends Controller
{
	public function logout(Request $request)
	{
		evavel_logout();

		if (isset($_GET['token'])) {
			Token::delete(sanitize_text_field($_GET['token']));
		}

		return $this->response(['success' => true, 'redirect' => evavel_site_url().ALEXR_DASHBOARD]);
	}
}
