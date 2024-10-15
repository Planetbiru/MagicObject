<?php

namespace MagicObject\Database;

use MagicObject\SecretObject;

/**
 * PicoDatabaseCredentials class
 * 
 * This class encapsulates database credentials and utilizes the SecretObject to encrypt all attributes,
 * ensuring the security of database configuration details from unauthorized access.
 * 
 * It provides getter methods to retrieve database connection parameters such as driver, host, port,
 * username, password, database name, schema, and application time zone.
 * 
 * Example usage:
 * ```php
 * $credentials = new PicoDatabaseCredentials();
 * $credentials->setHost('localhost');
 * $credentials->setUsername('user');
 * $credentials->setPassword('password');
 * ```
 * 
 * The attributes are automatically encrypted when set, providing a secure way to handle sensitive
 * information within your application.
 * 
 * @link https://github.com/Planetbiru/MagicObject
 */
class PicoDatabaseCredentials extends SecretObject
{
    /**
     * Database driver (e.g., 'mysql', 'pgsql').
     *
     * @var string
     */
    protected $driver = 'mysql';

    /**
     * Database server host.
     *
     * @EncryptIn
     * @DecryptOut
     * @var string
     */
    protected $host = 'localhost';

    /**
     * Database server port.
     *
     * @var int
     */
    protected $port = 3306;

    /**
     * Database username.
     *
     * @EncryptIn
     * @DecryptOut
     * @var string
     */
    protected $username = "";

    /**
     * Database user password.
     *
     * @EncryptIn
     * @DecryptOut
     * @var string
     */
    protected $password = "";

    /**
     * Database name.
     *
     * @EncryptIn
     * @DecryptOut
     * @var string
     */
    protected $databaseName = "";

    /**
     * Database schema (default: 'public').
     *
     * @EncryptIn
     * @DecryptOut
     * @var string
     */
    protected $databaseSchema = "public"; 

    /**
     * Application time zone.
     *
     * @var string
     */
    protected $timeZone = "Asia/Jakarta";

    /**
     * Get the database driver.
     *
     * @return string Returns the database driver.
     */
    public function getDriver()
    {
        return $this->driver;
    }

    /**
     * Get the database host.
     *
     * @return string Returns the database host.
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * Get the database port.
     *
     * @return int Returns the database port.
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * Get the database username.
     *
     * @return string Returns the database username.
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Get the database password.
     *
     * @return string Returns the database password.
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Get the database name.
     *
     * @return string Returns the database name.
     */
    public function getDatabaseName()
    {
        return $this->databaseName;
    }

    /**
     * Get the database schema.
     *
     * @return string Returns the database schema.
     */
    public function getDatabaseSchema()
    {
        return $this->databaseSchema;
    }

    /**
     * Get the application time zone.
     *
     * @return string Returns the application time zone.
     */
    public function getTimeZone()
    {
        return $this->timeZone;
    }
}
