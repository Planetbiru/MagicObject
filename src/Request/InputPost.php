<?php

namespace MagicObject\Request;

class  InputPost extends PicoRequestBase {
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->loadData($_POST);
    }

    /**
     * Get global variable $_POST
     *
     * @return array
     */
    public static function requestPost()
    {
        return $_POST;
    }
}