<?php

namespace Alexr\Models\Traits;

use Alexr\Enums\UserRole;
use Alexr\Models\Role;

trait ManagePermissions {

	protected function getPermissionsAttribute()
	{
		// For each restaurant
		// Find the role and get the permissions based on the role

		if ($this->role == UserRole::ADMINISTRATOR){
			return ['all'];
		}

		$user_rests = $this->restaurants;

		$list = [];
		foreach($user_rests as $user_rest)
		{
			$restaurant_id = $user_rest->id;
			$role = $user_rest->user_role;

			$m_role = Role::where('restaurant_id', $restaurant_id)->where('role', $role)->first();

			if ($m_role == null)
			{
				Role::createDefaultRoles($restaurant_id);
				$m_role = Role::where('restaurant_id', $restaurant_id)->where('role', $role)->first();
			}

			if ($m_role) {
				$permissions = $m_role->permissions;
			} else {
				$permissions = null;
			}

			$list[] = [
				'restaurantId' => $restaurant_id,
				'role' => $role,
				'permissions' => $permissions
			];

		}

		return $list;
	}

	protected function getRestaurantPermissions($restaurant_id)
	{
		$restaurants = $this->restaurants;
		foreach($restaurants as $restaurant){
			if ($restaurant->id == $restaurant_id){
				$role = $restaurant->user_role;
				$m_role = Role::where('restaurant_id', $restaurant_id)->where('role', $role)->first();
				if ($m_role){
					return $m_role->permissions;
				}
			}
		}
		return null;
	}

	protected function canAction($resource_name, $action, $restaurant_id)
	{
		$permissions = $this->getRestaurantPermissions($restaurant_id);

		if (isset($permissions[$resource_name]) && isset($permissions[$resource_name][$action])) {
			return $permissions[$resource_name][$action];
		}

		return false;
	}

	public function canManage($restaurant_id)
	{
		if ($this->role == UserRole::ADMINISTRATOR) return true;

		$tenants = $this->restaurants->filter(function($restaurant) use($restaurant_id) {
			return $restaurant->active == 1 && $restaurant->id == $restaurant_id;
		});

		return $tenants->count() == 1;
	}

	public function canView($resource_name, $restaurant_id)
	{
		return $this->role == UserRole::ADMINISTRATOR ?
			true :
			$this->canAction($resource_name, 'view', $restaurant_id);
	}

	public function canEdit($resource_name, $restaurant_id)
	{
		return $this->role == UserRole::ADMINISTRATOR ?
			true :
			$this->canAction($resource_name, 'edit', $restaurant_id);
	}

	public function canCreate($resource_name, $restaurant_id)
	{
		return $this->role == UserRole::ADMINISTRATOR ?
			true :
			$this->canAction($resource_name, 'create', $restaurant_id);
	}

	public function canDelete($resource_name, $restaurant_id)
	{
		return $this->role == UserRole::ADMINISTRATOR ?
			true :
			$this->canAction($resource_name, 'delete', $restaurant_id);
	}

	public function canExport($resource_name, $restaurant_id)
	{
		return $this->role == UserRole::ADMINISTRATOR ?
			true :
			$this->canAction($resource_name, 'export', $restaurant_id);
	}

	public function canImport($resource_name, $restaurant_id)
	{
		return $this->role == UserRole::ADMINISTRATOR ?
			true :
			$this->canAction($resource_name, 'import', $restaurant_id);
	}
}
