<?php
/**
 * @format
 */

namespace Eightbitsnl\PolicyAndPermissionGenerator\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;

class GenerateModelPermissions extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = "permissions:generate {--model= : Specify a model}";

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = "Create new permissions, based on the App models";

  /**
   * Create a new command instance.
   *
   * @return void
   */
  public function __construct()
  {
    parent::__construct();
  }

  private function findModelClasses($path = null)
  {
    $path = is_null($path)
      ? (is_dir(app_path("Models"))
        ? app_path("Models")
        : app_path(""))
      : $path;

    return collect(File::allFiles($path))
      ->map(function ($path) {
        if (File::isDirectory($path)) {
          return $this->findModelClasses($path);
        }

        if (File::extension($path) == "php") {
          return "App" .
            str_replace("/", "\\", substr($path, strlen(app_path()), -4)) .
            "";
        }
      })
      ->flatten()
      ->filter(function ($classname) {
        return is_subclass_of($classname, Model::class);
      })
      ->values();
  }

  /**
   * Execute the console command.
   *
   * @return int
   */
  public function handle()
  {
    $models = $this->findModelClasses();

    $input_model = $this->option("model");

    $selected = null;

    if (!is_null($input_model)) {
      if ($models->contains($input_model)) {
        $selected = [$input_model];
      } else {
        if (
          class_exists($input_model) &&
          is_subclass_of($input_model, Model::class)
        ) {
          $models->push($input_model);
          $selected = [$input_model];
        } else {
          $this->error("Model not found.");
        }
      }
    }

    $selected =
      $selected ??
      $this->choice(
        "Create Permissions for which models? Use , to select multiple: 1,2,3",
        $models->toArray(),
        null,
        null,
        true
      );

    $abilities = collect([
      "create",
      "view",
      "update",
      "delete",
      "viewAny",
      "restore",
      "forceDelete",
    ]);

    // create Permission for selected classes
    collect($selected)
      ->map(function ($fqcn) {
        return class_basename($fqcn);
      })
      ->crossJoin($abilities)
      ->each(function ($group) {
        $permission_name = implode(".", $group);

        $permission = Permission::firstOrCreate(["name" => $permission_name]);

        if ($permission->wasRecentlyCreated) {
          $this->info("- Permission " . $permission_name . " created");
        } else {
          $this->line("- Permission " . $permission_name . " skipped");
        }
      });

    // detect Policies
    $stub_path = __DIR__ . "/Stubs/ModelPolicy.stub";
    if (is_file($stub_path)) {
      $policiesDir = app_path("Policies");
      $stub = file_get_contents($stub_path);
      collect($selected)->each(function ($fqcn) use ($policiesDir, $stub) {
        $policy_class_name = class_basename($fqcn) . "Policy";
        $targetPath = $policiesDir . "/" . $policy_class_name . ".php";

        if (!file_exists($targetPath)) {
          if ($this->confirm("No Policy found for `$fqcn` Create?")) {
            $replace = [
              "DummyModelFqcn" => $fqcn,
              "DummyModelBasename" => class_basename($fqcn),
              "DummyPolicyClass" => $policy_class_name,
            ];

            $contents = str_replace(
              array_keys($replace),
              array_values($replace),
              $stub
            );

            file_put_contents($targetPath, $contents);
            $this->info("- Policy " . $policy_class_name . " created");
          }
        }
      });
    }
  }
}
