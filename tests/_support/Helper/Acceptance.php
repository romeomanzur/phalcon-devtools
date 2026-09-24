<?php
declare(strict_types=1);

namespace Helper;

use Codeception\Module;
use Codeception\TestInterface;
use PDO;
use RuntimeException;

class Acceptance extends Module
{
    /**
     * @var string
     */
    protected $projectName = 'web-tools-tests';

    /**
     * @var string
     */
    protected $projectPath;

    public function _initialize()
    {
        $this->projectPath = tests_path(
            '_data/acceptance/' . $this->projectName
        );
    }

    public function _before(TestInterface $test)
    {
        $driver = $test->getMetadata()->getCurrent('env');

        if (!empty($driver)) {
            $this->driver = $driver;
        }

        if (true === in_array($driver, ['mysql', 'pgsql'], true)) {
            $codeceptionDataFile = PATH_DATA .
                'acceptance' .
                DIRECTORY_SEPARATOR .
                $driver .
                DIRECTORY_SEPARATOR .
                'config.php';

            $targetWebtoolFile = PROJECT_PATH .
                'webtools' .
                DIRECTORY_SEPARATOR .
                'app' .
                DIRECTORY_SEPARATOR .
                'config' .
                DIRECTORY_SEPARATOR .
                'config.php';

            copy($codeceptionDataFile, $targetWebtoolFile);
        }

        parent::_before($test);
    }

    public function _after(TestInterface $test)
    {
        parent::_after($test);
    }

    /**
     * Restore the customers table to its original acceptance-test state.
     */
    public function resetCustomersFixture(): void
    {
        $fixtureFile = PATH_DATA .
            'schemas' .
            DIRECTORY_SEPARATOR .
            'mysql' .
            DIRECTORY_SEPARATOR .
            'customers.sql';

        if (!is_file($fixtureFile)) {
            throw new RuntimeException(
                sprintf(
                    'Customers fixture was not found: %s',
                    $fixtureFile
                )
            );
        }

        $sql = file_get_contents($fixtureFile);

        if (false === $sql) {
            throw new RuntimeException(
                sprintf(
                    'Unable to read customers fixture: %s',
                    $fixtureFile
                )
            );
        }

        $host = getenv('MYSQL_DB_HOST') ?: 'mysql';
        $port = getenv('MYSQL_DB_PORT') ?: '3306';
        $database = getenv('MYSQL_DB_NAME') ?: 'devtools';
        $username = getenv('MYSQL_DB_USERNAME') ?: 'devtools';
        $password = getenv('MYSQL_DB_PASSWORD') ?: 'password';

        $pdo = new PDO(
            sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=utf8',
                $host,
                $port,
                $database
            ),
            $username,
            $password,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]
        );

        $statements = preg_split(
            '/;\s*(?:\r?\n|$)/',
            $sql,
            -1,
            PREG_SPLIT_NO_EMPTY
        );

        if (false === $statements) {
            throw new RuntimeException(
                'Unable to parse customers fixture.'
            );
        }

        foreach ($statements as $statement) {
            $statement = trim($statement);

            if ('' !== $statement) {
                $pdo->exec($statement);
            }
        }
    }
}