<?php

namespace MagicObject;

use DOMDocument;
use MagicObject\Util\PicoAnnotationParser;
use MagicObject\Util\StringUtil;
use stdClass;

class DataTable extends SetterGetter
{
    
    const ANNOTATION_ATTRIBUTES = "Attributes";
    const CLASS_LIST = "ClassList";
    const ANNOTATION_ID = "@Id";
    const ANNOTATION_COLUMN = "@Column";
    const ANNOTATION_VAR = "@var";
    const ANNOTATION_GENERATED_VALUE = "@GeneratedValue";
    
    const ANNOTATION_NOT_NULL = "@NotNull";
    const ANNOTATION_DEFAULT_COLUMN = "@DefaultColumn";
    
    const KEY_PROPERTY_TYPE = "property_type";
    const KEY_PROPERTY_NAME = "property_name";
    
    const KEY_NAME = "name";
    
    private $className = "";
    
    /**
     * Constructor
     *
     * @param MagicObject|self|stdClass|array $data
     */
    public function __construct($data = null)
    {
        if(isset($data))
        {
            $this->loadData($data);
        }
    }
    
    /**
     * Load data to object
     * @param mixed $data
     * @return self
     */
    public function loadData($data)
    {
        if($data != null)
        {
            if($data instanceof MagicObject)
            {
                $values = $data->value();
                foreach ($values as $key => $value) {
                    $key2 = StringUtil::camelize($key);
                    $this->set($key2, $value, true);
                }
            }
            else if (is_array($data) || is_object($data)) {
                foreach ($data as $key => $value) {
                    $key2 = StringUtil::camelize($key);
                    $this->set($key2, $value, true);
                }
            }
        }
        return $this;
    }
    
    public function __toString()
    {
        $className = get_class($this);
        $reflexClass = new PicoAnnotationParser($className);
        $attributes = $reflexClass->parseKeyValue($reflexClass->getParameter(self::ANNOTATION_ATTRIBUTES));
        $classList = $reflexClass->parseKeyValue($reflexClass->getParameter(self::CLASS_LIST));
        
        
        
        
        $obj = clone $this;
        $data = $obj->value($this->isSnake());
        
        
        
        $doc = new DOMDocument();
        $table = $doc->appendChild($doc->createElement('table'));
        $table->setAttribute('class', implode(' ', $classList));
        $tbody = $table->appendChild($doc->createElement('tbody'));
        


        
        $doc->formatOutput = true;
      
        
        
        foreach($data as $key=>$value)
        {
            $tr = $tbody->appendChild($doc->createElement('tr'));
            $td1 = $tr->appendChild($doc->createElement('td'));
            $td2 = $tr->appendChild($doc->createElement('td'));
            
            $td1->setAttribute("class", "td-label");
            $td2->setAttribute("class", "td-label");
            
            $td1->textContent = $key;
            $td2->textContent = $value;
        }
        return $doc->saveHTML();
    }
    
    public function getTableInfo() // NOSONAR
    {
        $reflexClass = new PicoAnnotationParser($this->className);
        $attributesAnnotation = $reflexClass->getParameter(self::ANNOTATION_ATTRIBUTES);

        $attributes = $reflexClass->parseKeyValue($attributesAnnotation);
        
        

        $columns = array();
        $joinColumns = array();
        $primaryKeys = array();
        $autoIncrementKeys = array();
        $notNullColumns = array();
        $props = $reflexClass->getProperties();
        $defaultValue = array();

        // iterate each properties of the class
        foreach($props as $prop)
        {
            $reflexProp = new PicoAnnotationParser($this->className, $prop->name, PicoAnnotationParser::PROPERTY);
            $parameters = $reflexProp->getParameters();

            // get column name of each parameters
            foreach($parameters as $param=>$val)
            {
                if(strcasecmp($param, self::ANNOTATION_COLUMN) == 0)
                {
                    $values = $reflexProp->parseKeyValue($val);
                    if(!empty($values))
                    {
                        $columns[$prop->name] = $values;
                    }
                }
            }
            // set column type
            foreach($parameters as $param=>$val)
            {
                if(strcasecmp($param, self::ANNOTATION_VAR) == 0 && isset($columns[$prop->name]))
                {
                    $type = explode(' ', trim($val, " \r\n\t "))[0];
                    $columns[$prop->name][self::KEY_PROPERTY_TYPE] = $type;
                }
                if(strcasecmp($param, self::SQL_DATE_TIME_FORMAT) == 0)
                {
                    $values = $reflexProp->parseKeyValue($val);
                    if(isset($values['pattern']))
                    {
                        $columns[$prop->name][self::DATE_TIME_FORMAT] = $values['pattern'];
                    }
                }
            }
               

            // list primary key
            foreach($parameters as $param=>$val)
            {
                if(strcasecmp($param, self::ANNOTATION_ID) == 0 && isset($columns[$prop->name]))
                {
                    $primaryKeys[$prop->name] = array(self::KEY_NAME=>$columns[$prop->name][self::KEY_NAME]);
                }
            }

            // list autogenerated column
            foreach($parameters as $param=>$val)
            {
                if(strcasecmp($param, self::ANNOTATION_GENERATED_VALUE) == 0 && isset($columns[$prop->name]))
                {
                    $vals = $reflexClass->parseKeyValue($val);
                    $autoIncrementKeys[$prop->name] = array(
                        self::KEY_NAME=>isset($columns[$prop->name][self::KEY_NAME])?$columns[$prop->name][self::KEY_NAME]:null,
                        self::KEY_STRATEGY=>isset($vals[self::KEY_STRATEGY])?$vals[self::KEY_STRATEGY]:null,
                        self::KEY_GENERATOR=>isset($vals[self::KEY_GENERATOR])?$vals[self::KEY_GENERATOR]:null
                    );
                }
            }
            
            // define default column value
            foreach($parameters as $param=>$val)
            {
                if(strcasecmp($param, self::ANNOTATION_DEFAULT_COLUMN) == 0)
                {
                    $vals = $reflexClass->parseKeyValue($val);
                    if(isset($vals[self::KEY_VALUE]))
                    {
                        $defaultValue[$prop->name] = array(
                            self::KEY_NAME=>isset($columns[$prop->name][self::KEY_NAME])?$columns[$prop->name][self::KEY_NAME]:null,
                            self::KEY_VALUE=>$vals[self::KEY_VALUE],
                            self::KEY_PROPERTY_TYPE=>$columns[$prop->name][self::KEY_PROPERTY_TYPE]
                        );
                    }
                }
            }

            // list not null column
            foreach($parameters as $param=>$val)
            {
                if(strcasecmp($param, self::ANNOTATION_NOT_NULL) == 0 && isset($columns[$prop->name]))
                {
                    $notNullColumns[$prop->name] = array(self::KEY_NAME=>$columns[$prop->name][self::KEY_NAME]);
                }
            }
        }
        // bring it together
        $info = new stdClass;
        $info->tableName = $picoTableName;
        $info->columns = $columns;
        $info->joinColumns = $joinColumns;
        $info->primaryKeys = $primaryKeys;
        $info->autoIncrementKeys = $autoIncrementKeys;
        $info->defaultValue = $defaultValue;
        $info->notNullColumns = $notNullColumns;
        return $info;
    }
    
}