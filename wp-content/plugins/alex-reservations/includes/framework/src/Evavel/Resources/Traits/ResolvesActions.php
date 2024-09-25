<?php

namespace Evavel\Resources\Traits;

use Evavel\Http\Request\Request;

trait ResolvesActions
{
    public function availableActions(Request $request)
    {
        return $this->resolveActions($request);
    }

    public function availableActionsOnIndex(Request $request)
    {
        return $this->resolveActions($request);
    }

    public function availableActionsOnDetail(Request $request)
    {
        return $this->resolveActions($request);
    }

    public function availableActionsOnTableRow(Request $request)
    {
        return $this->resolveActions($request);
    }

    public function resolveActions(Request $request)
    {
        return $this->actions($request);
    }
}
