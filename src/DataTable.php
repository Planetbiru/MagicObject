<?php

namespace MagicObject;

use DOMDocument;
use MagicObject\Util\PicoAnnotationParser;
use MagicObject\Util\StringUtil;
use MagicObject\Util\TableUtil;
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
    
    const KEY_PROPERTY_TYPE = "property_type";
    const KEY_PROPERTY_NAME = "property_name";
    
    const KEY_NAME = "name";
    const KEY_VALUE = "value";
    const SQL_DATE_TIME_FORMAT = "Y-m-d H:i:s";
    const DATE_TIME_FORMAT = "datetimeformat";
    
    private $attributes = array();
    private $classList = array();
    
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
     * Magic method to string
     *
     * @return string
     */
    public function __toString()
    {
        $className = get_class($this);
        
        $obj = clone $this;
        $data = $obj->value($this->isSnake());
        
        $doc = new DOMDocument();
        $table = $doc->appendChild($doc->createElement('table'));

        TableUtil::setAttributes($table, $this->attributes);
        TableUtil::setClassList($table, $this->classList);
        TableUtil::setIdentity($table, $this->tableIdentity);
       
        $tbody = $table->appendChild($doc->createElement('tbody'));
        $doc->formatOutput = true;
        
        foreach($data as $key=>$value)
        {

            
            $label = $key;
            $tr = $tbody->appendChild($doc->createElement('tr'));
            
            $reflexProp = new PicoAnnotationParser($className, $key, PicoAnnotationParser::PROPERTY);
            if($reflexProp != null)
            {
                $parameters = $reflexProp->getParametersAsObject();
                if($parameters->issetLabel())
                {
                    $attrs = $reflexProp->parseKeyValueAsObject($parameters->getLabel());
                    $label = $attrs->issetContent() ? $attrs->getContent() : $label;
                }
            }

            $td1 = $tr->appendChild($doc->createElement('td'));
            $td2 = $tr->appendChild($doc->createElement('td'));
            
            $td1->setAttribute("class", "td-label");
            $td2->setAttribute("class", "td-value");
            
            $td1->textContent = $label;
            $td2->textContent = $value;
        }
        return $doc->saveHTML();
    }
    
    
    
}