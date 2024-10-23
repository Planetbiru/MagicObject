<?php

namespace MagicObject\Util\Database;

use Exception;
use MagicObject\Database\PicoDatabase;
use MagicObject\Database\PicoDatabaseQueryBuilder;
use MagicObject\Database\PicoDatabaseType;
use MagicObject\Database\PicoPageData;
use MagicObject\Database\PicoTableInfo;
use MagicObject\MagicObject;
use MagicObject\SecretObject;
use PDO;

/**
 * Class PicoDatabaseUtilPostgreSql
 *
 * Utility class for managing PostgreSQL database operations in the framework.
 * This class provides methods for retrieving table structures, generating SQL
 * statements for creating tables, dumping data into SQL insert statements,
 * and importing data from one database to another.
 *
 * Key Functionalities:
 * - Retrieve and display column information for tables.
 * - Generate SQL statements to create tables based on existing structures.
 * - Dump data from various sources into SQL insert statements.
 * - Facilitate the import of data between source and target databases, including
 *   handling pre and post-import scripts.
 * - Ensure data integrity by fixing types during the import process.
 *
 * @author Kamshory
 * @package MagicObject\Util\Database
 * @link https://github.com/Planetbiru/MagicObject
 */
class PicoDatabaseUtilPostgreSql extends PicoDatabaseUtilBase implements PicoDatabaseUtilInterface //NOSONAR
{
    const KEY_NAME = "name";

    /**
     * Retrieves a list of columns for a specified table.
     *
     * @param PicoDatabase $database Database connection.
     * @param string $picoTableName Table name.
     * @return array An array of column details.
     */
    public function getColumnList($database, $picoTableName)
    {
        $schema = $database->getDatabaseCredentials()->getDatabaseSchema();
        if(!isset($schema) || empty($schema))
        {
            $schema = "public";
        }
        $sql = "SELECT column_name, data_type, is_nullable, column_default 
                FROM information_schema.columns 
                WHERE table_schema = '$schema' AND table_name = '$picoTableName'";
        return $database->fetchAll($sql);
    }

    /**
     * Dumps the structure of a table as a SQL statement.
     *
     * @param PicoTableInfo $tableInfo Table information.
     * @param string $picoTableName Table name.
     * @param bool $createIfNotExists Whether to add "IF NOT EXISTS" in the create statement.
     * @param bool $dropIfExists Whether to add "DROP TABLE IF EXISTS" before the create statement.
     * @return string SQL statement to create the table.
     */
    public function dumpStructure($tableInfo, $picoTableName, $createIfNotExists = false, $dropIfExists = false, $engine = null, $charset = null)
    {
        $query = [];
        if ($dropIfExists) {
            $query[] = "-- DROP TABLE IF EXISTS \"$picoTableName\";";
            $query[] = "";
        }

        $createStatement = "CREATE TABLE";
        if ($createIfNotExists) {
            $createStatement .= " IF NOT EXISTS";
        }

        $autoIncrementKeys = $this->getAutoIncrementKey($tableInfo);

        $query[] = "$createStatement \"$picoTableName\" (";

        foreach ($tableInfo->getColumns() as $column) {
            $query[] = $this->createColumn($column);
        }
        $query[] = implode(",\r\n", $query);
        $query[] = ");";

        $pk = $tableInfo->getPrimaryKeys();
        if (isset($pk) && is_array($pk) && !empty($pk)) {
            $query[] = "";
            $query[] = "ALTER TABLE \"$picoTableName\"";
            foreach ($pk as $primaryKey) {
                $query[] = "\tADD PRIMARY KEY (\"$primaryKey[name]\")";
            }
            $query[] = ";";
        }

        return implode("\r\n", $query);
    }

    /**
     * Creates a column definition for a SQL statement.
     *
     * @param array $column Column details.
     * @return string SQL column definition.
     */
    public function createColumn($column)
    {
        $col = [];
        $col[] = "\t";
        $col[] = "\"" . $column[self::KEY_NAME] . "\"";
        $col[] = $column['type'];

        if (isset($column['nullable']) && strtolower(trim($column['nullable'])) == 'true') {
            $col[] = "NULL";
        } else {
            $col[] = "NOT NULL";
        }

        if (isset($column['default_value'])) {
            $defaultValue = $column['default_value'];
            $defaultValue = $this->fixDefaultValue($defaultValue, $column['type']);
            $col[] = "DEFAULT $defaultValue";
        }

        return implode(" ", $col);
    }

    /**
     * Fixes the default value for SQL insertion based on its type.
     *
     * @param string $defaultValue Default value to fix.
     * @param string $type Data type of the column.
     * @return string Fixed default value.
     */
    public function fixDefaultValue($defaultValue, $type)
    {
        if (strtolower($defaultValue) == 'true' || strtolower($defaultValue) == 'false' || strtolower($defaultValue) == 'null') {
            return $defaultValue;
        }

        if (stripos($type, 'varchar') !== false || stripos($type, 'char') !== false || stripos($type, 'text') !== false) {
            return "'" . addslashes($defaultValue) . "'";
        }

        return $defaultValue;
    }

    /**
     * Dumps a single record into an SQL insert statement.
     *
     * @param array $columns Columns of the target table.
     * @param string $picoTableName Table name.
     * @param MagicObject $record Data record.
     * @return string SQL insert statement.
     */
    public function dumpRecord($columns, $picoTableName, $record)
    {
        $value = $record->valueArray();
        $rec = [];
        foreach ($value as $key => $val) {
            if (isset($columns[$key])) {
                $rec[$columns[$key][self::KEY_NAME]] = $val;
            }
        }

        $queryBuilder = new PicoDatabaseQueryBuilder(PicoDatabaseType::DATABASE_TYPE_POSTGRESQL);
        $queryBuilder->newQuery()
            ->insert()
            ->into($picoTableName)
            ->fields(array_keys($rec))
            ->values(array_values($rec));

        return $queryBuilder->toString();
    }

    /**
     * Shows the columns of a specified table.
     *
     * @param PicoDatabase $database Database connection.
     * @param string $tableName Table name.
     * @return string[] An associative array of column names and their types.
     */
    public function showColumns($database, $tableName)
    {
        $schema = $database->getDatabaseCredentials()->getDatabaseSchema();
        if(!isset($schema) || empty($schema))
        {
            $schema = "public";
        }
        $sql = "SELECT column_name, data_type 
                FROM information_schema.columns 
                WHERE table_schema = '$schema' AND table_name = '$tableName'";
        $result = $database->fetchAll($sql, PDO::FETCH_ASSOC);

        $columns = [];
        foreach ($result as $row) {
            $columns[$row['column_name']] = $row['data_type'];
        }
        return $columns;
    }

    /**
     * Autoconfigure import data
     *
     * @param SecretObject $config Configuration
     * @return SecretObject
     */
    public function autoConfigureImportData($config)
    {
        $databaseConfigSource = $config->getDatabaseSource();
        $databaseConfigTarget = $config->getDatabaseTarget();

        $databaseSource = new PicoDatabase($databaseConfigSource);
        $databaseTarget = new PicoDatabase($databaseConfigTarget);

        $schemaSource = $databaseConfigSource->getDatabaseSchema();
        if(!isset($schemaSource) || empty($schemaSource))
        {
            $schemaSource = "public";
        }
        $schemaTarget = $databaseConfigTarget->getDatabaseSchema();
        if(!isset($schemaTarget) || empty($schemaTarget))
        {
            $schemaTarget = "public";
        }
        try {
            $databaseSource->connect();
            $databaseTarget->connect();
            $tables = $config->getTable();

            $existingTables = [];
            foreach ($tables as $tb) {
                $existingTables[] = $tb->getTarget();
            }

            $sourceTableList = $databaseSource->fetchAll("SELECT table_name FROM information_schema.tables WHERE table_schema='$schemaSource'", PDO::FETCH_NUM);
            $targetTableList = $databaseTarget->fetchAll("SELECT table_name FROM information_schema.tables WHERE table_schema='$schemaTarget'", PDO::FETCH_NUM);

            foreach ($sourceTableList as $sourceTable) {
                if (!in_array($sourceTable[0], $existingTables) && in_array($sourceTable[0], $targetTableList)) {
                    $config->addTable($sourceTable[0], $sourceTable[0]);
                }
            }

            return $config;
        } catch (Exception $e) {
            throw new Exception("Error during database connection: " . $e->getMessage());
        }
    }

    /**
     * Fix import data
     *
     * @param mixed[] $data Data
     * @param string[] $columns Columns
     * @return mixed[]
     */
    public function fixImportData($data, $columns)
    {
        foreach($data as $name=>$value)
        {
            if(isset($columns[$name]))
            {
                $type = $columns[$name];
                if(strtolower($type) == 'tinyint(1)' || strtolower($type) == 'boolean' || strtolower($type) == 'bool')
                {
                    $data = $this->fixBooleanData($data, $name, $value);
                }
                else if(stripos($type, 'integer') !== false || stripos($type, 'int(') !== false)
                {
                    $data = $this->fixIntegerData($data, $name, $value);
                }
                else if(stripos($type, 'float') !== false || stripos($type, 'double') !== false || stripos($type, 'decimal') !== false)
                {
                    $data = $this->fixFloatData($data, $name, $value);
                }
            }
        }
        return $data;
    }

    /**
     * Create query insert with multiple record
     *
     * @param string $tableName Table name
     * @param array $data Data
     * @return string
     */
    public function insert($tableName, $data)
    {
        // Kumpulkan semua kolom
        $columns = array();
        foreach ($data as $record) {
            $columns = array_merge($columns, array_keys($record));
        }
        $columns = array_unique($columns);

        // Buat placeholder untuk prepared statement
        $placeholdersArr = array_fill(0, count($columns), '?');
        $placeholders = '(' . implode(', ', $placeholdersArr) . ')';

        // Buat query INSERT
        $query = "INSERT INTO $tableName (" . implode(', ', $columns) . ") \r\nVALUES \r\n".
        implode(",\r\n", array_fill(0, count($data), $placeholders));

        // Siapkan nilai untuk bind
        $values = array();
        foreach ($data as $record) {
            foreach ($columns as $column) {
                $values[] = isset($record[$column]) && $record[$column] !== null ? $record[$column] : null;
            }
        }

        // Fungsi untuk menambahkan single quote jika elemen adalah string

        // Format elemen array
        $formattedElements = array_map(function($element){
            return $this->fixData($element);
        }, $values);

        // Ganti tanda tanya dengan elemen array yang telah diformat
        return vsprintf(str_replace('?', '%s', $query), $formattedElements);
    }
}
