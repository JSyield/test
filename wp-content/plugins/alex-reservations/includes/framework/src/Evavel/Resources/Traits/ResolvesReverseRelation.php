<?php

namespace Evavel\Resources\Traits;

use Evavel\Http\Request\Request;

trait ResolvesReverseRelation
{
    public function isReverseRelation(Request $request)
    {
        return true;
    }
}
