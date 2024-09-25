<?php

namespace Alexr\Observers;

use Alexr\Models\Table;

class TableObserver {
	public function saving(Table $table)
	{
		// A shareable table cannot be bookable online
		if ($table->shareable == 1) {
			$table->bookable_online = 0;
		}
	}

	public function saved(Table $table)
	{
		//ray('Table has been saved '.$table->id);
	}

	public function updating(Table $table)
	{
		//ray('Table is updating '.$table->id);
	}

	public function updated(Table $table)
	{
		//ray('Table has been updated '.$table->id);
	}

	public function creating(Table $table)
	{
		//ray('Table is creating '.$table->id);
	}

	public function created(Table $table)
	{
		//ray('Table has been created '.$table->id);
	}
}
