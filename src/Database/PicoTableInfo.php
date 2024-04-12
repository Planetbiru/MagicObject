<?php

namespace MagicObject\Database;

class PicoTableInfo
{
    /**
     * Table name
     *
     * @var string
     */
    public $tableName = "";

    /**
     * Columns
     *
     * @var array
     */
    public $columns = array();

    /**
     * Join columns
     *
     * @var array
     */
    public $joinColumns = array();

    /**
     * Primary keys
     *
     * @var array
     */
    public $primaryKeys = array();

    /**
     * Auto increment keys
     *
     * @var array
     */
    public $autoIncrementKeys = array();

    /**
     * Default value keys
     *
     * @var array
     */
    public $defaultValue = array();

    /**
     * Not null columns
     *
     * @var array
     */
    public $notNullColumns = array();

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
}