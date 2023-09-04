<?php

namespace Coreproc\DockerInternalInstallCommand;

use Illuminate\Console\Command;
use Illuminate\Process\Pipe;
use Illuminate\Support\Facades\Process;

class DockerInternalInstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'docker:install {--e|environment=internal}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Integrates Docker made for our internal environment.';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $phpVersion = $this->choice('Choose a PHP version:', ['8.1', '8.2'], 1);

        $nodeVersion = $this->choice('Choose a Node.js version:', ['16', '18', '20'], 1);

        $environment = strtolower($this->option('environment'));

        Process::pipe(function (Pipe $pipe) use (
            $environment,
            $phpVersion,
            $nodeVersion
        ) {
            $dockerComposeDir = 'docker-compose-laravel-internal';
            $pipe->command('git clone git@github.com:CoreProc/docker-compose-laravel-internal.git');

            // Move the cloned files to their locations
            $pipe->command('cp -R ' . $dockerComposeDir . '/docker .');
            $pipe->command('cp -R ./docker/environment ./docker/' . $environment);
            $pipe->command('rm -rf ./docker/environment');

            // Delete the cloned directory
            $pipe->command('rm -rf ' . $dockerComposeDir);

            // Replace Dockerfile contents
            $pipe->command('sed -i "s/_REPLACE_PHP_VERSION_/' . $phpVersion . '/g" ./Dockerfile');
            $pipe->command('sed -i "s/_REPLACE_NODE_VERSION_/' . $nodeVersion . '/g" ./Dockerfile');
            $pipe->command('sed -i "s/_REPLACE_ENVIRONMENT_/' . $environment . '/g" ./Dockerfile');

            // Rename Dockerfile
            $pipe->command('mv ./Dockerfile ./Dockerfile.' . $environment);
        }, function (string $type, string $output) {
            $this->info($type);
            $this->info($output);
        });
    }
}
