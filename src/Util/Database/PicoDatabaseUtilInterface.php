<?php

namespace MagicObject\Util\Database;

interface PicoDatabaseUtilInterface
{
    public function getColumnList($database, $picoTableName);
    public function getAutoIncrementKey($tableInfo);
    public function dumpStructure($tableInfo, $picoTableName, $createIfNotExists = false, $dropIfExists = false, $engine = 'InnoDB', $charset = 'utf8mb4');
    public function createColumn($column);
    public function fixDefaultValue($defaultValue, $type);
    public function dumpData($columns, $picoTableName, $data, $maxRecord = 100, $callbackFunction = null);
    public function dumpRecords($columns, $picoTableName, $data);
    public function dumpRecord($columns, $picoTableName, $record);
    public function showColumns($database, $tableName);
    public function autoConfigureImportData($config);
    public function updateConfigTable($databaseSource, $databaseTarget, $tables, $sourceTables, $target, $existingTables);
    public function createMapTemplate($databaseSource, $databaseTarget, $target);
    public function importData($config, $callbackFunction);
    public function isNotEmpty($array);
    public function importDataTable($databaseSource, $databaseTarget, $tableNameSource, $tableNameTarget, $tableInfo, $maxRecord, $callbackFunction);
    public function getMaxRecord($tableInfo, $maxRecord);
    public function processDataMapping($data, $columns, $maps = null);
    public function fixImportData($data, $columns);
    public function fixData($value);
    public function fixBooleanData($data, $name, $value);
    public function fixIntegerData($data, $name, $value);
    public function fixFloatData($data, $name, $value);
    public function insert($tableName, $data);
}