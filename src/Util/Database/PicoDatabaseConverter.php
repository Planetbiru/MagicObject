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
            "datetime" => "TIMESTAMP WITHOUT TIME ZONE",
            "date" => "DATE",
            "timestamptz" => "TIMESTAMP WITH TIME ZONE",
            "timestamp" => "TIMESTAMP WITH TIME ZONE",
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
    private function translateFieldType($type, $sourceDialect, $targetDialect)
    {
        $type = strtolower(trim($type));
        $type = preg_replace('/\s+/', ' ', $type); // Normalize spaces

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
                    return ($baseType === 'bigserial' ? 'BIGINT' : 'INT');
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
    public function mysqlToPostgreSQL($sql)
    {
        $sql = trim($sql);

        // Extract table name and body using a custom parser to handle nested parentheses
        if (!preg_match('/CREATE\s+TABLE\s+(IF\s+NOT\s+EXISTS\s+)?`?([^\s`(]+)`?\s*\(/i', $sql, $matches, PREG_OFFSET_CAPTURE)) {
            throw new DatabaseConversionException("Invalid MySQL CREATE TABLE statement format.");
        }

        $ifNotExists = isset($matches[1][0]) ? 'IF NOT EXISTS ' : '';
        $tableName = $this->quoteIdentifier($matches[2][0], 'postgresql');
        $startPos = $matches[0][1] + strlen($matches[0][0]) - 1;

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

        // Parse lines manually to preserve commas inside data types or defaults
        $lines = preg_split('/,(?![^\(\)]*\))/', $columnsSection);
        $newLines = [];
        $tableConstraints = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') continue;

            // Column definition
            if (preg_match('/^`?([^`\s]+)`?\s+([a-zA-Z0-9_\(\)]+)(.*)$/i', $line, $colMatches)) {
                $columnName = $this->quoteIdentifier($colMatches[1], 'postgresql');
                $columnType = strtolower(trim($colMatches[2]));
                $columnDefinition = trim($colMatches[3]);

                // Translate type
                $translatedType = $this->translateFieldType($columnType, 'mysql', 'postgresql');

                // Handle AUTO_INCREMENT
                if (stripos($columnDefinition, 'AUTO_INCREMENT') !== false) {
                    $translatedType = stripos($columnType, 'bigint') !== false ? 'BIGSERIAL' : 'SERIAL';
                    $columnDefinition = str_ireplace('AUTO_INCREMENT', '', $columnDefinition);
                    if (stripos($columnDefinition, 'PRIMARY KEY') === false) {
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
    public function postgresqlToMySQL($sql)
    {
        $sql = trim($sql);
        $sql = preg_replace('/\s+/', ' ', $sql); // Normalize whitespace

        // Find opening parenthesis for table definition
        $posOpen = strpos(strtoupper($sql), '(');
        if ($posOpen === false) {
            throw new DatabaseConversionException("Invalid CREATE TABLE: missing opening parenthesis.");
        }

        // Find the matching closing parenthesis
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
        if (!preg_match('/CREATE TABLE (IF NOT EXISTS\s+)?("?)([^"\s]+)("?)/i', substr($sql, 0, $posOpen), $matches)) {
            throw new DatabaseConversionException("Cannot parse table name.");
        }
        $ifNotExists = isset($matches[1]) ? 'IF NOT EXISTS ' : '';
        $tableName = $this->quoteIdentifier($matches[3], 'mysql');

        // Get column and constraint definitions
        $columnsDef = trim(substr($sql, $posOpen + 1, $posClose - $posOpen - 1));

        // Split into lines, handling nested parentheses
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
            // Ubah CHARACTER VARYING(n) ke VARCHAR(n)
            if (preg_match('/^("?)([^"\s]+)\1\s+character varying\s*\((\d+)\)(.*)$/i', $line, $colMatch)) {
                $colName = $this->quoteIdentifier($colMatch[2], 'mysql');
                $length = $colMatch[3];
                $rest = trim($colMatch[4]);
                $newLines[] = "{$colName} VARCHAR({$length}) {$rest}";
            } else {
                // Untuk baris lain, hanya ubah identifier dari PostgreSQL-style ke MySQL-style
                $line = preg_replace_callback('/"([^"]+)"/', function ($m) {
                    return $this->quoteIdentifier($m[1], 'mysql');
                }, $line);
                $newLines[] = $line;
            }
        }

        $finalSql = "CREATE TABLE {$ifNotExists}{$tableName} (\n    " . implode(",\n    ", $newLines) . "\n)";
        $finalSql .= "\nENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

        $finalSql = str_replace('TIMESTAMP WITH TIME ZONE', 'TIMESTAMP', $finalSql); // Convert PostgreSQL's TIMESTAMP WITH TIME ZONE to MySQL's TIMESTAMP
        $finalSql = str_replace('TIMESTAMP WITHOUT TIME ZONE', 'DATETIME', $finalSql); // Convert PostgreSQL's TIMESTAMP WITHOUT TIME ZONE to MySQL's DATETIME
        $finalSql = str_replace('SERIAL', 'BIGINT AUTO_INCREMENT', $finalSql); // Convert PostgreSQL's SERIAL to MySQL's INT AUTO_INCREMENT
        $finalSql = str_replace('BIGSERIAL', 'BIGINT AUTO_INCREMENT', $finalSql); // Convert PostgreSQL's BIGSERIAL to MySQL's BIGINT AUTO_INCREMENT
        $finalSql = str_replace('BOOLEAN', 'TINYINT(1)', $finalSql); // Convert PostgreSQL's BOOLEAN to MySQL's TINYINT(1)
        $finalSql = str_replace('JSONB', 'JSON', $finalSql); // Convert PostgreSQL's JSONB to MySQL's JSON
        $finalSql = str_replace('JSON', 'JSON', $finalSql); // Convert PostgreSQL's JSON to MySQL's JSON

        $finalSql = $this->fixLines($finalSql);

        return trim($finalSql);
    }

    /**
     * Translates a CREATE TABLE statement from PostgreSQL to SQLite.
     *
     * @param string $sql The PostgreSQL CREATE TABLE statement.
     * @return string The translated SQLite CREATE TABLE statement.
     * @throws DatabaseConversionException If the SQL format is invalid.
     */
    public function postgresqlToSQLite($sql)
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
            if (empty($line)) continue;

            // Column definition
            if (preg_match('/^("?)([^"\s]+)("?)\s+([a-zA-Z0-9_() ]+)(.*)$/i', $line, $colMatches)) {
                $columnName = $this->quoteIdentifier($colMatches[2], 'sqlite');
                $columnType = strtolower(trim($colMatches[4]));
                $columnDefinition = trim($colMatches[5]);

                $translatedType = $this->translateFieldType($columnType, 'postgresql', 'sqlite');

                // Handle SERIAL/BIGSERIAL
                if ($this->strContains(strtoupper($columnType), 'SERIAL')) {
                    $translatedType = 'INTEGER';
                    if ($this->strContains(strtoupper($columnDefinition), 'PRIMARY KEY')) {
                        $columnDefinition = str_ireplace('PRIMARY KEY', 'AUTOINCREMENT', $columnDefinition);
                        $primaryKeyColumnFound = true;
                    } else {
                        // If SERIAL but not primary key, just make it INTEGER
                        $columnDefinition = str_ireplace('PRIMARY KEY', '', $columnDefinition); // Remove any PRIMARY KEY if not main autoinc
                    }
                }

                // Handle BOOLEAN
                if ($this->strContains(strtoupper($columnType), 'BOOLEAN')) {
                    $translatedType = 'INTEGER'; // SQLite uses INTEGER for BOOLEAN
                    $columnDefinition = str_ireplace("DEFAULT TRUE", "DEFAULT 1", $columnDefinition);
                    $columnDefinition = str_ireplace("DEFAULT FALSE", "DEFAULT 0", $columnDefinition);
                }

                // Handle TIMESTAMP WITH TIME ZONE / WITHOUT TIME ZONE
                if ($this->strContains(strtoupper($columnType), 'TIMESTAMP WITH TIME ZONE') || $this->strContains(strtoupper($columnType), 'TIMESTAMP WITHOUT TIME ZONE')) {
                    $translatedType = 'TIMESTAMP'; // SQLite doesn't have explicit TIMESTAMP types, TIMESTAMP is common
                }
                
                // Handle JSONB
                if ($this->strContains(strtoupper($columnType), 'JSONB')) {
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
                    $newLines[] = 'PRIMARY KEY (' .trim($keyColumns) . ')';
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

        $finalSql = str_replace('CHARACTER VARYING', 'NVARCHAR', $finalSql); // Convert CHARACTER VARYING to NVARCHAR

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

        $lines = explode(',', $columnsAndConstraints);
        $newLines = [];
        $primaryKeyColumnFound = false;

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            // Column definition
            if (preg_match('/^("?)([^"\s]+)("?)\s+([a-zA-Z0-9_() ]+)(.*)$/i', $line, $colMatches)) {
                $columnName = $this->quoteIdentifier($colMatches[2], 'mysql');
                $columnType = strtolower(trim($colMatches[4]));
                $columnDefinition = trim($colMatches[5]);

                $translatedType = $this->translateFieldType($columnType, 'sqlite', 'mysql');

                // Handle INTEGER PRIMARY KEY AUTOINCREMENT
                if ($this->strContains(strtoupper($columnType), 'INTEGER') && $this->strContains(strtoupper($columnDefinition), 'AUTOINCREMENT')) {
                    $translatedType = 'INT'; // MySQL uses INT
                    $columnDefinition = str_ireplace('AUTOINCREMENT', 'AUTO_INCREMENT', $columnDefinition);
                    $primaryKeyColumnFound = true;
                }
                
                // Handle BOOLEAN (INTEGER)
                if ($this->strContains(strtoupper($columnType), 'INTEGER') && ($this->strContains(strtoupper($columnDefinition), 'DEFAULT 1') || $this->strContains(strtoupper($columnDefinition), 'DEFAULT 0'))) {
                    // This is a heuristic, assuming INTEGER with default 0/1 is boolean
                    $translatedType = 'TINYINT(1)';
                    $columnDefinition = str_ireplace("DEFAULT 1", "DEFAULT '1'", $columnDefinition);
                    $columnDefinition = str_ireplace("DEFAULT 0", "DEFAULT '0'", $columnDefinition);
                }

                // Handle DATETIME
                if ($this->strContains(strtoupper($columnType), 'DATETIME')) {
                    $translatedType = 'DATETIME'; // MySQL DATETIME
                }
                
                // Handle TEXT (for JSON)
                if ($this->strContains(strtoupper($columnType), 'TEXT') && $this->strContains(strtoupper($columnName), 'json')) {
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
    public function sqliteToPostgreSQL($sql)
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
            if (empty($line)) continue;

            // Column definition
            if (preg_match('/^("?)([^"\s]+)("?)\s+([a-zA-Z0-9_() ]+)(.*)$/i', $line, $colMatches)) {
                $columnName = $this->quoteIdentifier($colMatches[2], 'postgresql');
                $columnType = strtolower(trim($colMatches[4]));
                $columnDefinition = trim($colMatches[5]);

                $translatedType = $this->translateFieldType($columnType, 'sqlite', 'postgresql');

                // Handle INTEGER PRIMARY KEY AUTOINCREMENT
                if ($this->strContains(strtoupper($columnType), 'INTEGER') && $this->strContains(strtoupper($columnDefinition), 'AUTOINCREMENT')) {
                    $translatedType = 'SERIAL'; // PostgreSQL uses SERIAL
                    $columnDefinition = str_ireplace('AUTOINCREMENT', '', $columnDefinition);
                    $columnDefinition .= ' PRIMARY KEY'; // Add PRIMARY KEY explicitly
                    $primaryKeyColumnFound = true;
                }

                // Handle BOOLEAN (INTEGER)
                if ($this->strContains(strtoupper($columnType), 'INTEGER') && ($this->strContains(strtoupper($columnDefinition), 'DEFAULT 1') || $this->strContains(strtoupper($columnDefinition), 'DEFAULT 0'))) {
                    // This is a heuristic, assuming INTEGER with default 0/1 is boolean
                    $translatedType = 'BOOLEAN';
                    $columnDefinition = str_ireplace("DEFAULT 1", "DEFAULT TRUE", $columnDefinition);
                    $columnDefinition = str_ireplace("DEFAULT 0", "DEFAULT FALSE", $columnDefinition);
                }

                // Handle DATETIME
                if ($this->strContains(strtoupper($columnType), 'DATETIME')) {
                    $translatedType = 'TIMESTAMP WITHOUT TIME ZONE';
                }
                
                // Handle TEXT (for JSON)
                if ($this->strContains(strtoupper($columnType), 'TEXT') && $this->strContains(strtoupper($columnName), 'json')) {
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
        $sql = str_replace(["\r\n", "\r"], "\n", $sql);
        $sql = trim($sql);

        // Split into individual lines
        $lines = explode("\n", $sql);
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

        // Remove trailing spaces before a comma
        $line = preg_replace('/\s+,(\s*)$/', ',$1', $line);

        // Normalize internal spaces (preserve line breaks externally)
        $line = preg_replace('/\s+/', ' ', $line);

        return rtrim($line);
    }


}
