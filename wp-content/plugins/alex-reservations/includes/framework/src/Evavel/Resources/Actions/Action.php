<?php

namespace Evavel\Resources\Actions;

use Evavel\Http\Request\ActionRequest;
use Evavel\Http\Request\Request;
use Evavel\Http\Validation\Validator;
use Evavel\Interfaces\ToJsonSerialize;
use Evavel\Resources\Traits\Metable;
use Evavel\Support\Str;

class Action implements ToJsonSerialize
{
    use Metable;

    public $name;
    public $component = 'confirm-action-modal';
    public $availableForEntireResource = false;
    public $withoutConfirmation = false;
    public $onlyOnIndex = false;
    public $onlyOnDetail = false;
    public $showOnIndex = true;
    public $showOnDetail = true;
    public $showOnTableRow = false;
    public $confirmButtonText = 'Run Action';
    public $cancelButtonText = 'Cancel';
    public $confirmText = 'Are you sure you want to run this action?';
    public $standalone = false;

    public function name()
    {
        return $this->name ?: Str::humanize($this);
    }

	public function component()
	{
		return $this->component;
	}

    public function uriKey()
    {
        return Str::slug($this->name(), '-');
    }

    public function actionClass()
    {
        return $this instanceof DestructiveAction
            ? 'btn-danger'
            : 'btn-primary';
    }

    public function shownOnIndex()
    {
        if ($this->onlyOnIndex == true) {
            return true;
        }

        if ($this->onlyOnDetail) {
            return false;
        }

        return $this->showOnIndex;
    }

    public function shownOnDetail()
    {
        if ($this->onlyOnDetail) {
            return true;
        }

        if ($this->onlyOnIndex) {
            return false;
        }

        return $this->showOnDetail;
    }

    public function shownOnTableRow()
    {
        return $this->showOnTableRow;
    }

    public function confirmButtonText($text)
    {
        $this->confirmButtonText = $text;

        return $this;
    }

    public function cancelButtonText($text)
    {
        $this->cancelButtonText = $text;

        return $this;
    }

    public function confirmText($text)
    {
        $this->confirmText = $text;

        return $this;
    }

    public function standalone()
    {
        $this->standalone = true;

        return $this;
    }

    public function isStandalone()
    {
        return $this->standalone;
    }

	public function fields(Request $request)
	{
		return [];
	}

	public function validateFields(ActionRequest $request)
	{
		$fields = evavel_collect($this->fields($request));

		$rules = $fields->mapWithKeys(function($field) use($request){
			return $field->getCreationRules($request);
		})->toArray();

		$validator = Validator::make($request->params, $rules);

		if ($validator->fails()){

			$response = [
				'message' => __eva('The given data was invalid.'),
				'errors' => $validator->errors()
			];

			evavel_send_json($response, 422);
		}

		return true;
	}

	public function handleRequest(ActionRequest $request)
	{
		// Call to handle method with the models
		$fields = $request->resolveFields();
		$models = $request->resolveModels();

		return $this->handle($fields, $models);
	}

	public static function message($message)
	{
		return ['message' => $message];
	}

	public static function danger($message)
	{
		return ['danger' => $message];
	}

	public static function deleted()
	{
		return ['deleted' => true];
	}

	public static function redirect($url)
	{
		return ['redirect' => $url];
	}

    public function toJsonSerialize()
    {
        $request = evavel_make('request');

        return array_merge([
	        'component' => $this->component(),
            'cancelButtonText' => __eva($this->cancelButtonText),
            'confirmButtonText' => __eva($this->confirmButtonText),
            'confirmText' => __eva($this->confirmText),
            'class' => $this->actionClass(),
            'destructive' => $this instanceof DestructiveAction,
            'name' => $this->name(),
            'uriKey' => $this->uriKey(),
            'fields' => $this->fields($request),
            'availableForEntireResource' => $this->availableForEntireResource,
            'showOnDetail' => $this->shownOnDetail(),
            'showOnIndex' => $this->shownOnIndex(),
            'showOnTableRow' => $this->shownOnTableRow(),
            'standalone' => $this->isStandalone(),
            'withoutConfirmation' => $this->withoutConfirmation,
        ], $this->meta());
    }
}
