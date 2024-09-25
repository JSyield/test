<?php

namespace Evavel\Cards;

use Evavel\Interfaces\ToJsonSerialize;
use Evavel\Resources\Traits\Metable;

abstract class Element implements ToJsonSerialize
{
	use Metable;

	/**
	 * Component name
	 *
	 * @var string
	 */
	public $component;

	/**
	 * Assign the component
	 *
	 * @param $component
	 */
	public function __construct($component = null)
	{
		$this->component = $component ? $component : $this->component;
	}

	/**
	 * Fetch the component name
	 *
	 * @return string
	 */
	public function component()
	{
		return $this->component;
	}

	public function toJsonSerialize()
	{
		return array_merge([
			'component' => $this->component()
		], $this->meta());
	}
}
