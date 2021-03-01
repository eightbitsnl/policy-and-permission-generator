<?php

namespace EightbitsNL\PolicyAndPermissionGenerator;

use EightbitsNL\PolicyAndPermissionGenerator\Console\Commands\GenerateModelPermissions;
use EightbitsNL\PolicyAndPermissionGenerator\Console\Commands\GenerateModelPolicies;
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