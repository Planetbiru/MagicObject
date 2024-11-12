# MagicObject Version 2

## What is New

1.  **Native Query**
    
    -   Introduced support for native SQL queries, allowing users to execute raw SQL statements directly within the framework. This feature enhances flexibility and provides greater control over complex queries that may not be easily constructed using the ORM layer.
2.  **Multiple Database Connection**
    
    -   Added the ability to configure and manage multiple database connections. This allows developers to connect to different databases within the same application seamlessly, facilitating multi-database architectures and more complex application requirements.
3.  **Enable or Disable Entity Cache on Join**
    
    -   Introduced a feature to enable or disable entity caching specifically for join operations. This gives developers fine-tuned control over caching strategies, improving performance while also allowing for fresh data retrieval when necessary.
4.  **Enhanced Documentation**
    
    -   Comprehensive updates to the documentation for classes, properties, functions, and annotations. This includes clearer explanations, examples, and usage guidelines, making it easier for developers to understand and utilize the framework effectively.
5.  **Bug Fixes on Previous Version**
    
    -   Addressed various bugs and issues reported in earlier versions. This includes performance improvements, stability enhancements, and corrections of minor errors that could affect the functionality of the framework.

## Additional Features

-   **Improved Error Handling**: Enhanced mechanisms for error detection and handling, providing more informative messages to assist developers in troubleshooting.
-   **Performance Optimizations**: Various internal optimizations that improve the overall performance of the framework, particularly in database interactions.
-   **Backward Compatibility**: Ensured backward compatibility with version 1, allowing for a smooth transition for existing users to upgrade without significant changes to their codebase.

## Migration Notes

-   When upgrading from version 1 to version 2, please review the migration notes for any breaking changes or required adjustments to your codebase. Detailed guidelines are provided to facilitate a smooth upgrade process.

# MagicObject Version 2.1

## What is New

MagicObject 2.1 introduces package annotations for entities, enhancing the process of joining them. These annotations are essential, as the namespace is required to properly join entities. The join class should be referenced by its base name only, without the namespace; otherwise, MagicObject may fail to recognize the class.

PHP does not provide a native method to retrieve a class's namespace. Earlier versions of MagicObject attempted to obtain this information by reading the PHP script, a method that proved both unsafe and inefficient.

With the addition of package annotations to each entity, MagicObject now offers a safer and more efficient way to join entities. However, if a package annotation is not available on an entity, version 2.1 will still revert to the old method.

MagicObject 2.1 introduces a suite of powerful database utilities aimed at enhancing database management and interoperability. One of the key features is the ability to seamlessly convert databases between PostgreSQL and MySQL, enabling developers to migrate their data and applications with ease. This conversion tool ensures that data types, constraints, and structures are accurately translated, reducing the potential for errors during migration.

Additionally, MagicObject 2.1 allows users to parse table structures directly from SQL statements without the need to first dump them into a database. This functionality streamlines the process of understanding and manipulating database schemas, making it easier for developers to work with existing SQL code or to integrate with third-party systems.

These utilities not only enhance efficiency but also provide a robust foundation for database development, allowing users to focus on building applications rather than wrestling with database compatibility issues. With MagicObject 2.1, database management becomes more intuitive and accessible, empowering developers to harness the full potential of their data.


# MagicObject Version 2.7

## What is New

### PDO Support

With the release of **MagicObject 2.7**, a significant update has been introduced to allow users to leverage **PDO** (PHP Data Objects) for database connections. In previous versions, **MagicObject** required the use of **PicoDatabase**, its custom database handling class. However, recognizing that many developers are accustomed to establishing database connections via traditional PDO, this new version introduces flexibility by allowing PDO connections to be passed directly to the **MagicObject** constructor.

This update aims to bridge the gap between traditional PDO-based database management and the advanced features provided by **MagicObject**, thus enhancing compatibility while retaining all the powerful functionality of the framework.

#### Why PDO Support?

The decision to support **PDO** was made to accommodate users who have already established database connections in their applications using PDO, instead of relying on **PicoDatabase** from the start. By supporting PDO, **MagicObject** allows users to continue working with their preferred method of connecting to the database while still benefiting from the full range of features and utilities **MagicObject** offers.

While PDO is now an option for initializing **MagicObject**, it is used only in the constructor. Once the object is initialized, **MagicObject** continues to use **PicoDatabase** for all subsequent database interactions, ensuring that users can still benefit from **PicoDatabase**'s advanced features like automatic query building, database abstraction, and optimized query execution.

#### How PDO Support Works

In **MagicObject 2.7**, when you pass a **PDO** connection object to the constructor, it is automatically converted into a **PicoDatabase** instance using the `PicoDatabase::fromPdo()` static method. This ensures that even though PDO is used to establish the initial connection, the object will still operate using **PicoDatabase** for all subsequent database operations. The constructor of **MagicObject** ensures that the database connection is properly initialized and the type of database is correctly detected based on the PDO driver.

### Pageable and Sortable in Native Query in MagicObject 2.7

In **MagicObject version 2.7**, support for **pageable** and **sortable** functionality has been added to native queries. Previously, native queries did not support pagination and sorting directly. Instead, users had to manually include `SORT BY` and `LIMIT OFFSET` clauses in their queries, which made them less flexible. This approach was problematic because each Database Management System (DBMS) has its own syntax for writing queries, making it cumbersome to adapt queries for different platforms.

With the introduction of pageable and sortable support in version 2.7, users can now easily pass **pagination** parameters using the `PicoPageable` type and **sorting** parameters using the `PicoSortable` type directly into their native queries. These parameters can be placed anywhere within the query, but it is recommended to position them either at the beginning or the end of the query for optimal readability and organization.

This enhancement makes native queries more flexible and easier to maintain, as the logic for pagination and sorting is handled automatically, without requiring manual intervention for each DBMS. As a result, users can now write cleaner, more efficient, and database-agnostic native queries.

### Transactional

MagicObject version 2.7 introduces new features for transactional database management, namely `startTransaction()`, `commit()`, and `rollback()`. These functions allow entities to directly initiate and manage transactions within their scope. The `startTransaction()` function begins a new transaction, while `commit()` ensures that all changes made during the transaction are permanently saved to the database. On the other hand, `rollback()` can be used to revert any changes made during the transaction in case of an error or interruption. These functions require an active database connection to operate, providing a streamlined way for entities to manage data consistency and integrity within their transactions.
