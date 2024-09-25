<?php

namespace Alexr\Actions;

use Alexr\Enums\BookingStatus;
use Evavel\Http\Request\Request;
use Evavel\Resources\Actions\Action;
use Evavel\Resources\Actions\ActionFields;
use Evavel\Models\Collections\Collection;

use Evavel\Resources\Fields\Select;

class ChangeStatus extends Action
{
    public function name()
    {
        return __eva('Change Status');
    }

    public function handle(ActionFields $fields, Collection $models)
    {

	    $status = $fields->status;

		foreach($models->toArray() as $model)
		{
			//ray($model);
			$model->status = $status;
			$model->save();
		}

	    //return Action::danger(__eva('WRONG'));
		return Action::message(__eva('Done!'));
    }

	public function fields(Request $request)
    {
        return [
            Select::make( __eva('Status'), 'status')
                ->required()
                ->options(BookingStatus::listing()),
        ];
    }
}
