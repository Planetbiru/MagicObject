## Database Query Builder

```php
<?php

use MagicObject\Database\PicoDatabaseQueryBuilder;

use MagicObject\Database\PicoDatabase;
use MagicObject\Database\PicoDatabaseCredentials;
use MusicProductionManager\Config\ConfigApp;

use MusicProductionManager\Config\ConfigApp;

use MusicProductionManager\Data\Entity\Album;

require_once dirname(__DIR__)."/vendor/autoload.php";

$cfg = new ConfigApp(null, true);
$cfg->loadYamlFile(dirname(__DIR__)."/.cfg/app.yml", true, true);

$databaseCredentials = new PicoDatabaseCredentials($cfg->getDatabase());
$database = new PicoDatabase($databaseCredentials);
try
{
    $database->connect();
  
    $queryBuilder = new PicoDatabaseQueryBuilder($database);
  
    $queryBuilder
        ->newQuery()
        ->select("u.*")
        ->from("user")
        ->alias("u")
        ->where("u.username = ? and u.password = ? and u.active = ?", $username, $password, true)
        ;
    $stmt = $database->executeQuery($queryBuilder);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach($rows as $user)
    {
        var_dump($user);
    }
  
}
catch(Ecxeption $e)
{
  
}
```

### Methods

**newQuery()**

`newQuery()` is method to clear all properties from previous query. Allways invoke this method before create new query to ensure the query is correct.

**insert()**

`insert()` is method to start the `INSERT` query.

Example 1:

```php
$queryBuilder = new PicoDatabaseQueryBuilder($database);
$queryBuilder->newQuery()
    ->insert()
    ->into("song")
    ->fields("(song_id, name, title, time_create)")
    ->values("('123456', 'Lagu 0001', 'Membendung Rindu', '2024-03-03 10:11:12')");
```

**into($query)**

`into($query)` is method for `INTO`

Example 1:

```php
$queryBuilder = new PicoDatabaseQueryBuilder($database);
$queryBuilder->newQuery()
    ->insert()
    ->into("song")
    ->fields("(song_id, name, title, time_create)")
    ->values("('123456', 'Lagu 0001', 'Membendung Rindu', '2024-03-03 10:11:12')");
```

**fields($query)**

`fields($query)` is method to send field on query `INSERT`. The parameter can be an array or string.

**values($query)**

`values($query)` is method to send values on query `INSERT`. The parameter can be an array or string.

Example 1:

```php
$queryBuilder = new PicoDatabaseQueryBuilder($database);
$queryBuilder->newQuery()
    ->insert()
    ->into("song")
    ->fields("(song_id, name, title, time_create)")
    ->values("('123456', 'Lagu 0001', 'Membendung Rindu', '2024-03-03 10:11:12')");
```

Example 2:

```php
$queryBuilder = new PicoDatabaseQueryBuilder($database);
$queryBuilder->newQuery()
    ->insert()
    ->into("song")
    ->fields("(song_id, name, title, time_create)")
    ->values("(?, ?, ?, ?)", '123456', 'Lagu 0001', 'Membendung Rindu', '2024-03-03 10:11:12');
```

Example 23:

```php
$queryBuilder = new PicoDatabaseQueryBuilder($database);
$queryBuilder->newQuery()
    ->insert()
    ->into("song")
    ->fields(array("song_id", "name", "title", "time_create"))
    ->values(array('123456', 'Lagu 0001', 'Membendung Rindu', '2024-03-03 10:11:12'));
```

Example 4:

```php
$queryBuilder = new PicoDatabaseQueryBuilder($database);
$queryBuilder->newQuery()
    ->insert()
    ->into("song")
    ->fields("(song_id, name, title, time_create)")
    ->values(array('123456', 'Lagu 0001', 'Membendung Rindu', '2024-03-03 10:11:12'));
```

Example 5:

```php
$queryBuilder = new PicoDatabaseQueryBuilder($database);
$data = array(
    "song_id"=>'123456', 
    "name"=>'Lagu 0001', 
    "title"=>'Membendung Rindu', 
    "time_create"=>'2024-03-03 10:11:12'
    );
$queryBuilder->newQuery()
    ->insert()
    ->into("song")
    ->fields(array_keys($data))
    ->values(array_values($data));
```