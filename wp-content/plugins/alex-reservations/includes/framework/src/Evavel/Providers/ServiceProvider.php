<?php

namespace Evavel\Providers;

abstract class ServiceProvider
{
    protected $app;

    public function register() {}

	public function boot() {}
}
