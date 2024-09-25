<?php

namespace Evavel\Http\Request;

use Evavel\Resources\Actions\ActionFields;

class ActionRequest extends Request
{
	public function validateFields()
	{
		return $this->action()->validateFields($this);
	}

	public function action()
	{
		//$hasResources = ! empty($this->resources);

		return evavel_collect($this->availableActions())
			/*->filter(function($action) use($hasResources) {
				return $hasResources ? true : $action->isStandalone();
			})*/
			->first(function($action) {
				return $action->uriKey() == $this->query('action');
			});
	}

	protected function availableActions()
	{
		return $this->resolveActions();
	}

	protected function resolveActions()
	{
		return $this->newResource()->resolveActions($this);
	}

	public function resolveFields()
	{
		$results = evavel_collect($this->action()->fields($this))
			->mapWithKeys(function($field) {
				return [$field->attribute => $field->fillForAction($this, null)];
			})->toArray();

		return new ActionFields($results);
	}

	public function resolveModels()
	{
		$modelClass = $this->modelClass();
		$resources = explode(',',$this->resources());

		$models = [];
		foreach($resources as $resourceId){
			$models[] = $modelClass::withId($resourceId);
		}

		return evavel_collect($models);
	}
}
