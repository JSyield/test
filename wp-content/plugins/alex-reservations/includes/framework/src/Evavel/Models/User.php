<?php

namespace Evavel\Models;

use Evavel\Models\Traits\Authorizable;
use Evavel\Interfaces\Authorizable as AuthorizableContract;

class User extends Model implements AuthorizableContract
{
	use Authorizable;
}
