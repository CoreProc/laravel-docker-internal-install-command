<?php

namespace Coreproc\DockerInternalInstallCommand;

use Illuminate\Support\ServiceProvider;

class DockerInternalInstallProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->commands([
            DockerInternalInstallCommand::class,
        ]);
    }
}
