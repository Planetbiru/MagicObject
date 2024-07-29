<?php

namespace MagicObject\Database;

class ExcelDataType
{
    /**
     * Column type
     *
     * @var string
     */
    private $columnType;
    
    /**
     * Type map
     *
     * @var string[]
     */
    private $map = array(
        "double"=>"double",
        "float"=>"double",
        "bigint"=>"integer",
        "smallint"=>"integer",
        "tinyint(1)"=>"string",
        "tinyint"=>"integer",
        "int"=>"integer",
        "varchar"=>"string",
        "char"=>"string",
        "tinytext"=>"string",
        "mediumtext"=>"string",
        "longtext"=>"string",
        "text"=>"string",   
        "enum"=>"string",   
        "bool"=>"string",
        "boolean"=>"string",
        "timestamp"=>"string",
        "datetime"=>"string",
        "date"=>"string",
        "time"=>"string"
    );
    
    /**
     * Constructor
     *
     * @param string $columnType
     */
    public function __construct($columnType)
    {
        $this->columnType = $columnType;
    }
    
    /**
     * Convert to Excel
     *
     * @return void
     */
    public function convertToExcel()
    {
        foreach($this->map as $key=>$value)
        {
            if(stripos($this->columnType, $key) !== false)
            {
                return $value;
            }
        }
        return "string";
    }
    
    /**
     * Convert to string
     *
     * @return string
     */
    public function toString()
    {
        return $this->__toString();
    }
    
    /**
     * Convert to string
     *
     * @return string
     */
    public function __toString()
    {
        return $this->convertToExcel();
    }
}

    