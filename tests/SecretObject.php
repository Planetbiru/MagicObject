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
$song1->loadYamlString(
"
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
timeCreate: 2024-03-03 12:12:12
timeEdit: 2024-03-03 13:13:13
",
false, true, true
);


$song2 = new SongSecret2();
$song2->loadYamlString($song1->dumpYaml(10, 4), false, true, true);

echo $song2->dumpYaml(10, 4);