<?php

use MagicObject\Request\InputServer;
use MagicObject\Util\PicoTestValueUtil;

require_once dirname(__DIR__) . "/vendor/autoload.php";

//echo (new PicoTestValueUtil())->doReturnAttributeChecked()->whenEquals(1, 1);

$inputServer = new InputServer();
echo $inputServer->userLanguage(true);
