<?php

use MagicObject\DataLabel\DataLabel;

class Label extends DataLabel
{
    /**
     * @Properties(name="name" label="Name")
     * @var string
     */
    protected $name;
}

$label = new Label(null);
$label->test = "OK";