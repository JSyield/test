<?php

namespace Alexr\Models;

use Evavel\Models\Model;
use Evavel\Support\Str;

class Token extends Model {

	public static $table_name = 'tokens';
	public static $table_meta = false;

	protected $casts = [
		'expire_date' => 'datetime',
	];

	protected $appends = [
		'wpUserId',
	];

	public static function booted()
	{
		static::creating(function($token){
			$token->uuid = Str::uuid('to');
			$token->token = Str::upper(Str::random(50));
		});
	}

	public function user()
	{
		return $this->belongsTo(User::class);
	}

	public function getWpUserIdAttribute()
	{
		return $this->user->wp_user_id;
	}

	// STATIC HELPERS
	//---------------------------------------------------
	public static function isDisabled()
	{
		$disabled = alexr_get_setting('auth_token_disable');
		return ($disabled == 'true' || $disabled === true);
	}

	public static function generateToken($user_id)
	{
		if (self::isDisabled()) return null;

		$token = self::create([
			'user_id' => $user_id,
			'expire_date' => evavel_date_now()->addDays(365)
		])	;

		return $token->token;
	}

	public static function userToLogin($auth_token)
	{
		if (self::isDisabled()) return null;

		$token = Token::where('token', $auth_token)
           ->where('expire_date', '>', evavel_now())
           ->first();

		if ($token != null) {
			return $token->user;
		}

		return null;
	}

	public static function delete($auth_token)
	{
		self::where('token', $auth_token)->delete();
	}
}
