<?php

namespace Alexr\Http\Controllers;

use Alexr\Enums\UserRole;
use Alexr\Models\Role;
use Evavel\Eva;
use Evavel\Http\Controllers\Controller;
use Evavel\Http\Request\Request;

class RolesController extends Controller
{
	protected function isAdministrator()
	{
		$user = Eva::make('user');
		return $user->role == UserRole::ADMINISTRATOR;
	}

	public function index(Request $request)
	{
		if (!$this->isAdministrator()){
			return $this->response(['success' => false, 'error' => __eva('You cannot view roles.')]);
		}

		$tenant_id = $request->tenant;

		Role::createDefaultRoles($tenant_id);

		if ($tenant_id == null){
			$roles = Role::whereIsNull('restaurant_id')->get()->toArray();
		} else {
			$roles = Role::where('restaurant_id', $tenant_id)->get()->toArray();
		}

		// Mix with default permissions in case I have added new permissions
		$roles_arr = [];
		foreach($roles as $role)
		{
			$role_arr = $role->toArray();

			// Fill default permissions with values fetched
			$role_arr['permissions'] = Role::completeMissedPermissions($role_arr['permissions'], $role_arr['role']);

			$roles_arr[] = $role_arr;
		}


		return $this->response([
			'success' => true,
			'resources' => $roles_arr,
			'permissions_labels' => Role::permissionsLabels(),
			'actions' => Role::actions()
		]);
	}

	public function update(Request $request)
	{
		if (!$this->isAdministrator()){
			return $this->response(['success' => false, 'error' => __eva('You cannot edit roles.')]);
		}

		$roles_data = json_decode($request->roles);
		$tenant_id = $request->tenant;


		foreach($roles_data as $role_data) {

			if ($tenant_id == null) {
				$role = Role::whereIsNull('restaurant_id')
		            ->where('role', $role_data->role)
		            ->first();
			}
			else {
				$role = Role::where('restaurant_id', $tenant_id)
		            ->where('role', $role_data->role)
		            ->first();
			}

			if ($role) {
				$role->permissions = $role_data->permissions;
				$role->save();
			}

		}

		return $this->response(['success' => true]);
	}
}
