<?php

namespace Evavel\Http\Controllers;

use Evavel\Http\Request\Request;

class DevToolsController extends Controller
{

    // Do not forget to allow CORS headers when using Vite with devtools
    public function config(Request $request) {

        $dashboard = new \SRR_Dashboard();
        $dashboard->build(false);

        return $this->response([
            'config' => json_decode( EVAVEL()->configJson() )
        ], 200);
    }

}
