# MagicObject Version 2

## What's New

**MagicObject Version 2** brings several exciting new features and enhancements aimed at increasing flexibility, improving performance, and providing better control over database interactions. Below are the key updates introduced in this version:

### 1. Native Query Support

**Native SQL Queries** are now supported in **MagicObject**, allowing users to execute raw SQL statements directly within the framework. This enhancement gives developers greater control over complex queries, which may not be easily handled by the ORM layer. You can now execute any SQL command directly, enabling the use of advanced SQL features and custom queries that go beyond the capabilities of the built-in ORM.

### 2. Multiple Database Connection Support

The new version of **MagicObject** introduces the ability to configure and manage **multiple database connections** within a single application. This feature allows developers to easily connect to and manage different databases simultaneously, making it ideal for applications that need to interact with multiple databases or implement multi-database architectures. Whether you're working with multiple MySQL instances, different types of databases (e.g., PostgreSQL, SQLite), or managing different environments (development, production), this feature significantly simplifies database management.

### 3. Entity Cache Control on Joins

**MagicObject Version 2** gives developers greater control over **entity caching** when performing **join operations**. The new feature allows you to enable or disable caching specifically for joins, providing fine-tuned control over your caching strategy. This improves performance by reducing unnecessary database hits while still ensuring fresh data retrieval when needed. You can now optimize caching on a per-query basis, making it easier to manage large data sets efficiently.

### 4. Enhanced Documentation

The documentation for **MagicObject** has been thoroughly updated. In this release, we've made significant improvements to the documentation for **classes**, **properties**, **functions**, and **annotations**. The documentation now includes clearer explanations, improved examples, and comprehensive usage guidelines. These changes are designed to make it easier for developers to understand and fully leverage the power of the framework, reducing the learning curve and streamlining the development process.

### 5. Bug Fixes and Stability Enhancements

Several bugs and issues from previous versions have been addressed in **MagicObject Version 2**. This includes improvements to **performance**, **stability**, and the **correction of minor errors** that may have affected the functionality of the framework. With these fixes, users can expect a more reliable and robust framework that performs well across a variety of use cases.

## Additional Features

-   **Improved Error Handling**: We've introduced enhanced mechanisms for detecting and handling errors. The error messages are now more informative, helping developers to troubleshoot and resolve issues faster. This improvement also includes better stack trace information and more specific error types.
    
-   **Performance Optimizations**: Internally, **MagicObject Version 2** has been optimized to improve overall performance. Key database interaction operations have been streamlined, leading to faster query execution times and better resource utilization.
    
-   **Backward Compatibility**: **MagicObject Version 2** maintains **backward compatibility** with **Version 1**, ensuring that existing users can upgrade smoothly without having to make significant changes to their codebase. This allows for an easy transition to the new version while still maintaining compatibility with legacy systems.


## Migration Notes

If you are upgrading from **MagicObject Version 1** to **Version 2**, please review the migration notes carefully. The documentation includes detailed guidelines and best practices for handling any potential breaking changes, as well as adjustments that may be necessary to ensure a smooth transition. By following these guidelines, you can ensure that your upgrade process is as seamless as possible, minimizing disruptions to your development workflow.

# MagicObject Version 2.1

## What's New

**MagicObject 2.1** introduces several powerful new features aimed at improving entity management, database interoperability, and overall ease of use. This version builds on the foundational updates from previous releases, making database handling even more efficient and developer-friendly. Here’s a detailed overview of the new additions:

### 1. Package Annotations for Entity Joins

One of the most notable features in **MagicObject 2.1** is the introduction of **package annotations** for entities. These annotations are essential when joining entities, as they provide the necessary namespace information that is critical for the framework to correctly recognize and associate entity classes.

#### Why Package Annotations?

PHP does not natively provide a way to directly retrieve the namespace of a class, which presented a challenge for earlier versions of **MagicObject** when attempting to perform joins. To work around this, **MagicObject** previously attempted to infer namespaces by reading the PHP script, but this method proved to be both inefficient and prone to errors.

With **MagicObject 2.1**, the introduction of package annotations on each entity allows the framework to safely and efficiently join entities by referencing the class's base name, without needing to manually specify or infer the namespace. This makes the process of joining entities more robust and reliable.

#### Backwards Compatibility

If a package annotation is not present on an entity, **MagicObject 2.1** will gracefully revert to the old method of namespace inference, ensuring backwards compatibility with previous versions. However, it is strongly recommended to utilize the new package annotations for better performance and accuracy when performing entity joins.

### 2. Seamless Database Conversion Between PostgreSQL and MySQL

**MagicObject 2.1** introduces a powerful utility that allows developers to seamlessly convert databases between **PostgreSQL** and **MySQL**. This feature greatly simplifies the process of migrating applications and data between these two popular database systems.

#### Key Features of Database Conversion:

-   **Data Type Mapping**: MagicObject handles the conversion of data types between PostgreSQL and MySQL, ensuring that the equivalent types are correctly mapped.
-   **Constraints and Structures**: The conversion tool also accounts for database constraints, indexes, and table structures, ensuring that the integrity of the database schema is maintained during migration.
-   **Error Reduction**: By automating the conversion process, MagicObject reduces the chances of errors that can occur during manual migration, saving time and effort for developers.

This new functionality provides developers with a simple and efficient way to migrate data between PostgreSQL and MySQL, which is particularly useful for projects that need to switch databases or support multiple database systems.

### 3. Parsing Table Structures from SQL Statements

Another significant enhancement in **MagicObject 2.1** is the ability to **parse table structures directly from SQL statements**. Developers no longer need to first dump the schema into a database before they can interact with it. Instead, MagicObject allows you to read and manipulate the structure of a database directly from SQL.

#### Benefits of Table Structure Parsing:

-   **Streamlined Workflow**: This feature eliminates the need for a two-step process (first dumping, then reading the schema) and allows developers to work more directly with SQL code.
-   **Integrate with Third-Party Systems**: Developers can now easily parse and manipulate schemas from third-party systems that provide SQL code, without needing to import the data into a database first.
-   **Improved Efficiency**: This utility speeds up the process of understanding and working with complex database schemas, making it easier to integrate and maintain SQL-driven projects.

By providing a direct way to parse table structures, **MagicObject 2.1** significantly simplifies database schema management and makes it more accessible, especially for developers working with raw SQL or third-party integrations.

## Summary

**MagicObject 2.1** brings a suite of powerful features designed to enhance database management, simplify entity relationships, and improve the overall development process. Key updates include:

-   **Package annotations** for safer and more efficient entity joins.
-   A **seamless database conversion tool** between PostgreSQL and MySQL, simplifying migrations.
-   **Direct parsing of SQL table structures**, eliminating the need for intermediate steps.

These updates significantly improve the flexibility and efficiency of **MagicObject**, making it even easier for developers to manage databases and integrate with various systems. With **MagicObject 2.1**, developers can focus more on building applications and less on wrestling with database compatibility and entity management.


# MagicObject Version 2.7

## What's New

**MagicObject 2.7** brings a set of powerful updates to improve database interaction, query flexibility, and transaction management. The main highlights of this release include support for PDO connections, enhanced native query capabilities with pagination and sorting, and new transactional methods for improved data management.

### 1. PDO Support

One of the most significant changes in **MagicObject 2.7** is the introduction of support for **PDO** (PHP Data Objects). In previous versions, **MagicObject** required the use of its custom database handler, **PicoDatabase**. However, to accommodate developers who prefer working with PDO connections, this new version allows users to pass a PDO connection directly to the **MagicObject** constructor.

#### Why PDO Support?

The decision to include PDO support was driven by the need to make **MagicObject** more versatile for developers who are already using PDO in their applications. By allowing PDO connections, **MagicObject** now supports a broader range of use cases and provides users with the flexibility to integrate with existing PDO-based database connections.

While PDO is supported for the initial connection setup, **MagicObject** continues to use **PicoDatabase** for all subsequent database operations. This ensures that users still benefit from **PicoDatabase**'s advanced features, such as automatic query building, database abstraction, and optimized query execution.

#### How PDO Support Works

In **MagicObject 2.7**, when you pass a **PDO** connection object to the constructor, it is internally converted into a **PicoDatabase** instance via the `PicoDatabase::fromPdo()` static method. This ensures that although PDO is used for establishing the initial connection, **PicoDatabase** manages the actual database interactions. Additionally, **MagicObject** automatically detects the database type based on the PDO driver to ensure smooth operation.

### 2. Pageable and Sortable in Native Queries

Another important enhancement in **MagicObject 2.7** is the introduction of **pageable** and **sortable** support in native queries. Prior to this release, native queries lacked direct support for pagination and sorting. Developers had to manually include `ORDER BY` and `LIMIT OFFSET` clauses in their queries, leading to more cumbersome code that was difficult to maintain and adapt across different database platforms.

With **MagicObject 2.7**, you can now pass **pagination** parameters using the `PicoPageable` type and **sorting** parameters using the `PicoSortable` type directly into your native queries. These parameters can be placed at any point in the query, though it's recommended to position them either at the beginning or end for optimal readability and organization.

This improvement enhances the flexibility of native queries, as the logic for pagination and sorting is handled automatically, reducing the need for manual intervention. By supporting these features, **MagicObject 2.7** allows you to write cleaner, more efficient, and database-agnostic queries. You can now easily handle pagination and sorting logic regardless of the underlying database system.

### 3. Transaction Management

**MagicObject 2.7** introduces enhanced support for transactional database operations, including three new methods: `startTransaction()`, `commit()`, and `rollback()`. These methods provide an easy and efficient way to manage database transactions within **MagicObject**.

-   **startTransaction()**: Begins a new database transaction.
-   **commit()**: Commits the current transaction, saving all changes made during the transaction to the database.
-   **rollback()**: Rolls back the current transaction, undoing any changes made since the transaction began.

These methods are designed to work seamlessly with an active database connection, allowing developers to handle transactions directly within the context of their application. Whether you're managing financial transactions or ensuring data consistency during batch processing, these functions streamline the management of transaction-based operations.


# MagicObject Version 2.11

## What's New

1.  **Dynamic Query Template for Native Queries**
    MagicObject now supports dynamic query templates for native SQL queries. This feature allows developers to build SQL queries dynamically, providing flexibility and improving code reusability. The dynamic query template can be customized to suit specific use cases, making it easier to construct complex queries while maintaining cleaner and more maintainable code.
    
2.  **Added `DROP TABLE IF EXISTS` and `CREATE TABLE IF NOT EXISTS` to SQL Code Generation**  
    MagicObject now generates SQL code with `DROP TABLE IF EXISTS` and `CREATE TABLE IF NOT EXISTS` statements when creating tables from entities. This ensures that the table creation process is more robust, preventing errors when a table already exists.

3.  **Standardization of Annotation Attribute Naming with Camel Case Strategy**  
    The annotation attributes in MagicObject have been standardized to follow the camel case naming convention, ensuring consistency and better readability throughout the codebase.
    
4.  **Refactoring of Methods with Constant Usage**  
    To prevent duplication and errors in the code, several methods have been refactored to utilize constants. This improves maintainability and reduces the chances of mistakes during development.

5.  **Option to Prettify Serialize for `SecretObject`**  
    A new option has been added to allow `SecretObject` to be serialized with pretty formatting. This makes it easier to read and inspect serialized `SecretObject` data, especially useful during development or debugging. The `prettify` flag can be enabled when serializing the object, ensuring the output is more human-readable with proper indentation.

6.  **Updated Documentation**  
    The documentation has been updated to reflect the latest changes in MagicObject. This includes clarifications, examples, and explanations to assist developers in understanding and utilizing the library effectively.


# MagicObject Version 3.0

## What's New

1.  **SQL Server Database Support**  
    This feature expands user options by providing support for SQL Server, offering more flexibility in choosing DBMS.
    
2.  **Time Zone Conversion for SQLite and SQL Server Databases**  
    This feature automatically converts time in databases that do not natively support time zone conversion, such as SQLite and SQL Server.
    
3.  **Database Time Zone Change After Object Construction**  
    Users can now change the database's time zone at any time as needed. This provides greater flexibility when handling data across multiple time zones, especially when the application is used by users from different time zones, all without the need to modify the application configuration.

4.  **Yaml Parser and Dumper**  
    MagicObject version 3.0 no longer depends on external libraries. The Yaml parsing and dumping functions are now fully handled by a class that is part of MagicObject itself, reducing its overall complexity.
5.  **Added `BETWEEN` Filter for Predicate Queries**  
    MagicObject now supports `BETWEEN` filters for predicate-based queries, allowing users to perform range-based filtering efficiently. This improves query performance and simplifies conditions when working with numerical or date ranges.


# MagicObject Version 3.6

## What's New

**MagicObject 3.6** introduces new enhancements and improvements to provide greater flexibility in query building.

### Key Features & Updates:

- **String-Based Specifications in `WHERE` Clauses**
- **String-Based Sortable in `ORDER BY` Clauses**
- **Update Constructor for `PicoDatabaseQueryBuilder` class**
- **New `bindSqlParams` Function for Secure Parameter Binding**

### String-Based Specifications in `WHERE` Clauses

You can now use raw SQL strings as part of the WHERE clause, allowing for more complex conditions that predicates alone cannot handle. This gives users full control over query construction, adapting to different DBMS requirements.

**Example Usage:**

```php
$specs->addAnd((string) (new PicoDatabaseQueryBuilder($database))->bindSqlParams('artist_name LIKE ?', "%O'ben%"));
```

### String-Based Sorting in `ORDER BY` Clauses

MagicObject now supports string-based sorting, allowing users to define custom `ORDER BY` clauses dynamically. This feature enhances flexibility when ordering query results.

**Example Usage:**

```php
$sortable = new PicoSortable();
$sortable->add("artist_name DESC, album_year ASC");
```

or

```php
$sortable = new PicoSortable();
$sortable->add(["artist_name", "DESC"], true);
$sortable->add(["album_year", "ASC"], true);
```

### Updated Constructor for `PicoDatabaseQueryBuilder`

When construct an object of `PicoDatabaseQueryBuilder` class, user can send a `PDO` object as parameter. `PicoDatabaseQueryBuilder` will retrieve database type information from it. So, user not require to construct a `PicoDatabase` to get the datatabase type from it.

**Before (Previous Approach):**

```php
$databaseCredentials = new SecretObject();
$databaseCredentials->loadYamlFile("db.yml");
$database = new PicoDatabase($databaseCredentials);
$database->connect();
$queryBuilder = new PicoDatabaseQueryBuilder($database);
```

**Now (New Approach in v3.6):**

```php
$queryBuilder = new PicoDatabaseQueryBuilder($pdo);
```

### **New `bindSqlParams` Function in `PicoDatabaseQueryBuilder`**

A new function, `bindSqlParams`, has been introduced to safely bind SQL parameters, helping to escape values properly and prevent SQL injection.

### **Key Improvements**

-   **Increased flexibility** for defining custom SQL conditions.
-   **Support for complex `WHERE` clauses** with direct SQL strings.
-   **More control over query sorting** through string-based `ORDER BY` clauses.
-   **Improved compatibility** with different database systems.
-   **Enhanced sorting capabilities** with dynamic and flexible `ORDER BY` handling.

Several functions in the class with **private access level** have undergone changes, including function names, parameter names, and parameter order, to improve maintainability. These changes do not affect compatibility with previous versions, as the functions are only accessed within the class itself.

Upgrade to **MagicObject 3.6** now and enjoy a more powerful and flexible query-building experience!

# MagicObject version 3.7

## What's New

MagicObject version **3.7** introduces significant improvements in SQL query handling with a new feature:

### **Added `trimQueryString` for Processing Queries in Docblocks**

Now, MagicObject can **extract and clean up SQL queries** written in the `@query` annotation inside docblocks.

####  **Key Features:**

- **Extract SQL Queries from Docblocks** – Automatically retrieves queries from the `@query` annotation.  
- **Supports Trim Parameter** – Removes `*` and leading spaces from each line.  
- **Flexible Processing** – Ensures queries remain readable even when written in a multiline format within docblocks.

####  **Usage Example:**

```
<?php

use MagicObject\Database\PicoPageable;
use MagicObject\Database\PicoSortable;
use MagicObject\MagicObject;

class SupervisorExport extends MagicObject
{
    /**
     * Exports active supervisors based on the given active status.
     *
     * @param bool $aktif The active status filter (true for active, false for inactive).
     * @param PicoPageable $pageable Pagination details.
     * @param PicoSortable $sortable Sorting details.
     * @return PDOStatement The result of the executed query.
     * @query("
     *      SELECT supervisor.*
     *      FROM supervisor
     *      WHERE supervisor.aktif = :aktif
     * ", trim=true)
    */
    public function exportActive($aktif, $pageable, $sortable)
    {
        return $this->executeNativeQuery();
    }
}
```

This feature ensures that queries stay clean and usable without unnecessary formatting issues.

### **Improved Return Type Handling in `handleReturnObject`**

MagicObject now supports **both classic and PHP 7+ array return type annotations**:

#### **Key Enhancements:**

- **Supports Classic (`stdClass[]`) and PHP 7+ (`array<stdClass>`) Notation** – Now handles both styles seamlessly.
- **Regex-Based Type Parsing** – Automatically detects and processes return types.
- **Stronger Type Handling** – Improved switch case handling with lowercase normalization.

#### **Updated Logic in `handleReturnObject`**

Now, when specifying return types, you can use either:

```php
/**
 * @return stdClass[]
 */
```
**or**

```php
/**
 * @return array<stdClass>
 */
```

This ensures compatibility across different PHP versions while maintaining flexibility in return type definitions.

### **Added `maskPropertyName` for String Masking in Properties**

A new feature, `maskPropertyName`, allows you to **mask specific property values** within objects by replacing certain characters with a masking character. This enhances data privacy or prevents exposing sensitive information.

#### **Key Features:**

- **Mask Specific Property Values** – Easily mask specific parts of any property value.  
- **Customizable Masking** – Supports various positions for masking: `start`, `center`, or `end`.
- **Customizable Mask Character** – Choose your own masking character (defaults to `*`).

#### **Usage Example:**

```php
<?php

use MagicObject\MagicObject;

class UserProfile extends MagicObject
{

}

$object = new UserProfile();
$object->setName('John Doe');
$object->setEmail('john.doe@example.com');
// Masks email property value by replacing certain characters with a masking character.
echo $object->maskEmail(10, 6, '*')."\r\n";  // Output: john.doe@******e.com
echo $object->maskEmail(-10, 6, '*')."\r\n"; // Output: john.******ample.com
```

# MagicObject version 3.8

## What's New

- **dateFormat**: Added a function to format a date value into a specified format.  
  - **Example Usage:**  
    ```php
    $formattedDate = $object->dateFormatDate("j F Y H:i:s");
    ```
  
- **numberFormat**: Added a function to format a number with grouped thousands.  
  - **Example Usage:**  
    ```php
    $numberFormat = $object->numberFormatData(6, ".", ",");
    ```

- **format**: Added a function to format a value using a specified format string.  
  - **Example Usage:**  
    ```php
    $formattedData = $object->formatData("%7.3f");
    ```

With the addition of this formatting function, users can easily format object properties according to their needs.

# MagicObject version 3.9

## What's New

- **Add Magic Methods**

    -  **trim**: A function to retrieves the property value and trims any leading and trailing whitespace.  
       
       **Example Usage:**  
        ```php
        $object = new MagicObject();
        $name = $object->trimName();
        ```
    -  **upper**: A function to retrieves the property value and transform it to uppercase.  
       
       **Example Usage:**  
       
        ```php
        $object = new MagicObject();
        $code = $object->upperCode();
        ```
    -  **lower**: A function to retrieves the property value and transform it to lowercase.  
       
       **Example Usage:**  
        ```php
        $object = new MagicObject();
        $username = $object->lowerUsername();
        ```
    -  **dms**: A function to retrieves the property value and convert it to DMS (Degrees, Minutes, Seconds) format.  
       
       **Example Usage:**  
        ```php
        $object = new MagicObject();
        $dms = $object->dmsDuration(true, ":", 2, true, 2, true);
        ```
        
        **move**: A function to move uploaded file via callback function.
        
        **Example Usage**
        ```php
        $inputFiles = new PicoUploadFile();
        $inputFiles->moveMyVideo(function($file){
            foreach($file->getAll() as $fileItem)
            {
                $fileItem->moveTo("upload/".$fileItem->getName());
            }
        });
        ```
- **Add PicoFileRenderer Class**
  MagicObject add utility class to render various file types (images, audio, video, files, links, text) into corresponding HTML elements from plain strings or JSON-encoded arrays.
  
- **Update documentation**
  The documentation has been updated to reflect the new magic methods added in this version, ensuring clarity on how to use them in your code.
  
This version introduces essential new functions for better handling of data transformations such as trimming, converting to uppercase or lowercase, and formatting data into DMS (Degrees, Minutes, Seconds). These enhancements streamline property value manipulation and provide additional flexibility when interacting with data.

# MagicObject version 3.10

## What's New

### **New Feature: `retrieve()` Method**

We’re excited to introduce the **`retrieve()`** method in MagicObject 3.10, designed to make it easier to access deeply nested properties within your objects. This new feature allows you to pass multiple keys as arguments, making it incredibly efficient to traverse complex nested structures.

#### **How it Works:**

-   The `retrieve()` method takes one or more keys (in camelCase format) as parameters.
-   It will traverse through the object, fetching values based on the provided keys.
-   If a key is missing at any level, the method will return `null`.
-   This is especially useful when dealing with objects that contain deep nested data.

#### **Example:**

```php
$object = new  MagicObject();
$yaml = '
  prop1:
    prop2:
      prop3: Test
';
$object->loadYamlString($yaml, false, true, true);
echo  $object->retrieve('prop1', 'prop2', 'prop3');
```

In this example:
-   The method will first check `prop1`, then move to `prop2`, and finally `prop3`.
-   If any of these keys do not exist, it will return `null`.    

### **New Feature: `mergeWith()` Method**

We’ve also added a powerful new method called **`mergeWith()`**, which allows you to merge two `MagicObject` instances with ease.

#### What it Does:

-   Combines properties from another object into the current one.
-   If a property doesn’t exist in the current object, it will be added.
-   If the property already exists:
    -   If both values are `MagicObject` instances, they will be **merged recursively**.
    -   Otherwise, the value will be **overwritten**.

#### Example:

```php
$obj1 = new  MagicObject();
$obj1->loadYamlString('
user:
  name: ALice
client:
  address: Jakarta
', false, true, true);

$obj2 = new  MagicObject();
$obj2->loadYamlString('
user:
  email: alice@example.com
client:
  name: Ana
', false, true, true);
$obj1->mergeWith($obj2);

echo  $obj1;

// {"user":{"name":"ALice","email":"alice@example.com"},"client":{"address":"Jakarta","name":"Ana"}}
```

This method simplifies combining nested objects and ensures consistency in structured data merging.

### **Bug Fixes & Performance Improvements**

-   Various small bug fixes related to edge cases.
-   Optimizations made to improve performance when accessing deeply nested data within MagicObject.
    

### **Other Changes**

-   Internal code refactoring for improved readability and maintainability.
-   Enhanced flexibility in property name handling, especially for camelCase formatting.


# MagicObject version 3.11

## What's New

### Added: Matrix Calculation Class
A new `MatrixCalculator` class has been introduced to perform basic matrix operations such as addition, subtraction, multiplication, and element-wise division.  
This class is useful for numerical or scientific processing involving 2D arrays of real numbers.

### Added: `toFixed` Magic Method in MagicObject
A new `toFixed` magic method has been added to the core `MagicObject` class.  
This allows any numeric property to be formatted as a string with a fixed number of decimal places using dynamic method access.

**Example usage:**

```php
$object = new MagicObject();
$object->setData(100.123456);
echo $object->toFixedData(2)."\r\n"; // Outputs 100.12
echo $object->toFixedData(4)."\r\n"; // Outputs 100.1235
```

### Bug Fixes

-  Fixed an issue where countBy() returned 1 even when no records matched the condition.
   The method now correctly uses fetchColumn() to retrieve the result from SELECT COUNT(*).

### **Other Changes**

-   Internal code refactoring for improved readability and maintainability.
-   Enhanced flexibility in property name handling, especially for camelCase formatting.


# MagicObject version 3.12

## What's New

- **Removed Exception Throwing for Empty Results in Multi-Record Finders**  
  In this version, `EmptyResultException` and `NoRecordFoundException` are no longer thrown when methods for finding multiple records return an empty result. Instead, these methods will simply return an empty array or collection. This change improves developer experience by making it easier to handle cases where no records are found, without the need for additional exception handling.


# MagicObject version 3.13

## What's New

### Added: `alwaysTrue()`  Specification Method

A new static method `PicoSpecification::alwaysTrue()` has been added.  
This method returns a specification that always evaluates to `TRUE` (`WHERE 1 = 1`  in SQL). It is especially useful for scenarios where developers need to update, delete, or retrieve **all records**  from a table without any filtering.

**Example usage:**

```php
$specs = PicoSpecification::alwaysTrue();

$userFinder = new UserMin(null, $database);
try {
    $pageData = $userFinder->findAll($specs);
    foreach($pageData->getResult() as $user)
    {
      echo $user."\r\n";
    }
} catch (Exception $e) {
    // Optional: handle or ignore
}
```


# MagicObject Version 3.14

## What's New

### Table Structure Conversion Support

MagicObject 3.14 introduces a robust SQL dialect conversion utility, powered by the `PicoDatabaseConverter` class, for seamless translation of table structures between **MySQL**, **PostgreSQL**, and **SQLite**.

**Key features of the conversion utility:**
- Converts `CREATE TABLE` statements between MySQL, PostgreSQL, and SQLite, including:
    - Data type mapping and normalization
    - Identifier quoting and syntax adaptation
    - Handling of constraints, keys, and auto-increment fields
    - Keyword and function normalization
- Supports round-trip conversion (e.g., MySQL → PostgreSQL → MySQL)
- Can parse and split SQL column/constraint definitions, respecting nested parentheses
- Provides type translation utilities for mapping field types between dialects
- Offers value quoting, escaping, and PHP type conversion helpers for SQL literals
- Enables migration and data-dump scenarios between different RDBMS platforms

This class is typically used for database migration, schema portability, and interoperability between different database engines, without requiring entity definitions.

Developers can now easily transform `CREATE TABLE` statements from one dialect to another with proper handling of:

-   Data type conversion
-   Identifier quoting
-   Keyword and syntax normalization

Table structure conversion can be performed without the need to create entities beforehand. In addition to converting table structures, MagicObject version 3.14 also provides tools to dump data from one database to another DBMS.

#### Example Use Case

```php
<?php

use MagicObject\Database\PicoDatabaseType;
use MagicObject\Util\Database\PicoDatabaseConverter;

require_once dirname(__DIR__) . "/vendor/autoload.php";

$converter = new PicoDatabaseConverter();

$mySql = <<<SQL
CREATE TABLE IF NOT EXISTS `admin` (
  `admin_id` varchar(40) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `username` varchar(100) DEFAULT NULL,
  `password` varchar(100) DEFAULT NULL,
  `admin_level_id` varchar(40) DEFAULT NULL,
  `gender` varchar(1) DEFAULT NULL,
  `birth_day` date DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(100) DEFAULT NULL,
  `language_id` varchar(40) DEFAULT NULL,
  `validation_code` text,
  `last_reset_password` timestamp NULL DEFAULT NULL,
  `blocked` tinyint(1) DEFAULT '0',
  `time_create` timestamp NULL DEFAULT NULL,
  `time_edit` timestamp NULL DEFAULT NULL,
  `admin_create` varchar(40) DEFAULT NULL,
  `admin_edit` varchar(40) DEFAULT NULL,
  `ip_create` varchar(50) DEFAULT NULL,
  `ip_edit` varchar(40) DEFAULT NULL,
  `active` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`admin_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
SQL;


$postgreSql = $converter->translateCreateTable($mySql, PicoDatabaseType::DATABASE_TYPE_MARIADB, PicoDatabaseType::DATABASE_TYPE_POSTGRESQL);
$sqlite = $converter->translateCreateTable($mySql, PicoDatabaseType::DATABASE_TYPE_MARIADB, PicoDatabaseType::DATABASE_TYPE_SQLITE);

echo "MySQL:\n";
echo $mySql . "\n\n";

echo "PostgreSQL:\n";
echo $postgreSql . "\n\n";

echo "SQLite:\n";
echo $sqlite . "\n\n";

echo "Now, let's convert the PostgreSQL back to MySQL:\n";
$mySqlConverted = $converter->translateCreateTable($postgreSql, PicoDatabaseType::DATABASE_TYPE_POSTGRESQL, PicoDatabaseType::DATABASE_TYPE_MARIADB);
echo $mySqlConverted . "\n\n";

echo "Now, let convert PostgreSQL to SQLite:\n";
$sqliteConverted = $converter->translateCreateTable($postgreSql, PicoDatabaseType::DATABASE_TYPE_POSTGRESQL, PicoDatabaseType::DATABASE_TYPE_SQLITE);   
echo $sqliteConverted . "\n\n";

echo "Now, let convert SQLite to MySQL:\n";
$mysqlConverted2 = $converter->translateCreateTable($sqliteConverted, PicoDatabaseType::DATABASE_TYPE_SQLITE, PicoDatabaseType::DATABASE_TYPE_MYSQL);   
echo $mysqlConverted2 . "\n\n";

echo "Now, let convert SQLite to PostgreSQL:\n";
$postgresqlConverted2 = $converter->translateCreateTable($sqliteConverted, PicoDatabaseType::DATABASE_TYPE_SQLITE, PicoDatabaseType::DATABASE_TYPE_POSTGRESQL);   
echo $postgresqlConverted2 . "\n\n";
```

### Enhanced Property Validation

The `ValidationUtil` class has been significantly enhanced to provide a robust and flexible object property validation mechanism. Inspired by Jakarta Bean Validation (JSR 380), developers can now apply a comprehensive set of annotations directly in property docblocks to enforce data integrity.

The following validation annotations are now supported, grouped by their function:

#### Presence & Nullability
-   **`@Required(message="...")`**: Ensures the property value is not `null`.
-   **`@NotEmpty(message="...")`**: Checks if a string is not empty (`""`) or an array is not empty.
-   **`@NotBlank(message="...")`**: Validates that a string is not empty and not just whitespace characters.

#### Value Range & Size
-   **`@Min(value=X, message="...")`**: Asserts that a numeric property's value is greater than or equal to a minimum value.
-   **`@Max(value=X, message="...")`**: Asserts that a numeric property's value is less than or equal to a maximum value.
-   **`@DecimalMin(value="...", message="...")`**: Validates that a numeric property (can be float/string) is greater than or equal to a specified decimal value.
-   **`@DecimalMax(value="...", message="...")`**: Validates that a numeric property (can be float/string) is less than or equal to a specified decimal value.
-   **`@Range(min=X, max=Y, message="...")`**: Validates that a numeric property's value falls within an inclusive range.
-   **`@Size(min=X, max=Y, message="...")`**: Verifies that the length of a string or the count of an array is within a specified range.
-   **`@Length(min=X, max=Y, message="...")`**: Similar to `@Size`, specifically for string lengths within a range.
-   **`@Digits(integer=X, fraction=Y, message="...")`**: Checks that a numeric property has at most `X` integer digits and `Y` fractional digits.

#### Numeric Sign
-   **`@Positive(message="...")`**: Ensures a numeric value is positive (> 0).
-   **`@PositiveOrZero(message="...")`**: Ensures a numeric value is positive or zero (>= 0).
-   **`@Negative(message="...")`**: Ensures a numeric value is negative (< 0).
-   **`@NegativeOrZero(message="...")`**: Ensures a numeric value is negative or zero (<= 0).

#### Pattern & Format
-   **`@Pattern(regexp="...", message="...")`**: Validates a string property against a specified regular expression.
-   **`@Email(message="...")`**: Checks if a string property is a well-formed email address.
-   **`@Url(message="...")`**: Ensures a string is a valid URL.
-   **`@Ip(message="...")`**: Ensures a string is a valid IP address.
-   **`@DateFormat(format="...", message="...")`**: Ensures a string matches a specific date format.
-   **`@Phone(message="...")`**: Ensures a string is a valid phone number.
-   **`@NoHtml(message="...")`**: Checks if a string property contains any HTML tags.

#### Date & Time
-   **`@Past(message="...")`**: Ensures a `DateTimeInterface` property represents a date/time in the past.
-   **`@Future(message="...")`**: Ensures a `DateTimeInterface` property represents a date/time in the future.
-   **`@PastOrPresent(message="...")`**: Ensures a date/time is in the past or present.
-   **`@FutureOrPresent(message="...")`**: Ensures a `DateTimeInterface` property represents a date/time in the future or the present.
-   **`@BeforeDate(date="...", message="...")`**: Ensures a date is before a specified date.
-   **`@AfterDate(date="...", message="...")`**: Ensures a date is after a specified date.

#### Boolean
-   **`@AssertTrue(message="...")`**: Asserts that a boolean property's value is strictly `true`.

#### Enum & Allowed Values
-   **`@Enum(message="...", allowedValues={...}, caseSensitive=true|false)`**: Ensures a string property's value is one of a predefined set of allowed values, with an option for case-sensitive or case-insensitive comparison.

#### String Content & Structure
-   **`@Alpha(message="...")`**: Ensures a string contains only alphabetic characters.
-   **`@AlphaNumeric(message="...")`**: Ensures a string contains only alphanumeric characters.
-   **`@StartsWith(prefix="...", caseSensitive=true|false, message="...")`**: Ensures a string starts with a specified prefix, with optional case sensitivity.
-   **`@EndsWith(suffix="...", caseSensitive=true|false, message="...")`**: Ensures a string ends with a specified suffix, with optional case sensitivity.
-   **`@Contains(substring="...", caseSensitive=true|false, message="...")`**: Ensures a string contains a specified substring, with optional case sensitivity.

#### Nested Validation
-   **`@Valid`**: Recursively validates nested `MagicObject` and `MagicDto` instances.

**Class `UserProfile`**

```php
// Define an example entity class demonstrating various validations
class UserProfile extends MagicObject
{
    /**
     * @Required(message="Username cannot be null")
     * @NotBlank(message="Username cannot be blank")
     * @Length(min=4, max=20, message="Username must be 4-20 characters long")
     * @Pattern(regexp="^[a-zA-Z0-9_]+$", message="Username can only contain letters, numbers, and underscores")
     * @var string
     */
    protected $username;

    /**
     * @Email(message="Invalid email address format")
     * @Required(message="Email cannot be null")
     * @var string
     */
    protected $email;

    /**
     * @Min(value=18, message="Age must be at least 18")
     * @Max(value=99, message="Age cannot exceed 99")
     * @var int
     */
    protected $age;

    /**
     * @NoHtml(message="About Me field contains unsupported HTML tags")
     * @Size(max=500, message="About Me cannot exceed 500 characters")
     * @var string
     */
    protected $aboutMe;

    /**
     * @Past(message="Birth date must be in the past")
     * @var DateTime
     */
    protected $birthDate;

    /**
     * @Enum(message="Gender must be 'Male' or 'Female'", allowedValues={"Male", "Female"})
     * @var string
     */
    protected $gender;

    /**
     * @Enum(message="Status must be 'active', 'inactive', or 'pending'", allowedValues={"active", "inactive", "pending"}, caseSensitive=false)
     * @var string
     */
    protected $status;

    // A nested object to demonstrate @Valid
    /**
     * @Valid
     * @var Address
     */
    protected $address;
```

**Class `Address`**

```php
class Address extends MagicObject
{
    /**
     * @Required(message="Street cannot be null")
     * @NotBlank(message="Street cannot be blank")
     * @var string
     */
    protected $street;

    /**
     * @Required(message="City cannot be null")
     * @NotBlank(message="City cannot be blank")
     * @var string
     */
    protected $city;
}
```

**Validation**

```php
// Test 1: Valid user profile
try {
    $user = new UserProfile();
    $user->setUsername("john_doe");
    $user->setEmail("john.doe@example.com");
    $user->setAge(30);
    $user->setAboutMe("Hello, I am John Doe. I like programming.");
    $user->setBirthDate(new DateTime('1995-01-15'));
    $user->setGender("Male");
    $user->setStatus("active");

    $address = new Address();
    $address->setStreet("123 Main St");
    $address->setCity("Anytown");
    $user->setAddress($address);

    ValidationUtil::getInstance()->validate($user);
    echo "Test 1: Valid User Profile - PASSED.\n";
} catch (InvalidValueException $e) {
    echo "Test 1: FAILED (Unexpected) - " . $e->getPropertyName() . ": " . $e->getValidationMessage() . "\n";
}

// Test 2: Invalid username (too short)
try {
    $user = new UserProfile();
    $user->setUsername("joh"); // Too short
    $user->setEmail("john.doe@example.com");
    $user->setAge(30);
    $user->setAboutMe("Hello, I am John Doe.");
    $user->setBirthDate(new DateTime('1995-01-15'));
    $user->setGender("Male");
    $user->setStatus("active");
    
    $address = new Address();
    $address->setStreet("123 Main St");
    $address->setCity("Anytown");
    $user->setAddress($address);

    ValidationUtil::getInstance()->validate($user);
    echo "Test 2: Valid User Profile (should have failed) - PASSED.\n";
} catch (InvalidValueException $e) {
    echo "Test 2: FAILED (Expected) - " . $e->getPropertyName() . ": " . $e->getValidationMessage() . "\n";
}

// Test 3: Invalid email format
try {
    $user = new UserProfile();
    $user->setUsername("jane_doe");
    $user->setEmail("invalid-email"); // Invalid format
    $user->setAge(25);
    $user->setAboutMe("Hello, I am Jane Doe.");
    $user->setBirthDate(new DateTime('1999-03-20'));
    $user->setGender("Female");
    $user->setStatus("inactive");

    $address = new Address();
    $address->setStreet("123 Main St");
    $address->setCity("Anytown");
    $user->setAddress($address);

    ValidationUtil::getInstance()->validate($user);
    echo "Test 3: Valid User Profile (should have failed) - PASSED.\n";
} catch (InvalidValueException $e) {
    echo "Test 3: FAILED (Expected) - " . $e->getPropertyName() . ": " . $e->getValidationMessage() . "\n";
}

// Test 4: Invalid Enum value (case-sensitive)
try {
    $user = new UserProfile();
    $user->setUsername("test_user");
    $user->setEmail("test@example.com");
    $user->setAge(40);
    $user->setAboutMe("Some text.");
    $user->setBirthDate(new DateTime('1980-05-01'));
    $user->setGender("male"); // Should fail due to case-sensitivity (expected "Male")
    $user->setStatus("pending");

    $address = new Address();
    $address->setStreet("123 Main St");
    $address->setCity("Anytown");
    $user->setAddress($address);

    ValidationUtil::getInstance()->validate($user);
    echo "Test 4: Valid User Profile (should have failed) - PASSED.\n";
} catch (InvalidValueException $e) {
    echo "Test 4: FAILED (Expected) - " . $e->getPropertyName() . ": " . $e->getValidationMessage() . "\n";
}

// Test 5: Nested validation failure
try {
    $user = new UserProfile();
    $user->setUsername("test_nest");
    $user->setEmail("nest@example.com");
    $user->setAge(22);
    $user->setAboutMe("Testing nested validation.");
    $user->setBirthDate(new DateTime('2000-01-01'));
    $user->setGender("Female");
    $user->setStatus("active");

    $address = new Address();
    $address->setStreet(""); // Blank street, should fail @NotBlank
    $address->setCity("Anytown");
    $user->setAddress($address);

    ValidationUtil::getInstance()->validate($user);
    echo "Test 5: Valid User Profile (should have failed due to nested object) - PASSED.\n";
} catch (InvalidValueException $e) {
    echo "Test 5: FAILED (Expected due to nested object) - " . $e->getPropertyName() . ": " . $e->getValidationMessage() . "\n";
}
```

Users can perform validation on objects that extend from the following base classes:
- **MagicObject**
- **MagicDto**
- **InputPost**
- **InputGet**

This means that property validation is supported not only for entities derived from `MagicObject`, but also for data transfer objects (`MagicDto`) and HTTP input wrappers (`InputPost`, `InputGet`).  
You can annotate properties in any of these classes with validation annotations, and the validation mechanism will recursively check all nested properties, ensuring robust data integrity across your application's data models and input layers.


### Fluent Setter Chaining

MagicObject 3.14 introduces a new **`with()`** method within `PicoDatabasePersistenceExtended`, designed to enhance the readability and flow of setting multiple properties through method chaining. This simple yet powerful addition allows developers to initiate a setter chain with improved clarity, especially when configuring objects before persistence operations.

**Key feature:**

-   **`with()` Method**: Provides a convenient entry point for fluent setter chaining, returning the current object instance to allow for sequential method calls.

# MagicObject Version 3.14.1

## What's Changed

### Enhanced Validation Flexibility

MagicObject 3.14.1 introduces an additional parameter to the `validate()` method across `MagicObject` and related input classes (`InputPost`, `InputGet`, `MagicDto`, `SetterGetter`, `SecretObject`, and `PicoDatabasePersistenceExtended`). This enhancement provides more granular control over validation behavior, particularly when utilizing a **reference object** for validation annotations.

The `validate()` method now accepts a new boolean parameter:

-   **`$validateIfReferenceEmpty`**:
    -   **`true`** (default): If a `$reference` object is provided but it contains no properties (is considered "empty" in terms of its defined attributes), the validation will still proceed. In this scenario, the validation annotations from the current object (`$this`) will be used to validate the current object's data.
    -   **`false`**: If a `$reference` object is provided and it has no properties, the validation process will be **skipped entirely**. This is useful when you want validation to occur _only_ if the reference model actually defines validation rules.

This new parameter provides developers with more precise control over when and how validation occurs, especially in dynamic scenarios where reference models might not always contain defined properties.


### New `@MaxLength` Validation Annotation

MagicObject now supports a dedicated **`@MaxLength`** annotation for string properties. This new annotation allows you to quickly and clearly enforce a maximum length constraint without needing to specify a minimum length.

-   **`@MaxLength(value=X, message="...")`**: Ensures that a string property's value does not exceed `X` characters.

This simplifies common validation scenarios where only an upper bound on string length is required.

# MagicObject Version 3.14.2

## What's Changed

### Validator Generator Enhancement with `tableName` Support

In version 3.14.2, the **validator class generator** has been enhanced to support an optional `tableName` parameter. This addition provides improved integration with annotation-based ORMs or systems that benefit from structural metadata within validator classes.

#### New Behavior:

When the `tableName` parameter is provided to the `PicoEntityGenerator::generateValidatorClass()` method:

-   The generated validator class will include the following additional class-level annotations:
    
    -   `@Validator`
        
    -   `@Table(name="your_table_name")`
        

#### Benefits:

-   Enables clearer association between the validator class and the underlying database table.
    
-   Improves compatibility with tools or frameworks that rely on metadata annotations for mapping or validation contexts.
    
-   Provides a better foundation for auto-documentation or introspection tools.
    

#### Example Output:

```php
/**
 * Represents a validator class for the `user` module.
 *
 * @Validator
 * @Table(name="user_account")
 */
class UserValidator extends MagicObject
{
    ...
}

```

This enhancement makes the validator generator more expressive and future-proof, especially when building layered architectures or generating documentation automatically.




# MagicObject Version 3.14.5

## Improvements

### Enhancement: Flexible Nested Retrieval in `retrieve()` Method

The `retrieve(...$keys)` method now supports multiple input formats for accessing nested object properties:

- Dot notation: `$obj->retrieve('user.profile.name')`
- Arrow notation: `$obj->retrieve('user->profile->name')`
- Multiple arguments: `$obj->retrieve('user', 'profile', 'name')`

Each key is automatically camelized for consistent property access.  
If any key in the chain does not exist or returns `null`, the method will return `null`.

This enhancement improves developer ergonomics when working with deeply nested data structures.

### Validation: New `@TimeRange` Annotation

Added support for the `@TimeRange` validation annotation to validate time values within a specific range.

**Usage Example:**

```php
/**
 * @TimeRange(min="08:00", max="17:00")
 */
public $attendanceIn;
```

* Accepts time strings in `HH:MM` or `HH:MM:SS` format.
* Ensures the value falls within the defined range (inclusive).
* Useful for scheduling, availability windows, or working hours validation.

This new validation rule strengthens form-level data validation where time constraints are critical.



# MagicObject Version 3.14.7

## Bug Fixes

### Fix: Default Value Parsing for `tinyint(1)` in PHP 8

Fixed the parsing of default values for `tinyint(1)` columns in `CREATE TABLE` statements to ensure compatibility with PHP 5, 7, and 8.

Previously, in PHP 8, values like `'0'` or `'false'` could be incorrectly interpreted as `true`.

Now, default values are accurately converted to `TRUE` or `FALSE` based on their literal meaning.

Berikut adalah versi yang sudah diperbarui untuk catatan rilis MagicObject v3.16.0:


# MagicObject Version 3.16.0

## Features

### Add: `deleteRecordByPrimaryKey` Method

Added a new method `deleteRecordByPrimaryKey($primaryKeyValue)` to allow deleting a database record by its primary key, including support for composite keys.

This method ensures the database connection is active and delegates deletion to the persistence layer.


## Bug Fixes

### Fix: Session Handling with Redis

Resolved a compatibility issue when using Redis as the PHP session handler. Previously, sessions could fail to initialize or persist correctly under certain configurations.

Now, session storage works reliably with `session.save_handler = redis`, ensuring better support for scalable session storage backends.


# MagicObject Version 3.16.1

## Bug Fixes

* **`MagicObject::countAll()` and `MagicObject::countBy()`**: Fixed a bug where the counting methods did not function correctly with **SQLite** databases. These fixes ensure consistent counting functionality across all database types.


# MagicObject Version 3.16.2

## Bug Fixes

* **Session Handling**: Suppressed warnings when calling `session_start()` to prevent unnecessary error messages when a session is already active or headers have already been sent.


# MagicObject Version 3.16.3

## Bug Fixes

* **Redis Session Save Path Parsing**
  Fixed an issue where `session.save_path` values containing IPv6 addresses (e.g., `tcp://::1`) were parsed incorrectly.
  The parser now properly handles IPv6 addresses—with or without square brackets—to ensure correct host and port extraction when connecting to Redis.


# MagicObject Version 3.16.4

## Enhancement: Support for Exact Text Matching (`textequals`)

MagicObject now supports a new filter type called **`textequals`**, allowing developers to create filters that perform **exact string comparisons** (`=`) instead of case-insensitive partial matches using `LIKE`.

### What Changed?

A new condition was added to the `fromUserInput()` method:

```php
elseif ($filter->isTextEquals()) {
    $specification->addAnd(PicoPredicate::getInstance()->equals($filter->getColumnName(), $filterValue));
}
```

This enables behavior like:

```php
$specMap = array(
    "artistId" => PicoSpecification::filter("artistId", "number"),
    "genreId" => PicoSpecification::filter("genreId", "textequals")
);

$specification = PicoSpecification::fromUserInput($inputGet, $specMap);
```

With this map, any request like `?genreId=Jazz` will produce:

```sql
WHERE genre_id = 'Jazz'
```

Instead of:

```sql
WHERE LOWER(genre_id) LIKE '%jazz%'
```

### Why It Matters?

* **Improved Performance:** Exact matches are faster and use indexes more effectively.
* **Tighter Filtering:** You now have finer control over which fields use partial or exact text search.
* **More Predictable Behavior:** Prevents accidental partial matches, especially useful for enums or codes.


# MagicObject Version 3.16.8

## Bug Fix: Data Conversion on Export to SQL Server

Fixed an issue with **BIT** value conversion when exporting data to SQL Server.
Previously, `TRUE` and `FALSE` values were exported as literal text strings.
After this fix, they are correctly exported as numeric values:

* `TRUE` → `1`
* `FALSE` → `0`

This ensures proper compatibility with SQL Server’s `BIT` data type and avoids errors when importing exported data.

## Bug Fix: Default Value Handling in ALTER TABLE

Fixed an issue where **DEFAULT VALUE** clauses were not correctly generated when altering a table after entity changes.
The fix covers **both NULL and non-null default values**:

* Correctly applies `DEFAULT NULL` when specified.
* Properly formats non-null default values (e.g., `DEFAULT 0`, `DEFAULT 'ACTIVE'`, etc.) according to the column type and target database.
* Ensures compatibility with all supported database types (MySQL, PostgreSQL, SQLite, SQL Server).

### Example

**Before:**

```sql
ALTER TABLE users MODIFY status VARCHAR(20);
```

*(Default value lost after column change)*

**After:**

```sql
ALTER TABLE users MODIFY status VARCHAR(20) DEFAULT 'ACTIVE';
```

or

```sql
ALTER TABLE users MODIFY last_login TIMESTAMP DEFAULT NULL;
```

This prevents migration errors and ensures schema changes retain and apply consistent default values across databases.


# MagicObject Version 3.17.0

## Enhancement: Configurable Database Connection Timeout

Added support for **connection timeout** configuration, retrieved directly from the database connection settings.

**Details**

* The timeout value is defined in the database configuration (e.g., `core.yml` or application-specific config).
* Applied when establishing a PDO connection to all supported RDBMS drivers (MySQL, PostgreSQL, SQLite, SQL Server).
* The timeout ensures that connection attempts fail gracefully if the database server does not respond within the specified time.

**Example (`core.yml`):**

```yaml
database:
    driver: mysql
    host: localhost
    port: 3306
    username: app_user
    password: secret
    database_name: appdb
    connection_timeout: 10
```

**Impact**
This feature gives developers better control over database connectivity, especially in environments with slow or unreliable networks, by preventing applications from hanging indefinitely during connection attempts.


# MagicObject v3.17.1 — Release Notes

## What’s Changed

* **PostgreSQL & SQLite Export:** Removed unnecessary double quotes (`"`) around **table names** when exporting database schema.

## Details

Previously, exported DDL wrapped table names in quotes:

```sql
CREATE TABLE "useraccount" (...);
```

Starting from v3.17.1, table names are written without quotes:

```sql
CREATE TABLE useraccount (...);
```

## Why This Change?

* **Cleaner SQL Output:** Makes exported schema easier to read.
* **Improved Compatibility:** Some tools and workflows expect unquoted identifiers in both PostgreSQL and SQLite.

## Notes

* **No naming strategy changes** were introduced. Table names remain exactly the same; only the surrounding quotes are removed.
* If your schema relies on **case-sensitive identifiers** or **reserved keywords**, you may still need to add quotes manually.


# MagicObject Version 3.18.0

## Enhancement: Database Migration

A new parameter has been added to **Database Migration** to provide greater flexibility.
Previously, all migration queries had to be dumped into a SQL file before execution.
With this update, developers can now choose to **run queries directly on the target database**, reducing steps and improving efficiency in deployment workflows.

This makes migrations faster, easier to automate, and less error-prone—especially useful for CI/CD pipelines.

## Bug Fixes: Undefined Array Index in `PicoPageData::applySubqueryResult()`

Fixed an issue where an **undefined array index** error could occur when the provided data structure did not match the expected format.
This patch ensures more robust handling of unexpected input, improving the **stability and reliability** of query result processing.


# MagicObject Version 3.19.0

## New Feature: `XmlToJsonParser` Utility Class

A new utility class **`XmlToJsonParser`** has been introduced to parse XML documents into PHP arrays/JSON.
It supports:

* **Custom flattener element**: users can configure which XML elements should be treated as array items (e.g., `<entry>`, `<item>`, etc.).
* **Consistent output**: empty XML elements are automatically converted into `null` instead of empty arrays.
* **Round-trip support**: arrays can also be converted back into XML, with configurable wrapper element names.

This allows developers to manage application configuration using XML files, which are **less error-prone compared to YAML**, especially in environments where indentation issues are common.

**Example Usage:**

```php
$parser = new XmlToJsonParser(['entry', 'item']);
$config = $parser->parse(file_get_contents('config.xml'));
```

## Enhancement: `PicoCurlUtil` — Alternative to `curl`

A new class **`PicoCurlUtil`** has been added under `MagicObject\Util`.
This class provides an interface for making HTTP requests with **automatic fallback**:

* Uses **cURL** if the PHP `curl` extension is available.
* Falls back to **PHP streams** (`file_get_contents` + stream context) when `curl` is not available.

### Features

* Supports **GET** and **POST** requests (with planned extensions for PUT, DELETE, etc.).
* Allows setting **headers**, request body, and **SSL verification options**.
* Provides access to **response headers**, **body**, and **HTTP status code**.
* Automatically throws **`CurlException`** on error, for consistent error handling.

**Example Usage:**

```php
use MagicObject\Util\PicoCurlUtil;

$http = new PicoCurlUtil();
$response = $http->get("https://example.com/api/data");

if ($http->getHttpCode() === 200) {
    echo $response;
}
```

### Why It Matters?

* **Greater Flexibility:** Developers can now use XML configuration files instead of YAML.
* **Better Portability:** Applications can run even in environments where the `curl` extension is not installed.
* **Consistent API:** Whether using cURL or streams, you always interact via `PicoCurlUtil`.

## Enhancement: PicoSession Redis Database Parameter

**PicoSession** now supports specifying a **Redis database index** via the session save path.
This allows developers to isolate sessions in different Redis databases (e.g., separating staging and production data) without requiring additional Redis instances.
The parameter can be set using query options like `db`, `dbindex`, or `database` in the Redis connection string.

Example:

```
tcp://localhost:6379?db=3
```

## New Feature: `SqliteSessionHandler`

A new **`SqliteSessionHandler`** class has been introduced under `MagicObject\Session`.
This provides a **persistent session storage** mechanism using **SQLite** as the backend.

### Features

* Stores sessions in a **SQLite database file** instead of filesystem or memory.
* Automatically **creates the session table** if it does not exist.
* Implements the full session lifecycle:

  * **open** — Initializes session.
  * **read** — Reads serialized session data.
  * **write** — Writes or updates session data.
  * **destroy** — Removes a session by ID.
  * **gc** — Garbage collects expired sessions.
* Ensures **safe storage** even when multiple PHP processes are running.

### Why It Matters?

* **Portability:** No dependency on Redis or Memcached — only requires SQLite.
* **Lightweight:** Suitable for shared hosting or small applications.
* **Reliability:** Prevents session loss when PHP restarts, unlike file-based sessions.

# MagicObject Version 3.20.0

## Change: Removal of Math-Related Classes

In this release, several math-related classes have been **removed** from MagicObject to keep the core library lightweight and focused.

### Removed Modules

1. Complex Numbers  
2. Matrix Operations  
3. Geometry Utilities  

### Migration

These classes are **not discontinued**, but have been moved into a **new dedicated repository**:

👉 [Planetbiru/MagicMath](https://github.com/Planetbiru/MagicMath)

Developers who rely on these math utilities should install the new package separately:

```bash
composer require planetbiru/magic-math
```

## New Feature: PDO Connection Verification Method

A new method `isPdoConnected()` has been introduced to allow developers to verify not only the TCP-level connection, but also the ability of PHP to execute SQL statements on the database.

Here’s the corrected version with improved grammar and clarity:


## Bug Fix: Handle Exception in method getDatabaseCredentialsFromPdo($pdo, $driver, $dbType)

If an error occurs when executing:

```php
$dsn = $pdo->getAttribute(PDO::ATTR_CONNECTION_STATUS);
```

`getDatabaseCredentialsFromPdo($pdo, $driver, $dbType)` will return an empty instance of `SecretObject`.

## Enhancement: Improved `getDatabaseCredentialsFromPdo` Handling

The method **`getDatabaseCredentialsFromPdo`** has been updated to better handle 
PDO drivers that do not support certain attributes (e.g., `PDO::ATTR_CONNECTION_STATUS`).

### Key Improvements

* **Warning Suppression**  
  Suppresses warnings when `PDO::getAttribute(PDO::ATTR_CONNECTION_STATUS)` 
  is not supported by the active PDO driver (e.g., SQLite).

* **Graceful Fallback**  
  Introduced an optional `$databaseCredentials` parameter, which is used as a 
  fallback source for host, port, and database name if they cannot be extracted 
  from the PDO connection.

* **Driver-Agnostic Behavior**  
  Ensures compatibility across multiple database drivers (MySQL, PostgreSQL, SQLite, etc.) 
  without causing runtime warnings.

* **Consistent Output**  
  Always returns a populated `SecretObject` with connection details.  
  If extraction fails, either the provided `$databaseCredentials` is returned, 
  or a new empty `SecretObject` is created.

### Why It Matters?

* Prevents noisy **PHP warnings** in environments where PDO drivers expose limited attributes.  
* Provides a **more reliable and consistent mechanism** for retrieving database credentials.  
* Ensures **backward compatibility** while making the method more robust in multi-database environments.

