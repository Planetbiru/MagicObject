<?php

namespace MagicObject\Util\Database;

use Exception;
use MagicObject\Database\PicoDatabase;
use MagicObject\Database\PicoDatabaseQueryBuilder;
use MagicObject\Database\PicoDatabaseType;
use MagicObject\Database\PicoTableInfoExtended;
use MagicObject\MagicObject;
use MagicObject\SecretObject;
use PDO;

/**
 * Class PicoDatabaseUtilSqlServer
 *
 * Provides utility methods for SQL Server database operations, extending PicoDatabaseUtilBase 
 * and implementing PicoDatabaseUtilInterface. This class includes functions for retrieving 
 * column information, generating CREATE TABLE statements, dumping data to SQL insert statements, 
 * facilitating data imports, and ensuring data integrity during the import process.
 *
 * Key features:
 * - Retrieve column info from SQL Server tables.
 * - Generate CREATE TABLE statements.
 * - Convert data to SQL INSERT statements.
 * - Facilitate data import between databases.
 * - Ensure data integrity during imports.
 *
 * Designed for developers working with SQL Server to streamline database management tasks.
 *
 * @author Kamshory
 * @package MagicObject\Util\Database
 * @link https://github.com/Planetbiru/MagicObject
 */
class PicoDatabaseUtilSqlServer extends PicoDatabaseUtilBase implements PicoDatabaseUtilInterface // NOSONAR
{

    /**
     * Retrieves a list of columns for a specified table in SQL Server.
     *
     * This method queries the system views to obtain details about the columns
     * of the specified table, including their names, data types, nullability, 
     * default values, and whether they are part of the primary key.
     *
     * @param PicoDatabase $database The database connection instance.
     * @param string $tableName The name of the table to retrieve column information from.
     * @return array An array of associative arrays containing details about each column,
     *               where each associative array includes 'column_name', 'data_type',
     *               'is_nullable', 'column_default', and 'is_identity'.
     * @throws Exception If the database connection fails or the query cannot be executed.
     */
    public function getColumnList($database, $tableName)
    {
        $schema = $database->getDatabaseCredentials()->getDatabaseSchema();
        if (!isset($schema) || empty($schema)) {
            $schema = "dbo";  // Default schema for SQL Server is 'dbo'
        }

        $sql = "
            SELECT 
                c.name AS [ColumnName], 
                t.name AS [DataType], 
                c.is_nullable AS [IsNullable], 
                dc.definition AS [Default], 
                CASE 
                    WHEN ic.column_id IS NOT NULL THEN 'PRI'
                    ELSE ''
                END AS [Key], 
                CASE 
                    WHEN c.is_identity = 1 THEN 'AUTO_INCREMENT'
                    ELSE ''
                END AS [Extra]
            FROM sys.columns c
            INNER JOIN sys.tables ta ON c.object_id = ta.object_id
            INNER JOIN sys.types t ON c.user_type_id = t.user_type_id
            LEFT JOIN sys.default_constraints dc ON c.default_object_id = dc.object_id
            LEFT JOIN sys.index_columns ic ON c.column_id = ic.column_id AND ic.object_id = ta.object_id AND ic.index_id = 1
            WHERE ta.name = '$tableName' 
            AND SCHEMA_NAME(ta.schema_id) = '$schema'
        ";

        return $database->fetchAll($sql);
    }

    /**
     * Dumps the structure of a table as a SQL statement for SQL Server.
     *
     * This method generates a SQL CREATE TABLE statement based on the provided table information,
     * including the option to include or exclude specific clauses such as "IF NOT EXISTS" and 
     * "DROP TABLE IF EXISTS". It also handles the definition of primary keys if present.
     *
     * @param PicoTableInfoExtended $tableInfo The information about the table, including column details and primary keys.
     * @param string        $tableName         The name of the table for which the structure is being generated.
     * @param bool          $createIfNotExists Whether to add "IF NOT EXISTS" in the CREATE statement (default is false).
     * @param bool          $dropIfExists      Whether to add "DROP TABLE IF EXISTS" before the CREATE statement (default is false).
     * @param string|null   $engine            The storage engine to use for the table (optional, default is null).
     * @param string|null   $charset           The character set to use for the table (optional, default is null).
     * @return string                          The SQL statement to create the table, including column definitions and primary keys.
     */
    public function dumpStructure($tableInfo, $tableName, $createIfNotExists = false, $dropIfExists = false, $engine = 'InnoDB', $charset = 'utf8mb4')
    {
        $query = array();
        $columns = array();
        if($dropIfExists)
        {
            $query[] = "-- DROP TABLE IF EXISTS [$tableName];";
            $query[] = "";
        }
        $createStatement = "CREATE TABLE";
        if($createIfNotExists)
        {
            $createStatement .= " IF NOT EXISTS";
        }
        $autoIncrementKeys = $this->getAutoIncrementKey($tableInfo);

        $query[] = "$createStatement [$tableName] (";

        $cols = $tableInfo->getColumns();

        foreach($tableInfo->getSortedColumnName() as $columnName)
        {
            if(isset($cols[$columnName]))
            {
                $columns[] = $this->createColumn($cols[$columnName], $autoIncrementKeys, $tableInfo->getPrimaryKeys());
            }
        }
        $query[] = implode(",\r\n", $columns);
        $query[] = ");";

        return implode("\r\n", $query);
    }

    /**
     * Creates a column definition for a SQL statement for SQL Server.
     *
     * This method constructs a SQL column definition based on the provided column details,
     * including the column name, data type, nullability, default value, primary key status, 
     * and auto-increment settings. The resulting definition is formatted for use in a CREATE TABLE statement.
     *
     * @param array $column An associative array containing details about the column:
     *                      - string 'name': The name of the column.
     *                      - string 'type': The data type of the column (e.g., VARCHAR, INT).
     *                      - bool|string 'nullable': Indicates if the column allows NULL values 
     *                        ('true' or true for NULL; otherwise, NOT NULL).
     *                      - mixed MagicObject::KEY_DEFAULT_VALUE: The default value for the column (optional).
     * @param array $autoIncrementKeys An array of column names that should have IDENTITY(1,1) property.
     * @param array $primaryKeys An array of primary key columns, each being an associative array 
     *                           with at least a 'name' key.
     *
     * @return string The SQL column definition formatted as a string, suitable for inclusion in a CREATE TABLE statement.
     */
    public function createColumn($column, $autoIncrementKeys, $primaryKeys)
    {
        $pkCols = array();
        foreach ($primaryKeys as $col) {
            $pkCols[] = $col['name'];
        }

        $col = array();
        $col[] = "\t";  // Adding indentation for readability in SQL statements
        $columnName = $column[MagicObject::KEY_NAME];
        $columnType = $column[MagicObject::KEY_TYPE];

        $col[] = "[" . $columnName . "]";  // Enclose column name in square brackets
        $col[] = strtoupper($columnType);  // Add the column type (e.g., INT, VARCHAR)

        // Check if the column should auto-increment
        if (isset($autoIncrementKeys) && is_array($autoIncrementKeys) && in_array($column[MagicObject::KEY_NAME], $autoIncrementKeys)) {
            $col[] = 'IDENTITY(1,1)';
        }

        // Check if the column is part of primary keys
        if (in_array($columnName, $pkCols)) {
            $col[] = 'PRIMARY KEY';
        }

        // Determine if the column allows NULL values
        if (isset($column[self::KEY_NULLABLE]) && strtolower(trim($column[self::KEY_NULLABLE])) == 'true') {
            $col[] = "NULL";
        } else {
            $col[] = "NOT NULL";
        }

        // Set default value if specified
        if (isset($column[MagicObject::KEY_DEFAULT_VALUE])) {
            $defaultValue = $column[MagicObject::KEY_DEFAULT_VALUE];
            $defaultValue = $this->fixDefaultValue($defaultValue, $column[MagicObject::KEY_TYPE]);
            $col[] = "DEFAULT $defaultValue";
        }

        return implode(" ", $col);
    }

    /**
     * Fixes the default value for SQL insertion based on its type.
     *
     * This method processes the given default value according to the specified data type,
     * ensuring that it is correctly formatted for SQL insertion. For string-like types,
     * the value is enclosed in single quotes, while boolean and null values are returned 
     * as is.
     *
     * @param mixed $defaultValue The default value to fix, which can be a string, boolean, or null.
     * @param string $type The data type of the column (e.g., ENUM, CHAR, TEXT, INT, FLOAT, DOUBLE).
     *
     * @return mixed The fixed default value formatted appropriately for SQL insertion.
     */
    public function fixDefaultValue($defaultValue, $type)
    {
        $result = $defaultValue;
        if(stripos($type, 'tinyint(1)') !== false || self::isTypeBoolean($type))
        {
            $result = ($defaultValue != 0 || strtolower($defaultValue) == 'true') ? 'TRUE' : 'FALSE';
        }
        else if(self::isNativeValue($defaultValue))
        {
            $result =  $defaultValue;
        }
        else if(self::isTypeText($type))
        {
            $result =  "'".$defaultValue."'";
        }
        else if(self::isTypeInteger($type))
        {
            $defaultValue = preg_replace('/[^\d]/', '', $defaultValue);
            $result =  (int)$defaultValue;
        }
        else if(self::isTypeFloat($type))
        {
            $defaultValue = preg_replace('/[^\d.]/', '', $defaultValue);
            $result =  (float)$defaultValue;
        }
        return $result;
    }

    /**
     * Dumps a single record into an SQL INSERT statement for SQL Server.
     *
     * This method takes a data record and constructs an SQL INSERT statement 
     * for the specified table. It maps the values of the record to the corresponding 
     * columns based on the provided column definitions.
     *
     * @param array $columns An associative array where keys are column names and values are column details.
     * @param string $tableName The name of the table where the record will be inserted.
     * @param MagicObject $record The data record to be inserted, which provides a method to retrieve values.
     *
     * @return string The generated SQL INSERT statement.
     * @throws Exception If the record cannot be processed or if there are no values to insert.
     */
    public function dumpRecord($columns, $tableName, $record)
    {
        $value = $record->valueArray();
        $rec = array();
        foreach($value as $key=>$val)
        {
            if(isset($columns[$key]))
            {
                $rec[$columns[$key][MagicObject::KEY_NAME]] = $val;
            }
        }
        $queryBuilder = new PicoDatabaseQueryBuilder(PicoDatabaseType::DATABASE_TYPE_SQLSERVER);
        $queryBuilder->newQuery()
            ->insert()
            ->into($tableName)
            ->fields(array_keys($rec))
            ->values(array_values($rec));

        return $queryBuilder->toString();
    }

    /**
     * Retrieves the columns of a specified table from the database.
     *
     * This method executes a SQL query to show the columns of the given table and returns 
     * an associative array where the keys are column names and the values are their respective types.
     *
     * @param PicoDatabase $database Database connection object.
     * @param string $tableName Name of the table whose columns are to be retrieved.
     * @return array An associative array mapping column names to their types.
     * @throws Exception If the query fails or the table does not exist.
     */
    public function showColumns($database, $tableName)
    {
        $sql = "
            SELECT COLUMN_NAME, DATA_TYPE
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_NAME = '$tableName'
        ";
        $result = $database->fetchAll($sql, PDO::FETCH_ASSOC);

        $columns = array();
        foreach ($result as $row) {
            $columns[$row['COLUMN_NAME']] = $row['DATA_TYPE'];
        }
        return $columns;
    }

    /**
     * Automatically configures the import data settings based on the source and target databases.
     *
     * This method connects to the source and target databases, retrieves the list of existing 
     * tables, and updates the configuration for each target table by checking its presence in the 
     * source database. It handles exceptions and logs any errors encountered during the process.
     *
     * @param SecretObject $config The configuration object containing database and table information.
     * @return SecretObject The updated configuration object with modified table settings.
     */
    public function autoConfigureImportData($config)
    {
        $databaseConfigSource = $config->getDatabaseSource();
        $databaseConfigTarget = $config->getDatabaseTarget();

        $databaseSource = new PicoDatabase($databaseConfigSource);
        $databaseTarget = new PicoDatabase($databaseConfigTarget);
        try {
            $databaseSource->connect();
            $databaseTarget->connect();
            $tables = $config->getTable();

            $existingTables = array();
            foreach ($tables as $tb) {
                $existingTables[] = $tb->getTarget();
            }

            // For SQL Server, the query to list tables is different
            $sourceTableList = $databaseSource->fetchAll("SELECT name FROM sys.tables", PDO::FETCH_NUM);
            $targetTableList = $databaseTarget->fetchAll("SELECT name FROM sys.tables", PDO::FETCH_NUM);

            $sourceTables = call_user_func_array('array_merge', $sourceTableList);
            $targetTables = call_user_func_array('array_merge', $targetTableList);

            foreach ($targetTables as $target) {
                $tables = $this->updateConfigTable($databaseSource, $databaseTarget, $tables, $sourceTables, $target, $existingTables);
            }
            $config->setTable($tables);
        } catch (Exception $e) {
            error_log($e->getMessage());
        }
        return $config;
    }

    /**
     * Fixes imported data based on specified column types.
     *
     * This method processes the input data array and adjusts the values 
     * according to the expected types defined in the columns array. It 
     * supports boolean, integer, and float types.
     *
     * @param mixed[] $data The input data to be processed.
     * @param string[] $columns An associative array mapping column names to their types.
     * @return mixed[] The updated data array with fixed types.
     */
    public function fixImportData($data, $columns)
    {
        // Iterate through each item in the data array
        foreach ($data as $name => $value) {
            // Check if the column exists in the columns array
            if (isset($columns[$name])) {
                $type = $columns[$name];

                if (strtolower($type) == 'tinyint(1)' || self::isTypeBoolean($type)) {
                    // Process boolean types
                    $data = $this->fixBooleanData($data, $name, $value);
                } else if (self::isTypeInteger($type)) {
                    // Process integer types
                    $data = $this->fixIntegerData($data, $name, $value);
                } else if (self::isTypeFloat($type)) {
                    // Process float types
                    $data = $this->fixFloatData($data, $name, $value);
                }
            }
        }
        return $data;
    }

    /**
     * Returns the given column type without modification.
     *
     * This method simply returns the column type passed as an argument. It is useful 
     * when no conversion is needed, and you want to retrieve the column type as-is.
     *
     * @param string $columnType The column type to be returned.
     * @return string The same column type passed as input.
     */
    public function getColumnType($columnType)
    {
        return $columnType;
    }

}