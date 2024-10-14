## Native Query

In MagicObject version 2, native queries have been introduced as an efficient way to interact with the database.

Native queries offer significant performance improvements when handling large volumes of data, allowing users to craft highly efficient queries that meet diverse requirements.

Native queries can perform several tasks such as:
1. performing INSERT operations
2. performing SELECT operations with complex queries such as JOIN, UNION, IF-ELSE, CASE, and others
3. counting the number of rows without reading the data in detail
4. performing UPDATE operations
5. performing DELETE operations
6. calling functions
7. calling procedures and stored procedures

This method executes a database query using the parameters and annotations defined in the caller function.
It leverages reflection to access the query string specified in the caller's docblock, binds the relevant parameters,
and then runs the query against the database.

By analyzing the parameters and return type of the calling function, this method enables dynamic execution of queries
that are tailored to the specified return type. The supported return types include:

- **void**: The method will return `null`.
- **int** or **integer**: It will return the number of affected rows.
- **object** or **stdClass**: It will return a single result as an object.
- **stdClass[]**: All results will be returned as an array of stdClass objects.
- **array**: All results will be returned as an associative array.
- **string**: The results will be JSON-encoded.
- **PDOStatement**: The method can return a prepared statement for further operations if necessary.
- **MagicObject** and its derived classes: If the return type is a class name or an array of class names, instances
  of the specified class will be created for each row fetched.

MagicObject also supports return types `self` and `self[]` which will represent the respective class.

The method returns a mixed type result, which varies based on the caller function's return type:
- It will return `null` for void types.
- An integer representing the number of affected rows for int types.
- An object for single result types.
- An array of associative arrays for array types.
- A JSON string for string types.
- Instances of a specified class for class name matches.

If there is an error executing the database query, a **PDOException** will be thrown.

**Example:**

```php
<?php

use MagicObject\Database\PicoDatabase;
use MagicObject\MagicObject;
use MagicObject\SecretObject;

require_once dirname(__DIR__) . "/vendor/autoload.php";

$databaseCredential = new SecretObject();
$databaseCredential->loadYamlFile(dirname(dirname(__DIR__))."/test.yml.txt", false, true, true);
$database = new PicoDatabase($databaseCredential->getDatabase());
$database->connect();

class Supervisor extends MagicObject
{
    /**
     * Native query 1
     *
     * This method will return null.
     *
     * @param int $supervisorId The ID of the table to search for.
     * @param bool $aktif The active status to filter results.
     * @return void
     * @query("
      SELECT supervisor.* 
      FROM supervisor 
      WHERE supervisor.supervisor_id = :supervisorId 
      AND supervisor.aktif = :aktif
     ")
     */
    public function native1($supervisorId, $aktif)
    {
        // Call parent method to execute the query
        return parent::executeNativeQuery();
    }

    /**
     * Native query 2
     *
     * This method will return the number of affected rows.
     *
     * @param int $supervisorId The ID of the table to search for.
     * @param bool $aktif The active status to filter results.
     * @return int
     * @query("
      SELECT supervisor.* 
      FROM supervisor 
      WHERE supervisor.supervisor_id = :supervisorId 
      AND supervisor.aktif = :aktif
     ")
     */
    public function native2($supervisorId, $aktif)
    {
        // Call parent method to execute the query
        return parent::executeNativeQuery();
    }

    /**
     * Native query 3
     *
     * This method will return a single result as an object.
     *
     * @param int $supervisorId The ID of the table to search for.
     * @param bool $aktif The active status to filter results.
     * @return stdClass
     * @query("
      SELECT supervisor.* 
      FROM supervisor 
      WHERE supervisor.supervisor_id = :supervisorId 
      AND supervisor.aktif = :aktif
     ")
     */
    public function native3($supervisorId, $aktif)
    {
        // Call parent method to execute the query
        return parent::executeNativeQuery();
    }

    /**
     * Native query 4
     *
     * This method will return an array of stdClass objects.
     *
     * @param int $supervisorId The ID of the table to search for.
     * @param bool $aktif The active status to filter results.
     * @return stdClass[]
     * @query("
      SELECT supervisor.* 
      FROM supervisor 
      WHERE supervisor.supervisor_id = :supervisorId 
      AND supervisor.aktif = :aktif
     ")
     */
    public function native4($supervisorId, $aktif)
    {
        // Call parent method to execute the query
        return parent::executeNativeQuery();
    }

    /**
     * Native query 5
     *
     * This method will return an associative array.
     *
     * @param int $supervisorId The ID of the table to search for.
     * @param bool $aktif The active status to filter results.
     * @return array
     * @query("
      SELECT supervisor.* 
      FROM supervisor 
      WHERE supervisor.supervisor_id = :supervisorId 
      AND supervisor.aktif = :aktif
     ")
     */
    public function native5($supervisorId, $aktif)
    {
        // Call parent method to execute the query
        return parent::executeNativeQuery();
    }

    /**
     * Native query 6
     *
     * This method will return a JSON-encoded string.
     *
     * @param int $supervisorId The ID of the table to search for.
     * @param bool $aktif The active status to filter results.
     * @return string
     * @query("
      SELECT supervisor.* 
      FROM supervisor 
      WHERE supervisor.supervisor_id = :supervisorId 
      AND supervisor.aktif = :aktif
     ")
     */
    public function native6($supervisorId, $aktif)
    {
        // Call parent method to execute the query
        return parent::executeNativeQuery();
    }

    /**
     * Native query 7
     *
     * This method will return a prepared statement for further operations if necessary.
     *
     * @param int $supervisorId The ID of the table to search for.
     * @param bool $aktif The active status to filter results.
     * @return PDOStatement
     * @query("
      SELECT supervisor.* 
      FROM supervisor 
      WHERE supervisor.supervisor_id = :supervisorId 
      AND supervisor.aktif = :aktif
     ")
     */
    public function native7($supervisorId, $aktif)
    {
        // Call parent method to execute the query
        return parent::executeNativeQuery();
    }

    /**
     * Native query 8
     *
     * This method will return an object of Supervisor.
     *
     * @param int $supervisorId The ID of the table to search for.
     * @param bool $aktif The active status to filter results.
     * @return Supervisor
     * @query("
      SELECT supervisor.* 
      FROM supervisor 
      WHERE supervisor.supervisor_id = :supervisorId 
      AND supervisor.aktif = :aktif
     ")
     */
    public function native8($supervisorId, $aktif)
    {
        // Call parent method to execute the query
        return parent::executeNativeQuery();
    }

    /**
     * Native query 9
     *
     * This method will return an array of Supervisor object.
     *
     * @param int $supervisorId The ID of the table to search for.
     * @param bool $aktif The active status to filter results.
     * @return Supervisor[]
     * @query("
      SELECT supervisor.* 
      FROM supervisor 
      WHERE supervisor.supervisor_id = :supervisorId 
      AND supervisor.aktif = :aktif
     ")
     */
    public function native9($supervisorId, $aktif)
    {
        // Call parent method to execute the query
        return parent::executeNativeQuery();
    }
}

$obj = new Supervisor(null, $database);

$result1 = $obj->native1(1, true);

$result2 = $obj->native2(1, true);
print_r($result2);

$result3 = $obj->native3(1, true);
print_r($result3);

$result4 = $obj->native4(1, true);
print_r($result4);

$result5 = $obj->native5(1, true);
print_r($result5);

$result6 = $obj->native6(1, true);
print_r($result6);

$result7 = $obj->native7(1, true);
print_r($result7);

$result8 = $obj->native8(1, true);
print_r($result8);

$result9 = $obj->native9(1, true);
print_r($result9);

```

