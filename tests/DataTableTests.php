<?php

use MagicObject\DataTable;
use MagicObject\Util\ObjectParser;

require_once dirname(__DIR__) . "/vendor/autoload.php";

/**
 * Rumah
 * 
 * @Attributes(id="rumah" widh="100%" style="border-collapse:collapse; color:#333333")
 * @ClassList(content="table table-responsive")
 * @DefaultColumnName(content="label")
 * @Language(content="en")
 */
class Rumah extends DataTable
{
    /**
     * ID
     *
     * @Label(content="ID")
     * @Column(name="id")
     * @var string
     */
    protected $id;
    
    /**
     * Address
     *
     * @Label(content="Address")
     * @Column(name="address")
     * @var string
     */
    protected $address;
    
    /**
     * Color
     *
     * @Label(content="Color")
     * @Column(name="color")
     * @var string
     */
    protected $color;
    
}

$data = ObjectParser::parseYamlRecursive(
"id: 1
address: Jalan Inspeksi no 9
color: blue
"
);

$rumah = new Rumah($data);

echo $rumah;