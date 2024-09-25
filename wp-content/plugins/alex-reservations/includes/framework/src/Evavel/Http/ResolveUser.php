<?php

namespace Evavel\Http;

use Evavel\Support\Str;

class ResolveUser
{
	private $userClass = null;
	private $userRoleAdministrator = null;

	public function __construct()
	{
		$this->userClass = evavel_app_user_class();
		$this->userRoleAdministrator = evavel_app_user_role_administrator();
	}

	/**
	 * Find the user based on the wp current user
	 *
	 * @return mixed
	 */
	public function getApplicationUser()
	{
		if (!is_user_logged_in()) {
			return null;
		}
		$user_wp = wp_get_current_user();

		$email = $user_wp->data->user_email;

		// Comprobar antes si la tabla exists
		if (!evavel_has_installed_users_table()) {
			evavel_force_check_tables_installed();
		}

		$user = $this->userClass::where('email', $email)
		            ->where('wp_user_id', $user_wp->ID)
		            ->first();

		return $user ? $user : $this->createApplicationUser($user_wp);
	}

	/**
	 * Generate administrator on the fly if wp user is an administrator
	 * WP user administrator can manage everything
	 *
	 * @param $user_wp
	 *
	 * @return null
	 */
	protected function createApplicationUser($user_wp)
	{
		if ($user_wp === null) return null;

		if (in_array('administrator', $user_wp->roles)) {

			$first_name = get_user_meta($user_wp->ID, 'first_name', true);
			$last_name = get_user_meta($user_wp->ID, 'last_name', true);

			$name = $first_name.' '.$last_name;
			if (strlen($name) < 3){
				$name = $user_wp->data->user_nicename;
			}

			// Necesito añadir uuid aqui, el metodo booted no lo añade a tiempo
			$user = $this->userClass::create([
				'uuid' => Str::uuid('us'),
				'wp_user_id' => $user_wp->ID,
				'email' => $user_wp->data->user_email,
				'role' => $this->userRoleAdministrator,
				'name' => $name,
				'first_name' => $first_name,
				'last_name' => $last_name,
			]);

			$user->save();

			return $user;
		}

		return null;
	}

}
