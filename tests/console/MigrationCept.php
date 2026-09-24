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

if (is_dir($migrationsTestDir)) {
    $I->deleteDir($migrationsTestDir);
}

$I->runShellCommand(
    'phalcon migration generate --config=app/mysql/config.php --migrations=app/migrations_test ' .
    '--table=customers --version=1.0.0 --force'
);

$I->seeInShellOutput('Success: Version 1.0.0 was successfully generated');

$migrationFile = app_path('migrations_test/1.0.0/customers.php');

$I->seeFileFound($migrationFile);

$I->runShellCommand(
    'phalcon migration list --config=app/mysql/config.php --migrations=app/migrations_test'
);

$I->seeInShellOutput('Version');
$I->seeInShellOutput('Was applied');
$I->seeInShellOutput('1.0.0');

$I->deleteDir($migrationsTestDir);