## Multiple Database Connection

MagicObject requires a database connection for object construction using database connections. In this way, users can use multiple database connections in a module very easily.

Example:

```php
$album = new Album(null, $database1);
try
{
	$album->find("123456");
	$album->currentDatabase($database2);
	$album->save();
}
catch(Exception $e)
{
	error_log($e->getMessage());
}
```

From the example above, it can be seen that an entity can even use two databases interchangeably.

When constructed, the `$album` object uses the `$database1` database connection. The application retrieves data with the primary key `123456` from `$database1` and then stores it in `$database2` without having to change many parameters and lots of code. Please note that to save `$album` data, you must use the `save` method instead of `insert` or `update` because there is a possibility that data with the same primary key already exists in `$database2`.