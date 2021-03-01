<?php

namespace EightbitsNL\PolicyAndPermissionGenerator\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Spatie\Permission\Models\Permission;

class GenerateModelPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permissions:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create new permissions, based on the App models';

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
        

        $abilities = collect(['create','view','update','delete','viewAny','restore', 'forceDelete']);

        // find all files in /app folder 
        collect(File::files(app_path()))
            ->map(function ($item) use($abilities) {

                // model basename
                $basename = substr($item->getBasename(), 0, -4);

                return $abilities->map(function($ability) use ($basename){
                    return $basename.'.'.$ability;
                });

            })
            ->flatten()
            ->each(function($permission_name){
                $permission = Permission::firstOrCreate(['name' => $permission_name]);

                if($permission->wasRecentlyCreated)
                    $this->info('- Permission '.$permission_name. ' created');
                else
                    $this->line('- Permission '.$permission_name. ' skipped');
                    
            });
    }
}
