<?php

class ALEXR_User_admin
{
	public function __construct()
	{
		add_filter( 'manage_users_columns', [$this, 'new_modify_user_table'] );
		add_filter( 'manage_users_custom_column', [$this, 'new_modify_user_table_row'], 10, 3 );
	}

	public function new_modify_user_table( $column )
	{
		$column['alexr_user'] = __eva('ALEXR User');
		return $column;
	}

	public function new_modify_user_table_row( $val, $column_name, $user_id )
	{
		switch ($column_name){
			case 'alexr_user':
				$eva_user = \Alexr\Models\User::where('wp_user_id', $user_id)->first();
				if ($eva_user != null){
					return $eva_user->id.' ('.$eva_user->role.')';
				}
				return '-';
			default:
		}
		return $val;
	}
}

new ALEXR_User_admin();
