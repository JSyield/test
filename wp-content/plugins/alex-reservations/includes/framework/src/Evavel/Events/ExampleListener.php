<?php

namespace Evavel\Events;

class ExampleListener
{
	public function handle($name1, $name2)
	{
		return 'ExampleListener: '.$name1.' y '.$name2;
	}
}
