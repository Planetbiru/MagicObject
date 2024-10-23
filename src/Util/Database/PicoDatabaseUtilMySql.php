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
 * Class PicoDatabaseUtilMySql
 *
 * Utility class for managing MySQL database operations in the framework.
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
class PicoDatabaseUtilMySql //NOSONAR
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
        $sql = "SHOW COLUMNS FROM $picoTableName";
        return $database->fetchAll($sql);
    }

    /**
     * Gets the auto-increment keys from the provided table information.
     *
     * @param PicoTableInfo $tableInfo Table information.
     * @return array An array of auto-increment key names.
     */
    public function getAutoIncrementKey($tableInfo)
    {
        $autoIncrement = $tableInfo->getAutoIncrementKeys();
        $autoIncrementKeys = array();
        if(is_array($autoIncrement) && !empty($autoIncrement))
        {
            foreach($autoIncrement as $col)
            {
                if($col["strategy"] == 'GenerationType.IDENTITY')
                {
                    $autoIncrementKeys[] = $col["name"];
                }
            }
        }
        return $autoIncrementKeys;
    }

    /**
     * Dumps the structure of a table as a SQL statement.
     *
     * @param PicoTableInfo $tableInfo Table information.
     * @param string $picoTableName Table name.
     * @param bool $createIfNotExists Whether to add "IF NOT EXISTS" in the create statement.
     * @param bool $dropIfExists Whether to add "DROP TABLE IF EXISTS" before the create statement.
     * @param string $engine Storage engine (default is 'InnoDB').
     * @param string $charset Character set (default is 'utf8mb4').
     * @return string SQL statement to create the table.
     */
    public function dumpStructure($tableInfo, $picoTableName, $createIfNotExists = false, $dropIfExists = false, $engine = 'InnoDB', $charset = 'utf8mb4')
    {
        $query = array();
        $columns = array();
        if($dropIfExists)
        {
            $query[] = "-- DROP TABLE IF EXISTS `$picoTableName`;";
            $query[] = "";
        }
        $createStatement = "";

        $createStatement = "CREATE TABLE";
        if($createIfNotExists)
        {
            $createStatement .= " IF NOT EXISTS";
        }

        $autoIncrementKeys = $this->getAutoIncrementKey($tableInfo);

        $query[] = "$createStatement `$picoTableName` (";

        foreach($tableInfo->getColumns() as $column)
        {
            $columns[] = $this->createColumn($column);
        }
        $query[] = implode(",\r\n", $columns);
        $query[] = ") ENGINE=$engine DEFAULT CHARSET=$charset;";

        $pk = $tableInfo->getPrimaryKeys();
        if(isset($pk) && is_array($pk) && !empty($pk))
        {
            $query[] = "";
            $query[] = "ALTER TABLE `$picoTableName`";
            foreach($pk as $primaryKey)
            {
                $query[] = "\tADD PRIMARY KEY (`$primaryKey[name]`)";
            }
            $query[] = ";";
        }

        foreach($tableInfo->getColumns() as $column)
        {
            if(isset($autoIncrementKeys) && is_array($autoIncrementKeys) && in_array($column[self::KEY_NAME], $autoIncrementKeys))
            {
                $query[] = "";
                $query[] = "ALTER TABLE `$picoTableName` \r\n\tMODIFY ".trim($this->createColumn($column), " \r\n\t ")." AUTO_INCREMENT";
                $query[] = ";";
            }
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
        $col = array();
        $col[] = "\t";
        $col[] = "`".$column[self::KEY_NAME]."`";
        $col[] = $column['type'];
        if(isset($column['nullable']) && strtolower(trim($column['nullable'])) == 'true')
        {
            $col[] = "NULL";
        }
        else
        {
            $col[] = "NOT NULL";
        }
        if(isset($column['default_value']))
        {
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
        if(strtolower($defaultValue) == 'true' || strtolower($defaultValue) == 'false' || strtolower($defaultValue) == 'null')
        {
            return $defaultValue;
        }
        if(stripos($type, 'enum') !== false || stripos($type, 'char') !== false || stripos($type, 'text') !== false || stripos($type, 'int') !== false || stripos($type, 'float') !== false || stripos($type, 'double') !== false)
        {
            return "'".$defaultValue."'";
        }
        return $defaultValue;
    }

    /**
     * Dumps data from various sources into SQL INSERT statements.
     *
     * This method processes data from PicoPageData, MagicObject, or an array of MagicObject instances 
     * and generates SQL INSERT statements. It supports batching of records and allows for a callback 
     * function to handle the generated SQL statements.
     *
     * @param array $columns Array of columns for the target table.
     * @param string $picoTableName Name of the target table.
     * @param MagicObject|PicoPageData|array $data Data to be dumped. Can be a PicoPageData instance, 
     *                                             a MagicObject instance, or an array of MagicObject instances.
     * @param int $maxRecord Maximum number of records to process in a single query (default is 100).
     * @param callable|null $callbackFunction Optional callback function to process the generated SQL 
     *                                         statements. The function should accept a single string parameter 
     *                                         representing the SQL statement.
     * @return string|null SQL INSERT statements or null if no data was processed.
     */
    public function dumpData($columns, $picoTableName, $data, $maxRecord = 100, $callbackFunction = null) //NOSONAR
    {
        // Check if $data is an instance of PicoPageData
        if($data instanceof PicoPageData)
        {
            // Handle case where fetching data is not required
            if($data->getFindOption() & MagicObject::FIND_OPTION_NO_FETCH_DATA && $maxRecord > 0 && isset($callbackFunction) && is_callable($callbackFunction))
            {
                $records = array();
                $stmt = $data->getPDOStatement();
                // Fetch records in batches
                while($data = $stmt->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT))
                {
                    // Ensure data has all required columns
                    $data = $this->processDataMapping($data, $columns);
                    if(count($records) < $maxRecord)
                    {
                        $records[] = $data;
                    }
                    else
                    {
                        if(isset($callbackFunction) && is_callable($callbackFunction))
                        {
                            // Call the callback function with the generated SQL
                            $sql = $this->insert($picoTableName, $records);
                            call_user_func($callbackFunction, $sql);
                        }
                        // Reset the records buffer
                        $records = array();
                    }
                }
                // Handle any remaining records
                if(!empty($records) && isset($callbackFunction) && is_callable($callbackFunction))
                {
                    $sql = $this->insert($picoTableName, $records);
                    call_user_func($callbackFunction, $sql);
                }
            }
            else if(isset($data->getResult()[0]))
            {
                // If data is available, dump records directly
                return $this->dumpRecords($columns, $picoTableName, $data->getResult());
            }
        }
        else if($data instanceof MagicObject)
        {
            // Handle a single MagicObject instance
            return $this->dumpRecords($columns, $picoTableName, array($data));
        }
        else if(is_array($data) && isset($data[0]) && $data[0] instanceof MagicObject)
        {
            // Handle an array of MagicObject instances
            return $this->dumpRecords($columns, $picoTableName, $data);
        }
        return null; // Return null if no valid data was processed
    }

    /**
     * Constructs an SQL INSERT statement for a single record.
     *
     * This method takes a data record and maps it to the corresponding columns of the target table, 
     * generating an SQL INSERT statement. It uses the PicoDatabaseQueryBuilder to build the query.
     *
     * @param array $columns Associative array mapping column names to their definitions in the target table.
     * @param string $picoTableName Name of the target table where the record will be inserted.
     * @param MagicObject $record The data record to be dumped into the SQL statement.
     * @return string The generated SQL INSERT statement.
     */
    public function dumpRecords($columns, $picoTableName, $data)
    {
        $result = "";
        foreach($data as $record)
        {
            $result .= $this->dumpRecord($columns, $picoTableName, $record).";\r\n";
        }
        return $result;
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
        $rec = array();
        foreach($value as $key=>$val)
        {
            if(isset($columns[$key]))
            {
                $rec[$columns[$key][self::KEY_NAME]] = $val;
            }
        }
        $queryBuilder = new PicoDatabaseQueryBuilder(PicoDatabaseType::DATABASE_TYPE_MYSQL);
        $queryBuilder->newQuery()
            ->insert()
            ->into($picoTableName)
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
        $sql = "SHOW COLUMNS FROM $tableName";
        $result = $database->fetchAll($sql, PDO::FETCH_ASSOC);

        $columns = array();
        foreach($result as $row)
        {
            $columns[$row['Field']] = $row['Type'];
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
        try
        {
            $databaseSource->connect();
            $databaseTarget->connect();
            $tables = $config->getTable();

            $existingTables = array();
            foreach($tables as $tb)
            {
                $existingTables[] = $tb->getTarget();
            }

            $sourceTableList = $databaseSource->fetchAll("SHOW TABLES", PDO::FETCH_NUM);
            $targetTableList = $databaseTarget->fetchAll("SHOW TABLES", PDO::FETCH_NUM);

            $sourceTables = call_user_func_array('array_merge', $sourceTableList);
            $targetTables = call_user_func_array('array_merge', $targetTableList);

            foreach($targetTables as $target)
            {
                $tables = $this->updateConfigTable($databaseSource, $databaseTarget, $tables, $sourceTables, $target, $existingTables);
            }
            $config->setTable($tables);
        }
        catch(Exception $e)
        {
            error_log($e->getMessage());
        }
        return $config;
    }

    /**
     * Automatically configures import data settings from one database to another.
     *
     * This method checks if the target table exists in the existing tables. If it does not, it creates 
     * a new `SecretObject` for the table, determining whether the table is present in the source 
     * database and configuring the mapping accordingly.
     *
     * @param PicoDatabase $databaseSource The source database connection.
     * @param PicoDatabase $databaseTarget The target database connection.
     * @param array $tables The current array of table configurations.
     * @param array $sourceTables List of source table names.
     * @param string $target The name of the target table to be configured.
     * @param array $existingTables List of existing tables in the target database.
     * @return array Updated array of table configurations with the new table info added if applicable.
     */
    public function updateConfigTable($databaseSource, $databaseTarget, $tables, $sourceTables, $target, $existingTables)
    {
        if(!in_array($target, $existingTables))
        {
            $tableInfo = new SecretObject();
            if(in_array($target, $sourceTables))
            {
                // ada di database sumber
                $tableInfo->setTarget($target);
                $tableInfo->setSource($target);
                $map = $this->createMapTemplate($databaseSource, $databaseTarget, $target);
                if(isset($map) && !empty($map))
                {
                    $tableInfo->setMap($map);
                }
            }
            else
            {
                // tidak ada di database sumber
                $tableInfo->setTarget($target);
                $tableInfo->setSource("???");
            }
            $tables[] = $tableInfo;
        }
        return $tables;
    }

    /**
     * Creates a mapping template between source and target database tables.
     *
     * This method generates a mapping array that indicates which columns
     * in the target table do not exist in the source table, providing a template
     * for further processing or data transformation.
     *
     * @param PicoDatabase $databaseSource The source database connection.
     * @param PicoDatabase $databaseTarget The target database connection.
     * @param string $target The name of the target table.
     * @return string[] An array of mapping strings indicating missing columns in the source.
     */
    public function createMapTemplate($databaseSource, $databaseTarget, $target)
    {
        $targetColumns = array_keys($this->showColumns($databaseTarget, $target));
        $sourceColumns = array_keys($this->showColumns($databaseSource, $target));
        $map = array();
        foreach($targetColumns as $column)
        {
            if(!in_array($column, $sourceColumns))
            {
                $map[] = "$column : ???";
            }
        }
        return $map;
    }

    /**
     * Imports data from the source database to the target database.
     *
     * This method connects to the source and target databases, executes any pre-import scripts,
     * transfers data from the source tables to the target tables, and executes any post-import scripts.
     *
     * @param SecretObject $config Configuration object containing database and table details.
     * @param callable $callbackFunction Callback function to execute SQL scripts.
     * @return bool Returns true on successful import, false on failure.
     */
    public function importData($config, $callbackFunction)
    {
        $databaseConfigSource = $config->getDatabaseSource();
        $databaseConfigTarget = $config->getDatabaseTarget();

        $databaseSource = new PicoDatabase($databaseConfigSource);
        $databaseTarget = new PicoDatabase($databaseConfigTarget);
        try
        {
            $databaseSource->connect();
            $databaseTarget->connect();
            $tables = $config->getTable();
            $maxRecord = $config->getMaximumRecord();

            // query pre import data
            foreach($tables as $tableInfo)
            {
                $tableNameTarget = $tableInfo->getTarget();
                $tableNameSource = $tableInfo->getSource();
                $preImportScript = $tableInfo->getPreImportScript();
                if($this->isNotEmpty($preImportScript))
                {
                    foreach($preImportScript as $sql)
                    {
                        call_user_func($callbackFunction, $sql, $tableNameSource, $tableNameTarget);
                    }
                }
            }

            // import data
            foreach($tables as $tableInfo)
            {
                $tableNameTarget = $tableInfo->getTarget();
                $tableNameSource = $tableInfo->getSource();
                $this->importDataTable($databaseSource, $databaseTarget, $tableNameSource, $tableNameTarget, $tableInfo, $maxRecord, $callbackFunction);
            }

            // query post import data
            foreach($tables as $tableInfo)
            {
                $tableNameTarget = $tableInfo->getTarget();
                $tableNameSource = $tableInfo->getSource();
                $postImportScript = $tableInfo->getPostImportScript();
                if($this->isNotEmpty($postImportScript))
                {
                    foreach($postImportScript as $sql)
                    {
                        call_user_func($callbackFunction, $sql, $tableNameSource, $tableNameTarget);
                    }
                }
            }
        }
        catch(Exception $e)
        {
            error_log($e->getMessage());
            return false;
        }
        return true;
    }

    /**
     * Checks if the provided array is not empty.
     *
     * This method verifies that the input is an array and contains at least one element.
     *
     * @param array $array The array to be checked.
     * @return bool True if the array is not empty; otherwise, false.
     */
    public function isNotEmpty($array)
    {
        return $array != null && is_array($array) && !empty($array);
    }

    /**
     * Imports data from a source database table to a target database table.
     *
     * This method fetches records from the specified source table and processes them 
     * according to the provided mapping and column information. It uses a callback 
     * function to handle the generated SQL insert statements in batches, up to a 
     * specified maximum record count.
     *
     * @param PicoDatabase $databaseSource The source database from which to import data.
     * @param PicoDatabase $databaseTarget The target database where data will be inserted.
     * @param string $tableNameSource The name of the source table.
     * @param string $tableNameTarget The name of the target table.
     * @param SecretObject $tableInfo Information about the table, including mapping and constraints.
     * @param int $maxRecord The maximum number of records to process in a single batch.
     * @param callable $callbackFunction A callback function to handle the generated SQL statements.
     * @return bool True on success, false on failure.
     */
    public function importDataTable($databaseSource, $databaseTarget, $tableNameSource, $tableNameTarget, $tableInfo, $maxRecord, $callbackFunction)
    {
        $maxRecord = $this->getMaxRecord($tableInfo, $maxRecord);
        try
        {
            $columns = $this->showColumns($databaseTarget, $tableNameTarget);
            $queryBuilderSource = new PicoDatabaseQueryBuilder($databaseSource);
            $sourceTable = $tableInfo->getSource();
            $queryBuilderSource->newQuery()
                ->select("*")
                ->from($sourceTable);
            $stmt = $databaseSource->query($queryBuilderSource);
            $records = array();
            while($data = $stmt->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT))
            {
                $data = $this->processDataMapping($data, $columns, $tableInfo->getMap());
                if(count($records) < $maxRecord)
                {
                    $records[] = $data;
                }
                else
                {
                    if(isset($callbackFunction) && is_callable($callbackFunction))
                    {
                        $sql = $this->insert($tableNameTarget, $records);
                        call_user_func($callbackFunction, $sql, $tableNameSource, $tableNameTarget);
                    }
                    // reset buffer
                    $records = array();
                }
            }
            if(!empty($records) && isset($callbackFunction) && is_callable($callbackFunction))
            {
                $sql = $this->insert($tableNameTarget, $records);
                call_user_func($callbackFunction, $sql, $tableNameSource, $tableNameTarget);
            }
        }
        catch(Exception $e)
        {
            error_log($e->getMessage());
            return false;
        }
        return true;
    }

    /**
     * Retrieves the maximum record limit for a query.
     *
     * This method checks the specified table information for a maximum record value
     * and ensures that the returned value is at least 1. If the table's maximum
     * record limit is defined, it overrides the provided maximum record.
     *
     * @param SecretObject $tableInfo The table information containing maximum record settings.
     * @param int $maxRecord The maximum record limit per query specified by the user.
     * @return int The effective maximum record limit to be used in queries.
     */
    public function getMaxRecord($tableInfo, $maxRecord)
    {
        // Check if the table information specifies a maximum record limit
        if ($tableInfo->getMaximumRecord() !== null) {
            $maxRecord = $tableInfo->getMaximumRecord(); // Override with table's maximum record
        }

        // Ensure the maximum record is at least 1
        if ($maxRecord < 1) {
            $maxRecord = 1;
        }

        return $maxRecord; // Return the final maximum record value
    }
    
    /**
     * Processes data mapping according to specified column types and mappings.
     *
     * This method updates the input data by mapping source fields to target fields
     * based on the provided mappings, then filters and fixes the data types 
     * according to the column definitions.
     *
     * @param mixed[] $data The input data to be processed.
     * @param string[] $columns An associative array mapping column names to their types.
     * @param string[]|null $maps Optional array of mapping definitions in the format 'target:source'.
     * @return mixed[] The updated data array with fixed types and mappings applied.
     */
    public function processDataMapping($data, $columns, $maps = null)
    {
        // Check if mappings are provided and are in array format
        if(isset($maps) && is_array($maps))
        {
            foreach($maps as $map)
            {
                // Split the mapping into target and source
                $arr = explode(':', $map, 2);
                $target = trim($arr[0]);
                $source = trim($arr[1]);
                // Map the source value to the target key
                if (isset($data[$source])) {
                    $data[$target] = $data[$source];
                    unset($data[$source]); // Remove the source key
                }
            }
        }
        // Filter the data to include only keys present in columns
        $data = array_intersect_key($data, array_flip(array_keys($columns)));

        // Fix data types based on column definitions
        $data = $this->fixImportData($data, $columns);
        return $data; // Return the processed data
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
        foreach($data as $name=>$value)
        {
            // Check if the column exists in the columns array
            if(isset($columns[$name]))
            {
                $type = $columns[$name];
                
                if(strtolower($type) == 'tinyint(1)' || strtolower($type) == 'boolean' || strtolower($type) == 'bool')
                {
                    // Process boolean types
                    $data = $this->fixBooleanData($data, $name, $value);
                }
                else if(stripos($type, 'integer') !== false || stripos($type, 'int(') !== false)
                {
                    // Process integer types
                    $data = $this->fixIntegerData($data, $name, $value);
                }
                else if(stripos($type, 'float') !== false || stripos($type, 'double') !== false || stripos($type, 'decimal') !== false)
                {
                    // Process float types
                    $data = $this->fixFloatData($data, $name, $value);
                }
            }
        }
        return $data;
    }

    /**
     * Fixes data for safe use in SQL queries.
     *
     * This method processes a given value and formats it as a string suitable for SQL.
     * It handles strings, booleans, nulls, and other data types appropriately.
     *
     * @param mixed $value The value to be processed.
     * @return string The formatted string representation of the value.
     */
    public function fixData($value)
    {
        // Initialize the return variable
        $ret = null;

        // Process string values
        if (is_string($value))
        {
            $ret = "'" . addslashes($value) . "'"; // Escape single quotes for SQL
        }
        else if(is_bool($value))
        {
            $ret = $value === true ? 'true' : 'false'; // Convert boolean to string
        }
        else if ($value === null)
        {
            // Handle null values
            $ret = "null"; // Return SQL null representation
        }
        else
        {
            // Handle other types (e.g., integers, floats)
            $ret = $value; // Use the value as-is
        }
        return $ret; // Return the processed value
    }

    /**
     * Fixes boolean data in the provided array.
     *
     * This method updates the specified key in the input array to ensure 
     * that its value is a boolean. If the value is null or an empty string, 
     * it sets the key to null. Otherwise, it converts the value to a boolean 
     * based on the condition that if it equals 1, it is set to true; otherwise, false.
     *
     * @param mixed[] $data The input array containing data.
     * @param string $name The key in the array to update.
     * @param mixed $value The value to be processed.
     * @return mixed[] The updated array with the fixed boolean data.
     */
    public function fixBooleanData($data, $name, $value)
    {
        // Check if the value is null or an empty string
        if($value === null || $value === '')
        {
            $data[$name] = null; // Set to null if the value is not valid
        }
        else
        {
            // Convert the value to a boolean (true if 1, false otherwise)
            $data[$name] = $data[$name] == 1 ? true : false;
        }
        return $data; // Return the updated array
    }

    /**
     * Fixes integer data in the provided array.
     *
     * This method updates the specified key in the input array to ensure 
     * that its value is an integer. If the value is null or an empty string, 
     * it sets the key to null. Otherwise, it converts the value to an integer.
     *
     * @param mixed[] $data The input array containing data.
     * @param string $name The key in the array to update.
     * @param mixed $value The value to be processed.
     * @return mixed[] The updated array with the fixed integer data.
     */
    public function fixIntegerData($data, $name, $value)
    {
        // Check if the value is null or an empty string
        if($value === null || $value === '')
        {
            $data[$name] = null; // Set to null if value is not valid
        }
        else
        {
            // Convert the value to an integer
            $data[$name] = intval($data[$name]);
        }
        return $data; // Return the updated array
    }

    /**
     * Fixes float data in the provided array.
     *
     * This method updates the specified key in the input array to ensure 
     * that its value is a float. If the value is null or an empty string, 
     * it sets the key to null. Otherwise, it converts the value to a float.
     *
     * @param mixed[] $data The input array containing data.
     * @param string $name The key in the array to update.
     * @param mixed $value The value to be processed.
     * @return mixed[] The updated array with the fixed float data.
     */
    public function fixFloatData($data, $name, $value)
    {
        // Check if the value is null or an empty string
        if($value === null || $value === '')
        {
            $data[$name] = null; // Set to null if value is not valid
        }
        else
        {
            // Convert the value to a float
            $data[$name] = floatval($data[$name]);
        }
        return $data; // Return the updated array
    }

    /**
     * Creates an SQL INSERT query for multiple records.
     *
     * This method generates an INSERT statement for a specified table and prepares the values 
     * for binding in a batch operation. It supports multiple records and ensures proper 
     * formatting of values.
     *
     * @param string $tableName Name of the table where data will be inserted.
     * @param array $data An array of associative arrays, where each associative array 
     *                    represents a record to be inserted.
     * @return string The generated SQL INSERT statement with placeholders for values.
     */
    public function insert($tableName, $data)
    {
        // Collect all unique columns from the data records
        $columns = array();
        foreach ($data as $record) {
            $columns = array_merge($columns, array_keys($record));
        }
        $columns = array_unique($columns);

        // Create placeholders for the prepared statement
        $placeholdersArr = array_fill(0, count($columns), '?');
        $placeholders = '(' . implode(', ', $placeholdersArr) . ')';

        // Build the INSERT query
        $query = "INSERT INTO $tableName (" . implode(', ', $columns) . ") \r\nVALUES \r\n".
        implode(",\r\n", array_fill(0, count($data), $placeholders));

        // Prepare values for binding
        $values = array();
        foreach ($data as $record) {
            foreach ($columns as $column) {
                // Use null if the value is not set
                $values[] = isset($record[$column]) && $record[$column] !== null ? $record[$column] : null;
            }
        }

        // Format each value for safe SQL insertion
        $formattedElements = array_map(function($element){
            return $this->fixData($element);
        }, $values);

        // Replace placeholders with formatted values
        return vsprintf(str_replace('?', '%s', $query), $formattedElements);
    }

    /**
     * Converts a MariaDB CREATE TABLE query to a PostgreSQL compatible query.
     *
     * This function takes a SQL CREATE TABLE statement written for MariaDB 
     * and transforms it into a format compatible with PostgreSQL. It handles 
     * common data types and syntax differences between the two databases.
     *
     * @param string $mariadbQuery The MariaDB CREATE TABLE query to be converted.
     * @return string The converted PostgreSQL CREATE TABLE query.
     */
    public function convertMariaDbToPostgreSql($mariadbQuery) {
        // Remove comments
        $query = preg_replace('/--.*?\n|\/\*.*?\*\//s', '', $mariadbQuery);
        
        // Replace MariaDB data types with PostgreSQL data types
        $replacements = [
            'int' => 'INTEGER',
            'tinyint(1)' => 'BOOLEAN', // MariaDB TINYINT(1) as BOOLEAN
            'tinyint' => 'SMALLINT',
            'smallint' => 'SMALLINT',
            'mediumint' => 'INTEGER', // No direct equivalent, use INTEGER
            'bigint' => 'BIGINT',
            'float' => 'REAL',
            'double' => 'DOUBLE PRECISION',
            'decimal' => 'NUMERIC', // Decimal types
            'date' => 'DATE',
            'time' => 'TIME',
            'datetime' => 'TIMESTAMP', // Use TIMESTAMP for datetime
            'timestamp' => 'TIMESTAMP',
            'varchar' => 'VARCHAR', // Variable-length string
            'text' => 'TEXT',
            'blob' => 'BYTEA', // Binary data
            'mediumtext' => 'TEXT', // No direct equivalent
            'longtext' => 'TEXT', // No direct equivalent
            'json' => 'JSONB', // Use JSONB for better performance in PostgreSQL
            // Add more type conversions as needed
        ];

        $query = str_ireplace(array_keys($replacements), array_values($replacements), $query);

        // Handle AUTO_INCREMENT
        $query = preg_replace('/AUTO_INCREMENT=\d+/', '', $query);
        $query = preg_replace('/AUTO_INCREMENT/', '', $query);
        
        // Handle default values for strings and booleans
        $query = preg_replace('/DEFAULT \'(.*?)\'/', 'DEFAULT \'\1\'', $query);
        
        // Handle "ENGINE=InnoDB" or other ENGINE specifications
        $query = preg_replace('/ENGINE=\w+/', '', $query);
        
        // Remove unnecessary commas
        $query = preg_replace('/,\s*$/', '', $query);
        
        // Trim whitespace
        $query = trim($query);

        return $query;
    }

}