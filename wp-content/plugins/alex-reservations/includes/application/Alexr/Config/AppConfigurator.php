<?php

namespace Alexr\Config;

use Alexr\Enums\BookingStatus;
use Alexr\Enums\CurrencyType;
use Alexr\Enums\UserRole;
use Alexr\Models\Restaurant;
use Evavel\Eva;
use Evavel\Query\Query;

/**
 * Returns the User configuration for the dashboard
 */
class AppConfigurator
{
	public $user;
	public $tenants;
	public $current_tenant;
	public $translations;
	public $languages;
	public $all_languages;

	public function getConfiguration()
	{
		//ray('Get config FREE');

		$this->user = Eva::make('user');

		$this->setupTenants();
		$this->setupTranslations();

		$user = Eva::make('user');

		if (defined('ALEXR_PRO_VERSION')) {
			$version = ALEXR_PRO_VERSION;
		} else {
			$version = ALEXR_VERSION;
		}

		if (defined('ALEXR_PRO_RELEASE')) {
			$release = ALEXR_PRO_RELEASE;
		} else {
			$release = ALEXR_RELEASE;
		}

		if (defined('ALEXR_PRO_REQUIRED_FREE_VERSION')) {
			$free_version_required = ALEXR_PRO_REQUIRED_FREE_VERSION;
		} else {
			$free_version_required = ALEXR_VERSION;
		}

		//@TODO: modificar
		return [
			'timezone' => 'UTC', // Not used
			'userTimezone' => 'UTC', // Not used
			'version' => $version,
			'release' => $release,
			'admin_ajax' => evavel_ajaxurl(),

			// For the update of the plugin
			'updates_nonce' => evavel_create_nonce( 'updates' ),

			'licenseServer' => ALEXR_SERVER_LICENSE,
			'docsServer' => ALEXR_SERVER_DOCS,

			'navigationLeft' => $this->navigationLeft(),

			'navigation' => $this->navigationMain(),
			'navigationUser' => $this->navigationUser(),
			'navigationSettings' => $this->navigationSettings(),
			'navigationSidebarIcons' => $this->navigationSidebarIcons(),
			'tenantId' => $this->current_tenant,
			'tenants' => $this->tenants,
			'resources' => $this->resources(), // @TODO review

			'translations' => $this->translations,
			'languages' => $this->languages,
			'all_languages' => $this->all_languages,

			'lang' => evavel_current_user_lang(), // Not used
			'user' => $user,
			'role' => $user->role,
			'permissions' => $user->permissions,
			'statuses' => [
				'list_all_allowed' => BookingStatus::all_allowed(),
				'for_new_booking' => BookingStatus::for_new_bookings()
			],
			'currency' => [
				'labels' => CurrencyType::labels(),
				'symbols' => CurrencyType::symbols()
			],
			'heartbeat' => 15, // Every X seconds. Updates new bookings
			'login' => ALEXR_SITE_URL.ALEXR_DASHBOARD,
			'alexr_site_url' => ALEXR_SITE_URL,
			'required_free_version' => $free_version_required,
			'dashboard_menu_message' => alexr_get_setting('dashboard_menu_message'),
			'dashboard_menu_message_link' => alexr_get_setting('dashboard_menu_message_link'),

			'logo_custom_image_url' => alexr_get_setting('logo_custom_image_url'),
			'first_page_to_load' => alexr_get_setting('first_page_to_show'),
			'saas_enable_events_menu' => alexr_get_setting('saas_enable_events_menu'),
			'saas_hide_info_windows' => alexr_get_setting('saas_hide_info_windows'),
			'saas_custom_link_module_not_available' => alexr_get_setting('saas_custom_link_module_not_available'),

			'param_frame_embed_widget' => defined('ALEXR_PRO_EMBED_WIDGET_SCRIPT') ? ALEXR_PRO_EMBED_WIDGET_SCRIPT : '',
			'param_frame_show_widget' => defined('ALEXR_PRO_SHOW_WIDGET_HTML') ? ALEXR_PRO_SHOW_WIDGET_HTML : '',
		];

	}

	protected function navigationLeft()
	{
		// For administrators is always return to WP
		if ($this->user->role == UserRole::ADMINISTRATOR)
		{
			return alexr_config('navigation.navigation_left');
		}
		// For managers can be a custom link (only used by SAAS)
		else {
			$navigation_left = alexr_config('navigation.navigation_left');

			$redirect_to_wp = alexr_get_setting('redirect_to_wp');

			// No SAAS force redirection, then fallback to role
			if ($redirect_to_wp != 'yes')
			{
				// Check the permission based on role called 'WP back button'
				$navigation_left[0]['mode'] = 'role_based';
				return $navigation_left;
			}

			// Replace the redirect back to WP with the custom login
			if ($navigation_left[0]['name'] == 'WP')
			{
				$navigation_left[0]['name'] = alexr_get_setting('redirect_to_button_title');
				$navigation_left[0]['name_mobile'] = alexr_get_setting('redirect_to_button_title');
				$navigation_left[0]['href'] = alexr_get_setting('redirect_to_wp_custom_url');
			}

			return $navigation_left;
		}
	}

	protected function navigationMain()
	{
		$nav = alexr_config('navigation');
		$nav_items = $nav['navigation_items'];
		$nav_roles = $nav['navigation_roles'];

		$user = Eva::make('user');
		$menu = isset($nav_roles[$user->role]) ? $nav_roles[$user->role] : $nav_roles['default'];

		$navigation = [];
		foreach($menu as $key){
			$navigation[] = $nav_items[$key];
		}

		return $navigation;
	}

	// @todo: depends on the user
	protected function navigationUser()
	{
		return alexr_config('navigation.navigation_user');
	}

	protected function navigationSettings()
	{
		return alexr_config('navigation-settings');
	}

	protected function navigationSidebarIcons()
	{
		return alexr_config('navigation.navigation_sidebar_icons');
	}

	protected function setupTenants()
	{
		$this->setTenants();
		$this->setCurrentTenant();
	}

	/**
	 * Find all tenants used by the user
	 * @return void
	 */
	protected function setTenants()
	{
		$user = Eva::make('user');

		$this->tenants = [];

		if ($user->role == UserRole::ADMINISTRATOR) {
			$this->fetchAllTenants();
		}
		else if ($user->role == UserRole::USER) {
			$this->fetchTenantsForOwner($user);
		}
	}

	protected function fetchAllTenants()
	{
		$tenants = Restaurant::all()->map(function($restaurant){
			return $this->mapRestaurant($restaurant);
		})->toArray();

		// I need at least 1 restaurant
		if (empty($tenants))
		{
			Restaurant::create([
				'name' => 'MyRestaurantName'
			]);

			$tenants = Restaurant::all()->map(function($restaurant){
				return $this->mapRestaurant($restaurant);
			})->toArray();

		}

		$this->tenants = $tenants;
	}

	protected function fetchTenantsForOwner($user)
	{
		$tenants = $user->restaurants
			->filter(function($restaurant){
				return $restaurant->active == 1;
			})
			->map(function($restaurant){
				return $this->mapRestaurant($restaurant);
			})
			->toArray();

		$tenants = array_values($tenants);

		$this->tenants = $tenants;
	}

	protected function mapRestaurant($restaurant)
	{
		return [
			'id' => $restaurant->id,
			'uuid' => $restaurant->uuid,
			'active' => $restaurant->active,
			'name' => $restaurant->name,
			'timezone' => $restaurant->timezone,
			'language' => $restaurant->language,
			'firstDayOfWeek' => $restaurant->first_day_of_week,
			'currency' => $restaurant->currency,
			'currencySymbol' => CurrencyType::symbolFor($restaurant->currency),
			'vipColorCustomer' => $restaurant->vipColorCustomer,
			'vipColorBooking' => $restaurant->vipColorBooking,
			'soundUrl' => $restaurant->soundUrl,
			'dateFormat' => $restaurant->date_format ?: 'mdy',
			'timeFormat' => $restaurant->time_format ?: '12h'
		];
	}

	protected function setCurrentTenant()
	{
		$tenants = $this->tenants;

		// @todo: Add description to each tenant
		for($i = 0;  $i < count($tenants); $i++) {
			$tenants[$i]['description'] = '';
		}

		// @todo: based on current user, just now the first tenant is good enough
		$current_tenant = $tenants[0]['id'];

		$this->tenants = $tenants;
		$this->current_tenant = $current_tenant;
	}

	protected function resources()
	{
		$resources = alexr_config('app.resources', []);

		$config = [];
		foreach ($resources as $resource){
			$resourceClassName = evavel_resource_prefix().ucfirst(evavel_singular($resource));
			$config[$resource] = $resourceClassName::config();
		}

		return $config;
	}

	// @todo: en vez de echar todos los languages, enviar solo el del usuario ?
	protected function setupTranslations()
	{
		$this->translations = evavel_load_language_files_used_by_tenants();

		// Languages with the green dot
		$this->languages = evavel_languages_allowed();

		// All languages allowed
		$this->all_languages = evavel_languages_all();
	}
}
