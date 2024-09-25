<?php

namespace Alexr\Actions;

use Evavel\Http\Request\Request;
use Evavel\Resources\Actions\Action;
use Evavel\Resources\Actions\ActionFields;
use Evavel\Models\Collections\Collection;

use Evavel\Resources\Fields\Select;

class ChangeDate  extends Action
{
	public function name()
	{
		return __eva('Change Date');
	}

	public function handle(ActionFields $fields, Collection $models)
	{
		return Action::message(__eva('Done!'));
	}

	public function fields(Request $request)
	{
		return [
			Select::make( __eva('Date'), 'date')
			      ->required()
			      ->options([
					  'today' => 'Today',
				      'yesterday' => 'Yesterday',
				      'tomorrow' => 'Tomorrow'
			      ]),
		];
	}
}
