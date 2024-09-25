<?php

namespace Alexr\Providers;

use Alexr\Config\AppConfigurator;
use Evavel\Container\EvaContainer;
use Evavel\Eva;
use Evavel\Http\ResolveUser;
use Evavel\Providers\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
	public function register()
	{
		// Bind the WP user
		Eva::bind('user_wp', wp_get_current_user());

		// Bind the Application User
		Eva::bind('user', (new ResolveUser)->getApplicationUser());

		// Bind the configurator of the user dashboard
		Eva::bind('app-configurator', AppConfigurator::class);
	}

	public function boot()
	{

	}

}
