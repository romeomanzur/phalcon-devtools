<?php

/**
 * This file is part of the Phalcon Developer Tools.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\DevTools\Tests\Console;

use Codeception\Scenario;
use ConsoleTester;

/**
 * @var Scenario $scenario
 */

$I = new ConsoleTester($scenario);

$I->wantToTest('Generating and listing migrations');
$I->amInPath(dirname(app_path()));

$migrationsTestDir = app_path('migrations_test');
$configFile = app_path('mysql/config-migrations.php');
$noAutoIncrementDir = app_path('migrations_no_auto_increment_test');

/**
 * Clean up leftovers from previous failed/incomplete test runs.
 */
if (is_dir($migrationsTestDir)) {
    $I->deleteDir($migrationsTestDir);
}

if (is_dir($noAutoIncrementDir)) {
    $I->deleteDir($noAutoIncrementDir);
}

if (is_file($configFile)) {
    $I->deleteFile($configFile);
}

/**
 * Generate a regular migration.
 */
$I->runShellCommand(
    'phalcon migration generate --config=app/mysql/config.php --migrations=app/migrations_test ' .
    '--table=customers --version=1.0.0 --force'
);

$I->seeInShellOutput('Success: Version 1.0.0 was successfully generated');

$migrationFile = app_path('migrations_test/1.0.0/customers.php');

$I->seeFileFound($migrationFile);

$I->openFile($migrationFile);
$I->seeInThisFile("'autoIncrement' => true");

/**
 * List generated migrations.
 */
$I->runShellCommand(
    'phalcon migration list --config=app/mysql/config.php --migrations=app/migrations_test'
);

$I->seeInShellOutput('Version');
$I->seeInShellOutput('Was applied');
$I->seeInShellOutput('1.0.0');

/**
 * Create a migration configuration that disables preserving the current
 * AUTO_INCREMENT table value.
 */
$I->haveFile(
    $configFile,
    <<<'PHP'
<?php

use Phalcon\Config\Config;

return new Config([
    'database' => [
        'adapter'  => 'Mysql',
        'host'     => env('MYSQL_DB_HOST') ?: '127.0.0.1',
        'username' => env('MYSQL_DB_USERNAME') ?: 'root',
        'password' => env('MYSQL_DB_PASSWORD') ?: '',
        'dbname'   => env('MYSQL_DB_NAME') ?: 'devtools',
        'port'     => (int) (env('MYSQL_DB_PORT') ?: 3306),
    ],
    'application' => [
        'no-auto-increment' => true,
    ],
]);
PHP
);

/**
 * Generate another migration using no-auto-increment from the config file.
 */
$I->runShellCommand(
    'phalcon migration generate --config=app/mysql/config-migrations.php ' .
    '--migrations=app/migrations_no_auto_increment_test ' .
    '--table=customers --version=1.0.0 --force'
);

$I->seeInShellOutput('Success: Version 1.0.0 was successfully generated');

$noAutoIncrementFile = app_path(
    'migrations_no_auto_increment_test/1.0.0/customers.php'
);

$I->seeFileFound($noAutoIncrementFile);

$I->openFile($noAutoIncrementFile);

/**
 * no-auto-increment does not remove the AUTO_INCREMENT attribute from
 * the column. It prevents the current table AUTO_INCREMENT counter from
 * being preserved in the generated migration.
 */
$I->seeInThisFile("'autoIncrement' => true");
$I->seeInThisFile("'AUTO_INCREMENT' => ''");

/**
 * Clean up generated test files.
 */
$I->deleteDir($noAutoIncrementDir);
$I->deleteFile($configFile);
$I->deleteDir($migrationsTestDir);
