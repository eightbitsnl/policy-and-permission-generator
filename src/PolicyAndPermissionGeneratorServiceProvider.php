<?php

namespace Eightbits\PolicyAndPermissionGenerator;

use Eightbits\PolicyAndPermissionGenerator\Console\Commands\GenerateModelPermissions;
use Eightbits\PolicyAndPermissionGenerator\Console\Commands\GenerateModelPolicies;
use Illuminate\Support\ServiceProvider;

class PolicyAndPermissionGeneratorServiceProvider extends ServiceProvider
{
	public function register()
	{
		//
	}
	
	public function boot()
	{
		// Register the command if we are using the application via the CLI
		if ($this->app->runningInConsole()) {
			$this->commands([
				GenerateModelPermissions::class,
				GenerateModelPolicies::class
			]);
		}
	}
}