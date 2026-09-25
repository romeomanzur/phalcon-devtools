# Phalcon DevTools

[![Tests](https://github.com/phalcon/phalcon-devtools/actions/workflows/tests.yml/badge.svg?branch=5.0.x)](https://github.com/phalcon/phalcon-devtools/actions/workflows/tests.yml)
[![codecov](https://codecov.io/gh/phalcon/phalcon-devtools/branch/5.0.x/graph/badge.svg)](https://codecov.io/gh/phalcon/phalcon-devtools)
[![Latest Version](https://img.shields.io/packagist/v/phalcon/devtools.svg?style=flat-square)][:devtools:]
[![Software License](https://img.shields.io/badge/license-BSD--3-brightgreen.svg?style=flat-square)][:license:]
[![Total Downloads](https://img.shields.io/packagist/dt/phalcon/devtools.svg?style=flat-square)][:packagist:]
[![Daily Downloads](https://img.shields.io/packagist/dd/phalcon/devtools.svg?style=flat-square)][:packagist:]

## What's Phalcon?

Phalcon is an open-source PHP framework focused on performance, low overhead, and a clean, expressive API.

Phalcon 6 is consumed through Composer using the `phalcon/phalcon` package. This version of Phalcon DevTools does not require the legacy `ext-phalcon` extension.

## What are DevTools?

Phalcon DevTools provides command-line utilities and development tools for applications built with the Phalcon Framework.

It includes commands for generating projects, controllers, models, scaffolds and migrations, as well as WebTools and an interactive console.

## Requirements

- PHP `>= 8.2 < 9.0`
- Composer 2
- PDO (`ext-pdo`)
- Phalcon 6 (`phalcon/phalcon`)

Database-related commands also require the appropriate PDO driver, such as `pdo_mysql`, `pdo_pgsql`, or `pdo_sqlite`.

## Installing via Composer

The Phalcon 6-compatible DevTools work is currently available from the active development branch.

Some Phalcon companion packages used by DevTools are still published as development versions, so Composer must allow development dependencies while preferring stable packages:

```bash
composer config minimum-stability dev
composer config prefer-stable true
composer require --dev phalcon/devtools:5.0.x-dev
```

Composer exposes the DevTools executable in your project's `vendor/bin` directory:

```bash
vendor/bin/phalcon --help
```

Once a stable Phalcon 6-compatible DevTools release is published, projects will be able to use the corresponding stable version constraint instead.

## Installation via Git

Clone the repository and install its dependencies:

```bash
git clone https://github.com/phalcon/phalcon-devtools.git
cd phalcon-devtools
composer install
```

Run DevTools directly:

```bash
./phalcon --help
```

You can also create a symlink in a directory available in your `PATH`:

```bash
sudo ln -s "$(pwd)/phalcon" /usr/local/bin/phalcon
```

Then DevTools can be invoked globally:

```bash
phalcon --help
```

## Usage

To list the available commands:

```bash
phalcon commands help
```

The available commands include:

```text
info             (alias of: i)
commands         (alias of: list, enumerate)
controller       (alias of: create-controller)
module           (alias of: create-module)
model            (alias of: create-model)
all-models       (alias of: create-all-models)
project          (alias of: create-project)
scaffold         (alias of: create-scaffold)
migration        (alias of: create-migration)
webtools         (alias of: create-webtools)
serve            (alias of: server)
console          (alias of: shell, psysh)
```

Run command-specific help when additional options are needed:

```bash
phalcon model --help
phalcon scaffold --help
phalcon migration --help
```

## Database configuration

Commands that interact with a database require a database configuration.

For MySQL:

```php
<?php

use Phalcon\Config\Config;

return new Config([
    'database' => [
        'adapter'  => 'Mysql',
        'host'     => '127.0.0.1',
        'username' => 'root',
        'password' => '',
        'dbname'   => 'application',
        'port'     => 3306,
    ],
]);
```

For PostgreSQL:

```php
<?php

use Phalcon\Config\Config;

return new Config([
    'database' => [
        'adapter'  => 'Postgresql',
        'host'     => '127.0.0.1',
        'username' => 'postgres',
        'password' => '',
        'dbname'   => 'application',
        'port'     => 5432,
    ],
]);
```

## Migrations

Phalcon DevTools integrates with `phalcon/migrations` v4.

Common migration commands include:

```bash
phalcon migration generate
phalcon migration list
phalcon migration run
```

Migration behavior can also be configured in the application's configuration:

```php
'application' => [
    'migrationsDir'       => 'app/migrations',
    'migrationsTsBased'   => false,
    'logInDb'             => false,
    'no-auto-increment'   => false,
    'skip-ref-schema'     => false,
    'skip-foreign-checks' => false,
],
```

## DevTools configuration

A project-level DevTools configuration can define default options for individual commands.

For example:

```json
{
    "migration": {
        "migrations": "App/Migrations",
        "config": "App/Config/db.php"
    },
    "controller": {
        "namespace": "App\\Controllers",
        "directory": "App/Controllers",
        "base-class": "App\\Controllers\\BaseController"
    }
}
```

Command-line arguments override values defined in the DevTools configuration.

For example:

```bash
phalcon migration run
phalcon controller SomeClass
```

## Build `phalcon.phar`

Install the development dependencies:

```bash
composer install
```

Validate the Box configuration:

```bash
vendor/bin/box validate
```

Build the PHAR:

```bash
vendor/bin/box compile -v
```

Verify the generated PHAR:

```bash
php phalcon.phar --version
```

The release workflow performs the same validation, compilation, and smoke test before attaching `phalcon.phar` to a GitHub Release.

## Development

Install all dependencies:

```bash
composer install
```

Run the test suites:

```bash
vendor/bin/codecept run
```

Run PHPStan:

```bash
vendor/bin/phpstan analyse
```

Run Psalm:

```bash
vendor/bin/psalm --threads=1
```

Run PHP_CodeSniffer:

```bash
vendor/bin/phpcs
```

## Contributing

Contributions are welcome. Please read [CONTRIBUTING.md](CONTRIBUTING.md) before opening a pull request.

## License

Phalcon Developer Tools is open-source software licensed under the [New BSD License][:license:].

© Phalcon Framework Team and contributors

[:packagist:]: https://packagist.org/packages/phalcon/devtools
[:devtools:]: https://github.com/phalcon/phalcon-devtools
[:license:]: LICENSE.txt
