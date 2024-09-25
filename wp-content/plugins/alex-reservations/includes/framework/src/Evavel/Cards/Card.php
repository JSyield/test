<?php

namespace Evavel\Cards;

abstract class Card extends Element
{
	const FULL_WIDTH = 'full';
	const ONE_THIRD_WIDTH = '1/3';
	const ONE_HALF_WIDTH = '1/2';
	const ONE_QUARTER_WIDTH = '1/4';
	const TWO_THIRDS_WIDTH = '2/3';
	const THREE_QUARTERS_WIDTH = '3/4';

	/**
	 * Width
	 *
	 * @var string
	 */
	public $width = '1/3';

	/**
	 * Set width
	 *
	 * @param $width
	 *
	 * @return $this
	 */
	public function width($width)
	{
		$this->width = $width;

		return $this;
	}

	public function toJsonSerialize()
	{
		return array_merge([
			'width' => $this->width
		], parent::toJsonSerialize());
	}
}
