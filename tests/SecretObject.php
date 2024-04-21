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