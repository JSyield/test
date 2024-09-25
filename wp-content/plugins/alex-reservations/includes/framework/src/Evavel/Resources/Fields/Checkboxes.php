<?php

namespace Evavel\Resources\Fields;

use Evavel\Http\Request\Request;
use function DI\string;

class Checkboxes extends Field
{
	public $component = 'checkboxes-field';

	public function options($options, $meta = 'options')
	{
		if (is_callable($options)){
			$options = $options();
		}

		$list = evavel_collect( isset($options) ? $options : [])
			->map(function($label, $value){
				return [
					'label' => $label,
					'value' => $value
				];
			})
			->values()
			->all();

		return $this->withMeta([$meta => $list]);
	}

	public function saveAsString()
	{
		return $this->withMeta([
			'save_as_string' => true
		]);
	}

	public function saveUncheckedValues()
	{
		return $this->withMeta([
			'save_unchecked' => true
		]);
	}

	public function columns($columns = 1)
	{
		return $this->withMeta([
			'columns' => $columns
		]);
	}

	/**
	 * Show labels as HTML
	 * @return Checkboxes
	 */
	public function asHtml()
	{
		return $this->withMeta(['asHtml' => true]);
	}

	protected function shouldSaveAsString()
	{
		return (
			array_key_exists('save_as_string', $this->meta)
			&& $this->meta['save_as_string']
		);
	}

	protected function shouldSaveUnchecked()
	{
		return (
			array_key_exists('save_unchecked', $this->meta)
			&& $this->meta['save_unchecked']
		);
	}

	// @todo: change field to use this function instead of resolveValueFromModel
	//public function resolveAttribute($resource, $attribute = null) {}

	/**
	 * Convert to a list [id => true]
	 * @param $model
	 *
	 * @return false|mixed|void|null
	 */
	public function resolveValueFromModel($model)
	{
		$items = $model->{$this->attribute};

		// Convert to a list of trues
		$list = [];
		foreach($items as $item) {
			$list[$item->id] = true;
		}

		$this->value = json_encode($list);
	}

	protected function fillAttributeFromRequest(Request $request, $requestAttribute, $model, $attribute)
	{
		//if ($request->exists($requestAttribute)) {
			// @todo: how to make $request behaves like an array
			//$data = json_decode($request[$requestAttribute]);
			$data = json_decode($request->{$requestAttribute}, true);

			if ($this->shouldSaveAsString()) {
				$value = implode(',', $this->onlyChecked($data));
			} elseif ($this->shouldSaveUnchecked()) {
				$value = $data;
			} else {
				$value = $this->onlyChecked($data);
			}

			$model->{$attribute} = $value;
		//}
	}

	protected function onlyChecked($data)
	{
		return evavel_collect($data)
			->filter(function ($isChecked) {
				return $isChecked;
			})
			->keys()
			->all();
	}
}
