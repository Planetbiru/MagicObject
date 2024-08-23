<?php

use MagicObject\SecretObject;
use MagicObject\Util\Database\PicoDatabaseUtilMySql;

require_once dirname(__DIR__) . "/vendor/autoload.php";

$config = new SecretObject();
$config->loadYamlFile('import.yml', true, true, true);

$sql = PicoDatabaseUtilMySql::importData($config, function($sql, $source, $target){
    $fp = fopen(__DIR__.'/db.sql', 'a');
    fwrite($fp, "-- Import data from source.$source to target.$target\r\n\r\n");
    fwrite($fp, $sql.";\r\n\r\n");
    fclose($fp);
});
