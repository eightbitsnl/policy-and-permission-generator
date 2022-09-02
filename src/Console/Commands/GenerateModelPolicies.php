<?php
/**
 * @format
 */

namespace Eightbitsnl\PolicyAndPermissionGenerator\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\File;

class GenerateModelPolicies extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = "policies:generate {--model= : Specify a model}";

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = "Create new Policy classes, based on the App models";

  /**
   * Create a new command instance.
   *
   * @return void
   */
  public function __construct()
  {
    parent::__construct();
  }

  /**
   * Execute the console command.
   *
   * @return int
   */
  public function handle()
  {
    $stub = file_get_contents(__DIR__ . "/../Stubs/policy.stub");

    $targetDir = app_path("Policies");

    // find all models
    $models_dir = is_dir(app_path("Models")) ? app_path("Models") : app_path();

    $models = $this->option("model")
      ? collect($this->option("model"))
      : collect(File::glob($models_dir . "/*.php"))->map(function ($str) {
        return substr($str, 0, -4);
      });

    $models->each(function ($path) use ($stub, $targetDir) {
      // model basename
      $model_name = class_basename($path);
      $policy_name = $model_name . "Policy";

      $targetPath = $targetDir . "/" . $policy_name . ".php";

      if (!file_exists($targetPath)) {
        $replace = [
          "DummyNamespace" => "App\Policies",
          "DummyFullModelClass" => "App\\" . $model_name,
          "DummyModelClass" => $model_name,
          "DummyClass" => $policy_name,
        ];

        $contents = str_replace(
          array_keys($replace),
          array_values($replace),
          $stub
        );

        file_put_contents($targetPath, $contents);
        $this->info("- Policy " . $policy_name . " created");
      }
    });
  }
}
