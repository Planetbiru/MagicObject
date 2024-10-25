<?php

namespace MagicObject\Database;

use MagicObject\MagicObject;
use PDO;
use PDOException;
use ReflectionClass;
use ReflectionProperty;

/**
 * Class PicoSqlite
 *
 * A simple wrapper for SQLite database operations using PDO.
 */
class PicoSqlite extends PicoDatabase
{
    /**
     * Database file path
     *
     * @var string
     */
    private $databaseFilePath;

    /**
     * Constructor to initialize the SQLite database connection.
     *
     * @param string $databaseFilePath The path to the SQLite database file.
     * @param callable|null $callbackExecuteQuery Callback for executing modifying queries. Parameter 1 is SQL, parameter 2 is one of query type (PicoDatabase::QUERY_INSERT, PicoDatabase::QUERY_UPDATE, PicoDatabase::QUERY_DELETE, PicoDatabase::QUERY_TRANSACTION).
     * @param callable|null $callbackDebugQuery Callback for debugging queries. Parameter 1 is SQL.
     * @throws PDOException if the connection fails.
     */
    public function __construct($databaseFilePath, $callbackExecuteQuery = null, $callbackDebugQuery = null) {
        $this->databaseFilePath = $databaseFilePath;

        if ($callbackExecuteQuery !== null && is_callable($callbackExecuteQuery)) {
            $this->callbackExecuteQuery = $callbackExecuteQuery;
        }

        if ($callbackDebugQuery !== null && is_callable($callbackDebugQuery)) {
            $this->callbackDebugQuery = $callbackDebugQuery;
        }

        $this->databaseType = PicoDatabaseType::DATABASE_TYPE_SQLITE;
    }

    /**
     * Connect to the database.
     *
     * @param bool $withDatabase Flag to select the database when connected.
     * @return bool True if the connection is successful, false if it fails.
     */
    public function connect($withDatabase = true)
    {
        $connected = false;
        try {
            $this->databaseConnection = new PDO("sqlite:" . $this->databaseFilePath);
            $this->databaseConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $connected = true;
            $this->connected = true;
        } catch (PDOException $e) {
            throw new PDOException($e->getMessage(), intval($e->getCode()));
        }
        return $connected;
    }

    /**
     * Check if a table exists in the database.
     *
     * @param string $tableName The name of the table to check.
     * @return bool True if the table exists, false otherwise.
     */
    public function tableExists($tableName)
    {
        $query = "SELECT name FROM sqlite_master WHERE type='table' AND name=:tableName";
        $stmt = $this->databaseConnection->prepare($query);
        $stmt->bindValue(':tableName', $tableName);
        $stmt->execute();
        return $stmt->fetch() !== false;
    }

    /**
     * Create a new table in the database.
     *
     * @param string $tableName The name of the table to create.
     * @param string[] $columns An array of columns in the format 'column_name TYPE'.
     * @return int|false Returns the number of rows affected or false on failure.
     */
    public function createTable($tableName, $columns) {
        $columnsStr = implode(", ", $columns);
        $sql = "CREATE TABLE IF NOT EXISTS $tableName ($columnsStr)";
        return $this->databaseConnection->exec($sql);
    }

    /**
     * Insert a new record into the specified table.
     *
     * @param string $tableName The name of the table to insert into.
     * @param array $data An associative array of column names and values to insert.
     * @return bool Returns true on success or false on failure.
     */
    public function insert($tableName, $data) {
        $columns = implode(", ", array_keys($data));
        $placeholders = ":" . implode(", :", array_keys($data));
        $sql = "INSERT INTO $tableName ($columns) VALUES ($placeholders)";
        $stmt = $this->databaseConnection->prepare($sql);

        foreach ($data as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }

        return $stmt->execute();
    }

    /**
     * Select records from the specified table with optional conditions.
     *
     * @param string $tableName The name of the table to select from.
     * @param array $conditions An associative array of conditions for the WHERE clause.
     * @return array Returns an array of fetched records as associative arrays.
     */
    public function select($tableName, $conditions = []) {
        $sql = "SELECT * FROM $tableName";
        if (!empty($conditions)) {
            $conditionStr = implode(" AND ", array_map(function($key) {
                return "$key = :$key";
            }, array_keys($conditions)));
            $sql .= " WHERE $conditionStr";
        }

        $stmt = $this->databaseConnection->prepare($sql);
        foreach ($conditions as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Update existing records in the specified table based on conditions.
     *
     * @param string $tableName The name of the table to update.
     * @param array $data An associative array of column names and new values.
     * @param array $conditions An associative array of conditions for the WHERE clause.
     * @return bool Returns true on success or false on failure.
     */
    public function update($tableName, $data, $conditions) {
        $dataStr = implode(", ", array_map(function($key) {
            return "$key = :$key";
        }, array_keys($data)));

        $conditionStr = implode(" AND ", array_map(function($key) {
            return "$key = :$key";
        }, array_keys($conditions)));

        $sql = "UPDATE $tableName SET $dataStr WHERE $conditionStr";
        $stmt = $this->databaseConnection->prepare($sql);

        foreach (array_merge($data, $conditions) as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }

        return $stmt->execute();
    }

    /**
     * Delete records from the specified table based on conditions.
     *
     * @param string $tableName The name of the table to delete from.
     * @param array $conditions An associative array of conditions for the WHERE clause.
     * @return bool Returns true on success or false on failure.
     */
    public function delete($tableName, $conditions) {
        $conditionStr = implode(" AND ", array_map(function($key) {
            return "$key = :$key";
        }, array_keys($conditions)));

        $sql = "DELETE FROM $tableName WHERE $conditionStr";
        $stmt = $this->databaseConnection->prepare($sql);

        foreach ($conditions as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }

        return $stmt->execute();
    }

    /**
     * Generates a SQL CREATE TABLE query based on the provided class annotations.
     *
     * This function inspects the given class for its properties and their annotations
     * to construct a SQL statement that can be used to create a corresponding table in a database.
     * It extracts the table name from the `@Table` annotation and processes each property 
     * to determine the column definitions from the `@Column` annotations.
     *
     * @param MagicObject $entity The instance of the class whose properties will be used
     *                             to generate the table structure.
     * @param bool $ifNotExists If true, the query will include an "IF NOT EXISTS" clause.
     * @return string The generated SQL CREATE TABLE query.
     * 
     * @throws ReflectionException If the class does not exist or is not accessible.
     */
    function showCreateTable($entity, $ifNotExists = false) {        
        $tableInfo = $entity->tableInfo();
        $tableName = $tableInfo->getTableName();
    
        // Start building the CREATE TABLE query
        if($ifNotExists)
        {
            $condition = " IF NOT EXISTS";
        }
        else
        {
            $condition = "";
        }
        $query = "CREATE TABLE$condition $tableName (\n";
    
        // Define primary key
        $primaryKey = null;

        $pKeys = $tableInfo->getPrimaryKeys();
        if(isset($pKeys) && is_array($pKeys) && !empty($pKeys))
        {
            $pKeyArr = [];
            $pkVals = array_values($pKeys);
            foreach($pkVals as $pk)
            {
                $pKeyArr[] = $pk['name'];
            }
            $primaryKey = implode(", ", $pKeyArr);
        }

        foreach ($tableInfo->getColumns() as $column) {
        
            $columnName = $column['name'];
            $columnType = $column['type'];
            $length = isset($column['length']) ? $column['length'] : null;
            $nullable = (isset($column['nullable']) && $column['nullable'] === 'true') ? 'NULL' : 'NOT NULL';
            $defaultValue = isset($column['defaultValue']) ? "DEFAULT '{$column['defaultValue']}'" : '';

            // Convert column type for SQL
            $columnType = strtolower($columnType); // Convert to lowercase for case-insensitive comparison

            if (strpos($columnType, 'varchar') !== false) {
                $sqlType = "VARCHAR($length)";
            } elseif ($columnType === 'int') {
                $sqlType = 'INT';
            } elseif ($columnType === 'float') {
                $sqlType = 'FLOAT';
            } elseif ($columnType === 'text') {
                $sqlType = 'TEXT';
            } elseif ($columnType === 'longtext') {
                $sqlType = 'LONGTEXT';
            } elseif ($columnType === 'date') {
                $sqlType = 'DATE';
            } elseif ($columnType === 'timestamp') {
                $sqlType = 'TIMESTAMP';
            } elseif ($columnType === 'tinyint(1)') {
                $sqlType = 'TINYINT(1)';
            } else {
                $sqlType = 'VARCHAR(255)'; // Fallback type
            }

            // Add to query
            $query .= "    $columnName $sqlType $nullable,\n";
            
        }
    
        // Remove the last comma and add primary key constraint
        $query = rtrim($query, ",\n") . "\n";
        
        if ($primaryKey) {
            $query = rtrim($query, ",\n");
            $query .= ",\n    PRIMARY KEY ($primaryKey)\n";
        }
    
        $query .= ");";
    
        return str_replace("\n", "\r\n", $query);
    }
    

}
