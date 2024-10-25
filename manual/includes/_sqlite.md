## PicoSqlite

### Overview

`PicoSqlite` is a PHP class designed for simplified interactions with SQLite databases using PDO (PHP Data Objects). This class extends `PicoDatabase` and provides methods for connecting to the database, creating tables, and performing basic CRUD (Create, Read, Update, Delete) operations.

Here are some advantages of using SQLite:

1.  **Lightweight**: SQLite is a serverless, self-contained database engine that requires minimal setup and uses a single file to store the entire database, making it easy to manage and deploy.
    
2.  **Easy to Use**: Its simple API allows for straightforward integration with PHP, enabling quick database operations without the overhead of complex configurations.
    
3.  **No Server Required**: Unlike other database systems, SQLite does not require a separate server process, which simplifies the development process and reduces resource usage.
    
4.  **Cross-Platform**: SQLite databases are cross-platform and can be used on various operating systems without compatibility issues.
    
5.  **Fast Performance**: For smaller databases and applications, SQLite often outperforms more complex database systems, thanks to its lightweight architecture.
    
6.  **ACID Compliance**: SQLite provides full ACID (Atomicity, Consistency, Isolation, Durability) compliance, ensuring reliable transactions and data integrity.
    
7.  **Rich Feature Set**: Despite being lightweight, SQLite supports many advanced features like transactions, triggers, views, and complex queries.
    
8.  **No Configuration Required**: SQLite is easy to set up and requires no configuration, allowing developers to focus on building applications rather than managing the database server.
    
9.  **Great for Prototyping**: Its simplicity makes it ideal for prototyping applications before moving to a more complex database system.
    
10.  **Good for Read-Heavy Workloads**: SQLite performs well in read-heavy scenarios, making it suitable for applications where data is frequently read but rarely modified.
    

These features make SQLite a popular choice for many PHP applications, especially for smaller projects or for applications that need a lightweight database solution.


### Requirements

-    PHP 7.0 or higher
-    PDO extension enabled

### Installation

To use the `PicoSqlite` class, include it in your PHP project. Ensure that your project structure allows for proper namespace loading.

```php
use MagicObject\Database\PicoSqlite;

// Example usage:
$db = new PicoSqlite('path/to/database.sqlite');
```

### Class Methods

#### Constructor

```php
public function __construct($databaseFilePath)
```

**Parameters:**

    string $databaseFilePath: The path to the SQLite database file.

**Throws:** PDOException if the connection fails.

**Usage Example:**

```php
$sqlite = new PicoSqlite('path/to/database.sqlite');
```

#### Connecting to the Database

```php
public function connect($withDatabase = true)
```

**Parameters:**
-    bool $withDatabase: Optional. Default is true. Indicates whether to select the database when connecting.

**Returns:** `bool` - True if the connection is successful, false otherwise.

**Usage Example:**

```php
if ($sqlite->connect()) {
    echo "Connected to database successfully.";
} else {
    echo "Failed to connect.";
}
```

#### Check Table

```php
public function tableExists($tableName) : bool
```

**Parameters:**

-    string $tableName: The name of the table to check.

**Returns:** `bool` - True if the table exists, false otherwise.

**Usage Example:**

```php
if ($sqlite->tableExists('users')) {
    echo "Table exists.";
} else {
    echo "Table does not exist.";
}
```

#### Create Table

```php
public function createTable($tableName, $columns) : int|false
```

**Parameters:**

-    string $tableName: The name of the table to create.
-    string[] $columns: An array of columns in the format 'column_name TYPE'.

**Returns:** `int|false` - Number of rows affected or false on failure.

**Usage Example:**

```php
$columns = ['id INTEGER PRIMARY KEY', 'name TEXT', 'email TEXT'];
$sqlite->createTable('users', $columns);
```

#### Insert

```php
public function insert($tableName, $data) : array 
```

**Parameters:**

-    string $tableName: The name of the table to insert into.
-    array $data: An associative array of column names and values to insert.

**Returns:** `bool` - True on success, false on failure.

**Usage Example:**

```php
$data = ['name' => 'John Doe', 'email' => 'john@example.com'];
$sqlite->insert('users', $data);
```

```php
public function update($tableName, $data, $conditions) : bool
```

**Parameters:**

-    string $tableName: The name of the table to update.
    array $data: An associative array of column names and new values.
-    array $conditions: An associative array of conditions for the WHERE clause.

**Returns:** `bool` - True on success, false on failure.

**Usage Example:**

```php
$data = ['name' => 'John Smith'];
$conditions = ['id' => 1];
$sqlite->update('users', $data, $conditions);
```

#### Delete

```php
public function delete($tableName, $conditions) : bool 
```

**Parameters:**

-    string $tableName: The name of the table to delete from.
-    array $conditions: An associative array of conditions for the WHERE clause.

**Returns:** `bool` - True on success, false on failure.

**Usage Example:**

```php
$conditions = ['id' => 1];
$sqlite->delete('users', $conditions);
```

### Error Handling

If an operation fails, `PicoSqlite` may throw exceptions or return false. It is recommended to implement error handling using try-catch blocks to catch `PDOException` for connection-related issues.

### Conclusion

`PicoSqlite` provides an efficient way to interact with SQLite databases. Its straightforward API allows developers to perform common database operations with minimal code. For more advanced database operations, consider extending the class or using additional PDO features.