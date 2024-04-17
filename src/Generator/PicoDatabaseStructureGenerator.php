<?php

namespace MagicObject\Generator;

use MagicObject\Database\PicoDatabase;
use MagicObject\Database\PicoDatabasePersistence;
use MagicObject\Database\PicoDatabaseType;
use MagicObject\MagicObject;
use MagicObject\Util\Database\PicoDatabaseUtilMySql;

class PicoDatabaseStructureGenerator
{
    /**
     * Entity
     *
     * @var MagicObject
     */
    private $entity;
    
    /**
     * Database
     *
     * @var PicoDatabase
     */
    private $database;
    
    /**
     * Database type
     *
     * @var string
     */
    private $databaseType = null;
    
    /**
     * Constructor
     *
     * @param MagicObject $entity
     * @param PicoDatabase $database
     * @param string $databaseType
     */
    public function __construct($entity, $database = null, $databaseType = null)
    {
        $this->entity = $entity;
        if($database != null)
        {
            $this->database = $database;
            $this->databaseType = $database->getDatabaseType();
        }
        else
        {
            $database = $entity->currentDatabase();
            if($database != null)
            {
                $database = $entity->currentDatabase();
                $this->databaseType = $database->getDatabaseType();
            }
            else if($databaseType != null)
            {
                $this->databaseType = $databaseType;
            }
        }
    }
    
    public function dumpStructure($createIfNotExists = false, $dropIfExists = false, $engine = 'InnoDB', $charset = 'utf8mb4')
    {
        $databasePersist = new PicoDatabasePersistence($this->database, $this->entity);
        $tableInfo = $databasePersist->getTableInfo();
        $picoTableName = $tableInfo->getTableName();
        
        if($this->databaseType == PicoDatabaseType::DATABASE_TYPE_MARIADB || $this->databaseType == PicoDatabaseType::DATABASE_TYPE_MYSQL)
        {
            return PicoDatabaseUtilMySql::dumpStructure($tableInfo, $picoTableName, $createIfNotExists, $dropIfExists, $engine, $charset);
        }
        else
        {
            return "";
        }
    }
    
}