<?php

namespace PhpProjects\AuthDev\Database;

/**
 * Manages the database connection for the application.
 *
 * @package UrlShortener
 */
class DatabaseService
{
    /**
     * @var \PDO
     */
    private $pdo;

    /**
     * Creates a new database service with the given $pdo object.
     *
     * @param \PDO $pdo
     */
    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Returns the application's database connection.
     *
     * @return \PDO
     */
    public function getPdo() : \PDO
    {
        return $this->pdo;
    }

    /**
     * @var DatabaseService
     */
    private static $instance;

    /**
     * @var array
     */
    private static $defaultPdoParameters;

    /**
     * Sets the parameters used to create the pdo connection for the shared instance returned by getInstance()
     *
     * @param array $defaultPdoParameters
     */
    public static function setDefaultPdoParameters(array $defaultPdoParameters)
    {
        self::$defaultPdoParameters = $defaultPdoParameters;
    }

    /**
     * Returns a shared database service instance for the application
     *
     * @return DatabaseService
     */
    public static function getInstance() : DatabaseService
    {
        if (empty(self::$instance))
        {
            $pdo = new \PDO(...self::$defaultPdoParameters);
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            self::$instance = new self($pdo);
        }

        return self::$instance;
    }
}
