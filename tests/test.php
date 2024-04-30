<?php

use MagicObject\Util\AttrUtil;

require_once dirname(__DIR__) . "/vendor/autoload.php";


echo (new AttrUtil())->doReturnAttributeSelected()->whenEquals(1, 1);

