<?php

namespace Alexr\Observers;

use Alexr\Models\Token;

class TokenObserver
{
	public function saving(Token $token)
	{
		//ray('Token is saving '.$token->id);
	}

	public function saved(Token $token)
	{
		//ray('Token has been saved '.$token->id);
	}

	public function updating(Token $token)
	{
		//ray('Token is updating '.$token->id);
	}

	public function updated(Token $token)
	{
		//ray('Token has been updated '.$token->id);
	}

	public function creating(Token $token)
	{
		//ray('Token is creating '.$token->id);
	}

	public function created(Token $token)
	{
		//ray('Token has been created '.$token->id);
	}
}
