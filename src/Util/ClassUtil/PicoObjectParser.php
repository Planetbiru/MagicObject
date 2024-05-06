<?php

namespace MagicObject\Util\ClassUtil;

use MagicObject\MagicObject;
use MagicObject\Util\PicoStringUtil;
use stdClass;
use Symfony\Component\Yaml\Yaml;

/**
 * Object parser
 */
class PicoObjectParser
{
    /**
     * Parse MagicObject
     * @param MagicObject $data
     * @param boolean $parseNullAndBool
     * @return MagicObject
     */
    private static function parseMagicObject($data, $parseNullAndBool = false)
    {
        $magicObject = new MagicObject();
        $values = $data->value();
        foreach ($values as $key => $value) {
            $key2 = PicoStringUtil::camelize($key);
            if(is_scalar($value))
            {
                $magicObject->set($key2, $value, true);
            }
            else
            {
                $magicObject->set($key2, self::parseRecursiveObject($value, $parseNullAndBool), true);
            }
        }
        return $magicObject;
    }
    
    /**
     * Parse Object
     * @param stdClass|array $data
     * @param boolean $parseNullAndBool
     * @return MagicObject
     */
    private static function parseObject($data, $parseNullAndBool = false)
    {
        $magicObject = new MagicObject();
        foreach ($data as $key => $value) {
            $key2 = PicoStringUtil::camelize($key);
            if(is_scalar($value))
            {
                $magicObject->set($key2, $value, true);
            }
            else
            {
                $magicObject->set($key2, self::parseRecursiveObject($value, $parseNullAndBool), true);
            }
        }
        return $magicObject;
    }
    
    /**
     * Check if input is associated array
     *
     * @param array $array
     * @return boolean
     */
    private static function hasStringKeys($array) {
        return count(array_filter(array_keys($array), 'is_string')) > 0;
    }
    
    /**
     * Parse recursive
     * @param mixed $data
     * @param boolean $parseNullAndBool
     * @return mixed
     */
    public static function parseRecursiveObject($data, $parseNullAndBool = false)
    {
        $result = null;
        if($data != null)
        {
            if($data instanceof MagicObject)
            {
                $result = self::parseMagicObject($data, $parseNullAndBool);
            }
            else if (is_array($data) || is_object($data) || $data instanceof stdClass) {
                $obj = new MagicObject();
                foreach($data as $key=>$val)
                {
                    $obj = self::updateObject($obj, $key, $val, $parseNullAndBool);
                }
                $result = $obj;
            }
            else
            {
                $result = $data;
            }
        }
        return $result;
    }
    
    /**
     * Update object
     *
     * @param MagicObject $obj
     * @param string $key
     * @param mixed $val
     * @param boolean $parseNullAndBool
     * @return MagicObject
     */
    private static function updateObject($obj, $key, $val, $parseNullAndBool = false)
    {
        if (self::isObject($val))
        {
            $obj->set($key, self::parseRecursiveObject($val, $parseNullAndBool));
        }
        else if (is_array($val))
        {
            if(self::hasStringKeys($val))
            {
                $obj->set($key, self::parseRecursiveObject($val, $parseNullAndBool));
            }
            else
            {
                $obj->set($key, self::parseRecursiveArray($val, $parseNullAndBool));
            }
        }
        else
        {
            $obj->set($key, $val);
        }
        return $obj;
    }
    
    /**
     * Check if value is object
     *
     * @param [type] $value
     * @return boolean
     */
    private static function isObject($value)
    {
        if ($value instanceof stdClass || is_object($value))  
        {
            return true;
        }
        return false;
    }
    
    /**
     * Parse recursive
     * @param array $data
     * @param boolean $parseNullAndBool
     */
    public static function parseRecursiveArray($data, $parseNullAndBool = false)
    {
        $result = array();
        if($data != null)
        {
            foreach($data as $val)
            {
                if (self::isObject($val))
                {
                    $result[] = self::parseRecursiveObject($val, $parseNullAndBool);
                }
                else if (is_array($val))
                {
                    if(self::hasStringKeys($val))
                    {
                        $result[] = self::parseRecursiveObject($val, $parseNullAndBool);
                    }
                    else
                    {
                        $result[] = self::parseRecursiveArray($val, $parseNullAndBool);
                    }
                }
                else
                {
                    $result[] = $val;
                }
            }
        }
        return $result;
    }
    
    /**
     * Parse from Yaml recursively
     * @param string $yamlString
     * @param boolean $parseNullAndBool
     */
    public static function parseYamlRecursive($yamlString, $parseNullAndBool = false)
    {
        if($yamlString != null)
        {
            $data = Yaml::parse($yamlString);
            if (is_array($data) || is_object($data)) {
                return self::parseObject($data, $parseNullAndBool);
            }
        }
        return null;
    }
    
    /**
     * Parse from JSON recursively
     * @param mixed $data
     * @param boolean $parseNullAndBool
     */
    public static function parseJsonRecursive($data, $parseNullAndBool = false)
    {
        if($data != null)
        {
            if(is_scalar($data) && is_string($data))
            {
                return self::parseObject(json_decode($data), $parseNullAndBool);
            }
            else if (is_array($data) || is_object($data)) {
                return self::parseObject($data, $parseNullAndBool);
            }
            else
            {
                return $data;
            }
        }
        return null;
    }

    /**
     * Parse string
     *
     * @param string $data
     * @return mixed
     */
    public static function parseString($data)
    {
        if($data == 'null')
        {
            return null;
        }
        else if($data == 'false')
        {
            return false;
        }
        else if($data == 'true')
        {
            return true;
        }
        else
        {
            return $data;
        }
    }
}