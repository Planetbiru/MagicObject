## Session

Session variables keep information about one single user, and are available to all pages in one application.

### Session with File

**Yaml File**

```yaml
session:
  name: MUSICPRODUCTIONMANAGER
  max_life_time: 86400
  save_handler: files
```

**PHP Script**

```php
<?php

use MagicObject\SecretObject;
use MagicObject\Session\PicoSession;

require_once __DIR__ . "/vendor/autoload.php";

$cfg = new ConfigApp(null, true);
$cfg->loadYamlFile(__DIR__ . "/.cfg/app.yml", true, true);

$sessConf = new SecretObject($cfg->getSession());
$sessions = new PicoSession($sessConf);

$sessions->startSession();
```

### Session with Redis

**Yaml File**

```yaml
session:
  name: MUSICPRODUCTIONMANAGER
  max_life_time: 86400
  save_handler: redis
  save_path: tcp://127.0.0.1:6379?auth=${REDIS_AUTH}
```

**PHP Script**

```php
<?php

use MagicObject\SecretObject;
use MagicObject\Session\PicoSession;

require_once __DIR__ . "/vendor/autoload.php";

$cfg = new ConfigApp(null, true);
$cfg->loadYamlFile(__DIR__ . "/.cfg/app.yml", true, true);

$sessConf = new SecretObject($cfg->getSession());
$sessions = new PicoSession($sessConf);

$sessions->startSession();
```