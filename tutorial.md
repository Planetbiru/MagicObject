# Tutorial and Example

## Simpe Object

### Set and Get Properties Value

```php
<?php
use MagicObject\MagicObject;

require_once __DIR__ . "/vendor/autoload.php";

$someObject = new MagicObject();
$someObject->setId(1);
$someObject->setRealName("Someone");
$someObject->setPhone("+62811111111");
$someObject->setEmail("someone@domain.tld");

echo "ID        : " . $someObject->getId() . "\r\n";
echo "Real Name : " . $someObject->getRealName() . "\r\n";
echo "Phone     : " . $someObject->getPhone() . "\r\n";
echo "Email     : " . $someObject->getEmail() . "\r\n";

// get JSON string of the object
echo $someObject;

// or you can debug with
error_log($someObject);
```

### Unset Properties

```php
<?php
use MagicObject\MagicObject;

require_once __DIR__ . "/vendor/autoload.php";

$someObject = new MagicObject();
$someObject->setId(1);
$someObject->setRealName("Someone");
$someObject->setPhone("+62811111111");
$someObject->setEmail("someone@domain.tld");

echo "ID        : " . $someObject->getId() . "\r\n";
echo "Real Name : " . $someObject->getRealName() . "\r\n";
echo "Phone     : " . $someObject->getPhone() . "\r\n";
echo "Email     : " . $someObject->getEmail() . "\r\n";

$someObject->unsetPhone();
echo "Phone     : " . $someObject->getPhone() . "\r\n";

// get JSON string of the object
echo $someObject;

// or you can debug with
error_log($someObject);
```

### Check if Properties has Value

```php
<?php
use MagicObject\MagicObject;

require_once __DIR__ . "/vendor/autoload.php";

$someObject = new MagicObject();
$someObject->setId(1);
$someObject->setRealName("Someone");
$someObject->setPhone("+62811111111");
$someObject->setEmail("someone@domain.tld");

echo "ID        : " . $someObject->getId() . "\r\n";
echo "Real Name : " . $someObject->getRealName() . "\r\n";
echo "Phone     : " . $someObject->getPhone() . "\r\n";
echo "Email     : " . $someObject->getEmail() . "\r\n";

$someObject->unsetPhone();
if($someObject->hasValuePhone())
{
    echo "Phone     : " . $someObject->getPhone() . "\r\n";
}
else
{
    echo "Phone value is not set\r\n";
}
// get JSON string of the object
echo $someObject;

// or you can debug with
error_log($someObject);
```

## Multilevel Object

```php
<?php
use MagicObject\MagicObject;

require_once __DIR__ . "/vendor/autoload.php";

$car = new MagicObject();
$tire = new MagicObject();
$body = new MagicObject();

$tire->setDiameter(12);
$tire->setPressure(60);

$body->setLength(320);
$body->setWidth(160);
$body->Height(140);
$body->setColor("red");

$car->setTire($tire);
$car->setBody($body);

echo $car;

/*
{"tire":{"diameter":12,"pressure":60},"body":{"length":320,"width":160,"height":140,"color":"red"}}
*/

// to get color

echo $car->getBody()->getColor();

```

## Object from Yaml File

```yaml
# config.yml

tire: 
  diameter: 12
  pressure: 60
body: 
  length: 320
  width: 160
  height: 140
  color: red

```

```php
<?php
use MagicObject\MagicObject;

require_once __DIR__ . "/vendor/autoload.php";

$car = new MagicObject();
// load file config.yml
// will not replace value with environment variable
// load as object instead of associated array
$car->loadYamlFile("config.yml", false, true);

echo $car;
/*
{"tire":{"diameter":12,"pressure":60},"body":{"length":320,"width":160,"height":140,"color":"red"}}
*/

// to get color

echo $car->getBody()->getColor();

```

## Replace Value with Environment Variable

```yaml
# config.yml

tire: 
  diameter: ${TIRE_DIAMETER}
  pressure: ${TIRE_PRESSURE}
body: 
  length: ${BODY_LENGTH}
  width: ${BODY_WIDTH}
  height: ${BODY_HEIGHT}
  color: ${BODY_COLOR}

```

Before execute this script, user must set environment variable for `TIRE_DIAMETER`, `TIRE_PRESSURE`, `BODY_LENGTH`, `BODY_WIDTH`, `BODY_HEIGHT`, and `BODY_COLOR` depend on the operating system used.

```php
<?php
use MagicObject\MagicObject;

require_once __DIR__ . "/vendor/autoload.php";

$car = new MagicObject();
// load file config.yml
// will replace value with environment variable
// load as object instead of associated array
$car->loadYamlFile("config.yml", true, true);

echo $car;

// to get color

echo $car->getBody()->getColor();

```

## Secret Object

Secret Objects are very important in applications that use very sensitive and secret configurations. This configuration must be encrypted so that it cannot be seen either when someone tries to open the configuration file, environment variables, or even when the developer accidentally debugs an object related to the database so that the properties of the database object are exposed including the host name, database name, username and even password.

```php
<?php

namespace MagicObject\Database;

use MagicObject\SecretObject;

class PicoDatabaseCredentials extends SecretObject
{
	/**
	 * Database driver
	 *
	 * @var string
	 */
	protected $driver = 'mysql';

	/**
	 * Database server host
	 *
	 * @EncryptIn
	 * @DecryptOut
	 * @var string
	 */
	protected $host = 'localhost';

	/**
	 * Database server port
	 * @var integer
	 */
	protected $port = 3306;

	/**
	 * Database username
	 *
	 * @EncryptIn
	 * @DecryptOut
	 * @var string
	 */
	protected $username = "";

	/**
	 * Database user password
	 *
	 * @EncryptIn
	 * @DecryptOut
	 * @var string
	 */
	protected $password = "";

	/**
	 * Database name
	 *
	 * @EncryptIn
	 * @DecryptOut
	 * @var string
	 */
	protected $databaseName = "";
	
	/**
	 * Database schema
	 *
	 * @EncryptIn
	 * @DecryptOut
	 * @var string
	 */
	protected $databseSchema = "public";

	/**
	 * Application time zone
	 *
	 * @var string
	 */
	protected $timeZone = "Asia/Jakarta";
}
```

The `@EncryptIn` annotation will encrypt the value before it is assigned to the associated property with the `set` method. The `@DecryptOut` annotation will decrypt the property value after it is retrieved by the `get` method. Once all values are encrypted, the user does not need to annotate `@EncryptIn` again.

```php
<?php

namespace MagicObject\Database;

use MagicObject\SecretObject;

class PicoDatabaseCredentials extends SecretObject
{
	/**
	 * Database driver
	 *
	 * @var string
	 */
	protected $driver = 'mysql';

	/**
	 * Database server host
	 *
	 * @DecryptOut
	 * @var string
	 */
	protected $host = 'localhost';

	/**
	 * Database server port
	 * @var integer
	 */
	protected $port = 3306;

	/**
	 * Database username
	 *
	 * @DecryptOut
	 * @var string
	 */
	protected $username = "";

	/**
	 * Database user password
	 *
	 * @DecryptOut
	 * @var string
	 */
	protected $password = "";

	/**
	 * Database name
	 *
	 * @DecryptOut
	 * @var string
	 */
	protected $databaseName = "";
	
	/**
	 * Database schema
	 *
	 * @DecryptOut
	 * @var string
	 */
	protected $databseSchema = "public";

	/**
	 * Application time zone
	 *
	 * @var string
	 */
	protected $timeZone = "Asia/Jakarta";
}
```

## Input GET/POST/REQUEST/COOKIE

### Input GET

```php

use MagicObject\Request\InputGet;

require_once __DIR__ . "/vendor/autoload.php";

$inputGet = new InputGet();

$name = $inputGet->getRealName();
// equivalen to 
$name = $_GET['real_name'];

```

### Input Post

```php

use MagicObject\Request\InputPost;

require_once __DIR__ . "/vendor/autoload.php";

$inputPost = new InputPost();

$name = $inputPost->getRealName();
// equivalen to 
$name = $_POST['real_name'];

```

### Input Request

```php

use MagicObject\Request\InputRequest;

require_once __DIR__ . "/vendor/autoload.php";

$inputRequest = new InputRequest();

$name = $inputRequest->getRealName();
// equivalen to 
$name = $_REQUEST['real_name'];

```

### Input Cookie

```php

use MagicObject\Request\InputCookie;

require_once __DIR__ . "/vendor/autoload.php";

$inputCookie = new InputCookie();

$name = $inputCookie->getRealName();
// equivalen to 
$name = $_COOKIE['real_name'];

```

### Filter Input

Filter input from InputGet, InputPost, InputRequest and InputCookie

```php

use MagicObject\Request\InputGet;
use MagicObject\Request\PicoFilterConstant;

require_once __DIR__ . "/vendor/autoload.php";

$inputGet = new InputGet();

$name = $inputGet->getRealName(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS);
// equivalen to 
$name =  filter_input(
    INPUT_GET,
    'real_name',
    FILTER_SANITIZE_SPECIAL_CHARS
);


// another way to filter input
$inputGet->filterRealName(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS);
$name = $inputGet->getRealName();

```

List of filter

```
FILTER_SANITIZE_NO_DOUBLE_SPACE = 512;
FILTER_SANITIZE_PASSWORD = 511;
FILTER_SANITIZE_ALPHA = 510;
FILTER_SANITIZE_ALPHANUMERIC = 509;
FILTER_SANITIZE_ALPHANUMERICPUNC = 506;
FILTER_SANITIZE_NUMBER_UINT = 508;
FILTER_SANITIZE_NUMBER_INT = 519;
FILTER_SANITIZE_URL = 518;
FILTER_SANITIZE_NUMBER_FLOAT = 520;
FILTER_SANITIZE_STRING_NEW = 513;
FILTER_SANITIZE_ENCODED = 514;
FILTER_SANITIZE_STRING_INLINE = 507;
FILTER_SANITIZE_STRING_BASE64 = 505;
FILTER_SANITIZE_IP = 504;
FILTER_SANITIZE_NUMBER_OCTAL = 503;
FILTER_SANITIZE_NUMBER_HEXADECIMAL = 502;
FILTER_SANITIZE_COLOR = 501;
FILTER_SANITIZE_POINT = 500;
FILTER_SANITIZE_BOOL = 600;
FILTER_VALIDATE_URL = 273;
FILTER_VALIDATE_EMAIL = 274;
FILTER_SANITIZE_EMAIL = 517;
FILTER_SANITIZE_SPECIAL_CHARS = 515;
FILTER_SANITIZE_ASCII = 601;
```

## Entity

```php
<?php

namespace MusicProductionManager\Data\Entity;

use MagicObject\MagicObject;

/**
 * @Entity
 * @JSON(property-naming-strategy=SNAKE_CASE)
 * @Table(name="album")
 */
class Album extends MagicObject
{
	/**
	 * Album ID
	 * 
	 * @Id
	 * @GeneratedValue(strategy=GenerationType.UUID)
	 * @NotNull
	 * @Column(name="album_id", type="varchar(50)", length=50, nullable=false)
	 * @var string
	 */
	protected $albumId;

	/**
	 * Name
	 * 
	 * @Column(name="name", type="varchar(50)", length=50, nullable=true)
	 * @var string
	 */
	protected $name;

	/**
	 * Description
	 * 
	 * @Column(name="description", type="longtext", nullable=true)
	 * @var string
	 */
	protected $description;

	/**
	 * Release Date
	 * 
	 * @Column(name="release_date", type="date", nullable=true)
	 * @var string
	 */
	protected $releaseDate;

	/**
	 * Number Of Song
	 * 
	 * @Column(name="number_of_song", type="int(11)", length=11, nullable=true)
	 * @var integer
	 */
	protected $numberOfSong;

	/**
	 * Duration
	 * 
	 * @Column(name="duration", type="float", nullable=true)
	 * @var double
	 */
	protected $duration;

	/**
	 * Sort Order
	 * 
	 * @Column(name="sort_order", type="int(11)", length=11, nullable=true)
	 * @var integer
	 */
	protected $sortOrder;

	/**
	 * Time Create
	 * 
	 * @Column(name="time_create", type="timestamp", length=19, nullable=true, updatable=false)
	 * @var string
	 */
	protected $timeCreate;

	/**
	 * Time Edit
	 * 
	 * @Column(name="time_edit", type="timestamp", length=19, nullable=true)
	 * @var string
	 */
	protected $timeEdit;

	/**
	 * Admin Create
	 * 
	 * @Column(name="admin_create", type="varchar(40)", length=40, nullable=true, updatable=false)
	 * @var string
	 */
	protected $adminCreate;

	/**
	 * Admin Edit
	 * 
	 * @Column(name="admin_edit", type="varchar(40)", length=40, nullable=true)
	 * @var string
	 */
	protected $adminEdit;

	/**
	 * IP Create
	 * 
	 * @Column(name="ip_create", type="varchar(50)", length=50, nullable=true, updatable=false)
	 * @var string
	 */
	protected $ipCreate;

	/**
	 * IP Edit
	 * 
	 * @Column(name="ip_edit", type="varchar(50)", length=50, nullable=true)
	 * @var string
	 */
	protected $ipEdit;

	/**
	 * Active
	 * 
	 * @Column(name="active", type="tinyint(1)", length=1, default_value="1", nullable=true)
	 * @DefaultColumn(value="1")
	 * @var bool
	 */
	protected $active;

	/**
	 * As Draft
	 * 
	 * @Column(name="as_draft", type="tinyint(1)", length=1, default_value="1", nullable=true)
	 * @DefaultColumn(value="1")
	 * @var bool
	 */
	protected $asDraft;

}
```

MagicObject will not update `time_create`, `admin_create`, and `ip_create` because `updatable=false`. So, even if the application wants to update this value, this column will be ignored when performing an update query to the database.

### Usage

```php
<?php

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
    
    // create new 
    
    $album1 = new Album(null, $database);
    $album1->setAibumId("123456");
    $album1->setName("Album 1");
    $album1->setAdminCreate("USER1");
    $album1->setDuration(300);
    
    
    
    // other way to create object
    // create object from stdClass or other object with match property (snake case or camel case)
    $data = new stdClass;
    // snake case
    $data->album_id = "123456";
    $data->name = "Album 1";
    $data->admin_create = "USER1";
    $data->duration = 300;
    
    // or camel case
    $data->albumId = "123456";
    $data->name = "Album 1";
    $data->adminCreate = "USER1";
    $data->duration = 300;
    
    $album1 = new Album($data, $database); 
    
    // other way to create object
    // create object from associated array with match property (snake case or camel case)
    $data = array();
    // snake case
    $data["album_id"] "123456";
    $data["name"] = "Album 1";
    $data["admin_create"] = "USER1";
    $data["duration"] = 300;
    
    // or camel case
    $data["albumId"] = "123456";
    $data["name"] = "Album 1";
    $data["adminCreate"] = "USER1";
    $data["duration"] = 300;
    $album1 = new Album($data, $database);
    
    
    // get value from form
    // this way is not safe
    $album1 = new Album($_POST, $database);
    
    
    // we can use other way
    $inputPost = new InputPost();
    
    // we can apply filter
    $inputPost->filterName(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS);
    $inputPost->filterDescription(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS);
    
    // if property not present in $inputPost, we can set default value
    // please note that user can modify form and add update any unwanted properties to be updated
    $inputPost->checkboxActive(false);
    $inputPost->checkboxAsDraft(true);
    
    // we can remove any property data from object $inputPost before apply it to entity
    // it will not saved to database
    $inputPost->setSortOrder(null);
    
    $album1 = new Album($inputPost, $database);
    
    // insert to database
    $album1->insert();
    
    // insert or update
    $album1->save();
    
    // update
    // NoRecordFoundException if ID not found
    $album1->update();
    
    // convert to JSON
    $json = $album1->toString();
    // or
    $json = $album1 . "";
    
    // send to buffer output
    // automaticaly converted to string
    echo $album1;
    
    // find one by ID
    $album2 = new Album(null, $database);
    $album2->findOneByAlbumId("123456");
    
    // find multiple
    $album2 = new Album(null, $database);
    $albums = $album2->findByAdminCreate("USER1");
    $rows = $albums->getResult();
    foreach($rows as $albumSaved)
    {
        // $albumSaved is instance of Album
        
        // we can update data
        $albumSaved->setAdminEdit("USER1");
        $albumSaved->setTimeEdit(date('Y-m-d H:i:s'));
        
        // this value will not be saved to database because has no column
        $albumSaved->setAnyValue("ANY VALUE");
        
        $albumSaved->update();
    }
    
    
}
catch(Exception $e)
{
    // do nothing
}

```