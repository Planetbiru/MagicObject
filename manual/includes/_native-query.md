
This method executes a database query using the parameters and annotations defined in the caller function.
It leverages reflection to access the query string specified in the caller's docblock, binds the relevant parameters,
and then runs the query against the database.

By analyzing the parameters and return type of the calling function, this method enables dynamic execution of queries
that are tailored to the specified return type. The supported return types include:

- **void**: The method will return `null`.
- **int** or **integer**: It will return the number of affected rows.
- **object**: It will return a single result as an object.
- **stdClass[]**: All results will be returned as an array of stdClass objects.
- **array**: All results will be returned as an associative array.
- **string**: The results will be JSON-encoded.
- **PDOStatement**: The method can return a prepared statement for further operations if necessary.
- **MagicObject** and its derived classes: If the return type is a class name or an array of class names, instances
  of the specified class will be created for each row fetched.

Magic Object also supports return types `self` and `self[]` which will represent the respective class.

The method returns a mixed type result, which varies based on the caller function's return type:
- It will return `null` for void types.
- An integer representing the number of affected rows for int types.
- An object for single result types.
- An array of associative arrays for array types.
- A JSON string for string types.
- Instances of a specified class for class name matches.

If there is an error executing the database query, a **PDOException** will be thrown.

