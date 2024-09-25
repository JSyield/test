<?php

namespace Alexr\Models;

use Alexr\Enums\UserRole;
use Alexr\Mail\MailManager;
use Alexr\Models\Traits\ManagePermissions;
use Evavel\Models\User as Authenticatable;
use Evavel\Support\Str;

class User extends Authenticatable
{
	use ManagePermissions;

	public static $table_name = 'users';
	public static $table_meta = 'user_meta';

	public static function booted()
	{
		static::creating(function($user) {
			$user->uuid = Str::uuid('us');
		});
	}

	public function isActive()
	{
		return $this->active == 1;
	}

	public function isInactive()
	{
		return $this->active != 1;
	}

	public function bookings()
	{
		return $this->hasMany(Booking::class, 'user_id');
	}

	public function restaurant()
	{
		return $this->belongsTo(Restaurant::class);
	}

	public function restaurants()
	{
		$belongsToMany = $this->belongsToMany(Restaurant::class, 'restaurant_user', 'user_id', 'restaurant_id');

		$belongsToMany->addRelationColumns(['role as user_role', 'settings as user_settings']);

		return $belongsToMany;
	}

	public function restaurantsIds()
	{
		return evavel_collect($this->restaurants)
			->map(function($item){
				return $item->id;
			})
			->all();
	}

	public function generateAndSendMagicCode()
	{
		$this->deleteMagicCode();
		$magiccode = $this->generateMagicCode();
		$this->sendEmailMagicCode();
	}

	public function deleteMagicCode()
	{
		$this->magic_code = null;
		$this->save();
	}

	protected function generateMagicCode()
	{
		$this->magic_code = random_int(100000, 999999);
		$this->save();
	}

	protected function sendEmailMagicCode()
	{
		$result = wp_mail($this->email, 'Magic Code', 'Your code is: ' . $this->magic_code);
	}


}
