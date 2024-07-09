<?php

namespace MagicObject\Database;

use stdClass;

class PicoTableInfo
{
    const NAME = "name";
    /**
     * Table name
     *
     * @var string
     */
    protected $tableName = null;

    /**
     * Columns
     *
     * @var array
     */
    protected $columns = array();

    /**
     * Join columns
     *
     * @var array
     */
    protected $joinColumns = array();

    /**
     * Primary keys
     *
     * @var array
     */
    protected $primaryKeys = array();

    /**
     * Auto increment keys
     *
     * @var array
     */
    protected $autoIncrementKeys = array();

    /**
     * Default value keys
     *
     * @var array
     */
    protected $defaultValue = array();

    /**
     * Not null columns
     *
     * @var array
     */
    protected $notNullColumns = array();

    /**
     * Get instance
     *
     * @return self
     */
    public static function getInstance()
    {
        return new self(null, array(), array(), array(), array(), array(), array());
    }

    /**
     * Constructor
     *
     * @param string $picoTableName
     * @param array $columns
     * @param array $joinColumns
     * @param array $primaryKeys
     * @param array $autoIncrementKeys
     * @param array $defaultValue
     * @param array $notNullColumns
     */
    public function __construct($picoTableName, $columns, $joinColumns, $primaryKeys, $autoIncrementKeys, $defaultValue, $notNullColumns)
    {
        $this->tableName = $picoTableName;
        $this->columns = $columns;
        $this->joinColumns = $joinColumns;
        $this->primaryKeys = $primaryKeys;
        $this->autoIncrementKeys = $autoIncrementKeys;
        $this->defaultValue = $defaultValue;
        $this->notNullColumns = $notNullColumns;
    }

    
    
    /**
     * Magic method to debug object
     *
     * @return string
     */
    public function __toString()
    {
        // create new object because all properties are private
        $stdClass = new stdClass;
        $stdClass->tableName = $this->tableName;
        $stdClass->columns = $this->columns;
        $stdClass->joinColumns = $this->joinColumns;
        $stdClass->primaryKeys = $this->primaryKeys;
        $stdClass->autoIncrementKeys = $this->autoIncrementKeys;
        $stdClass->defaultValue = $this->defaultValue;
        $stdClass->notNullColumns = $this->notNullColumns;
        return json_encode($stdClass);
    }

    /**
     * Unique column
     *
     * @return self
     */
    public function uniqueColumns()
    {
        $tmp = array();
        $test = array();
        foreach($this->columns as $elem)
        {
            if(!in_array($elem[self::NAME], $test))
            {
                $tmp[] = $elem;
                $test[] = $elem[self::NAME];
            }
        }
        $this->columns = $tmp;
        return $this;
    }

    /**
     * Unique join column
     *
     * @return self
     */
    public function uniqueJoinColumns()
    {
        $tmp = array();
        $test = array();
        foreach($this->joinColumns as $elem)
        {
            if(!in_array($elem[self::NAME], $test))
            {
                $tmp[] = $elem;
                $test[] = $elem[self::NAME];
            }
        }
        $this->joinColumns = $tmp;
        return $this;
    }

    /**
     * Unique primary key
     *
     * @return self
     */
    public function uniquePrimaryKeys()
    {
        $tmp = array();
        $test = array();
        foreach($this->primaryKeys as $elem)
        {
            if(!in_array($elem[self::NAME], $test))
            {
                $tmp[] = $elem;
                $test[] = $elem[self::NAME];
            }
        }
        $this->primaryKeys = $tmp;
        return $this;
    }

    /**
     * Unique auto increment
     *
     * @return self
     */
    public function uniqueAutoIncrementKeys()
    {
        $tmp = array();
        $test = array();
        foreach($this->autoIncrementKeys as $elem)
        {
            if(!in_array($elem[self::NAME], $test))
            {
                $tmp[] = $elem;
                $test[] = $elem[self::NAME];
            }
        }
        $this->autoIncrementKeys = $tmp;
        return $this;
    }

    /**
     * Unique default value
     *
     * @return self
     */
    public function uniqueDefaultValue()
    {
        $tmp = array();
        $test = array();
        foreach($this->defaultValue as $elem)
        {
            if(!in_array($elem[self::NAME], $test))
            {
                $tmp[] = $elem;
                $test[] = $elem[self::NAME];
            }
        }
        $this->defaultValue = $tmp;
        return $this;
    }

    /**
     * Unique not null column
     *
     * @return self
     */
    public function uniqueNotNullColumns()
    {
        $tmp = array();
        $test = array();
        foreach($this->notNullColumns as $elem)
        {
            if(!in_array($elem[self::NAME], $test))
            {
                $tmp[] = $elem;
                $test[] = $elem[self::NAME];
            }
        }
        $this->notNullColumns = $tmp;
        return $this;
    }


    /**
     * Get table name
     *
     * @return string
     */ 
    public function getTableName()
    {
        return $this->tableName;
    }

    /**
     * Set table name
     *
     * @param string $tableName  Table name
     *
     * @return self
     */ 
    public function setTableName($tableName)
    {
        $this->tableName = $tableName;

        return $this;
    }

    /**
     * Get columns
     *
     * @return array
     */ 
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * Set columns
     *
     * @param array $columns  Columns
     *
     * @return self
     */ 
    public function setColumns($columns)
    {
        $this->columns = $columns;

        return $this;
    }

    /**
     * Get join columns
     *
     * @return array
     */ 
    public function getJoinColumns()
    {
        return $this->joinColumns;
    }

    /**
     * Set join columns
     *
     * @param array $joinColumns  Join columns
     *
     * @return self
     */ 
    public function setJoinColumns($joinColumns)
    {
        $this->joinColumns = $joinColumns;

        return $this;
    }

    /**
     * Get primary keys
     *
     * @return array
     */ 
    public function getPrimaryKeys()
    {
        return $this->primaryKeys;
    }

    /**
     * Set primary keys
     *
     * @param array $primaryKeys  Primary keys
     *
     * @return self
     */ 
    public function setPrimaryKeys($primaryKeys)
    {
        $this->primaryKeys = $primaryKeys;

        return $this;
    }

    /**
     * Get auto increment keys
     *
     * @return array
     */ 
    public function getAutoIncrementKeys()
    {
        return $this->autoIncrementKeys;
    }

    /**
     * Set auto increment keys
     *
     * @param array $autoIncrementKeys  Auto increment keys
     *
     * @return self
     */ 
    public function setAutoIncrementKeys($autoIncrementKeys)
    {
        $this->autoIncrementKeys = $autoIncrementKeys;

        return $this;
    }

    /**
     * Get default value keys
     *
     * @return array
     */ 
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    /**
     * Set default value keys
     *
     * @param array $defaultValue  Default value keys
     *
     * @return self
     */ 
    public function setDefaultValue($defaultValue)
    {
        $this->defaultValue = $defaultValue;

        return $this;
    }

    /**
     * Get not null columns
     *
     * @return array
     */ 
    public function getNotNullColumns()
    {
        return $this->notNullColumns;
    }

    /**
     * Set not null columns
     *
     * @param array $notNullColumns  Not null columns
     *
     * @return self
     */ 
    public function setNotNullColumns($notNullColumns)
    {
        $this->notNullColumns = $notNullColumns;

        return $this;
    }
}