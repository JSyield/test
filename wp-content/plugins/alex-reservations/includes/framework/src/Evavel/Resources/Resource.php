<?php

namespace Evavel\Resources;

use Evavel\Models\Model;
use Evavel\Resources\Traits\Authorizable;
use Evavel\Resources\Traits\FillsFields;
use Evavel\Resources\Traits\PerformsValidation;
use Evavel\Resources\Traits\ResolvesFields;
use Evavel\Http\Request\Request;
use Evavel\Policies\Policy;
use Evavel\Resources\Traits\ResolvesActions;
use Evavel\Resources\Traits\ResolvesFilters;
use Evavel\Resources\Traits\ResolvesLenses;
use Evavel\Support\Str;

abstract class Resource
{
    use ResolvesFields;
    use PerformsValidation;
    use FillsFields;
    use ResolvesFilters;
    use ResolvesActions;
	use ResolvesLenses;
	use Authorizable;

    public static $modelClass = Model::class;

    public $model = null;
    public $id = 0;
    protected $field_id = 'id';
    //protected $request = null;

    public static $title = 'id';

    public static $perPageViaRelationship = 5;

    public static $search = ['id'];

    /*public function __construct(Request $request, \Evavel\Models\Model $model = null) {
        $this->request = $request;
        if ($model != null) {
            $this->model = $model;
            $this->id = $model->id;
        }
    }*/

    public function __construct( \Evavel\Models\Model $model = null) {
        if ($model != null) {
            $this->model = $model;
            $this->id = $model->id;
        }
    }

    // Ask the model for the attribute
    public function __get($attr)
    {
        if (isset($this->$attr)){
            return $this->$attr;
        }

        if ($this->model) {
            return ($this->model)->$attr;
        }

        return null;
    }

    public function title()
    {
        return $this->{static::$title};
    }

    public static function indexQuery(Request $request, $query)
    {
        return $query;
    }

    public static function detailQuery(Request $request, $query)
    {
        return $query;
    }

    public static function uriKey()
    {
        $class_name = evavel_class_basename(get_called_class());
        $class_name = str_replace(evavel_resource_prefix(), '', $class_name);
        return Str::plural(Str::kebab($class_name));
    }



    public function fields(Request $request) {
        return [];
    }

    public function settings() {
        return [];
    }

    public function actions(Request $request) {
        return [];
    }

    public function filters(Request $request) {
        return [];
    }

	public function lenses(Request $request) {
		return [];
	}

	public static function config()
	{
		return [
			'uriKey' => static::uriKey(),
			'debounce' => 500,
			'authorizedToCreate' => true,
			'createButtonLabel' => __eva('Create')
		];
	}

	public static function newModel()
	{
		$model = static::$modelClass;
		return new $model;
	}

	public function serializeForIndex(Request $request)
	{
		return [
			'fields' => evavel_json($this->indexFields($request)),
			'id' => $this->idField($request),
			'authorizedToCreate' => $this->authorizedToCreate($request),
			'authorizedToDelete' => $this->authorizedToDelete($request),
			'authorizedToUpdate' => $this->authorizedToUpdate($request),
			'authorizedToView' => $this->authorizedToView($request),
		];
	}

	public function serializeForDetail(Request $request)
	{

		return [
			'fields' => $this->detailFields($request),
			'id' => $this->idField($request),
			'authorizedToCreate' => $this->authorizedToCreate($request),
			'authorizedToDelete' => $this->authorizedToDelete($request),
			'authorizedToUpdate' => $this->authorizedToUpdate($request),
			'authorizedToView' => $this->authorizedToView($request),
		];
	}



    // Authorization
    //---------------------------
	/*
	public function policy() {

		return [
			'authorizedToCreate' => true,
			'authorizedToDelete' => true,
			'authorizedToUpdate' => true,
			'authorizedToView' => true
		];
	}

	public static function policyClass()
	{
		$classResourcePolicy = str_replace('Resource', 'Policy', static::class);
		$classPolicy = class_exists($classResourcePolicy) ? $classResourcePolicy : Policy::class;
		return new $classPolicy();
	}

	public static function authorizedToViewAny()
	{
		// @todo: use current SRR user
		$user = new \Evavel\Models\Model();
		return (static::policyClass())->viewAny($user);
	}

	public static function authorizedToCreate()
	{
		// @todo: use current SRR user
		$user = new \Evavel\Models\Model();
		return (static::policyClass())->create($user);
	}*/


}
