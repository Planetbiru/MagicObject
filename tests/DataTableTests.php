<?php

use MagicObject\DataTable;
use MagicObject\Util\ObjectParser;

error_reporting(E_ALL);
require_once dirname(__DIR__) . "/vendor/autoload.php";

/**
 * House
 * 
 * @Attributes(id="house" widh="100%" style="border-collapse:collapse; color:#333333")
 * @ClassList(content="table table-responsive")
 * @DefaultColumnLabel(content="Label->content")
 * @Language(content="en")
 * @JSON(property-naming-strategy=SNAKE_CASE)
 * @Table(name="album")
 * @Id(content="house")
 */
class House extends DataTable
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

    /**
     * Time Create
     *
     * @Label(content="Time Create")
     * @Column(name="timeCreate")
     * @var DateTime
     */
    protected $timeCreate;
    
}

$data = ObjectParser::parseYamlRecursive(
"id: 1
address: Jalan Inspeksi no 9
color: blue
"
);

$rumah = new House($data);
$rumah->addClass("coba");
$rumah->removeClass('table');
echo $rumah;
