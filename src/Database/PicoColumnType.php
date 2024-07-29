<?php

namespace MagicObject\Database;

use MagicObject\Util\PicoStringUtil;

class PicoColumnType
{
    private $columns = array();
    
    /**
     * Constructor
     *
     * @param array $columns
     */
    public function __construct($columns)
    {
        $this->columns = array();
        foreach($columns as $propertyName => $column)
        {
            $newPropertyName = PicoStringUtil::camelize($propertyName);
            $this->columns[$newPropertyName] = $column;
        }
    }
    
    /**
     * Magic method
     *
     * @param string $name
     * @param array $arguments
     * @return mixed|null|void
     */
    public function __call($name, $arguments)
    {
        if(strpos($name, 0, 3) === 'get')
        {
            $newPropertyName = PicoStringUtil::camelize(substr($name, 3));
            if(isset($this->columns[$newPropertyName]))
            {
                $column = $this->columns[$newPropertyName];
                $type = isset($column['type']) ? $column['type'] : 'string';
                return $this->toExcelType($type);
            }
            return 'string';
        }
    }
    
    /**
     * Convert to Excel type
     *
     * @param string $type
     * @return ExcelDataType
     */
    public function toExcelType($type)
    {
        return new ExcelDataType($type);
    }
}