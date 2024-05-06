<?php

namespace MagicObject\Request;

use MagicObject\Util\ClassUtil\PicoObjectParser;

class  InputPost extends PicoRequestBase {
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