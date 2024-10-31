<?php

namespace MagicObject\Database;

/**
 * Class PicoDatabaseType
 *
 * This class defines constants representing various database types 
 * supported by the MagicObject framework. It provides a centralized 
 * way to reference these types, improving code clarity and maintainability.
 * 
 * Supported database types include MySQL, MariaDB, PostgreSQL, and SQLite.
 * 
 * @author Kamshory
 * @package MagicObject\Database
 * @link https://github.com/Planetbiru/MagicObject
 */
class PicoDatabaseType
{
    /**
     * Constant for MySQL database type.
     *
     * @var string
     */
    const DATABASE_TYPE_MYSQL = "mysql";

    /**
     * Constant for MariaDB database type.
     *
     * @var string
     */
    const DATABASE_TYPE_MARIADB = "mariadb";

    /**
     * Constant for PostgreSQL database type.
     *
     * @var string
     */
    const DATABASE_TYPE_POSTGRESQL = "postgresql";

    /**
     * Constant for SQLite database type.
     *
     * @var string
     */
    const DATABASE_TYPE_SQLITE = "sqlite";
}
