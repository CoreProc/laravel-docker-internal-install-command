# Laravel Docker Internal Install Command

Install this package to your Laravel project to add a new `docker:install` command to your artisan commands.

```
composer require coreproc/laravel-docker-internal-install-command
```

## Usage

Run the following command to install the docker files to your project.

```bash
php artisan docker:install
```

Test the installation by running the following command:

```bash
make start-internal
```

If you want to make a new environment (not named `internal`), you can do so by running the following command:

```bash
php artisan docker:install -e {environment_name}
```

Replace `{environment_name}` with the name of your environment (e.g. `qa1`)

Start the new environment by running the following command:

```bash
make start-{environment_name}
```

To destroy the environment, run the following command:

```bash
make destroy-{environment_name}
```

To build fresh images, run the following command. This can be used in our CI/CD process.

```bash
make fresh-{environment_name}
```
