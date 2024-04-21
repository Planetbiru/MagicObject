## Secret Object

### Definition

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

### Property Parameters

1. `@EncryptIn` annotation will encrypt the value before it is assigned to the associated property with the `set` method. 
2. `@DecryptIn` annotation will decrypt the value before it is assigned to the associated property with the `set` method. 
3. `@EncryptOut` annotation will encrypt the property when application call `get` method. 
4. `@DecryptOut` annotation will decrypt the property when application call `get` method. 

### Secure Config

Application configuration is usually written in a file or environment variable after being encrypted. This configuration cannot be read by anyone without decrypting it first. MagicObject will retrieve the encrypted value. If a user accidentally dumps an object using `var_dump` or `print_r`, then PHP will only display the encrypted value. When PHP makes a connection to the database using a credential, MagicObject will decrypt it but the value will not be stored in the object's properties.

Thus, to create an application configuration, it is enough to use the `@DecryptOut` annotation. Thus, MagicObject will only decrypt the configuration when it is ready to be used.

**Example 1**

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

### Create Secret

When creating a secure application configuration, users can simply use the `@EncryptOut` annotation. MagicObject will load the configuration as entered but will encrypt it when dumped to a file. For configurations that will not be encrypted, do not use `@EncryptIn`, `@DecryptIn`, `@EncryptOut`, or `@DecryptOut`. 

```php
<?php

namespace MagicObject\Database;

use MagicObject\SecretObject;

class SecretGenerator extends SecretObject
{
	/**
	 * Database driver
	 *
	 * @var string
	 */
	protected $driver;

	/**
	 * Database server host
	 *
	 * @EncryptOut
	 * @var string
	 */
	protected $host;

	/**
	 * Database server port
	 * @var integer
	 */
	protected $port;

	/**
	 * Database username
	 *
	 * @EncryptOut
	 * @var string
	 */
	protected $username;

	/**
	 * Database user password
	 *
	 * @EncryptOut
	 * @var string
	 */
	protected $password;

	/**
	 * Database name
	 *
	 * @EncryptOut
	 * @var string
	 */
	protected $databaseName;

	/**
	 * Database schema
	 *
	 * @EncryptOut
	 * @var string
	 */
	protected $databseSchema;

	/**
	 * Application time zone
	 *
	 * @var string
	 */
	protected $timeZone;
}
```

```php

$yaml = "  
time_zone_system: Asia/Jakarta
default_charset: utf8
driver: mysql
host: localhost
port: 3306
username: root
password: password
database_name: music
database_schema: public
time_zone: Asia/Jakarta
salt: GaramDapur
";

$config = new MagicObject();
$config->loadYamlString($yaml);
$generator = new SecretGenerator($config);

echo $generator; // will print JSON

$secretYaml = $generator->dumpYaml(null, 4, 0); // will print secret yaml

file_put_content("secret.yaml", $secretYaml); // will dump to file secret.yaml
```

Do not use standard encryption keys when creating or using SecretObjects. Always use your own lock. The encryption key must be generated using a callback function. Do not enter it as an object property or constant.

```php

$yaml = "  
time_zone_system: Asia/Jakarta
default_charset: utf8
driver: mysql
host: localhost
port: 3306
username: root
password: password
database_name: music
database_schema: public
time_zone: Asia/Jakarta
salt: GaramDapur
";

$config = new MagicObject();
$config->loadYamlString($yaml, false, true, true);
$generator = new SecretGenerator($config, function(){
	// define your own key here
	return "6619f3e7a1a9f0e75838d41ff368f72868e656b251e67e8358bef8483ab0d51c";
});

echo $generator; // will print JSON

$secretYaml = $generator->dumpYaml(null, 4, 0); // will print secret yaml

file_put_content("secret.yaml", $secretYaml); // will dump to file secret.yaml
```

or you can also call another function. 

```php

function getSecure()
{
	return "6619f3e7a1a9f0e75838d41ff368f72868e656b251e67e8358bef8483ab0d51c";
}

$yaml = "  
time_zone_system: Asia/Jakarta
default_charset: utf8
driver: mysql
host: localhost
port: 3306
username: root
password: password
database_name: music
database_schema: public
time_zone: Asia/Jakarta
salt: GaramDapur
";

$config = new MagicObject();
$config->loadYamlString($yaml, false, true, true);
$generator = new SecretGenerator($config, function(){
	// define your own key here
	return getSecure();
});

echo $generator; // will print JSON

$secretYaml = $generator->dumpYaml(null, 4, 0); // will print secret yaml

file_put_content("secret.yaml", $secretYaml); // will dump to file secret.yaml
```

### Multilevel Object Secure

MagicObject also support Multilevel Yaml.

**Example**

```php
<?php

use MagicObject\SecretObject;

require_once dirname(__DIR__) . "/vendor/autoload.php";

class SongSecret1 extends SecretObject
{
    /**
     * Cocalist
     * 
     * @EncryptOut
     * @var mixed
     */
    protected $vocalist;
}

class SongSecret2 extends SecretObject
{
    /**
     * Cocalist
     * 
     * @DecryptOut
     * @var mixed
     */
    protected $vocalist;
}

$song1 = new SongSecret1();

$yaml1 = "
songId: 1234567890
title: Lagu 0001
duration: 320
album:
  albumId: 234567
  name: Album 0001
genre:
  genreId: 123456
  name: Pop
vocalist:
  vovalistId: 5678
  name: Budi
  agency:
    agencyId: 1234
    name: Agency 0001
    company:
      companyId: 5678
      name: Company 1
      pic:
        - name: Kamshory
          gender: M
        - name: Mas Roy
          gender: M
timeCreate: 1709467932
timeEdit: 1709471593
";
$song1->loadYamlString(
$yaml1,
false, true, true
);

echo $song1->dumpYaml(null, 4);

$yaml2 = "
vocalist:
    vovalistId: fDkFGpLJcO/RTjGnoVSSHQ013vlgdD9J9kTxYLBAc2/VkTlJhXt73TEYjkfLpwFUh13HRvC/it6xaxnD3x5u9A==
    name: kCcX0Tn7ZRnmcl3sjbq1zEHl5HCBYwYep5T8kLUQ2l8uJwpBCCNainese0gjeJrgllYmgRAPixAbHTPwVYLu9g==
    agency:
        agencyId: YjqlFnXB6l8+ShPvc13WjjPiNYo82OEZ6uc7jZyoQ8c3BswS1dBw/yRPztzgbQ9rz9LNg31vJhx8KbUOQxFOvA==
        name: buprh5yvS5+4A+mGC987AgTpSzelyISOPFW5T3gX2KpgfAw21UWNehL79iN1sAftPkBzmK27r0OyFLfw71c/oA==
        company:
            companyId: ZD4rbvFUGdJfueUL69Yif5RGJVoaHFmma6FxYwuMmihPQ/MtcGPJqU65IFpgHAgNIJuWwwR7p/6P6WqHbAKTgg==
            name: csGl5cDHkZd8hysMtR6a518S10TYFe0NJsIRi90qmCoxbCQpSp1m0kEZF1n1mdS7bLM3Wsiz6RDeRAvvQAWqxQ==
            pic:
                -
                    name: old4N6OOA1Yb8a15Ptq8Yre7uvGSGnP6AhSN/pvp+rWL4gVC51b5zQ5pYNXKAMYNltvo+yeRgqXurDdmNwdV5w==
                    gender: JEHp9j0tyoYf4uO+uN8OMiWN4pxjbrcW1Z0ykAiDu5NmROwEVm5k7i8Xsm45paVbA2DZeQiA9OfwBY769qlWZQ==
                -
                    name: Wrt/ZWhSMZH2Fyo1KBN3LL7hDbu6/veTS4zOQ1iBXjp9K02K9KKFH2ND8x5l+fB0WPkp8hNTUAD4TFIdUaorow==
                    gender: 2hY0VsRTEkMUKVeVfhvH1r7fS1IMkQOmWtpzPRUihRPGJCs4ZNNY7zi/zMw/OH1yqplpOCSuUEJzamAz3fCIKg==
songId: 1234567890
title: 'Lagu 0001'
duration: 320
album:
    albumId: 234567
    name: 'Album 0001'
genre:
    genreId: 123456
    name: Pop
timeCreate: 1709467932
timeEdit: 1709471593
";

$song2 = new SongSecret2();
$song2->loadYamlString($yaml2, false, true, true);

echo $song2->dumpYaml(null, 4);
```