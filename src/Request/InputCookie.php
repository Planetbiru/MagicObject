<?php

namespace MagicObject\Request;

use MagicObject\Util\ClassUtil\PicoObjectParser;

class  InputCookie extends PicoRequestBase {
    /**
     * Recursive
     *
     * @var boolean
     */
    private $_recursive = false;

    /**
     * Parse null and boolean
     *
     * @var boolean
     */
    private $_parseNullAndBool = false;

    /**
     * Constructor
     * @param boolean $recursive
     * @param boolean $parseNullAndBool
     */
    public function __construct($recursive = false, $parseNullAndBool = false)
    {
        parent::__construct();
        $this->_recursive = $recursive; 
        $this->_parseNullAndBool = $parseNullAndBool;
        $this->loadData($_COOKIE);
    }

    /**
     * Get global variable $_COOKIE
     *
     * @return array
     */
    public static function requestCookie()
    {
        return $_COOKIE;
    }

    /**
     * Override loadData
     *
     * @param array $data
     * @return self
     */
    public function loadData($data)
    {
        if($this->_recursive)
        {
            $genericObject = PicoObjectParser::parseJsonRecursive($data, $this->_parseNullAndBool);
            if($genericObject != null)
            {
                $values = $genericObject->valueArray();
                if($values != null && is_array($values))
                {
                    $keys = array_keys($values);
                    foreach($keys as $key)
                    {
                        $this->{$key} = $genericObject->get($key);
                    }
                }
            }
        }
        else
        {
            parent::loadData($data);
        }
        return $this;
    } 
}