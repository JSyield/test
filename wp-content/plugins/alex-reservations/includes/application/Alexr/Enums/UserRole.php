<?php

namespace Alexr\Enums;

class UserRole
{
	const ADMINISTRATOR = "administrator";
	const USER = "user"; // This is for any manager, saved in the table _srr_users

	const SUPER_MANAGER = "super_manager";
	const MANAGER = "manager";
	const SUB_MANAGER = "sub_manager";

	// No lo estoy usando, era de antes
	const OWNER = 'owner';
	const EMPLOYE = 'employe';

	public static function listing() {
		return [
			self::SUPER_MANAGER => __eva('Super Manager'),
			self::MANAGER => __eva('Manager'),
			self::SUB_MANAGER => __eva('Sub Manager'),
		];
	}
}
