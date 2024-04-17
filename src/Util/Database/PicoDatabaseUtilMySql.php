<?php

namespace MagicObject\Util\Database;

use MagicObject\Database\PicoDatabase;
use MagicObject\Database\PicoTableInfo;

class PicoDatabaseUtilMySql
{
    /**
     * Get column list
     *
     * @param PicoDatabase $database
     * @param string $picoTableName
     * @return array
     */
    public static function getColumnList($database, $picoTableName)
    {
        $sql = "SHOW COLUMNS FROM $picoTableName";
        return $database->fetchAll($sql);
    }
    
    /**
     * Dump database structure
     *
     * @param PicoTableInfo $tableInfo
     * @param string $picoTableName
     * @return string
     */
    public static function dumpStructure($tableInfo, $picoTableName, $createIfNotExists = false, $dropIfExists = false, $engine = 'InnoDB', $charset = 'utf8mb4')
    {
        $query = array();
        $columns = array();
        if($dropIfExists)
        {
            $query[] = "-- DROP TABLE IF EXISTS `$picoTableName`;";
            $query[] = "";
        }
        $createStatement = "";
        if($createIfNotExists)
        {
            $createStatement = "CREATE TABLE IF NOT EXISTS";
        }
        else
        {
            $createStatement = "CREATE TABLE";
        }
        $query[] = "$createStatement `$picoTableName` (";
        
        foreach($tableInfo->getColumns() as $column)
        {
            $columns[] = self::createColumn($column);
        }
        $query[] = implode(",\r\n", $columns);
        $query[] = ") ENGINE=$engine DEFAULT CHARSET=$charset;";
        
        $pk = $tableInfo->getPrimaryKeys();
        if(isset($pk) && is_array($pk) && !empty($pk))
        {
            $query[] = "";
            $query[] = "ALTER TABLE `$picoTableName`";
            foreach($pk as $primaryKey)
            {
                $query[] = "\tADD PRIMARY KEY (`$primaryKey[name]`)";
            } 
            $query[] = ";";       
        }
        
        
        return implode("\r\n", $query);
    }
    
    /**
     * Create column
     *
     * @param array $column
     * @return string
     */
    public static function createColumn($column)
    {
        $col = array();
        $col[] = "\t";
        $col[] = "`".$column['name']."`";
        $col[] = $column['type'];
        if(isset($column['nullable']) && strtolower(trim($column['nullable'])) == 'true')
        {
            $col[] = "NULL";
        } 
        else
        {
            $col[] = "NOT NULL";
        }
        if(isset($column['default_value']))
        {
            $defaultValue = $column['default_value'];
            $defaultValue = self::fixDefaultValue($defaultValue, $column['type']);
            $col[] = "DEFAULT $defaultValue";
        }
        return implode(" ", $col);
    }
    
    /**
     * Fixing default value
     *
     * @param string $defaultValue
     * @param string $type
     * @return string
     */
    public static function fixDefaultValue($defaultValue, $type)
    {
        if(stripos($type, 'char') !== false || stripos($type, 'text') !== false || stripos($type, 'int') !== false || stripos($type, 'float') !== false || stripos($type, 'double') !== false)
        {
            return "'".$defaultValue."'";
        }
        return $defaultValue;
    }
}