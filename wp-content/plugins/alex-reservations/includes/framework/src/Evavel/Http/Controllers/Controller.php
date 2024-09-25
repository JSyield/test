<?php

namespace Evavel\Http\Controllers;

class Controller
{
    public function validate($params)
    {
        if (isset($params['resourceName'])){
            if (!in_array($params['resourceName'],
                $this->validResources()))
            {
                //return evavel_response(['response_code' => '404', 'error' => 'Invalid resource'], 200 );
                //echo 'Invalid resource';
                //exit();
                return false;
            }
        }

        return true;
    }

    public function validResources()
    {
        return evavel_config('app.resources');
    }

    public function response($response, $code = 200)
    {
        return evavel_response(evavel_json($response),  $code);
    }

    public static function toResponse($response, $code = 200)
    {
        return evavel_response(evavel_json($response),  $code);
    }

    public function response403($response = [])
    {
        return evavel_response($response, 403);
    }

    public function response404($response = [])
    {
        return evavel_response($response, 404);
    }

}
