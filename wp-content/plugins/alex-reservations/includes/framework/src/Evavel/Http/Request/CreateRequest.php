<?php

namespace Evavel\Http\Request;

use Evavel\Facades\Gate;
use Evavel\Models\Model;
use Evavel\Query\Query;
use Evavel\Resources\Fields\BelongsTo;
use mysql_xdevapi\Exception;

class CreateRequest extends Request
{
	public function authorize() {
		if (Gate::denies('create', [$this->modelClass()])) {
			evavel_403();
		}
	}

	/**
     * Get the creation rules from the resource.
     *
     * @return array
     */
    public function creationRules()
    {
        return ($this->newResource())->creationRules($this);
    }

    public function getAttributes()
    {
        return $this->body_params;
    }

}
