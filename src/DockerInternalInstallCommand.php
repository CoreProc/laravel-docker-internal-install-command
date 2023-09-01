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

        $containerName = strtolower($this->ask('Define container name:'));
        $containerPort = $this->ask('Define port:');

        $environment = strtolower($this->option('environment'));

        Process::pipe(function (Pipe $pipe) use (
            $containerName,
            $containerPort,
            $environment,
            $phpVersion,
            $nodeVersion
        ) {
            $dockerComposeDir = 'docker-compose-laravel-internal';
            $pipe->command('git clone -b integration git@github.com:CoreProc/docker-compose-laravel-internal.git');

            // Move the cloned files to their locations
            $pipe->command('cp -R ' . $dockerComposeDir . '/docker-compose.internal.yml ./docker-compose.' . $environment . '.yml');
            $pipe->command('cp -R ' . $dockerComposeDir . '/docker .');
            $pipe->command('cp -R ./docker/environment ./docker/' . $environment);
            $pipe->command('rm -rf ./docker/environment');

            // Append Makefile contents to the current Makefile
            $pipe->command('touch ./Makefile');
            $pipe->command('cat ' . $dockerComposeDir . '/Makefile >> ./Makefile');
            $pipe->command('sed -i "s/_REPLACE_ENVIRONMENT_/' . $environment . '/g" ./Makefile');

            // Delete the cloned directory
            $pipe->command('rm -rf ' . $dockerComposeDir);

            // Replace docker-compose.yml contents
            $pipe->command('sed -i "s/_REPLACE_CONTAINER_NAME_/' . $containerName . '/g" docker-compose.' . $environment . '.yml');
            $pipe->command('sed -i "s/_REPLACE_ENVIRONMENT_/' . $environment . '/g" docker-compose.' . $environment . '.yml');
            $pipe->command('sed -i "s/_REPLACE_APP_PORT_/' . $containerPort . '/g" docker-compose.' . $environment . '.yml');

            // Replace Dockerfile contents
            $pipe->command('sed -i "s/_REPLACE_PHP_VERSION_/' . $phpVersion . '/g" ./docker/' . $environment . '/Dockerfile');
            $pipe->command('sed -i "s/_REPLACE_NODE_VERSION_/' . $nodeVersion . '/g" ./docker/' . $environment . '/Dockerfile');
        }, function (string $type, string $output) {
            $this->info($type);
            $this->info($output);
        });
    }
}
