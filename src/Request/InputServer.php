<?php

namespace MagicObject\Request;

class  InputServer extends PicoRequestBase {
    public function __construct()
    {
        parent::__construct();
        $this->loadData($_SERVER);
    }
}