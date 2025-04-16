<?php

use MagicObject\MagicObject;

require_once dirname(__DIR__) . "/vendor/autoload.php";


$object = new MagicObject();

$yaml = '
prop1:
  prop2:
    prop3:
      prop4: Test
';

$object->loadYamlString($yaml, false, true, true);

echo $object->retrieve('prop1', 'any');