<?php

namespace Dorcas\ModulesIntegrations;
use Illuminate\Support\ServiceProvider;

class ModulesIntegrationsServiceProvider extends ServiceProvider {

	public function boot()
	{
		$this->loadRoutesFrom(__DIR__.'/routes/web.php');
		$this->loadViewsFrom(__DIR__.'/resources/views', 'modules-integrations');
		$this->publishes([
			__DIR__.'/config/modules-integrations.php' => config_path('modules-integrations.php'),
		], 'config');
		/*$this->publishes([
			__DIR__.'/assets' => public_path('vendor/modules-integrations')
		], 'public');*/
	}

	public function register()
	{
		//add menu config
		$this->mergeConfigFrom(
	        __DIR__.'/config/navigation-menu.php', 'navigation-menu.modules-integrations.sub-menu'
	     );
	}

}


?>