<?php

namespace MagicObject;

use DOMDocument;
use MagicObject\Util\PicoAnnotationParser;
use MagicObject\Util\StringUtil;
use MagicObject\Util\TableUtil;
use ReflectionClass;
use stdClass;

class DataTable extends SetterGetter
{
    const ANNOTATION_TABLE = "Table";
    const ANNOTATION_ATTRIBUTES = "Attributes";
    const CLASS_LIST = "ClassList";
    const ANNOTATION_ID = "Id";
    const ANNOTATION_COLUMN = "Column";
    const ANNOTATION_VAR = "var";
    const ANNOTATION_GENERATED_VALUE = "GeneratedValue";
    const ANNOTATION_NOT_NULL = "NotNull";
    const ANNOTATION_DEFAULT_COLUMN = "DefaultColumn";
    const ANNOTATION_DEFAULT_COLUMN_LABEL = "DefaultColumnLabel";
    const KEY_PROPERTY_TYPE = "property_type";
    const KEY_PROPERTY_NAME = "property_name";
    
    const KEY_NAME = "name";
    const KEY_CLASS = "class";
    const KEY_VALUE = "value";
    const SQL_DATE_TIME_FORMAT = "Y-m-d H:i:s";
    const DATE_TIME_FORMAT = "datetimeformat";
    
    const TAG_TABLE = "table";
    const TAG_THEAD = "thead";
    const TAG_TBODY = "tbody";
    const TAG_TR = "tr";
    const TAG_TH = "th";
    const TAG_TD = "td";
    
    const TD_LABEL = "td-label";
    const TD_VALUE = "td-value";
    
    private $attributes = array();
    private $classList = array();
    private $defaultColumnName = "key";
    
    /**
     * Table identity
     *
     * @var ParameterObject
     */
    private $tableIdentity;
      
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
        $this->init();
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
                    $this->set($key2, $value);
                }
            }
            else if (is_array($data) || is_object($data)) {
                foreach ($data as $key => $value) {
                    $key2 = StringUtil::camelize($key);
                    $this->set($key2, $value);
                }
            }
        }
        return $this;
    }
    
    private function init()
    {
        $className = get_class($this);
        $reflexClass = new PicoAnnotationParser($className);
        $this->attributes = TableUtil::parseElementAttributes($reflexClass->getParameter(self::ANNOTATION_ATTRIBUTES));    
        $classList = $reflexClass->parseKeyValueAsObject($reflexClass->getParameter(self::CLASS_LIST));
        $defaultColumnName = $reflexClass->parseKeyValueAsObject($reflexClass->getParameter(self::ANNOTATION_DEFAULT_COLUMN_LABEL));
        if($defaultColumnName->issetContent())
        {
            $this->defaultColumnName = $defaultColumnName->getContent();
        }    
        if($classList->issetContent())
        {
            $this->classList = explode(" ", preg_replace('/\s+/', ' ', $classList->getContent()));
        }
        $this->tableIdentity = $reflexClass->parseKeyValueAsObject($reflexClass->getParameter(self::ANNOTATION_TABLE));
    }
    
    /**
     * Add class to table
     *
     * @param string $className
     * @return self
     */
    public function addClass($className)
    {
        if(TableUtil::isValidClassName($className))
        {
            $this->classList[] = $className;
        }
        return $this;
    }
    
    /**
     * Remove class from table
     *
     * @param string $className
     * @return self
     */
    public function removeClass($className)
    {
        if(TableUtil::isValidClassName($className))
        {
            $tmp = array();
            foreach($this->classList as $cls)
            {
                if($cls != $className)
                {
                    $tmp[] = $cls;
                }
            }
            $this->classList = $tmp;
        }
        return $this;
    }
    
    /**
     * Replace class of the table
     *
     * @param string $search
     * @param string $replace
     * @return self
     */
    public function replaceClass($search, $replace)
    {
        $this->removeClass($search);
        $this->addClass($replace);
        return $this;
    }
    
    /**
     * Property list
     * @var bool $reflectSelf
     * @var bool $asArrayProps
     * @return array
     */
    protected function propertyList($reflectSelf = false, $asArrayProps = false)
    {
        $reflectionClass = $reflectSelf ? self::class : get_called_class();
        $class = new ReflectionClass($reflectionClass);

        // filter only the calling class properties
        // skip parent properties
        $properties = array_filter(
            $class->getProperties(),
            function($property) use($class) {
                return $property->getDeclaringClass()->getName() == $class->getName();
            }
        );
        if($asArrayProps)
        {
            $result = array();
            $index = 0;
            foreach ($properties as $key) {
                $prop = $key->name;
                $result[$index] = $prop;
                
                $index++;
            }
            return $result;
        }
        else
        {
            return $properties;
        }
    }
    
    /**
     * Define label
     *
     * @param PicoAnnotationParser $reflexProp
     * @param ParameterObject $parameters
     * @param string $defaultLabel
     * @return void
     */
    private function label($reflexProp, $parameters, $defaultLabel)
    {
        $label = $defaultLabel;
        if(stripos($this->defaultColumnName, "->"))
        {
            $cn = explode("->", $this->defaultColumnName);
            if($parameters->get(trim($cn[0])) != null)
            {
                $attrs = $reflexProp->parseKeyValueAsObject($parameters->get(trim($cn[0])));
                if($attrs->get(trim($cn[1])) != null)
                {
                    $label = $attrs->get(trim($cn[1]));
                }
            }
        }
        return $label;
    }
    
    /**
     * Magic method to string
     *
     * @return string
     */
    public function __toString()
    {
        $className = get_class($this);
        $doc = new DOMDocument();
        $table = $doc->appendChild($doc->createElement(self::TAG_TABLE));

        TableUtil::setAttributes($table, $this->attributes);
        TableUtil::setClassList($table, $this->classList);
        TableUtil::setIdentity($table, $this->tableIdentity);
       
        $tbody = $table->appendChild($doc->createElement(self::TAG_TBODY));
        $doc->formatOutput = true;
        
        $props = $this->propertyList();
        
        foreach($props as $prop)
        {
            $key = $prop->name;
            $label = $key;
            $value = $this->get($key);
            $tr = $tbody->appendChild($doc->createElement(self::TAG_TR));
            
            $reflexProp = new PicoAnnotationParser($className, $key, PicoAnnotationParser::PROPERTY);
            
            if($reflexProp != null)
            {
                $parameters = $reflexProp->getParametersAsObject();
                if($parameters->issetLabel())
                {
                    $label = $this->label($reflexProp, $parameters, $label);
                }
            }

            $td1 = $tr->appendChild($doc->createElement(self::TAG_TD));
            $td1->setAttribute(self::KEY_CLASS, self::TD_LABEL);
            $td1->textContent = $label;
            
            $td2 = $tr->appendChild($doc->createElement(self::TAG_TD));         
            $td2->setAttribute(self::KEY_CLASS, self::TD_VALUE);
            $td2->textContent = isset($value) ? $value : "";
        }
        return $doc->saveHTML();
    }
    
    
    
}