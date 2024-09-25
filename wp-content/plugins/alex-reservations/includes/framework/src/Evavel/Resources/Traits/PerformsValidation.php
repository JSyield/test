<?php

namespace Evavel\Resources\Traits;

use Evavel\Http\Request\CreateRequest;
use Evavel\Http\Request\UpdateRequest;
use Evavel\Http\Validation\Validator;

trait PerformsValidation
{
    public static function validateForCreation(CreateRequest $request)
    {
        $validator = Validator::make($request->getAttributes(), $request->creationRules());

        if ($validator->fails()){

            $response = [
                'message' => __eva('The given data was invalid.'),
                'errors' => $validator->errors()
            ];

            evavel_send_json($response, 422);
        }
    }

    public static function validateForUpdate(UpdateRequest $request)
    {
        $validator = Validator::make($request->body_params, $request->updateRules());

        if ($validator->fails()){
            $response = [
                'message' => __eva('The given data was invalid.'),
                'errors' => $validator->errors()
            ];

            evavel_send_json($response, 422);
        }
    }
}
