<?php

namespace MagicObject\Request;

class  InputGet extends PicoRequestBase {
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->loadData($_GET);
    }

    /**
     * Get global variable $_GET
     *
     * @return array
     */
    public static function requestGet()
    {
        return $_GET;
    }
}