<?php

namespace Evavel\Http\Request;

use Evavel\Container\EvaContainer;
use Evavel\Eva;
use Evavel\Http\Middleware\SanitizeMiddleware;
use Evavel\Http\Request\Interfaces\AuthorizeWhenResolved;
use Evavel\Http\Request\Traits\ManageSettings;
use Evavel\Pipeline\Pipeline;
use Evavel\Query\Query;
use Evavel\Enums\Context;

class Request implements AuthorizeWhenResolved
{
	use ManageSettings;

    protected $wp_request;
    public $params;
    public $body_params = [];

	// @todo
	public function authorize() {
		return true;
	}

	public function __construct($wp_or_eva_request)
    {
		// Received Request object already sanitized
		if ( get_class($wp_or_eva_request) == Request::class) {
			$this->wp_request = $wp_or_eva_request->wp_request;
			$this->params = $wp_or_eva_request->params;
			$this->body_params = $wp_or_eva_request->body_params;

			// Clean params
			if (isset($this->params['params'])){
				foreach($this->params['params'] as $key => $value){
					$this->params[$key] = $value;
				}
				unset($this->params['params']);
			}
		}

		// Received WP_request object that needs
		// to be sanitized
		else {
			$this->wp_request = $wp_or_eva_request;
			$this->params = $wp_or_eva_request->get_params();
			$this->body_params = $wp_or_eva_request->get_body_params();

			// Clean params
			if (isset($this->params['params'])){
				foreach($this->params['params'] as $key => $value){
					$this->params[$key] = $value;
				}
				unset($this->params['params']);
			}

			$this->applyMiddleware();
		}
    }

	protected function applyMiddleware()
	{
		$middlewares = [
			new SanitizeMiddleware()
		];

		(new Pipeline())
			->send($this)
			->through($middlewares)
			->via('handle')
			->then(function($request){});
	}

    public function resolveTable()
    {
        $modelClass = $this->modelClass();
        return $modelClass::$table_name;
    }

    protected function extract($keys = [], $default = 0, $parse = null)
    {
        $value = '';
        foreach($keys as $key){
            if (isset($this->params[$key])){
                $value = is_callable($parse) ? $parse($this->params[$key]) : $value = $this->params[$key];
            }
        }
        return empty($value) ? $default : $value;
    }

	public function query($param)
	{
		return isset($this->params[$param]) ? $this->params[$param] : null;
	}

    public function tenantId()
    {
		static $resultId = null;

		if ($resultId !== null) {
			return $resultId;
		}

        $identifier = $this->extract(['tender','tenant', 'tenantId', 'tenant_id'], false);
		if ($identifier == null) return null;

		$resourceTenant = evavel_tenant_resource();

		// Find based on the id or the uuid
		$tenantModel = Query::table($resourceTenant)
            ->where('id', $identifier)
			->orWhere('uuid', $identifier)
			->select('id')
			->first();

		if (!$tenantModel) {
			return null;
		}

		$resultId = $tenantModel->id;

	    return $resultId;
    }

    public function __call($method, $params)
    {
        if (method_exists($this, $method)){
            return $this->$method($params);
        }
        return true;
    }

    public function __get($property)
    {
        if (isset($this->params[$property])){
            return $this->params[$property];
        }

        //if (method_exists($this, $property)){
        //    return $this->$property();
        //}

        if (is_array($this->body_params) && array_key_exists($property, $this->body_params)){
            return $this->body_params[$property];
        }

        return null;
    }

    public function resourceName()
    {
        return $this->extract(['resourceName'], null);
    }

    public function relationshipType()
    {
        return $this->extract(['relationshipType'], null);
    }

    // ex: restaurants -> restaurant
    /*public function resourceModel()
    {
        $resourceName = $this->resourceName();

        ray(evavel_singular($resourceName));
        return evavel_singular($resourceName);
    }*/

    public function resources()
    {
        return $this->extract(['resources'], []);
    }

    // ex: restaurant -> SRR_Restaurant
    public function modelClass()
    {
        $class_resource = $this->resourceClass();
        return $class_resource::$modelClass;
    }

    public function fetchModel()
    {
        $modelClass = $this->modelClass();
        return $modelClass::withId($this->resourceId());
    }

    // bookings -> SRR_Resource_Booking
    public function resourceClass()
    {
        return evavel_resource_prefix().ucfirst(evavel_singular($this->resourceName()));
    }

    public function currentPage()
    {
        $resourceName_page = $this->resourceName().'_page';
        return $this->extract([$resourceName_page, 'page', 'currentPage'], 1, 'intval');
    }

    public function perPage()
    {
        $resourceName_per_page = $this->resourceName().'_per_page';
        $default = $this->viaResource() ? 5 : 25;
        return $this->extract([$resourceName_per_page, 'per_page', 'perPage'], $default, 'intval');
    }

    public function orderBy()
    {
        $resourceName_orderBy = $this->resourceName().'_orderBy';
        return $this->extract([$resourceName_orderBy, 'orderBy'], 'id');
    }

    public function orderByDirection()
    {
        $resourceName_orderByDirection = $this->resourceName().'_orderByDirection';
        return $this->extract([$resourceName_orderByDirection, 'orderByDirection'], 'desc');
    }

    public function search()
    {
        $resourceName_search = $this->resourceName().'_search';
        return $this->extract([$resourceName_search, 'search'], false);
    }

    public function resourceId()
    {
        return $this->extract(['model_id', 'resource_id', 'resourceId'], false, 'intval');
    }

    public function field()
    {
        return $this->extract(['field'], '');
    }

	public function lens()
	{
		return $this->extract(['lens'], '');
	}

    public function context()
    {
        return isset($this->params['context']) ? $this->params['context'] : Context::INDEX;
    }

    // /bookings?tenant=1&per_page=5&page=2&viaResource=customers&viaResourceId=811&viaRelationship=bookings
    public function viaResource()
    {
        return $this->extract(['viaResource'], false);
        //return isset($this->params['viaResource']) ? $this->params['viaResource'] : false;
    }

    public function viaResourceId()
    {
        return $this->extract(['viaResourceId'], false, 'intval');
        //return isset($this->params['viaResourceId']) ? $this->params['viaResourceId'] : false;
    }

    public function viaRelationship()
    {
        return $this->extract(['viaRelationship'], false);
        //return isset($this->params['viaRelationship']) ? $this->params['viaRelationship'] : false;
    }


    public function isIndex()
    {
        return isset($this->params['context']) && $this->params['context'] == Context::INDEX;
    }

    public function isDetail()
    {
        return isset($this->params['context']) && $this->params['context'] == Context::DETAIL;
    }

    public function isUpdate()
    {
        return isset($this->params['context']) && $this->params['context'] == Context::UPDATE;
    }

    public function isCreate()
    {
        return isset($this->params['context']) && $this->params['context'] == Context::CREATE;
    }

    public function queryResource()
    {
        $modelClass = $this->modelClass();
        $resourceClass = $this->resourceClass();

        $query = Query::table($this->resolveTable())
            ->where('id', $this->resourceId())
            ->toArray();

        $query = $resourceClass::detailQuery($this, $query);

        $row = $query->first();

        if (!$row){
            return null;
        }

        //return new $resourceClass($this, new $modelClass($row));
        return new $resourceClass(new $modelClass($row));
    }

    public function newResource()
    {
        $resourceClass = $this->resourceClass();
        //return new $resourceClass($this, null);
        return new $resourceClass();
    }

    public function querySearch($query, $where_fields, $search_value)
    {
        $closure = function($query) use($where_fields, $search_value) {
            for ($i = 0; $i < count($where_fields); $i++)
            {
                $w_field = $where_fields[$i];
                if ($i == 0){
                    $query->where($w_field, 'like', $search_value);
                } else {
                    $query->orWhere($w_field, 'like', $search_value);
                }
            }
            return $query;
        };

        $query->where($closure);
    }

}
