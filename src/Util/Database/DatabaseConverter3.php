<?php

namespace MagicObject\Util\Database;

use MagicObject\Exceptions\DatabaseConversionException;

/**
 * Class DatabaseConverter
 *
 * This class is responsible for translating database query structures between different SQL dialects.
 * It takes SQL queries written in one dialect (e.g., MySQL) and converts them into another dialect (e.g., PostgreSQL, SQLite).
 * The class handles differences in syntax, keywords, functions, and other database-specific features.
 *
 * Key functionalities of this class include:
 * - Translating data types between different SQL flavors.
 * - Adjusting query syntax to match the conventions of different database systems.
 * - Converting SQL-specific expressions like `AUTO_INCREMENT` to equivalent expressions in other databases.
 *
 * This class is typically used when migrating databases or working with systems that need to support multiple database engines.
 *
 * @package MagicObject\Util\Database
 */
class DatabaseConverter3 // NOSONAR
{
    /**
     * Array mapping of database field types to SQLite data types.
     *
     * @var array
     */
    private $dbToSqlite;

    /**
     * Array mapping of database field types to MySQL data types.
     *
     * @var array
     */
    private $dbToMySQL;

    /**
     * Array mapping of database field types to PostgreSQL data types.
     *
     * @var array
     */
    private $dbToPostgreSQL;

    public function __construct() {
        $this->dbToSqlite = [
            "int" => "INTEGER",
            "tinyint(1)" => "BOOLEAN", // NOSONAR
            "tinyint" => "INTEGER",
            "smallint" => "INTEGER",
            "mediumint" => "INTEGER",
            "bigint" => "INTEGER",
            "real" => "REAL",
            "float" => "REAL",
            "double" => "REAL",
            "decimal" => "REAL",
            "nvarchar" => "NVARCHAR",
            "varchar" => "NVARCHAR",
            "character varying" => "NVARCHAR", // NOSONAR
            "char" => "NVARCHAR",
            "tinytext" => "TEXT",
            "mediumtext" => "TEXT",
            "longtext" => "TEXT",
            "text" => "TEXT",
            "datetime" => "DATETIME",
            "timestamp" => "TIMESTAMP",
            "date" => "DATE",
            "time" => "TIME",
            "year" => "INTEGER",
            "boolean" => "INTEGER",
            "json" => "TEXT",
            "jsonb" => "TEXT",
            "integer" => "INTEGER",
            "serial" => "INTEGER",
            "bigserial" => "INTEGER",
            "double precision" => "REAL",
            "timestamptz" => "TIMESTAMP"
        ];

        $this->dbToMySQL = [
            "bigint" => "BIGINT",
            "mediumint" => "MEDIUMINT",
            "smallint" => "SMALLINT",
            "integer" => "INT",
            "double" => "DOUBLE",
            "float" => "FLOAT",
            "real" => "DOUBLE",
            "decimal" => "DECIMAL",
            "numeric" => "NUMERIC",
            "tinytext" => "TINYTEXT",
            "mediumtext" => "MEDIUMTEXT",
            "longtext" => "LONGTEXT",
            "text" => "TEXT",
            "nvarchar" => "VARCHAR",
            "varchar" => "VARCHAR",
            "character varying" => "VARCHAR",
            "tinyint(1)" => "TINYINT(1)", // NOSONAR
            "tinyint" => "TINYINT",
            "boolean" => "TINYINT(1)",
            "int" => "INT",
            "datetime" => "DATETIME",
            "date" => "DATE",
            "timestamptz" => "TIMESTAMP",
            "timestamp with time zone" => "TIMESTAMP",
            "timestamp without time zone" => "DATETIME",
            "timestamp" => "TIMESTAMPTZ",
            "json" => "JSON",
            "enum" => "ENUM",
            "set" => "SET",
            "char" => "CHAR"
        ];

        $this->dbToPostgreSQL = [
            "bigint" => "BIGINT",
            "mediumint" => "INTEGER",
            "smallint" => "INTEGER",
            "tinyint(1)" => "BOOLEAN",
            "tinyint" => "INTEGER",
            "integer" => "INTEGER",
            "real" => "REAL",
            "longtext" => "TEXT",
            "mediumtext" => "TEXT",
            "smalltext" => "TEXT",
            "tinytext" => "TEXT",
            "text" => "TEXT",
            "character varying" => "CHARACTER VARYING", // NOSONAR
            "nvarchar" => "CHARACTER VARYING",
            "varchar" => "CHARACTER VARYING",
            "char" => "CHARACTER",
            "boolean" => "BOOLEAN",
            "datetime" => "TIMESTAMP WITHOUT TIME ZONE", // NOSONAR
            "date" => "DATE",
            "timestamptz" => "TIMESTAMP WITH TIME ZONE", // NOSONAR
            "timestamp" => "TIMESTAMP WITH TIME ZONE", // NOSONAR
            "time" => "TIME",
            "json" => "JSONB"
        ];
    }

    /**
     * Translates a database field type from a source dialect to a target dialect.
     * This function primarily maps the base type, while specific translation methods
     * handle modifiers like AUTO_INCREMENT, SERIAL, BOOLEAN defaults, etc.
     *
     * @param string $type The field type to translate (e.g., "VARCHAR(255)", "INT").
     * @param string $sourceDialect The source database dialect (e.g., 'mysql', 'postgresql', 'sqlite').
     * @param string $targetDialect The target database dialect (e.g., 'mysql', 'postgresql', 'sqlite').
     * @return string The translated base field type.
     * @throws DatabaseConversionException If an unsupported target dialect is provided.
     */
    private function translateFieldType($type, $sourceDialect, $targetDialect) // NOSONAR
    {
        $type = strtolower(trim($type));
        // Normalize spaces
        $type = preg_replace('/\s+/', ' ', $type); // NOSONAR

        $targetMap = [];
        switch ($targetDialect) {
            case 'mysql': {
                $targetMap = $this->dbToMySQL;
                break;
            }
            case 'postgresql': {
                $targetMap = $this->dbToPostgreSQL;
                break;
            }
            case 'sqlite': {
                $targetMap = $this->dbToSqlite;
                break;
            }
            default: {
                throw new DatabaseConversionException("Unsupported target dialect: " . $targetDialect);
            }
        }

        // Extract base type and any parameters (e.g., "VARCHAR(255)" -> "varchar", "(255)")
        $baseType = $type;
        $params = '';
        if (preg_match('/^([a-zA-Z_]+)(\s*\(.*\))?$/', $type, $matches)) {
            $baseType = $matches[1];
            if (isset($matches[2])) {
                $params = $matches[2];
            }
        }

        // Handle specific type conversions that are not just direct map lookups
        if ($sourceDialect === 'mysql') {
            if ($baseType === 'enum' || $baseType === 'set') {
                return 'TEXT';
            }
            if ($baseType === 'tinyint' && $params === '(1)') {
                if ($targetDialect === 'postgresql') {
                    return 'BOOLEAN';
                }
                if ($targetDialect === 'sqlite') {
                    return 'INTEGER';
                }
            }
        } elseif ($sourceDialect === 'postgresql') {
            if ($baseType === 'serial' || $baseType === 'bigserial') {
                if ($targetDialect === 'mysql') {
                    return ($baseType === 'bigserial' ? 'BIGINT' : 'INT'); // NOSONAR
                }
                if ($targetDialect === 'sqlite') {
                    return 'INTEGER';
                }
            }
            if ($baseType === 'boolean') {
                if ($targetDialect === 'mysql') {
                    return 'TINYINT(1)';
                }
                if ($targetDialect === 'sqlite') {
                    return 'INTEGER';
                }
            }
            if ($baseType === 'jsonb') {
                if ($targetDialect === 'mysql') {
                    return 'JSON';
                }
                if ($targetDialect === 'sqlite') {
                    return 'TEXT';
                }
            }
            if ($baseType === 'timestamp') {
                if (str_contains($type, 'with time zone')) {
                    if ($targetDialect === 'mysql') {
                        return 'TIMESTAMP';
                    }
                    if ($targetDialect === 'sqlite') {
                        return 'DATETIME';
                    }
                } elseif (str_contains($type, 'without time zone')) {
                    if ($targetDialect === 'mysql') {
                        return 'DATETIME';
                    }
                    if ($targetDialect === 'sqlite') {
                        return 'DATETIME';
                    }
                }
            }
        } elseif ($sourceDialect === 'sqlite') {
            if ($baseType === 'datetime') {
                if ($targetDialect === 'mysql') {
                    return 'DATETIME';
                }
                if ($targetDialect === 'postgresql') {
                    return 'TIMESTAMP WITHOUT TIME ZONE';
                }
            }
        }

        // Fallback to direct mapping of base type
        if (isset($targetMap[$baseType])) {
            $translatedBaseType = $targetMap[$baseType];

            if (
                str_contains(strtoupper($translatedBaseType), 'VARCHAR') ||
                str_contains(strtoupper($translatedBaseType), 'CHARACTER VARYING') ||
                str_contains(strtoupper($translatedBaseType), 'CHAR')
            ) {
                return $translatedBaseType . $params;
            }

            return $translatedBaseType;
        }

        return strtoupper($type);
    }


    /**
     * Helper to normalize quotes for identifiers.
     *
     * @param string $identifier
     * @param string $dialect
     * @return string
     */
    private function quoteIdentifier($identifier, $dialect)
    {
        $identifier = trim($identifier, "`\"[]"); // Remove existing quotes
        switch ($dialect) {
            case 'mysql':
                return "`" . $identifier . "`";
            case 'postgresql':
            case 'sqlite':
                return "\"" . $identifier . "\"";
            default:
                return $identifier;
        }
    }

    /**
     * Translates a CREATE TABLE statement from MySQL to PostgreSQL.
     *
     * @param string $sql The MySQL CREATE TABLE statement.
     * @return string The translated PostgreSQL CREATE TABLE statement.
     * @throws DatabaseConversionException If the SQL format is invalid.
     */
    public function mysqlToPostgreSQL($sql) // NOSONAR
    {
        $sql = trim($sql);
        $sql = preg_replace('/\s+/', ' ', $sql); // Normalize spaces

        // Extract table name
        if (!preg_match('/CREATE TABLE (`?)([^`\s]+)(`?)\s*\((.*)\)/is', $sql, $matches)) {
            throw new DatabaseConversionException("Invalid MySQL CREATE TABLE statement format.");
        }
        $tableName = $this->quoteIdentifier($matches[2], 'postgresql');
        $columnsAndConstraints = trim($matches[4]);

        $lines = explode(',', $columnsAndConstraints);
        $newLines = [];
        $tableConstraints = [];
        $ifNotExists = str_contains(strtoupper($sql), 'IF NOT EXISTS') ? 'IF NOT EXISTS ' : ''; // NOSONAR

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) 
            {
                continue;
            }

            // Column definition
            if (preg_match('/^(`?)([^`\s]+)(`?)\s+([a-zA-Z0-9_() ]+)(.*)$/i', $line, $colMatches)) // NOSONAR
            {
                $columnName = $this->quoteIdentifier($colMatches[2], 'postgresql');
                $columnType = strtolower(trim($colMatches[4]));
                $columnDefinition = trim($colMatches[5]);

                $translatedType = $this->translateFieldType($columnType, 'mysql', 'postgresql');

                // Handle AUTO_INCREMENT
                if (str_contains(strtoupper($columnDefinition), 'AUTO_INCREMENT')) {
                    if (str_contains(strtoupper($columnType), 'BIGINT')) {
                        $translatedType = 'BIGSERIAL';
                    } else {
                        $translatedType = 'SERIAL';
                    }
                    $columnDefinition = str_ireplace('AUTO_INCREMENT', '', $columnDefinition);
                    // If it's AUTO_INCREMENT, it's implicitly PRIMARY KEY in MySQL, so ensure it is in PG
                    if (!str_contains(strtoupper($columnDefinition), 'PRIMARY KEY')) // NOSONAR
                    {
                         $columnDefinition .= ' PRIMARY KEY';
                    }
                }

                // Handle BOOLEAN (TINYINT(1))
                if (str_contains(strtoupper($columnType), 'TINYINT(1)')) {
                    $translatedType = 'BOOLEAN';
                    $columnDefinition = str_ireplace("DEFAULT '1'", "DEFAULT TRUE", $columnDefinition); // NOSONAR
                    $columnDefinition = str_ireplace("DEFAULT '0'", "DEFAULT FALSE", $columnDefinition); // NOSONAR
                }

                // Handle DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                if (str_contains(strtoupper($columnDefinition), 'ON UPDATE CURRENT_TIMESTAMP')) // NOSONAR
                {
                    $columnDefinition = str_ireplace('ON UPDATE CURRENT_TIMESTAMP', '', $columnDefinition);
                    // Note: ON UPDATE requires a trigger in PostgreSQL, not a column definition.
                    // For simplicity, we just remove it here.
                }
                
                // Handle ENUM/SET (already done in translateFieldType, but ensure no leftover params)
                if (str_contains(strtoupper($columnType), 'ENUM') || str_contains(strtoupper($columnType), 'SET')) {
                    $translatedType = 'TEXT';
                }

                $newLines[] = $columnName . ' ' . $translatedType . ' ' . trim($columnDefinition);
            } elseif (preg_match('/^(PRIMARY KEY|UNIQUE KEY)\s*(`?)([^`\s]+)(`?)\s*\((.*)\)/i', $line, $keyMatches)) {
                // Table-level PRIMARY KEY or UNIQUE KEY
                $keyType = strtoupper($keyMatches[1]);
                $keyName = $this->quoteIdentifier($keyMatches[3], 'postgresql');
                $keyColumns = $keyMatches[5];
                
                // Replace backticks with double quotes in column list
                $keyColumns = preg_replace('/`([^`]+)`/', '"$1"', $keyColumns);

                if ($keyType === 'PRIMARY KEY') 
                {
                    $tableConstraints[] = 'PRIMARY KEY (' . $keyColumns . ')'; // NOSONAR
                } 
                elseif ($keyType === 'UNIQUE KEY') // NOSONAR
                {
                    $tableConstraints[] = 'CONSTRAINT ' . $keyName . ' UNIQUE (' . $keyColumns . ')';
                }
            } else {
                // Other constraints or unparsed parts, try to add as is, but quote identifiers
                $line = preg_replace_callback('/(`)([^`]+)(`)/', function($m) {
                    return $this->quoteIdentifier($m[2], 'postgresql');
                }, $line);
                $newLines[] = $line;
            }
        }

        if (!empty($tableConstraints)) {
            $newLines = array_merge($newLines, $tableConstraints);
        }

        // Clean up table options (ENGINE, CHARSET, COLLATE)
        $finalSql = "CREATE TABLE " . $ifNotExists . $tableName . " (\n    " . implode(",\n    ", $newLines) . "\n)"; // NOSONAR
        $finalSql = preg_replace('/\s+ENGINE=[a-zA-Z0-9_]+\s*/i', '', $finalSql); // NOSONAR
        $finalSql = preg_replace('/\s+DEFAULT CHARSET=[a-zA-Z0-9_]+\s*/i', '', $finalSql); // NOSONAR
        $finalSql = preg_replace('/\s+COLLATE=[a-zA-Z0-9_]+\s*/i', '', $finalSql); // NOSONAR
        $finalSql = preg_replace('/\s+COMMENT\s+\'[^\']+\'/i', '', $finalSql); // Remove column comments

        return trim($finalSql) . ';';
    }

    /**
     * Translates a CREATE TABLE statement from MySQL to SQLite.
     *
     * @param string $sql The MySQL CREATE TABLE statement.
     * @return string The translated SQLite CREATE TABLE statement.
     * @throws DatabaseConversionException If the SQL format is invalid.
     */
    public function mysqlToSQLite($sql) // NOSONAR
    {
        $sql = trim($sql);
        $sql = preg_replace('/\s+/', ' ', $sql); // Normalize spaces

        // Extract table name
        if (!preg_match('/CREATE TABLE (`?)([^`\s]+)(`?)\s*\((.*)\)/is', $sql, $matches)) {
            throw new DatabaseConversionException("Invalid MySQL CREATE TABLE statement format.");
        }
        $tableName = $this->quoteIdentifier($matches[2], 'sqlite');
        $columnsAndConstraints = trim($matches[4]);

        $lines = explode(',', $columnsAndConstraints);
        $newLines = [];
        $primaryKeyColumnFound = false;
        $ifNotExists = str_contains(strtoupper($sql), 'IF NOT EXISTS') ? 'IF NOT EXISTS ' : '';

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line))
            {
                continue;
            }

            // Column definition
            if (preg_match('/^(`?)([^`\s]+)(`?)\s+([a-zA-Z0-9_() ]+)(.*)$/i', $line, $colMatches)) // NOSONAR
            {
                $columnName = $this->quoteIdentifier($colMatches[2], 'sqlite');
                $columnType = strtolower(trim($colMatches[4]));
                $columnDefinition = trim($colMatches[5]);

                $translatedType = $this->translateFieldType($columnType, 'mysql', 'sqlite');

                // Handle AUTO_INCREMENT for INTEGER PRIMARY KEY AUTOINCREMENT
                if (str_contains(strtoupper($columnDefinition), 'AUTO_INCREMENT')) {
                    if (str_contains(strtoupper($columnType), 'INT') && str_contains(strtoupper($columnDefinition), 'PRIMARY KEY')) {
                        $translatedType = 'INTEGER'; // SQLite only uses INTEGER for AUTOINCREMENT
                        $columnDefinition = str_ireplace('AUTO_INCREMENT', 'AUTOINCREMENT', $columnDefinition);
                        $primaryKeyColumnFound = true;
                    } else {
                        // If AUTO_INCREMENT but not primary key or not int, just remove it
                        $columnDefinition = str_ireplace('AUTO_INCREMENT', '', $columnDefinition);
                    }
                }

                // Handle BOOLEAN (TINYINT(1))
                if (str_contains(strtoupper($columnType), 'TINYINT(1)')) {
                    $translatedType = 'INTEGER'; // SQLite uses INTEGER for BOOLEAN
                    $columnDefinition = str_ireplace("DEFAULT '1'", "DEFAULT 1", $columnDefinition); // NOSONAR
                    $columnDefinition = str_ireplace("DEFAULT '0'", "DEFAULT 0", $columnDefinition); // NOSONAR
                }
                
                // Handle ENUM/SET
                if (str_contains(strtoupper($columnType), 'ENUM') || str_contains(strtoupper($columnType), 'SET')) {
                    $translatedType = 'TEXT';
                }

                // Handle DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                if (str_contains(strtoupper($columnDefinition), 'ON UPDATE CURRENT_TIMESTAMP')) {
                    $columnDefinition = str_ireplace('ON UPDATE CURRENT_TIMESTAMP', '', $columnDefinition);
                    // Note: ON UPDATE requires a trigger in SQLite, not a column definition.
                }

                $newLines[] = $columnName . ' ' . $translatedType . ' ' . trim($columnDefinition);
            } elseif (preg_match('/^(PRIMARY KEY|UNIQUE KEY)\s*(`?)([^`\s]+)(`?)\s*\((.*)\)/i', $line, $keyMatches)) {
                // Table-level PRIMARY KEY or UNIQUE KEY
                $keyType = strtoupper($keyMatches[1]);
                $keyColumns = $keyMatches[5];
                
                // Replace backticks with double quotes in column list
                $keyColumns = preg_replace('/`([^`]+)`/', '"$1"', $keyColumns);

                if ($keyType === 'PRIMARY KEY' && !$primaryKeyColumnFound) {
                    $newLines[] = 'PRIMARY KEY (' . $keyColumns . ')';
                } elseif ($keyType === 'UNIQUE KEY') {
                    $newLines[] = 'UNIQUE (' . $keyColumns . ')';
                }
            } else {
                // Other constraints or unparsed parts, try to add as is, but quote identifiers
                $line = preg_replace_callback('/(`)([^`]+)(`)/', function($m) {
                    return $this->quoteIdentifier($m[2], 'sqlite');
                }, $line);
                $newLines[] = $line;
            }
        }

        // Clean up table options
        $finalSql = "CREATE TABLE " . $ifNotExists . $tableName . " (\n    " . implode(",\n    ", $newLines) . "\n)";
        $finalSql = preg_replace('/\s+ENGINE=[a-zA-Z0-9_]+\s*/i', '', $finalSql); // NOSONAR
        $finalSql = preg_replace('/\s+DEFAULT CHARSET=[a-zA-Z0-9_]+\s*/i', '', $finalSql); // NOSONAR
        $finalSql = preg_replace('/\s+COLLATE=[a-zA-Z0-9_]+\s*/i', '', $finalSql); // NOSONAR
        $finalSql = preg_replace('/\s+COMMENT\s+\'[^\']+\'/i', '', $finalSql); // Remove column comments

        return trim($finalSql) . ';';
    }

    /**
     * Translates a CREATE TABLE statement from PostgreSQL to MySQL.
     *
     * @param string $sql The PostgreSQL CREATE TABLE statement.
     * @return string The translated MySQL CREATE TABLE statement.
     * @throws DatabaseConversionException If the SQL format is invalid.
     */
    public function postgresqlToMySQL($sql) // NOSONAR
    {
        $sql = trim($sql);
        $sql = preg_replace('/\s+/', ' ', $sql); // Normalize spaces

        // Extract table name
        if (!preg_match('/CREATE TABLE (IF NOT EXISTS\s+)?("?)([^"\s]+)("?)\s*\((.*)\)/is', $sql, $matches)) // NOSONAR
        {
            throw new DatabaseConversionException("Invalid PostgreSQL CREATE TABLE statement format.");
        }
        $ifNotExists = isset($matches[1]) ? 'IF NOT EXISTS ' : '';
        $tableName = $this->quoteIdentifier($matches[3], 'mysql');
        $columnsAndConstraints = trim($matches[5]);

        $lines = explode(',', $columnsAndConstraints);
        $newLines = [];
        $tableConstraints = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) 
            {
                continue;
            }
            // Column definition
            if (preg_match('/^("?)([^"\s]+)("?)\s+([a-zA-Z0-9_() ]+)(.*)$/i', $line, $colMatches)) // NOSONAR
            {
                $columnName = $this->quoteIdentifier($colMatches[2], 'mysql');
                $columnType = strtolower(trim($colMatches[4]));
                $columnDefinition = trim($colMatches[5]);

                $translatedType = $this->translateFieldType($columnType, 'postgresql', 'mysql');

                // Handle SERIAL/BIGSERIAL
                if (str_contains(strtoupper($columnType), 'SERIAL')) {
                    if (str_contains(strtoupper($columnType), 'BIGSERIAL')) {
                        $translatedType = 'BIGINT';
                    } else {
                        $translatedType = 'INT';
                    }
                    $columnDefinition = str_ireplace('PRIMARY KEY', '', $columnDefinition); // Remove PG's implicit PK
                    $columnDefinition .= ' AUTO_INCREMENT PRIMARY KEY'; // Add MySQL's AUTO_INCREMENT PK
                }

                // Handle BOOLEAN
                if (str_contains(strtoupper($columnType), 'BOOLEAN')) {
                    $translatedType = 'TINYINT(1)';
                    $columnDefinition = str_ireplace("DEFAULT TRUE", "DEFAULT '1'", $columnDefinition);
                    $columnDefinition = str_ireplace("DEFAULT FALSE", "DEFAULT '0'", $columnDefinition);
                }

                // Handle TIMESTAMP WITH TIME ZONE / WITHOUT TIME ZONE
                if (str_contains(strtoupper($columnType), 'TIMESTAMP WITH TIME ZONE')) {
                    $translatedType = 'TIMESTAMP';
                } elseif (str_contains(strtoupper($columnType), 'TIMESTAMP WITHOUT TIME ZONE')) {
                    $translatedType = 'DATETIME';
                }
                
                // Handle JSONB
                if (str_contains(strtoupper($columnType), 'JSONB')) {
                    $translatedType = 'JSON';
                }

                $newLines[] = $columnName . ' ' . $translatedType . ' ' . trim($columnDefinition);
            } 
            elseif (preg_match('/^(PRIMARY KEY|UNIQUE)\s*\((.*)\)/i', $line, $keyMatches)) // NOSONAR
            {
                // Table-level PRIMARY KEY or UNIQUE
                $keyType = strtoupper($keyMatches[1]);
                $keyColumns = $keyMatches[2];
                
                // Replace double quotes with backticks in column list
                $keyColumns = preg_replace('/"([^"]+)"/', '`$1`', $keyColumns); // NOSONAR

                if ($keyType === 'PRIMARY KEY') {
                    $tableConstraints[] = 'PRIMARY KEY (' . $keyColumns . ')';
                } elseif ($keyType === 'UNIQUE') {
                    // PostgreSQL's UNIQUE can be a constraint without a name, or named.
                    // MySQL's UNIQUE KEY can be named or unnamed.
                    // If it's a simple UNIQUE (col), we can just add UNIQUE to the column definition.
                    // If it's a named constraint, add it as a table constraint.
                    if (preg_match('/^CONSTRAINT\s+"?([^"]+)"?\s+UNIQUE\s*\((.*)\)/i', $line, $constraintMatches)) {
                        $constraintName = $this->quoteIdentifier($constraintMatches[1], 'mysql');
                        $tableConstraints[] = 'UNIQUE KEY ' . $constraintName . ' (' . $keyColumns . ')';
                    } else {
                        $tableConstraints[] = 'UNIQUE KEY (' . $keyColumns . ')';
                    }
                }
            } else {
                // Other constraints or unparsed parts, try to add as is, but quote identifiers
                $line = preg_replace_callback('/(")([^"]+?)(")/', function($m) // NOSONAR
                {
                    return $this->quoteIdentifier($m[2], 'mysql');
                }, $line);
                $newLines[] = $line;
            }
        }

        if (!empty($tableConstraints)) {
            $newLines = array_merge($newLines, $tableConstraints);
        }

        $finalSql = "CREATE TABLE " . $ifNotExists . $tableName . " (\n    " . implode(",\n    ", $newLines) . "\n)";
        $finalSql .= "\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;"; // Add common MySQL table options

        return trim($finalSql) . ';';
    }

    /**
     * Translates a CREATE TABLE statement from PostgreSQL to SQLite.
     *
     * @param string $sql The PostgreSQL CREATE TABLE statement.
     * @return string The translated SQLite CREATE TABLE statement.
     * @throws DatabaseConversionException If the SQL format is invalid.
     */
    public function postgresqlToSQLite($sql) // NOSONAR
    {
        $sql = trim($sql);
        $sql = preg_replace('/\s+/', ' ', $sql); // Normalize spaces

        // Extract table name
        if (!preg_match('/CREATE TABLE (IF NOT EXISTS\s+)?("?)([^"\s]+)("?)\s*\((.*)\)/is', $sql, $matches)) {
            throw new DatabaseConversionException("Invalid PostgreSQL CREATE TABLE statement format.");
        }
        $ifNotExists = isset($matches[1]) ? 'IF NOT EXISTS ' : '';
        $tableName = $this->quoteIdentifier($matches[3], 'sqlite');
        $columnsAndConstraints = trim($matches[5]);

        $lines = explode(',', $columnsAndConstraints);
        $newLines = [];
        $primaryKeyColumnFound = false;

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) 
            {
                continue;
            }

            // Column definition
            if (preg_match('/^("?)([^"\s]+)("?)\s+([a-zA-Z0-9_() ]+)(.*)$/i', $line, $colMatches)) // NOSONAR
            {
                $columnName = $this->quoteIdentifier($colMatches[2], 'sqlite');
                $columnType = strtolower(trim($colMatches[4]));
                $columnDefinition = trim($colMatches[5]);

                $translatedType = $this->translateFieldType($columnType, 'postgresql', 'sqlite');

                // Handle SERIAL/BIGSERIAL
                if (str_contains(strtoupper($columnType), 'SERIAL')) {
                    $translatedType = 'INTEGER';
                    if (str_contains(strtoupper($columnDefinition), 'PRIMARY KEY')) {
                        $columnDefinition = str_ireplace('PRIMARY KEY', 'AUTOINCREMENT', $columnDefinition);
                        $primaryKeyColumnFound = true;
                    } else {
                        // If SERIAL but not primary key, just make it INTEGER
                        $columnDefinition = str_ireplace('PRIMARY KEY', '', $columnDefinition); // Remove any PRIMARY KEY if not main autoinc
                    }
                }

                // Handle BOOLEAN
                if (str_contains(strtoupper($columnType), 'BOOLEAN')) {
                    $translatedType = 'INTEGER'; // SQLite uses INTEGER for BOOLEAN
                    $columnDefinition = str_ireplace("DEFAULT TRUE", "DEFAULT 1", $columnDefinition);
                    $columnDefinition = str_ireplace("DEFAULT FALSE", "DEFAULT 0", $columnDefinition);
                }

                // Handle TIMESTAMP WITH TIME ZONE / WITHOUT TIME ZONE
                if (str_contains(strtoupper($columnType), 'TIMESTAMP WITH TIME ZONE') || str_contains(strtoupper($columnType), 'TIMESTAMP WITHOUT TIME ZONE')) {
                    $translatedType = 'DATETIME'; // SQLite doesn't have explicit TIMESTAMP types, DATETIME is common
                }
                
                // Handle JSONB
                if (str_contains(strtoupper($columnType), 'JSONB')) {
                    $translatedType = 'TEXT';
                }

                $newLines[] = $columnName . ' ' . $translatedType . ' ' . trim($columnDefinition);
            } elseif (preg_match('/^(PRIMARY KEY|UNIQUE)\s*\((.*)\)/i', $line, $keyMatches)) {
                // Table-level PRIMARY KEY or UNIQUE
                $keyType = strtoupper($keyMatches[1]);
                $keyColumns = $keyMatches[2];
                
                // Replace double quotes with backticks in column list
                $keyColumns = preg_replace('/"([^"]+)"/', '"$1"', $keyColumns);

                if ($keyType === 'PRIMARY KEY' && !$primaryKeyColumnFound) {
                    $newLines[] = 'PRIMARY KEY (' . $keyColumns . ')';
                } elseif ($keyType === 'UNIQUE') {
                    // Remove CONSTRAINT name for SQLite
                    $line = preg_replace('/^CONSTRAINT\s+"?([^"]+)"?\s+UNIQUE\s*\((.*)\)/i', 'UNIQUE ($2)', $line);
                    $newLines[] = $line;
                }
            } else {
                // Other constraints or unparsed parts, try to add as is, but quote identifiers
                $line = preg_replace_callback('/(")([^"]+?)(")/', function($m) {
                    return $this->quoteIdentifier($m[2], 'sqlite');
                }, $line);
                $newLines[] = $line;
            }
        }

        $finalSql = "CREATE TABLE " . $ifNotExists . $tableName . " (\n    " . implode(",\n    ", $newLines) . "\n)";

        return trim($finalSql) . ';';
    }

    /**
     * Translates a CREATE TABLE statement from SQLite to MySQL.
     *
     * @param string $sql The SQLite CREATE TABLE statement.
     * @return string The translated MySQL CREATE TABLE statement.
     * @throws DatabaseConversionException If the SQL format is invalid.
     */
    public function sqliteToMySQL($sql) // NOSONAR
    {
        $sql = trim($sql);
        $sql = preg_replace('/\s+/', ' ', $sql); // Normalize spaces

        // Extract table name
        if (!preg_match('/CREATE TABLE (IF NOT EXISTS\s+)?("?)([^"\s]+)("?)\s*\((.*)\)/is', $sql, $matches)) {
            throw new DatabaseConversionException("Invalid SQLite CREATE TABLE statement format.");
        }
        $ifNotExists = isset($matches[1]) ? 'IF NOT EXISTS ' : '';
        $tableName = $this->quoteIdentifier($matches[3], 'mysql');
        $columnsAndConstraints = trim($matches[5]);

        $lines = explode(',', $columnsAndConstraints);
        $newLines = [];
        $primaryKeyColumnFound = false;

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) 
            {
                continue;
            }

            // Column definition
            if (preg_match('/^("?)([^"\s]+)("?)\s+([a-zA-Z0-9_() ]+)(.*)$/i', $line, $colMatches)) // NOSONAR
            {
                $columnName = $this->quoteIdentifier($colMatches[2], 'mysql');
                $columnType = strtolower(trim($colMatches[4]));
                $columnDefinition = trim($colMatches[5]);

                $translatedType = $this->translateFieldType($columnType, 'sqlite', 'mysql');

                // Handle INTEGER PRIMARY KEY AUTOINCREMENT
                if (str_contains(strtoupper($columnType), 'INTEGER') && str_contains(strtoupper($columnDefinition), 'AUTOINCREMENT')) {
                    $translatedType = 'INT'; // MySQL uses INT
                    $columnDefinition = str_ireplace('AUTOINCREMENT', 'AUTO_INCREMENT', $columnDefinition);
                    $primaryKeyColumnFound = true;
                }
                
                // Handle BOOLEAN (INTEGER)
                if (str_contains(strtoupper($columnType), 'INTEGER') && (str_contains(strtoupper($columnDefinition), 'DEFAULT 1') || str_contains(strtoupper($columnDefinition), 'DEFAULT 0'))) {
                    // This is a heuristic, assuming INTEGER with default 0/1 is boolean
                    $translatedType = 'TINYINT(1)';
                    $columnDefinition = str_ireplace("DEFAULT 1", "DEFAULT '1'", $columnDefinition);
                    $columnDefinition = str_ireplace("DEFAULT 0", "DEFAULT '0'", $columnDefinition);
                }

                // Handle DATETIME
                if (str_contains(strtoupper($columnType), 'DATETIME')) {
                    $translatedType = 'DATETIME'; // MySQL DATETIME
                }
                
                // Handle TEXT (for JSON)
                if (str_contains(strtoupper($columnType), 'TEXT') && str_contains(strtoupper($columnName), 'json')) {
                    // Heuristic: if text column name contains 'json', assume it's JSON
                    $translatedType = 'JSON';
                }

                $newLines[] = $columnName . ' ' . $translatedType . ' ' . trim($columnDefinition);
            } elseif (preg_match('/^(PRIMARY KEY|UNIQUE)\s*\((.*)\)/i', $line, $keyMatches)) {
                // Table-level PRIMARY KEY or UNIQUE
                $keyType = strtoupper($keyMatches[1]);
                $keyColumns = $keyMatches[2];
                
                // Replace double quotes with backticks in column list
                $keyColumns = preg_replace('/"([^"]+)"/', '`$1`', $keyColumns);

                if ($keyType === 'PRIMARY KEY' && !$primaryKeyColumnFound) {
                    $newLines[] = 'PRIMARY KEY (' . $keyColumns . ')';
                } elseif ($keyType === 'UNIQUE') {
                    $newLines[] = 'UNIQUE KEY (' . $keyColumns . ')';
                }
            } else {
                // Other constraints or unparsed parts, try to add as is, but quote identifiers
                $line = preg_replace_callback('/(")([^"]+?)(")/', function($m) {
                    return $this->quoteIdentifier($m[2], 'mysql');
                }, $line);
                $newLines[] = $line;
            }
        }

        $finalSql = "CREATE TABLE " . $ifNotExists . $tableName . " (\n    " . implode(",\n    ", $newLines) . "\n)";
        $finalSql .= "\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;"; // Add common MySQL table options

        return trim($finalSql) . ';';
    }

    /**
     * Translates a CREATE TABLE statement from SQLite to PostgreSQL.
     *
     * @param string $sql The SQLite CREATE TABLE statement.
     * @return string The translated PostgreSQL CREATE TABLE statement.
     * @throws DatabaseConversionException If the SQL format is invalid.
     */
    public function sqliteToPostgreSQL($sql) // NOSONAR
    {
        $sql = trim($sql);
        $sql = preg_replace('/\s+/', ' ', $sql); // Normalize spaces

        // Extract table name
        if (!preg_match('/CREATE TABLE (IF NOT EXISTS\s+)?("?)([^"\s]+)("?)\s*\((.*)\)/is', $sql, $matches)) {
            throw new DatabaseConversionException("Invalid SQLite CREATE TABLE statement format.");
        }
        $ifNotExists = isset($matches[1]) ? 'IF NOT EXISTS ' : '';
        $tableName = $this->quoteIdentifier($matches[3], 'postgresql');
        $columnsAndConstraints = trim($matches[5]);

        $lines = explode(',', $columnsAndConstraints);
        $newLines = [];
        $primaryKeyColumnFound = false;

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) 
            {
                continue;
            }

            // Column definition
            if (preg_match('/^("?)([^"\s]+)("?)\s+([a-zA-Z0-9_() ]+)(.*)$/i', $line, $colMatches)) // NOSONAR
            {
                $columnName = $this->quoteIdentifier($colMatches[2], 'postgresql');
                $columnType = strtolower(trim($colMatches[4]));
                $columnDefinition = trim($colMatches[5]);

                $translatedType = $this->translateFieldType($columnType, 'sqlite', 'postgresql');

                // Handle INTEGER PRIMARY KEY AUTOINCREMENT
                if (str_contains(strtoupper($columnType), 'INTEGER') && str_contains(strtoupper($columnDefinition), 'AUTOINCREMENT')) {
                    $translatedType = 'SERIAL'; // PostgreSQL uses SERIAL
                    $columnDefinition = str_ireplace('AUTOINCREMENT', '', $columnDefinition);
                    $columnDefinition .= ' PRIMARY KEY'; // Add PRIMARY KEY explicitly
                    $primaryKeyColumnFound = true;
                }

                // Handle BOOLEAN (INTEGER)
                if (str_contains(strtoupper($columnType), 'INTEGER') && (str_contains(strtoupper($columnDefinition), 'DEFAULT 1') || str_contains(strtoupper($columnDefinition), 'DEFAULT 0'))) {
                    // This is a heuristic, assuming INTEGER with default 0/1 is boolean
                    $translatedType = 'BOOLEAN';
                    $columnDefinition = str_ireplace("DEFAULT 1", "DEFAULT TRUE", $columnDefinition);
                    $columnDefinition = str_ireplace("DEFAULT 0", "DEFAULT FALSE", $columnDefinition);
                }

                // Handle DATETIME
                if (str_contains(strtoupper($columnType), 'DATETIME')) {
                    $translatedType = 'TIMESTAMP WITHOUT TIME ZONE';
                }
                
                // Handle TEXT (for JSON)
                if (str_contains(strtoupper($columnType), 'TEXT') && str_contains(strtoupper($columnName), 'json')) {
                    // Heuristic: if text column name contains 'json', assume it's JSONB
                    $translatedType = 'JSONB';
                }

                $newLines[] = $columnName . ' ' . $translatedType . ' ' . trim($columnDefinition);
            } elseif (preg_match('/^(PRIMARY KEY|UNIQUE)\s*\((.*)\)/i', $line, $keyMatches)) {
                // Table-level PRIMARY KEY or UNIQUE
                $keyType = strtoupper($keyMatches[1]);
                $keyColumns = $keyMatches[2];
                
                // Replace double quotes with backticks in column list
                $keyColumns = preg_replace('/"([^"]+)"/', '"$1"', $keyColumns);

                if ($keyType === 'PRIMARY KEY' && !$primaryKeyColumnFound) {
                    $newLines[] = 'PRIMARY KEY (' . $keyColumns . ')';
                } elseif ($keyType === 'UNIQUE') {
                    // SQLite's UNIQUE can be a constraint without a name.
                    // PostgreSQL's UNIQUE can be named or unnamed.
                    // If it's a simple UNIQUE (col), we can just add UNIQUE to the column definition.
                    // If it's a named constraint, add it as a table constraint.
                    $newLines[] = 'UNIQUE (' . $keyColumns . ')';
                }
            } else {
                // Other constraints or unparsed parts, try to add as is, but quote identifiers
                $line = preg_replace_callback('/(")([^"]+?)(")/', function($m) {
                    return $this->quoteIdentifier($m[2], 'postgresql');
                }, $line);
                $newLines[] = $line;
            }
        }

        $finalSql = "CREATE TABLE " . $ifNotExists . $tableName . " (\n    " . implode(",\n    ", $newLines) . "\n)";

        return trim($finalSql) . ';';
    }

    /**
     * Translates a CREATE TABLE statement from a source dialect to a target dialect.
     *
     * @param string $sql The CREATE TABLE statement.
     * @param string $sourceDialect The source database dialect (e.g., 'mysql', 'postgresql', 'sqlite').
     * @param string $targetDialect The target database dialect (e.g., 'mysql', 'postgresql', 'sqlite').
     * @return string The translated CREATE TABLE statement.
     * @throws DatabaseConversionException If an unsupported translation is requested or SQL format is invalid.
     */
    public function translateCreateTable($sql, $sourceDialect, $targetDialect) // NOSONAR
    {
        $sourceDialect = strtolower($sourceDialect);
        $targetDialect = strtolower($targetDialect);

        if ($sourceDialect === $targetDialect) {
            return $sql; // No translation needed
        }

        switch ($sourceDialect . 'To' . ucfirst($targetDialect)) {
            case 'mysqlToPostgresql':
                return $this->mysqlToPostgreSQL($sql);
            case 'mysqlToSqlite':
                return $this->mysqlToSQLite($sql);
            case 'postgresqlToMysql':
                return $this->postgresqlToMySQL($sql);
            case 'postgresqlToSqlite':
                return $this->postgresqlToSQLite($sql);
            case 'sqliteToMysql':
                return $this->sqliteToMySQL($sql);
            case 'sqliteToPostgresql':
                return $this->sqliteToPostgreSQL($sql);
            default:
                throw new DatabaseConversionException("Unsupported CREATE TABLE translation: from " . $sourceDialect . " to " . $targetDialect);
        }
    }
}
