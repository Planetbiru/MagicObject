<?php

namespace MagicObject\Util\Database;

use MagicObject\Database\PicoTableInfo;
use MagicObject\MagicObject;
use stdClass;

class EntityUtil
{
    /**
     * Table info
     * @param MagicObject $entity
     * @return string[]|array
     */
    public static function getPropertyColumn($entity)
    {
        $tableInfo = $entity->tableInfo();
        if($tableInfo == null)
        {
            return array();
        }
        $columns = $tableInfo->getColumns();
        $joinColumns = $tableInfo->getColumns();
        $propertyColumns = array();
        foreach($columns as $prop=>$column)
        {
            $propertyColumns[$prop] = $column['name'];
        }
        foreach($joinColumns as $prop=>$column)
        {
            $propertyColumns[$prop] = $column['name'];
        }
        return $propertyColumns;
    }

    /**
     * Get entity data
     * @param array|stdClass|MagicObject $data
     * @param string[] $map
     * @return array
     */
    public static function getEntityData($data, $map)
    {
        $newData = array();
        if(isset($data))
        {
            if(is_array($data))
            {
                $newData = self::fromArray($data, $map);
            }
            if($data instanceof stdClass)
            {
                $newData = self::fromStdClass($data, $map);
            }
            if($data instanceof MagicObject)
            {
                $newData = self::fromMagicObject($data, $map);
            }
        }
        return $newData;
    }

    /**
     * From array
     * @param array $data
     * @param string[] $map
     * @return array
     */
    private static function fromArray($data, $map)
    {
        $newData = array();
        foreach($map as $key=>$value)
        {
            if(isset($data[$value]))
            {
                $newData[$key] = $data[$value];
            }
        }
        return $newData;
    }

    /**
     * From stdClass
     * @param stdClass $data
     * @param string[] $map
     * @return array
     */
    private static function fromStdClass($data, $map)
    {
        $newData = array();
        foreach($map as $key=>$value)
        {
            if(isset($data->{$value}))
            {
                $newData[$key] = $data->{$value};
            }
        }
        return $newData;
    }

    /**
     * From MagicObject
     * @param MagicObject $data
     * @param string[] $map
     * @return array
     */
    private static function fromMagicObject($data, $map)
    {
        $newData = array();
        foreach($map as $key=>$value)
        {
            $newData[$key] = $data->get($value);
        }
        return $newData;
    }
}