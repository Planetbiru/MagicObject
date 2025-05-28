<?php

namespace MagicObject\Util\Database;

use MagicObject\Exceptions\DatabaseConversionException;

/**
 * Class PicoDatabaseConverter
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
class PicoDatabaseConverter // NOSONAR
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

    /**
     * Array mapping of SQL data types to PHP types.
     *
     * @var array
     */
    private $sqlToPhpType;

    public function __construct() // NOSONAR
    {
        $this->dbToSqlite = [
            "tinyint(1)" => "BOOLEAN", // NOSONAR
            "tinyint" => "INTEGER",
            "smallint" => "INTEGER",
            "mediumint" => "INTEGER",
            "bigint" => "INTEGER",
            "integer" => "INTEGER",
            "int" => "INTEGER",
            "bigserial" => "INTEGER",
            "serial" => "INTEGER",

            "real" => "REAL",
            "float" => "REAL",
            "double precision" => "REAL", // NOSONAR
            "double" => "REAL",
            "decimal" => "REAL",
            "numeric" => "REAL",
            "money" => "REAL",

            "bit" => "INTEGER",
            "boolean" => "INTEGER",

            "char" => "NVARCHAR",
            "nvarchar" => "NVARCHAR",
            "character varying" => "NVARCHAR", // NOSONAR
            "varchar" => "NVARCHAR",

            "tinytext" => "TEXT",
            "mediumtext" => "TEXT",
            "longtext" => "TEXT",
            "text" => "TEXT",
            "jsonb" => "TEXT",
            "json" => "TEXT",
            "uuid" => "TEXT",
            "xml" => "TEXT",
            "blob" => "BLOB",
            "binary" => "BLOB",
            "varbinary" => "BLOB",
            "timestamp with time zone" => "TIMESTAMP", // NOSONAR
            "timestamp without time zone" => "DATETIME", // NOSONAR
            "timestamptz" => "TIMESTAMP", // NOSONAR
            "datetime" => "DATETIME",
            "timestamptz" => "TIMESTAMP",
            "timestamp" => "TIMESTAMP",
            "date" => "DATE",
            "time" => "TIME",
            "year" => "INTEGER"
        ];

        $this->dbToMySQL = [
            "bigint" => "BIGINT",
            "mediumint" => "MEDIUMINT",
            "smallint" => "SMALLINT",
            "tinyint(1)" => "TINYINT(1)", // NOSONAR
            "tinyint" => "TINYINT",
            "integer" => "INT",
            
            "bigserial" => "BIGINT",
            "serial" => "INT",

            "int" => "INT",
            "float" => "FLOAT",
            "real" => "DOUBLE",
            "double precision" => "DOUBLE",
            "double" => "DOUBLE",
            "decimal" => "DECIMAL",
            "numeric" => "NUMERIC",
            "money" => "DECIMAL(19,4)",

            "bit" => "BIT",
            "boolean" => "TINYINT(1)",

            "char" => "CHAR",
            "nvarchar" => "VARCHAR",
            "varchar" => "VARCHAR",
            "character varying" => "VARCHAR",

            "tinytext" => "TINYTEXT",
            "mediumtext" => "MEDIUMTEXT",
            "longtext" => "LONGTEXT",
            "text" => "TEXT",
            "jsonb" => "JSON",
            "json" => "JSON",
            "uuid" => "CHAR(36)",
            "xml" => "TEXT",
            "binary" => "BINARY",
            "varbinary" => "VARBINARY",
            "blob" => "BLOB",
            "timestamp with time zone" => "TIMESTAMP", // NOSONAR
            "timestamp without time zone" => "DATETIME", // NOSONAR
            "timestamptz" => "TIMESTAMP", // NOSONAR
            "datetime" => "DATETIME",
            "date" => "DATE",
            "time" => "TIME",
            "year" => "YEAR",
            "enum" => "ENUM",
            "set" => "SET"
        ];

        $this->dbToPostgreSQL = [
            "bigint" => "BIGINT",
            "mediumint" => "INTEGER",
            "smallint" => "INTEGER",
            "tinyint(1)" => "BOOLEAN",
            "tinyint" => "INTEGER",
            "integer" => "INTEGER",
            "int" => "INTEGER",
            "bigserial" => "BIGSERIAL",
            "serial" => "SERIAL",
            "float" => "REAL",
            "real" => "REAL",
            "double precision" => "DOUBLE PRECISION",
            "double" => "DOUBLE PRECISION",
            "decimal" => "DECIMAL",
            "numeric" => "NUMERIC",
            "money" => "MONEY",
            "bit" => "BIT",
            "boolean" => "BOOLEAN",
            "char" => "CHARACTER",
            "nvarchar" => "CHARACTER VARYING", // NOSONAR
            "varchar" => "CHARACTER VARYING",
            "character varying" => "CHARACTER VARYING", // NOSONAR
            "tinytext" => "TEXT",
            "mediumtext" => "TEXT",
            "longtext" => "TEXT",
            "smalltext" => "TEXT",
            "text" => "TEXT",
            "json" => "JSONB",
            "jsonb" => "JSONB",
            "uuid" => "UUID",
            "xml" => "XML",
            "blob" => "BYTEA",
            "binary" => "BYTEA",
            "varbinary" => "BYTEA",
            "datetime" => "TIMESTAMP WITHOUT TIME ZONE", // NOSONAR
            "timestamp without time zone" => "TIMESTAMP WITHOUT TIME ZONE",
            "timestamp with time zone" => "TIMESTAMP WITH TIME ZONE", // NOSONAR
            "timestamptz" => "TIMESTAMP WITH TIME ZONE",
            "timestamp" => "TIMESTAMP WITH TIME ZONE",
            "date" => "DATE",
            "time" => "TIME",
            "year" => "INTEGER",
            "enum" => "TEXT", // PostgreSQL does support ENUM but requires definition
            "set" => "TEXT"   // no native SET, fallback to TEXT
        ];

        $this->sqlToPhpType = [
            // Integer types
            'tinyint' => 'int',
            'tinyint(1)' => 'bool', // special case often used for boolean
            'smallint' => 'int',
            'mediumint' => 'int',
            'int' => 'int',
            'integer' => 'int',
            'bigint' => 'int',
            'serial' => 'int',
            'bigserial' => 'int',
            'year' => 'int',
            'bit' => 'int',

            // Floating-point types
            'float' => 'float',
            'real' => 'float',
            'double' => 'float',
            'double precision' => 'float',
            'decimal' => 'float',
            'numeric' => 'float',
            'money' => 'float',

            // Boolean type
            'boolean' => 'bool',
            'bool' => 'bool',

            // String types
            'char' => 'string',
            'varchar' => 'string',
            'nvarchar' => 'string',
            'character varying' => 'string',
            'text' => 'string',
            'tinytext' => 'string',
            'mediumtext' => 'string',
            'longtext' => 'string',
            'smalltext' => 'string',
            'enum' => 'string',
            'set' => 'string',
            'uuid' => 'string',
            'xml' => 'string',

            // Date & Time types (usually stored as string in PHP)
            'datetime' => 'string',
            'timestamp' => 'string',
            'timestamp with time zone' => 'string',
            'timestamp without time zone' => 'string',
            'timestamptz' => 'string',
            'date' => 'string',
            'time' => 'string',

            // Binary types
            'blob' => 'string',
            'binary' => 'string',
            'varbinary' => 'string',
            'bytea' => 'string',

            // JSON types
            'json' => 'array',   // assuming it's decoded
            'jsonb' => 'array',  // assuming it's decoded
        ];

    }

    /**
     * Converts a raw database value to its appropriate native PHP type based on the SQL type and database dialect.
     *
     * @param mixed  $value   The raw input value (e.g., string, int, resource).
     * @param string $sqlType The SQL type name (e.g., 'int', 'boolean', 'json', etc.).
     * @param string $dialect The database dialect (e.g., 'mysql', 'postgresql', 'sqlite').
     * @return mixed The value converted to a native PHP type.
     */
    public function convertToPhpType($value, $sqlType, $dialect) // NOSONAR
    {
        // Normalize the SQL type: remove length/precision and convert to lowercase
        $normalizedType = strtolower(trim(preg_replace('/\s*\(.*\)/', '', $sqlType)));

        // If the value is already null, return it directly
        if ($value === null) {
            return null;
        }

        // Convert based on the normalized SQL type
        switch ($normalizedType) // NOSONAR
        {
            case 'int':
            case 'integer':
            case 'smallint':
            case 'mediumint':
            case 'bigint':
            case 'serial':
            case 'bigserial':
            case 'year':
            case 'bit':
                return (int) $value;

            case 'tinyint':
            case 'tinyint(1)':
            case 'boolean':
            case 'bool':
                // Convert to boolean; fall back to null if unrecognized
                $value = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                if (!isset($value)) {
                    return null;
                }
                // For SQLite, return as integer 0/1
                if (stripos($dialect, 'sqlite') !== false) {
                    return $value === true ? 1 : 0;
                }
                return $value;

            case 'float':
            case 'real':
            case 'double':
            case 'double precision':
            case 'decimal':
            case 'numeric':
            case 'money':
                return (float) $value;

            case 'json':
            case 'jsonb':
                // Decode JSON into an associative array
                return json_decode($value, true);

            case 'blob':
            case 'binary':
            case 'varbinary':
            case 'bytea':
                // If the value is a resource (e.g., stream), read it; otherwise cast to string
                return is_resource($value) ? stream_get_contents($value) : (string) $value;

            case 'date':
            case 'time':
            case 'datetime':
            case 'timestamp':
            case 'timestamp with time zone':
            case 'timestamp without time zone':
            case 'timestamptz':
                // Optionally return as DateTime object instead of string
                return (string) $value;

            default:
                // Fallback: treat as string
                return (string) $value;
        }
    }


    /**
     * Converts a PHP value to a valid SQL literal string based on native PHP type.
     *
     * @param mixed  $value The PHP value (e.g. int, string, array, etc).
     * @param string $phpType The native PHP type (e.g. 'int', 'string', 'bool', etc).
     * @return string SQL literal (e.g. 123, 'text', NULL).
     */
    public function convertPhpValueToSqlLiteral($value, $phpType) // NOSONAR
    {
        // Normalize PHP type
        $phpType = strtolower(trim($phpType));

        // NULL
        if ($value === null || $phpType === 'null') {
            return 'NULL';
        }

        switch ($phpType) {
            case 'int':
            case 'integer':
                return (string)(int)$value;

            case 'float':
            case 'double':
                return (string)(float)$value;

            case 'bool':
            case 'boolean':
                return $value ? '1' : '0';

            case 'array':
                // Convert array to JSON string and escape
                $json = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                return "'" . $this->escapeSqlString($json) . "'";

            case 'string':
            default:
                return "'" . $this->escapeSqlString((string)$value) . "'";
        }
    }

    /**
     * Converts a value and its SQL type to a valid SQL literal string
     * by first mapping the SQL type to a native PHP type.
     *
     * @param mixed  $value The input value to be converted.
     * @param string $type  The SQL type (e.g., 'int', 'varchar', 'boolean').
     * @return string A valid SQL literal (e.g., 123, 'text', NULL).
     */
    public function convertValueToSqlLiteral($value, $type)
    {
        $phpType = isset($this->sqlToPhpType[$type]) ? $this->sqlToPhpType[$type] : 'string';
        return $this->convertPhpValueToSqlLiteral($value, $phpType);
    }

    /**
     * Escapes a string for SQL by doubling single quotes.
     *
     * @param string $value
     * @return string
     */
    protected function escapeSqlString(string $value): string
    {
        return str_replace("'", "''", $value);
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
        // Normalize spaces in the type string
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
                    return $baseType === 'bigserial' ? 'BIGINT' : 'INT';
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
                if ($this->strContains($type, 'with time zone')) {
                    if ($targetDialect === 'mysql') {
                        return 'TIMESTAMP';
                    }
                    if ($targetDialect === 'sqlite') {
                        return 'DATETIME';
                    }
                } elseif ($this->strContains($type, 'without time zone')) {
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
                $this->strContains(strtoupper($translatedBaseType), 'VARCHAR') ||
                $this->strContains(strtoupper($translatedBaseType), 'CHARACTER VARYING') ||
                $this->strContains(strtoupper($translatedBaseType), 'CHAR')
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

        // Extract table name and body using a custom parser to handle nested parentheses
        if (!preg_match('/CREATE\s+TABLE\s+(IF\s+NOT\s+EXISTS\s+)?`?([^\s`(]+)`?\s*\(/i', $sql, $matches, PREG_OFFSET_CAPTURE)) {
            throw new DatabaseConversionException("Invalid MySQL CREATE TABLE statement format.");
        }

        $ifNotExists = isset($matches[1][0]) ? 'IF NOT EXISTS ' : ''; // NOSONAR
        $tableName = $this->quoteIdentifier($matches[2][0], 'postgresql');
        $startPos = $matches[0][1] + strlen($matches[0][0]) - 1;

        $depth = 1;
        $i = $startPos + 1;
        $len = strlen($sql);
        while ($i < $len && $depth > 0) {
            if ($sql[$i] === '(') 
            {
                $depth++;
            }
            elseif ($sql[$i] === ')') 
            {
                $depth--;
            }
            $i++;
        }

        if ($depth !== 0) {
            throw new DatabaseConversionException("Unbalanced parentheses in CREATE TABLE statement.");
        }

        $columnsSection = substr($sql, $startPos + 1, $i - $startPos - 2);
        $tableOptions = trim(substr($sql, $i));

        // Parse lines manually to preserve commas inside data types or defaults
        $lines = preg_split('/,(?![^\(\)]*\))/', $columnsSection);
        $newLines = [];
        $tableConstraints = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') 
            {
                continue;
            }

            // Column definition
            if (preg_match('/^`?([^`\s]+)`?\s+([a-zA-Z0-9_\(\)]+)(.*)$/i', $line, $colMatches)) /*M NOSONAR */ {
                $columnName = $this->quoteIdentifier($colMatches[1], 'postgresql');
                $columnType = strtolower(trim($colMatches[2]));
                $columnDefinition = trim($colMatches[3]);

                // Translate type
                $translatedType = $this->translateFieldType($columnType, 'mysql', 'postgresql');

                // Handle AUTO_INCREMENT
                if (stripos($columnDefinition, 'AUTO_INCREMENT') !== false) {
                    $translatedType = stripos($columnType, 'bigint') !== false ? 'BIGSERIAL' : 'SERIAL';
                    $columnDefinition = str_ireplace('AUTO_INCREMENT', '', $columnDefinition);
                    if (stripos($columnDefinition, 'PRIMARY KEY') === false) // NOSONAR
                    {
                        $columnDefinition .= ' PRIMARY KEY';
                    }
                }

                // Handle BOOLEAN (tinyint(1))
                if (preg_match('/tinyint\s*\(\s*1\s*\)/i', $columnType)) {
                    $translatedType = 'BOOLEAN';
                    $columnDefinition = str_ireplace("DEFAULT '1'", 'DEFAULT TRUE', $columnDefinition);
                    $columnDefinition = str_ireplace("DEFAULT '0'", 'DEFAULT FALSE', $columnDefinition);
                }

                // Remove MySQL-specific ON UPDATE
                if (stripos($columnDefinition, 'ON UPDATE CURRENT_TIMESTAMP') !== false) {
                    $columnDefinition = str_ireplace('ON UPDATE CURRENT_TIMESTAMP', '', $columnDefinition);
                }

                // ENUM/SET to TEXT
                if (stripos($columnType, 'enum') !== false || stripos($columnType, 'set') !== false) {
                    $translatedType = 'TEXT';
                }

                $newLines[] = $columnName . ' ' . $translatedType . ' ' . trim($columnDefinition);
            }
            // Table constraints
            elseif (preg_match('/^(PRIMARY KEY|UNIQUE KEY)\s*`?([^`]+)?`?\s*\((.+)\)/i', $line, $keyMatches)) {
                $keyType = strtoupper($keyMatches[1]);
                $keyName = $keyMatches[2];
                $keyColumns = preg_replace('/`([^`]+)`/', '"$1"', $keyMatches[3]);

                if ($keyType === 'PRIMARY KEY') {
                    $tableConstraints[] = 'PRIMARY KEY (' . $keyColumns . ')';
                } elseif ($keyType === 'UNIQUE KEY') {
                    $tableConstraints[] = 'CONSTRAINT ' . $this->quoteIdentifier($keyName, 'postgresql') . ' UNIQUE (' . $keyColumns . ')';
                }
            }
            // Other constraints
            else {
                $line = preg_replace_callback('/`([^`]+)`/', function ($m) {
                    return $this->quoteIdentifier($m[1], 'postgresql');
                }, $line);
                $newLines[] = $line;
            }
        }

        if (!empty($tableConstraints)) {
            $newLines = array_merge($newLines, $tableConstraints);
        }

        $finalSql = "CREATE TABLE " . $ifNotExists . $tableName . " (\n    " . implode(",\n    ", $newLines) . "\n)";

        // Clean up MySQL table options
        $finalSql = preg_replace('/ENGINE\s*=\s*[a-zA-Z0-9_]+/i', '', $finalSql);
        $finalSql = preg_replace('/DEFAULT\s+CHARSET\s*=\s*[a-zA-Z0-9_]+/i', '', $finalSql);
        $finalSql = preg_replace('/COLLATE\s*=\s*[a-zA-Z0-9_]+/i', '', $finalSql);
        $finalSql = preg_replace('/COMMENT\s+\'.*?\'/i', '', $finalSql);

        $finalSql = str_replace('`', '"', $finalSql); // Convert backticks to double quotes for PostgreSQL
        $finalSql = str_replace('"PRIMARY" KEY', 'PRIMARY KEY', $finalSql); // Fix PostgreSQL's PRIMARY KEY quoting
        $finalSql = str_replace('"UNIQUE" KEY', 'UNIQUE', $finalSql); // Fix PostgreSQL's UNIQUE KEY quoting
        $finalSql = str_replace('"FOREIGN" KEY', 'FOREIGN KEY', $finalSql); // Fix PostgreSQL's FOREIGN KEY quoting
        $finalSql = str_replace('"CHECK" (', 'CHECK (', $finalSql); // Fix PostgreSQL's CHECK constraint quoting
        $finalSql = str_replace('"DEFAULT" ', 'DEFAULT ', $finalSql); // Fix PostgreSQL's DEFAULT quoting
        $finalSql = str_replace('"NOT" NULL', 'NOT NULL', $finalSql); // Fix PostgreSQL's NOT NULL quoting
        $finalSql = str_replace('"NULL"', 'NULL', $finalSql); // Fix PostgreSQL's NULL quoting
        $finalSql = str_replace('"REFERENCES"', 'REFERENCES', $finalSql); // Fix PostgreSQL's REFERENCES quoting
        $finalSql = str_replace('"ON" DELETE', 'ON DELETE', $finalSql); // Fix PostgreSQL's ON DELETE quoting
        $finalSql = str_replace('"ON" UPDATE', 'ON UPDATE', $finalSql); // Fix PostgreSQL's ON UPDATE quoting
        $finalSql = str_replace('"USING"', 'USING', $finalSql); // Fix PostgreSQL's USING quoting
        $finalSql = str_replace('"WITH"', 'WITH', $finalSql); // Fix PostgreSQL's WITH quoting
        $finalSql = str_replace('"CONSTRAINT"', 'CONSTRAINT', $finalSql); // Fix PostgreSQL's CONSTRAINT quoting
        
        $finalSql = $this->fixLines($finalSql);

        return trim($finalSql) . ';';
    }


    /**
     * Translates a CREATE TABLE statement from MySQL to SQLite.
     *
     * @param string $sql The MySQL CREATE TABLE statement.
     * @return string The translated SQLite CREATE TABLE statement.
     * @throws DatabaseConversionException If the SQL format is invalid.
     */
    public function mysqlToSQLite($sql)
    {
        $sql = trim($sql);

        // Ambil nama tabel dan isi definisi kolom/constraint
        if (!preg_match('/CREATE\s+TABLE\s+(IF\s+NOT\s+EXISTS\s+)?`?([^\s`(]+)`?\s*\(/i', $sql, $matches, PREG_OFFSET_CAPTURE)) {
            throw new DatabaseConversionException("Invalid MySQL CREATE TABLE statement format.");
        }

        $ifNotExists = isset($matches[1][0]) ? 'IF NOT EXISTS ' : '';
        $tableName = $this->quoteIdentifier($matches[2][0], 'sqlite');
        $startPos = $matches[0][1] + strlen($matches[0][0]) - 1;

        // Ambil isi kolom dan constraint dengan tracking tanda kurung
        $depth = 1;
        $i = $startPos + 1;
        $len = strlen($sql);
        while ($i < $len && $depth > 0) {
            if ($sql[$i] === '(') $depth++;
            elseif ($sql[$i] === ')') $depth--;
            $i++;
        }

        if ($depth !== 0) {
            throw new DatabaseConversionException("Unbalanced parentheses in CREATE TABLE statement.");
        }

        $columnsSection = substr($sql, $startPos + 1, $i - $startPos - 2);
        $tableOptions = trim(substr($sql, $i));
        $lines = preg_split('/,(?![^\(]*\))/m', $columnsSection);

        $newLines = [];
        $primaryKeyColumnFound = false;

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') continue;

            // Column definition
            if (preg_match('/^`?([^`\s]+)`?\s+([a-zA-Z0-9_\(\)]+)(.*)$/i', $line, $colMatches)) {
                $columnName = $this->quoteIdentifier($colMatches[1], 'sqlite');
                $columnType = strtolower(trim($colMatches[2]));
                $columnDefinition = trim($colMatches[3]);

                $translatedType = $this->translateFieldType($columnType, 'mysql', 'sqlite');

                // AUTO_INCREMENT di SQLite hanya INTEGER PRIMARY KEY AUTOINCREMENT
                if (stripos($columnDefinition, 'AUTO_INCREMENT') !== false) {
                    if (stripos($columnType, 'int') !== false && stripos($columnDefinition, 'PRIMARY KEY') !== false && !$primaryKeyColumnFound) {
                        $translatedType = 'INTEGER';
                        $columnDefinition = str_ireplace('AUTO_INCREMENT', 'AUTOINCREMENT', $columnDefinition);
                        $primaryKeyColumnFound = true;
                    } else {
                        $columnDefinition = str_ireplace('AUTO_INCREMENT', '', $columnDefinition);
                    }
                }

                // BOOLEAN (TINYINT(1)) -> INTEGER
                if (preg_match('/tinyint\s*\(\s*1\s*\)/i', $columnType)) {
                    $translatedType = 'INTEGER';
                    $columnDefinition = str_ireplace("DEFAULT '1'", 'DEFAULT 1', $columnDefinition);
                    $columnDefinition = str_ireplace("DEFAULT '0'", 'DEFAULT 0', $columnDefinition);
                }

                // ENUM dan SET -> TEXT
                if (stripos($columnType, 'enum') !== false || stripos($columnType, 'set') !== false) {
                    $translatedType = 'TEXT';
                }

                // Hapus ON UPDATE CURRENT_TIMESTAMP
                if (stripos($columnDefinition, 'ON UPDATE CURRENT_TIMESTAMP') !== false) {
                    $columnDefinition = str_ireplace('ON UPDATE CURRENT_TIMESTAMP', '', $columnDefinition);
                }

                $newLines[] = $columnName . ' ' . $translatedType . ' ' . trim($columnDefinition);
            }
            // PRIMARY KEY atau UNIQUE KEY table-level
            elseif (preg_match('/^(PRIMARY KEY|UNIQUE KEY)\s*`?([^`]*)`?\s*\((.+)\)/i', $line, $keyMatches)) {
                $keyType = strtoupper($keyMatches[1]);
                $keyColumns = preg_replace('/`([^`]+)`/', '"$1"', $keyMatches[3]);

                if ($keyType === 'PRIMARY KEY' && !$primaryKeyColumnFound) {
                    $newLines[] = 'PRIMARY KEY (' . $keyColumns . ')';
                } elseif ($keyType === 'UNIQUE KEY') {
                    $newLines[] = 'UNIQUE (' . $keyColumns . ')';
                }
            }
            // Lainnya
            else {
                $line = preg_replace_callback('/`([^`]+)`/', function ($m) {
                    return $this->quoteIdentifier($m[1], 'sqlite');
                }, $line);
                $newLines[] = $line;
            }
        }

        // Finalisasi statement CREATE TABLE
        $finalSql = "CREATE TABLE " . $ifNotExists . $tableName . " (\n    " . implode(",\n    ", $newLines) . "\n)";

        // Bersihkan ENGINE, CHARSET, COLLATE, COMMENT
        $finalSql = preg_replace('/ENGINE\s*=\s*[a-zA-Z0-9_]+/i', '', $finalSql);
        $finalSql = preg_replace('/DEFAULT\s+CHARSET\s*=\s*[a-zA-Z0-9_]+/i', '', $finalSql);
        $finalSql = preg_replace('/COLLATE\s*=\s*[a-zA-Z0-9_]+/i', '', $finalSql);
        $finalSql = preg_replace('/COMMENT\s+\'.*?\'/i', '', $finalSql);

        $finalSql = str_replace('"PRIMARY" KEY', 'PRIMARY KEY', $finalSql); // Fix PostgreSQL's PRIMARY KEY quoting
        $finalSql = str_replace('"UNIQUE" KEY', 'UNIQUE', $finalSql); // Fix PostgreSQL's UNIQUE KEY quoting
        $finalSql = str_replace('"FOREIGN" KEY', 'FOREIGN KEY', $finalSql); // Fix PostgreSQL's FOREIGN KEY quoting
        $finalSql = str_replace('"CHECK" (', 'CHECK (', $finalSql); // Fix PostgreSQL's CHECK constraint quoting
        $finalSql = str_replace('"DEFAULT" ', 'DEFAULT ', $finalSql); // Fix PostgreSQL's DEFAULT quoting
        $finalSql = str_replace('"NOT" NULL', 'NOT NULL', $finalSql); // Fix PostgreSQL's NOT NULL quoting
        $finalSql = str_replace('"NULL"', 'NULL', $finalSql); // Fix PostgreSQL's NULL quoting
        $finalSql = str_replace('"REFERENCES"', 'REFERENCES', $finalSql); // Fix PostgreSQL's REFERENCES quoting
        $finalSql = str_replace('"ON" DELETE', 'ON DELETE', $finalSql); // Fix PostgreSQL's ON DELETE quoting
        $finalSql = str_replace('"ON" UPDATE', 'ON UPDATE', $finalSql); // Fix PostgreSQL's ON UPDATE quoting
        $finalSql = str_replace('"USING"', 'USING', $finalSql); // Fix PostgreSQL's USING quoting
        $finalSql = str_replace('"WITH"', 'WITH', $finalSql); // Fix PostgreSQL's WITH quoting
        $finalSql = str_replace('"CONSTRAINT"', 'CONSTRAINT', $finalSql); // Fix PostgreSQL's CONSTRAINT quoting
        $finalSql = str_replace('`', '"', $finalSql); // Convert backticks to double quotes for SQLite

        $finalSql = $this->fixLines($finalSql);

        return trim($finalSql) . ';';
    }

    /**
     * Translates a PostgreSQL CREATE TABLE statement to MySQL,
     * focusing only on converting CHARACTER VARYING(n) to VARCHAR(n),
     * and preserving other attributes like NULL/NOT NULL/DEFAULT.
     *
     * @param string $sql The PostgreSQL CREATE TABLE statement.
     * @return string The translated MySQL CREATE TABLE statement.
     * @throws DatabaseConversionException If the SQL format is invalid.
     */
    
    public function postgresqlToMySQL($sql) // NOSONAR
    {
        $sql = trim(preg_replace('/\s+/', ' ', $sql)); // Normalize whitespace

        $posOpen = strpos(strtoupper($sql), '(');
        if ($posOpen === false) {
            throw new DatabaseConversionException("Invalid CREATE TABLE: missing opening parenthesis.");
        }

        $len = strlen($sql);
        $parenCount = 0;
        $posClose = false;
        for ($i = $posOpen; $i < $len; $i++) {
            if ($sql[$i] === '(') 
            {
                $parenCount++;
            }
            elseif ($sql[$i] === ')') 
            {
                $parenCount--;
            }
            if ($parenCount === 0) {
                $posClose = $i;
                break;
            }
        }
        if ($posClose === false) {
            throw new DatabaseConversionException("Invalid CREATE TABLE: unbalanced parentheses.");
        }

        if (!preg_match('/CREATE TABLE (IF NOT EXISTS\s+)?("?)([^"\s]+)\2/i', substr($sql, 0, $posOpen), $matches)) {
            throw new DatabaseConversionException("Cannot parse table name.");
        }
        $ifNotExists = isset($matches[1]) ? 'IF NOT EXISTS ' : '';
        $tableName = $this->quoteIdentifier($matches[3], 'mysql');

        $columnsDef = trim(substr($sql, $posOpen + 1, $posClose - $posOpen - 1));

        // Parse column definitions considering nested parentheses
        $lines = [];
        $buffer = '';
        $parenLevel = 0;
        for ($i = 0; $i < strlen($columnsDef); $i++) {
            $char = $columnsDef[$i];
            if ($char === '(') 
            {
                $parenLevel++;
            }
            elseif ($char === ')') 
            {
                $parenLevel--;
            }

            if ($char === ',' && $parenLevel === 0) {
                $lines[] = trim($buffer);
                $buffer = '';
            } else {
                $buffer .= $char;
            }
        }
        if (trim($buffer) !== '') {
            $lines[] = trim($buffer);
        }

        $newLines = [];

        foreach ($lines as $line) {
            // Convert "character varying(n)" to VARCHAR(n)
            if (preg_match('/^("?)([^"\s]+)\1\s+character varying\s*\((\d+)\)(.*)$/i', $line, $colMatch)) {
                $colName = $this->quoteIdentifier($colMatch[2], 'mysql');
                $length = $colMatch[3];
                $rest = trim($colMatch[4]);
                $newLines[] = "{$colName} VARCHAR({$length}) {$rest}";
            } else {
                // Replace PostgreSQL-style identifiers with MySQL-style
                $line = preg_replace_callback('/"([^"]+)"/', function ($m) /* NOSONAR */{
                    return $this->quoteIdentifier($m[1], 'mysql');
                }, $line);
                $newLines[] = $line;
            }
        }

        $finalSql = "CREATE TABLE {$ifNotExists}{$tableName} (\n    " . implode(",\n    ", $newLines) . "\n)";
        $finalSql .= "\nENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

        // Type translations
        $replacements = [
            'TIMESTAMPTZ' => 'TIMESTAMP',
            'TIMESTAMP WITH TIME ZONE' => 'TIMESTAMP',
            'TIMESTAMP WITHOUT TIME ZONE' => 'DATETIME',
            'BOOLEAN' => 'TINYINT(1)',
            'JSONB' => 'JSON'
        ];
        $finalSql = str_ireplace(array_keys($replacements), array_values($replacements), $finalSql);

        // SERIAL and BIGSERIAL to BIGINT AUTO_INCREMENT — remove duplicate PRIMARY KEY if needed
        $finalSql = preg_replace_callback('/`(\w+)`\s+(BIG)?SERIAL\s+(.*)/i', function ($matches) {
            $col = "`{$matches[1]}`";
            $rest = strtoupper($matches[3]);
            $hasPK = strpos($rest, 'PRIMARY KEY') !== false;
            $type = 'BIGINT';
            $mod = 'NOT NULL AUTO_INCREMENT';
            $rest = str_ireplace('PRIMARY KEY', '', $rest); // remove if exists here
            $rest = trim($rest);
            return $hasPK
                ? "{$col} {$type} {$mod}" // no need to keep PRIMARY KEY inline
                : "{$col} {$type} {$mod} {$rest}";
        }, $finalSql);

        // Final formatting
        $finalSql = $this->fixLines($finalSql);
        return trim($finalSql) . ";";
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
        $sql = trim(preg_replace('/\s+/', ' ', $sql)); // Normalize whitespace

        // Find opening and closing parenthesis
        $posOpen = strpos(strtoupper($sql), '(');
        if ($posOpen === false) {
            throw new DatabaseConversionException("Invalid CREATE TABLE: missing opening parenthesis.");
        }

        $len = strlen($sql);
        $parenCount = 0;
        $posClose = false;
        for ($i = $posOpen; $i < $len; $i++) {
            if ($sql[$i] === '(') $parenCount++;
            elseif ($sql[$i] === ')') $parenCount--;
            if ($parenCount === 0) {
                $posClose = $i;
                break;
            }
        }
        if ($posClose === false) {
            throw new DatabaseConversionException("Invalid CREATE TABLE: unbalanced parentheses.");
        }

        // Extract table name
        if (!preg_match('/CREATE TABLE (IF NOT EXISTS\s+)?("?)([^"\s]+)\2/i', substr($sql, 0, $posOpen), $matches)) {
            throw new DatabaseConversionException("Cannot parse table name.");
        }
        $ifNotExists = isset($matches[1]) ? 'IF NOT EXISTS ' : '';
        $tableName = $this->quoteIdentifier($matches[3], 'sqlite');

        // Extract column and constraint definitions
        $columnsDef = trim(substr($sql, $posOpen + 1, $posClose - $posOpen - 1));

        // Split lines by commas outside of nested parentheses
        $lines = [];
        $buffer = '';
        $parenLevel = 0;
        for ($i = 0; $i < strlen($columnsDef); $i++) {
            $char = $columnsDef[$i];
            if ($char === '(') $parenLevel++;
            elseif ($char === ')') $parenLevel--;
            if ($char === ',' && $parenLevel === 0) {
                $lines[] = trim($buffer);
                $buffer = '';
            } else {
                $buffer .= $char;
            }
        }
        if (trim($buffer) !== '') {
            $lines[] = trim($buffer);
        }

        $newLines = [];

        foreach ($lines as $line) {
            // Convert CHARACTER VARYING(n) to NVARCHAR(n)
            if (preg_match('/^("?)([^"\s]+)\1\s+character varying\s*\((\d+)\)(.*)$/i', ltrim($line), $colMatch)) {
                $colName = $this->quoteIdentifier($colMatch[2], 'sqlite');
                $length = trim($colMatch[3]);
                $rest = trim($colMatch[4]);
                $newLines[] = "{$colName} NVARCHAR({$length}) {$rest}";
                continue;
            }

            // Convert SERIAL/BIGSERIAL to INTEGER (handled explicitly)
            if (preg_match('/^("?)([^"\s]+)\1\s+(BIG)?SERIAL(.*)$/i', $line, $colMatch)) {
                $colName = $this->quoteIdentifier($colMatch[2], 'sqlite');
                $rest = strtoupper(trim($colMatch[4]));
                // Remove inline PRIMARY KEY if exists
                $rest = str_ireplace('PRIMARY KEY', '', $rest);
                $rest = trim(preg_replace('/\s+/', ' ', $rest));
                $newLines[] = "{$colName} INTEGER NOT NULL" . (!empty($rest) ? " {$rest}" : "");
                continue;
            }

            // Replace PostgreSQL-style quoted identifiers
            $line = preg_replace_callback('/"([^"]+)"/', fn($m) => $this->quoteIdentifier($m[1], 'sqlite'), $line);
            $newLines[] = $line;
        }

        // Replace TRUE/FALSE with 1/0 for BOOLEAN types
        foreach ($newLines as &$line) {
            if (preg_match('/\b(BOOLEAN|TINYINT\s*\(1\))\b/i', $line)) {
                $line = preg_replace('/DEFAULT\s+TRUE/i', 'DEFAULT 1', $line);
                $line = preg_replace('/DEFAULT\s+FALSE/i', 'DEFAULT 0', $line);
            }
        }

        // Convert types globally (after line-by-line handling)
        $typeMap = [
            'BIGINT' => 'INTEGER',
            'TIMESTAMP WITH TIME ZONE' => 'TIMESTAMP',
            'TIMESTAMP WITHOUT TIME ZONE' => 'TIMESTAMP',
            'BOOLEAN' => 'BOOLEAN', // SQLite uses INTEGER for booleans
            'JSONB' => 'JSON',
            'JSON' => 'JSON'
        ];
        $finalSql = "CREATE TABLE {$ifNotExists}{$tableName} (\n    " . implode(",\n    ", $newLines) . "\n);";
        $finalSql = str_ireplace(array_keys($typeMap), array_values($typeMap), $finalSql);

        $finalSql = preg_replace('/DEFAULT\s+TRUE/i', 'DEFAULT 1', $finalSql);
        $finalSql = preg_replace('/DEFAULT\s+FALSE/i', 'DEFAULT 0', $finalSql);

        // Final cleanup to avoid duplicate PRIMARY KEY
        $finalSql = preg_replace('/INTEGER\s+PRIMARY KEY\s+AUTOINCREMENT\s+NOT NULL\s+PRIMARY KEY/i', 'INTEGER NOT NULL', $finalSql);
        $finalSql = preg_replace('/\s+PRIMARY KEY\s+PRIMARY KEY/i', ' PRIMARY KEY', $finalSql);

        $finalSql = $this->fixLines($finalSql);

        $finalSql = preg_replace('/\bNOT\s+NULL\b(?:\s+NOT\s+NULL\b)+/i', 'NOT NULL', $finalSql);

        return trim($finalSql);
    }


    /**
     * Translates a CREATE TABLE statement from SQLite to MySQL.
     *
     * @param string $sql The SQLite CREATE TABLE statement.
     * @return string The translated MySQL CREATE TABLE statement.
     * @throws DatabaseConversionException If the SQL format is invalid.
     */
    public function sqliteToMySQL($sql)
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

        $lines = $this->splitSqlByCommaRespectingParentheses($columnsAndConstraints);
        $newLines = [];
        $primaryKeyColumn = null;

        // Cari apakah ada PRIMARY KEY di tingkat tabel
        foreach ($lines as $line) {
            if (preg_match('/^PRIMARY KEY\s*\(\s*"([^"]+)"\s*\)/i', trim($line), $pkMatch)) {
                $primaryKeyColumn = $pkMatch[1];
                break;
            }
        }

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            // Column definition
            if (preg_match('/^("?)([^"\s]+)\1\s+([a-zA-Z0-9_()]+)(.*)$/i', $line, $colMatches)) {
                $columnRaw = $colMatches[2];
                $columnName = $this->quoteIdentifier($columnRaw, 'mysql');
                $columnType = strtolower(trim($colMatches[3]));
                $columnDefinition = trim($colMatches[4]);

                $translatedType = $this->translateFieldType($columnType, 'sqlite', 'mysql');

                // INTEGER → BIGINT(20), REAL → DOUBLE
                $translatedType = str_ireplace('INTEGER', 'BIGINT(20)', $translatedType);
                $translatedType = str_ireplace('REAL', 'DOUBLE', $translatedType);

                // BOOLEAN
                if (preg_match('/BOOLEAN/i', $columnType) || preg_match('/DEFAULT\s+[01]/i', $columnDefinition)) {
                    $translatedType = 'TINYINT(1)';
                    $columnDefinition = str_ireplace('DEFAULT 1', 'DEFAULT TRUE', $columnDefinition);
                    $columnDefinition = str_ireplace('DEFAULT 0', 'DEFAULT FALSE', $columnDefinition);
                }

                // JSON detection
                if ($this->strContains(strtoupper($columnType), 'TEXT') && $this->strContains(strtoupper($columnRaw), 'JSON')) {
                    $translatedType = 'JSON';
                }

                // AUTO_INCREMENT untuk kolom PRIMARY KEY
                if ($columnRaw === $primaryKeyColumn && preg_match('/NOT NULL/i', $columnDefinition)) {
                    $translatedType = 'BIGINT(20)';
                    $columnDefinition = preg_replace('/NOT NULL/i', '', $columnDefinition);
                    $columnDefinition .= ' AUTO_INCREMENT';
                }

                $definition = trim("$columnName $translatedType $columnDefinition");
                $newLines[] = preg_replace('/\s+/', ' ', $definition);
            } elseif (preg_match('/^PRIMARY KEY\s*\((.*?)\)/i', $line, $pkMatch)) {
                // Tangani PRIMARY KEY tabel jika belum dilakukan di atas
                $keyColumns = trim($pkMatch[1]);
                $keyColumns = preg_replace_callback('/"([^"]+)"/', function ($m) {
                    return $this->quoteIdentifier($m[1], 'mysql');
                }, $keyColumns);
                $newLines[] = "PRIMARY KEY ($keyColumns)";
            } elseif (preg_match('/^UNIQUE\s*\((.*?)\)/i', $line, $uqMatch)) {
                $keyColumns = trim($uqMatch[1]);
                $keyColumns = preg_replace_callback('/"([^"]+)"/', function ($m) {
                    return $this->quoteIdentifier($m[1], 'mysql');
                }, $keyColumns);
                $newLines[] = "UNIQUE KEY ($keyColumns)";
            } else {
                // Constraint lainnya
                $line = preg_replace_callback('/"([^"]+)"/', function ($m) {
                    return $this->quoteIdentifier($m[1], 'mysql');
                }, $line);
                $newLines[] = $line;
            }
        }

        $finalSql = "CREATE TABLE " . $ifNotExists . $tableName . " (\n    " . implode(",\n    ", $newLines) . "\n)";
        $finalSql .= "\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

        $finalSql = preg_replace('/`PRIMARY` KEY/i', 'PRIMARY KEY', $finalSql);

        return $this->fixLine(trim($finalSql));
    }

    /**
     * Translates a CREATE TABLE statement from SQLite to PostgreSQL.
     *
     * This method parses the SQLite CREATE TABLE SQL string, extracts columns and constraints,
     * and converts them into a format compatible with PostgreSQL. It handles data type
     * conversions, primary keys (both inline and table-level), and optional column definitions.
     *
     * @param string $sql The SQLite CREATE TABLE statement.
     * @return string The translated PostgreSQL CREATE TABLE statement.
     * @throws DatabaseConversionException If the SQL format is invalid.
     */
    public function sqliteToPostgreSQL($sql)
    {
        $sql = trim($sql);
        $sql = preg_replace('/\s+/', ' ', $sql); // Normalize multiple spaces to single space

        // Extract table name and column/constraint definitions
        if (!preg_match('/CREATE TABLE (IF NOT EXISTS\s+)?("?)([^"\s]+)("?)\s*\((.*)\)/is', $sql, $matches)) {
            throw new DatabaseConversionException("Invalid SQLite CREATE TABLE statement format.");
        }

        $ifNotExists = isset($matches[1]) ? 'IF NOT EXISTS ' : '';
        $tableName = $this->quoteIdentifier($matches[3], 'postgresql');
        $columnsAndConstraints = trim($matches[5]);

        // Split column and constraint definitions, respecting parentheses inside types or expressions
        $lines = $this->splitSqlByCommaRespectingParentheses($columnsAndConstraints);
        $newLines = [];

        $primaryKeyColumn = null;
        $primaryKeyHandled = false;

        // Step 1: Detect table-level PRIMARY KEY for a single column
        foreach ($lines as $line) {
            if (preg_match('/^"?PRIMARY"?\s+KEY\s*\(\s*"([^"]+)"\s*\)/i', trim($line), $pkMatch)) {
                $primaryKeyColumn = $pkMatch[1];
                break;
            }
        }

        // Step 2: Process each column and constraint line
        foreach ($lines as $line) {
            $line = trim($line);
            
            if (empty($line)) continue;

            // Column definition
            if (preg_match('/^("?)([^"\s]+)\1\s+([a-zA-Z0-9_()]+)(.*)$/i', $line, $colMatches)) {
                $columnRaw = $colMatches[2];
                $columnName = $this->quoteIdentifier($columnRaw, 'postgresql');
                $columnType = strtoupper(trim($colMatches[3]));
                $columnDefinition = trim($colMatches[4]);

                $translatedType = $this->translateFieldType(strtolower($columnType), 'sqlite', 'postgresql');

                // Replace NVARCHAR with CHARACTER VARYING
                if ($this->strContains($columnType, 'NVARCHAR')) {
                    $translatedType = str_ireplace('NVARCHAR', 'CHARACTER VARYING', $translatedType);
                }

                // Replace DATETIME with TIMESTAMP WITHOUT TIME ZONE
                if ($this->strContains($columnType, 'DATETIME')) {
                    $translatedType = 'TIMESTAMP WITHOUT TIME ZONE';
                }

                // Detect and convert JSON column names with TEXT type to JSONB
                if ($this->strContains($columnType, 'TEXT') && $this->strContains(strtolower($columnRaw), 'json')) {
                    $translatedType = 'JSONB';
                }

                // Convert BOOLEAN representations
                if (($this->strContains($translatedType, 'INTEGER') &&
                    ($this->strContains(strtoupper($columnDefinition), 'DEFAULT 1') ||
                    $this->strContains(strtoupper($columnDefinition), 'DEFAULT 0'))) ||
                    $this->strContains($translatedType, 'BOOL')) {
                    $translatedType = 'BOOLEAN';
                    $columnDefinition = str_ireplace('DEFAULT 1', 'DEFAULT TRUE', $columnDefinition);
                    $columnDefinition = str_ireplace('DEFAULT 0', 'DEFAULT FALSE', $columnDefinition);
                }

                // Convert INTEGER NOT NULL with table-level PK into BIGSERIAL PRIMARY KEY
                if ($columnRaw === $primaryKeyColumn &&
                    $columnType === 'INTEGER' &&
                    preg_match('/NOT\s+NULL/i', $columnDefinition))
                {
                    $translatedType = 'BIGSERIAL';
                    $columnDefinition = preg_replace('/NOT\s+NULL/i', '', $columnDefinition);
                    $columnDefinition = trim($columnDefinition) . ' PRIMARY KEY NOT NULL';
                    $primaryKeyHandled = true;
                }

                // Check if column already defines PRIMARY KEY
                if (preg_match('/PRIMARY\s+KEY/i', $line)) {
                    if ($primaryKeyHandled) {
                        continue;
                    }
                    $primaryKeyHandled = true;
                }

                // Combine and normalize spacing
                $definition = trim("$columnName $translatedType $columnDefinition");
                $definition = preg_replace('/\s+/', ' ', $definition);
                $newLines[] = $definition;
            }
            // Handle PRIMARY KEY constraint
            elseif (preg_match('/^"?PRIMARY"?\s+KEY\s*\((.*?)\)/i', trim($line), $pkMatch)) {
                if ($primaryKeyHandled) {
                    // PRIMARY KEY already defined in a column, skip
                    continue;
                }

                $keyColumns = trim($pkMatch[1]);
                $keyColumns = preg_replace_callback('/"([^"]+)"/', function ($m) {
                    return $this->quoteIdentifier($m[1], 'postgresql');
                }, $keyColumns);
                $newLines[] = "PRIMARY KEY ($keyColumns)";
                $primaryKeyHandled = true;
            }
            // Handle UNIQUE constraint
            elseif (preg_match('/^UNIQUE\s*\((.*?)\)/i', $line, $uqMatch)) {
                $keyColumns = trim($uqMatch[1]);
                $keyColumns = preg_replace_callback('/"([^"]+)"/', function ($m) {
                    return $this->quoteIdentifier($m[1], 'postgresql');
                }, $keyColumns);
                $newLines[] = "UNIQUE ($keyColumns)";
            }
            // Handle all other lines (fallback)
            else {
                $line = preg_replace_callback('/"([^"]+)"/', function ($m) {
                    return $this->quoteIdentifier($m[1], 'postgresql');
                }, $line);
                $newLines[] = $line;
            }
        }

        // Final CREATE TABLE assembly
        $finalSql = "CREATE TABLE " . $ifNotExists . $tableName . " (\n    " . implode(",\n    ", $newLines) . "\n);";

        return $this->fixLine(trim($finalSql));
    }

    /**
     * Splits a SQL column/constraint string into separate parts using commas,
     * while respecting commas that are inside parentheses (e.g., data types or expressions).
     *
     * @param string $sql The part inside CREATE TABLE (...) to be split.
     * @return array An array of lines representing column or constraint definitions.
     */
    private function splitSqlByCommaRespectingParentheses($sql)
    {
        $result = [];
        $buffer = '';
        $parenLevel = 0;
        $length = strlen($sql);

        for ($i = 0; $i < $length; $i++) {
            $char = $sql[$i];
            if ($char === '(') {
                $parenLevel++;
            } elseif ($char === ')') {
                $parenLevel--;
            } elseif ($char === ',' && $parenLevel === 0) {
                $result[] = $buffer;
                $buffer = '';
                continue;
            }
            $buffer .= $char;
        }

        if (trim($buffer) !== '') {
            $result[] = $buffer;
        }

        return $result;
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
    public function translateCreateTable($sql, $sourceDialect, $targetDialect)
    {
        //$sql = $this->normalizeCreateTableSql($sql);
        $sourceDialect = $this->normalizeDialect($sourceDialect);
        $targetDialect = $this->normalizeDialect($targetDialect);

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

    /**
     * Returns the canonical name for a given database dialect alias.
     *
     * This method normalizes various aliases for common database dialects
     * into their standard (canonical) names. For example, 'pgsql' and 'postgres'
     * both map to 'postgresql', and 'mariadb' maps to 'mysql'.
     *
     * Supported aliases:
     * - mysql, mariadb → mysql
     * - postgres, pgsql, postgresql → postgresql
     * - sqlite, sqlite3 → sqlite
     *
     * @param string $dialect The input dialect name (case-insensitive).
     * @return string The canonical dialect name.
     *
     * @throws DatabaseConversionException If the dialect is not supported.
     */
    public function normalizeDialect($dialect)
    {
        $dialect = strtolower(trim($dialect));

        $mapping = [
            'mysql'     => 'mysql',
            'mariadb'   => 'mysql',
            'postgres'  => 'postgresql',
            'pgsql'     => 'postgresql',
            'postgresql'=> 'postgresql',
            'sqlite'    => 'sqlite',
            'sqlite3'   => 'sqlite',
        ];

        if (isset($mapping[$dialect])) {
            return $mapping[$dialect];
        }

        throw new DatabaseConversionException("Unsupported database dialect: " . $dialect);
    }

    public function normalizeCreateTableSql($sql)
    {
        $sql = trim($sql);
        $sql = str_replace("`", "", $sql); // Remove backticks for standard SQL
        $sql = preg_replace('/\s+/', ' ', $sql); // Normalize spaces
        $sql = preg_replace('/\s*;\s*$/', '', $sql); // Remove trailing semicolon if present
        return $sql;
    }

    /**
     * Checks if a string contains a specific substring.
     *
     * @param string $haystack The string to search within.
     * @param string $needle   The substring to search for.
     * @return bool            True if the substring is found, false otherwise.
     */
    public function strContains($haystack, $needle)
    {
        return strpos($haystack, $needle) !== false;
    }

    /**
     * Cleans up a multi-line SQL statement without altering its line structure.
     *
     * @param string $sql The SQL string to clean.
     * @return string The cleaned SQL string with preserved line breaks.
     */
    public function fixLines($sql)
    {
        // Normalize line endings (Windows/Linux/macOS compatibility)
        $sql = str_replace("\n", "\r\n", $sql);
        $sql = str_replace("\r\r\n", "\r\n", $sql);
        $sql = str_replace("\r", "\r\n", $sql);
        $sql = str_replace("\r\n\n", "\r\n", $sql);
        $sql = trim($sql);

        // Split into individual lines
        $lines = explode("\r\n", $sql);
        $fixedLines = [];

        foreach ($lines as $line) {
            $line = $this->fixLine($line);
            if (trim($line) !== '') {
                $fixedLines[] = $line;
            }
        }

        return implode("\r\n", $fixedLines);
    }

    /**
     * Cleans a single line of SQL by removing comments and excess whitespace.
     *
     * @param string $line A single line of SQL.
     * @return string The cleaned line.
     */
    public function fixLine($line)
    {
        // Remove single-line comments
        $line = preg_replace('/--.*$/', '', $line);

        // Remove inline multi-line comments
        $line = preg_replace('/\/\*.*?\*\//s', '', $line);

        // Remove spaces before commas
        $line = preg_replace('/\s+,/', ',', $line);

        

        return rtrim($line);
    }


}
