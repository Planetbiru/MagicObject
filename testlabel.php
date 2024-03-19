<?php

use MagicObject\File\UplodFile;

require_once "vendor/autoload.php";

$files = new UplodFile();
$file1 = $files->test;
echo $file1;
$count = $file1->getFileCount();
for($i = 0; $i < $count; $i++)
{
    $file1->copy($i, function($fileItem){
        $temporaryName = $fileItem['tmp_name'];
        $name = $fileItem['name'];
        echo "$name | $temporaryName\r\n";
    });
}