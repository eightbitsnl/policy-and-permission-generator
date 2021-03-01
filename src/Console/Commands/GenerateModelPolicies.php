<?php

namespace Eightbits\PolicyAndPermissionGenerator\Console\Commands;

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
    protected $signature = 'policies:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create new Policy classes, based on the App models';

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
     
        $stub = file_get_contents(__DIR__ . '/../Stubs/policy.stub');


        $targetDir =  app_path('Policies');
    
        // find all models
        collect(File::files(app_path()))
            ->each(function ($item) use ($stub, $targetDir){

                // model basename
		        $model_name = substr($item->getBasename(), 0, -4);
                $policy_name = $model_name.'Policy';

                $targetPath = $targetDir .'/' . $policy_name . '.php';


                if (! file_exists($targetPath) )
                {
                    $replace = [
                        'DummyNamespace' => 'App\Policies',
                        'DummyFullModelClass' => 'App\\' . $model_name,
                        'DummyModelClass' => $model_name,
                        'DummyClass' => $policy_name
                    ];

                    $contents = str_replace(
                        array_keys($replace), array_values($replace), $stub
                    );

                    file_put_contents($targetPath, $contents);
                    $this->info('- Policy '. $policy_name. ' created');
                }

            });
    }
}
