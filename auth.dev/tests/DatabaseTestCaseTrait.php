<?php

namespace PhpProjects\AuthDev;

use PHPUnit_Extensions_Database_DB_IDatabaseConnection;

/**
 * Custom test case trait for implementing database interactivity with our tests.
 */
trait DatabaseTestCaseTrait
{
    /**
     * It is important to note that you never really extend one trait with another. You can do effectively the same 
     * thing by including the trait with 'use'
     */
    use \PHPUnit_Extensions_Database_TestCase_Trait;

    /**
     * @var PHPUnit_Extensions_Database_DB_IDatabaseConnection
     */
    private static $connection;

    /**
     * @var \PDO
     */
    private static $pdo;

    /**
     * Returns the test database connection.
     *
     * @return PHPUnit_Extensions_Database_DB_IDatabaseConnection
     */
    protected function getConnection()
    {
        if (empty(self::$connection))
        {
            self::$connection = new \PHPUnit_Extensions_Database_DB_DefaultDatabaseConnection($this->getPdo());
        }

        return self::$connection;
    }

    /**
     * @return \PDO
     */
    protected function getPdo()
    {
        if (empty(self::$pdo))
        {
            self::$pdo = new \PDO('sqlite:' . __DIR__ . '/../data/' . TEST_DATABASE_FILE);
        }
        return self::$pdo;
    }
}