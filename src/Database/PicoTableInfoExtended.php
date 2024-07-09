<?php

namespace MagicObject\Database;

class PicoTableInfoExtended extends PicoTableInfo
{
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
     * Merge list
     *
     * @param array $tmp
     * @param array $oldListCheck
     * @param array $newList
     * @return array
     */
    private function mergeList($tmp, $oldListCheck, $newList)
    {
        $prevColumName = "";
        $listToInsert = array();
        foreach($newList as $elem)
        {
            if(!in_array($elem[self::NAME], $oldListCheck))
            {
                $listToInsert[] = array('element'=>$elem, 'prevColumnName'=>$prevColumName);
            }
            $prevColumName = $elem[self::NAME];
        }
        foreach($listToInsert as $toInsert)
        {
            if(empty($prevColumName))
            {
                // insert to the end of table
                $tmp[] = $toInsert['element'];
            }
            else
            {
                $tmp2 = array();
                foreach($tmp as $elem2)
                {
                    $tmp2[] = $elem;
                    if($elem2[self::NAME] == $toInsert['prevColumnName'])
                    {
                        // insert after prevColumnName
                        $tmp2[] = $toInsert;
                    }
                }
                // update temporary list
                $tmp = $tmp2;
            }
        }
        return $tmp;
    }
    
    /**
     * Get oldlist check
     *
     * @param array $oldList
     * @return array
     */
    private function getOldListCheck($oldList)
    {
        $oldListCheck = array();
        foreach($oldList as $elem)
        {
            $oldListCheck[] = $elem[self::NAME];
        }
        return $oldListCheck;
    }
    
    /**
     * Unique column
     *
     * @param array $newList
     * @return self
     */
    public function mergeColumns($newList)
    {
        $tmp = $this->columns;
        $oldListCheck = $this->getOldListCheck($this->columns);
        $tmp = $this->mergeList($tmp, $oldListCheck, $newList);
        $this->columns = $tmp;
        return $this;
    }

    /**
     * Unique join column
     *
     * @param array $newList
     * @return self
     */
    public function mergeJoinColumns($newList)
    {
        $tmp = $this->joinColumns;
        $oldListCheck = $this->getOldListCheck($this->joinColumns);
        $tmp = $this->mergeList($tmp, $oldListCheck, $newList);
        $this->joinColumns = $tmp;
        return $this;
    }

    /**
     * Unique primary key
     *
     * @param array $newList
     * @return self
     */
    public function mergePrimaryKeys($newList)
    {
        $tmp = $this->primaryKeys;
        $oldListCheck = $this->getOldListCheck($this->primaryKeys);
        $tmp = $this->mergeList($tmp, $oldListCheck, $newList);
        $this->primaryKeys = $tmp;
        return $this;
    }

    /**
     * Unique auto increment
     *
     * @param array $newList
     * @return self
     */
    public function mergeAutoIncrementKeys($newList)
    {
        $tmp = $this->autoIncrementKeys;
        $oldListCheck = $this->getOldListCheck($this->autoIncrementKeys);
        $tmp = $this->mergeList($tmp, $oldListCheck, $newList);
        $this->autoIncrementKeys = $tmp;
        return $this;
    }

    /**
     * Unique default value
     *
     * @param array $newList
     * @return self
     */
    public function mergeDefaultValue($newList)
    {
        $tmp = $this->defaultValue;
        $oldListCheck = $this->getOldListCheck($this->defaultValue);
        $tmp = $this->mergeList($tmp, $oldListCheck, $newList);
        $this->defaultValue = $tmp;
        return $this;
    }

    /**
     * Unique not null column
     *
     * @param array $newList
     * @return self
     */
    public function mergeNotNullColumns($newList)
    {
        $tmp = $this->notNullColumns;
        $oldListCheck = $this->getOldListCheck($this->notNullColumns);
        $prevColumName = "";
        $listToInsert = array();
        foreach($newList as $elem)
        {
            if(!in_array($elem[self::NAME], $oldListCheck))
            {
                $listToInsert[] = array("element"=>$elem, 'prevColumnName'=>$prevColumName);
            }
            $prevColumName = $elem[self::NAME];
        }
        $this->notNullColumns = $tmp;
        return $this;
    }


    
}