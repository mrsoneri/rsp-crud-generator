<?php

namespace RSPCrud\Providers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider;
use RSPCrud\Console\RspCrudGeneratorCommand;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register the application's services.
     */
    public function register()
    {
        // Register the command
        $this->commands([
            RspCrudGeneratorCommand::class,
        ]);
        $this->bindRepositories();
    }
    public function boot()
    {
        // Register the console commands if running in the console
        if ($this->app->runningInConsole()) {
            $this->commands([
                RspCrudGeneratorCommand::class,
            ]);
        }
    }
    /**
     * Automatically bind repositories based on folder structure and naming conventions.
     */
    private function bindRepositories(): void
    {
        // Base namespace for Contracts and Implementations
        $baseContractNamespace = 'App\\Repositories\\Contract';
        $baseImplementationNamespace = 'App\\Repositories\\Eloquent';

        // Base paths for Contracts and Implementations
        $contractPath = app_path('Repositories/Contract');
        $implementationPath = app_path('Repositories/Eloquent');

        // Recursively scan the Contracts folder for interfaces
        $this->scanAndBind($contractPath, $implementationPath, $baseContractNamespace, $baseImplementationNamespace);
    }

    /**
     * Recursively scan and bind interfaces to implementations.
     */
    private function scanAndBind(string $contractPath, string $implementationPath, string $contractNamespace, string $implementationNamespace): void
    {
        if (! is_dir($contractPath)) {
            return;
        }
        foreach (File::allFiles($contractPath) as $file) {
            $relativePath = str_replace($contractPath . DIRECTORY_SEPARATOR, '', $file->getPathname());
            $relativeNamespace = str_replace(DIRECTORY_SEPARATOR, '\\', $relativePath);

            $interface = $contractNamespace . '\\' . pathinfo($relativeNamespace, PATHINFO_FILENAME);
            $implementation = $implementationNamespace . '\\' . str_replace('Interface', '', pathinfo($relativeNamespace, PATHINFO_FILENAME));
            $implementation = str_replace('\\Contract\\', '\\Eloquent\\', $implementation);

            if (class_exists($implementation)) {
                $this->app->bind($interface, $implementation);
            }
        }
    }

}
