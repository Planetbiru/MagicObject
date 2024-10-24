<?php

namespace MagicObject;

use MagicObject\Database\PicoDatabase;
use MagicObject\Database\PicoDatabaseEntity;
use MagicObject\Database\PicoDatabasePersistence;
use MagicObject\Util\ClassUtil\PicoAnnotationParser;
use MagicObject\Util\PicoGenericObject;
use MagicObject\Util\PicoStringUtil;
use ReflectionClass;
use ReflectionProperty;
use stdClass;

/**
 * Class MagicDto
 *
 * Represents a dynamic data transfer object that allows the user to create and manipulate 
 * properties on-the-fly. It can handle various data sources including INI, YAML, JSON, and 
 * databases. Users can perform CRUD operations on database records and manipulate properties 
 * as needed.
 *
 * @author Kamshory
 * @package MagicObject
 * @link https://github.com/Planetbiru/MagicObject
 */
class MagicDto extends stdClass // NOSONAR
{
    // Message constants
    const MESSAGE_NO_DATABASE_CONNECTION = "No database connection provided";
    const MESSAGE_NO_RECORD_FOUND = "No record found";

    // Property naming strategy
    const PROPERTY_NAMING_STRATEGY = "property-naming-strategy";

    // Key constants
    const KEY_PROPERTY_TYPE = "propertyType";
    const KEY_DEFAULT_VALUE = "default_value";
    const KEY_NAME = "name";
    const KEY_VALUE = "value";

    // Format constants
    const JSON = 'JSON';

    /**
     * Indicates whether the object is read-only.
     *
     * @var bool
     */
    private $_readonly = false; // NOSONAR

    /**
     * Database connection instance.
     *
     * @var PicoDatabase
     */
    private $_database; // NOSONAR

    /**
     * Class containing a database entity.
     *
     * @var PicoDatabaseEntity|null
     */
    private $_databaseEntity; // NOSONAR

    /**
     * Class parameters.
     *
     * @var array
     */
    private $_classParams = array(); // NOSONAR

    /**
     * List of null properties.
     *
     * @var array
     */
    private $_nullProperties = array(); // NOSONAR

    /**
     * Property labels.
     *
     * @var array
     */
    private $_label = array(); // NOSONAR

    /**
     * Database persistence instance.
     *
     * @var PicoDatabasePersistence|null
     */
    private $_persistProp = null; // NOSONAR

    /**
     * Data source.
     *
     * @var mixed
     */
    private $dataSource = null;

    /**
     * Constructor.
     *
     * Initializes the object with provided data and database connection.
     *
     * @param self|array|stdClass|object|null $data Initial data to populate the object.
     */
    public function __construct($data = null)
    {
        $this->dataSource = $data;
    }
    
    /**
     * Retrieves the list of null properties.
     *
     * @return array The list of properties that are currently null.
     */
    public function nullPropertyList()
    {
        return $this->_nullProperties;
    }

    /**
     * Loads data into the object.
     *
     * @param mixed $data Data to load, which can be another MagicObject, an array, or an object.
     * @return self Returns the current instance for method chaining.
     */
    public function loadData($data)
    {
        $this->dataSource = $data;
        return $this;
    }

    /**
     * Set the read-only state of the object.
     *
     * When set to read-only, setters will not change the value of its properties,
     * but loadData will still function normally.
     *
     * @param bool $readonly Flag to set the object as read-only
     * @return self Returns the instance of the current object for method chaining.
     */
    protected function readOnly($readonly)
    {
        $this->_readonly = $readonly;
        return $this;
    }

    /**
     * Remove properties except for the specified ones.
     *
     * @param object|array $sourceData Data to filter
     * @param array $propertyNames Names of properties to retain
     * @return object|array Filtered data
     */
    public function removePropertyExcept($sourceData, $propertyNames)
    {
        if(is_object($sourceData))
        {
            // iterate
            $resultData = new stdClass;
            foreach($sourceData as $key=>$val)
            {
                if(in_array($key, $propertyNames))
                {
                    $resultData->$key = $val;
                }
            }
            return $resultData;
        }
        if(is_array($sourceData))
        {
            // iterate
            $resultData = array();
            foreach($sourceData as $key=>$val)
            {
                if(in_array($key, $propertyNames))
                {
                    $resultData[$key] = $val;
                }
            }
            return $resultData;
        }
        return new stdClass;
    }

    /**
     * Modify null properties.
     *
     * @param string $propertyName Property name
     * @param mixed $propertyValue Property value
     * @return void
     */
    private function modifyNullProperties($propertyName, $propertyValue)
    {
        if($propertyValue === null && !isset($this->_nullProperties[$propertyName]))
        {
            $this->_nullProperties[$propertyName] = true;
        }
        if($propertyValue != null && isset($this->_nullProperties[$propertyName]))
        {
            unset($this->_nullProperties[$propertyName]);
        }
    }

    /**
     * Set property value.
     *
     * @param string $propertyName Property name
     * @param mixed|null $propertyValue Property value
     * @param bool $skipModifyNullProperties Skip modifying null properties
     * @return self Returns the instance of the current object for method chaining.
     */
    public function set($propertyName, $propertyValue, $skipModifyNullProperties = false)
    {
        $var = PicoStringUtil::camelize($propertyName);
        $this->{$var} = $propertyValue;
        if(!$skipModifyNullProperties && $propertyValue === null)
        {
            $this->modifyNullProperties($var, $propertyValue);
        }
        return $this;
    }

    /**
     * Adds an element to the end of an array property.
     *
     * @param string $propertyName Property name
     * @param mixed $propertyValue Property value
     * @return self Returns the instance of the current object for method chaining.
     */
    public function push($propertyName, $propertyValue)
    {
        $var = PicoStringUtil::camelize($propertyName);
        if(!isset($this->$var))
        {
            $this->$var = array();
        }
        array_push($this->$var, $propertyValue);
        return $this;
    }
    
    /**
     * Adds an element to the end of an array property (alias for push).
     *
     * @param string $propertyName Property name
     * @param mixed $propertyValue Property value
     * @return self Returns the instance of the current object for method chaining.
     */
    public function append($propertyName, $propertyValue)
    {
        return $this->push($propertyName, $propertyValue);
    }
    
    /**
     * Adds an element to the beginning of an array property.
     *
     * @param string $propertyName Property name
     * @param mixed $propertyValue Property value
     * @return self Returns the instance of the current object for method chaining.
     */
    public function unshift($propertyName, $propertyValue)
    {
        $var = PicoStringUtil::camelize($propertyName);
        if(!isset($this->$var))
        {
            $this->$var = array();
        }
        array_unshift($this->$var, $propertyValue);
        return $this;
    }
    
    /**
     * Adds an element to the beginning of an array property (alias for unshift).
     *
     * @param string $propertyName Property name
     * @param mixed $propertyValue Property value
     * @return self Returns the instance of the current object for method chaining.
     */
    public function prepend($propertyName, $propertyValue)
    {
        return $this->unshift($propertyName, $propertyValue);
    }

    /**
     * Remove the last element of an array property and return it.
     *
     * @param string $propertyName Property name
     * @return mixed
     */
    public function pop($propertyName)
    {
        $var = PicoStringUtil::camelize($propertyName);
        if(isset($this->$var) && is_array($this->$var))
        {
            return array_pop($this->$var);
        }
        return null;
    }
    
    /**
     * Remove the first element of an array property and return it.
     *
     * @param string $propertyName Property name
     * @return mixed
     */
    public function shift($propertyName)
    {
        $var = PicoStringUtil::camelize($propertyName);
        if(isset($this->$var) && is_array($this->$var))
        {
            return array_shift($this->$var);
        }
        return null;
    }

    /**
     * Get property value.
     *
     * @param string $propertyName Property name
     * @return mixed|null
     */
    public function get($propertyName)
    {
        $var = PicoStringUtil::camelize($propertyName);
        return isset($this->$var) ? $this->$var : null;
    }

    /**
     * Get property value or a default value if not set.
     *
     * @param string $propertyName Property name
     * @param mixed|null $defaultValue Default value
     * @return mixed|null
     */
    public function getOrDefault($propertyName, $defaultValue = null)
    {
        $var = PicoStringUtil::camelize($propertyName);
        return isset($this->$var) ? $this->$var : $defaultValue;
    }

    /**
     * Set property value (magic setter).
     *
     * @param string $propertyName Property name
     * @param mixed $propertyValue Property value
     */
    public function __set($propertyName, $propertyValue)
    {
        return $this->set($propertyName, $propertyValue);
    }

    /**
     * Get property value (magic getter).
     *
     * @param string $propertyName Property name
     * @return mixed|null
     */
    public function __get($propertyName)
    {
        $propertyName = lcfirst($propertyName);
        if($this->__isset($propertyName))
        {
            return $this->get($propertyName);
        }
    }

    /**
     * Check if a property has been set or not (including null).
     *
     * @param string $propertyName Property name
     * @return bool
     */
    public function __isset($propertyName)
    {
        $propertyName = lcfirst($propertyName);
        return isset($this->$propertyName);
    }

    /**
     * Unset property value.
     *
     * @param string $propertyName Property name
     * @return void
     */
    public function __unset($propertyName)
    {
        $propertyName = lcfirst($propertyName);
        unset($this->$propertyName);
    }

    /**
     * Copy values from another object.
     *
     * @param self|mixed $source Source data
     * @param array|null $filter Filter
     * @param bool $includeNull Flag to include null values
     * @return void
     */
    public function copyValueFrom($source, $filter = null, $includeNull = false)
    {
        if($filter != null)
        {
            $tmp = array();
            $index = 0;
            foreach($filter as $val)
            {
                $tmp[$index] = trim(PicoStringUtil::camelize($val));
                $index++;
            }
            $filter = $tmp;
        }
        $values = $source->value();
        foreach($values as $property=>$value)
        {
            if(
                ($filter == null || (is_array($filter) && !empty($filter) && in_array($property, $filter)))
                &&
                ($includeNull || $value != null)
                )
            {
                $this->set($property, $value);
            }
        }
    }

    /**
     * Remove property value and set it to null.
     *
     * @param string $propertyName Property name
     * @param bool $skipModifyNullProperties Skip modifying null properties
     * @return self Returns the instance of the current object for method chaining.
     */
    private function removeValue($propertyName, $skipModifyNullProperties = false)
    {
        return $this->set($propertyName, null, $skipModifyNullProperties);
    }
    
    /**
     * Get the object values
     *
     * @return stdClass An object containing the values of the properties
     */
    public function value()
    {
        $parentProps = $this->propertyList(true, true);
        $returnValue = new stdClass;

        foreach ($this as $key => $val) {
            if (!in_array($key, $parentProps)) {
                $doc = $this->getPropertyDocComment($key);
                $source = $this->extractSource($doc);
                $jsonProperty = $this->extractJsonProperty($doc);
                $var = $this->extractVar($doc);
                $propertyName = $jsonProperty ? $jsonProperty : $key;

                $objectTest = class_exists($var) ? new $var() : null;

                if ($this->isSelfInstance($var, $objectTest)) {
                    $returnValue->$propertyName = $this->handleSelfInstance($source, $var, $propertyName);
                } elseif ($this->isMagicObjectInstance($objectTest)) {
                    $returnValue->$propertyName = $this->handleMagicObject($source, $propertyName);
                } else {
                    $returnValue->$propertyName = $this->handleDefaultCase($source, $key, $propertyName);
                }
            }
        }
        return $returnValue;
    }

    private function getPropertyDocComment($key)
    {
        $propReflect = new ReflectionProperty($this, $key);
        return $propReflect->getDocComment();
    }

    private function extractSource($doc)
    {
        preg_match('/@Source\("([^"]+)"\)/', $doc, $matches);
        return !empty($matches[1]) ? $matches[1] : null;
    }

    private function extractJsonProperty($doc)
    {
        preg_match('/@JsonProperty\("([^"]+)"\)/', $doc, $matches);
        return !empty($matches[1]) ? $matches[1] : null;
    }

    private function extractVar($doc)
    {
        preg_match('/@var\s+(\S+)/', $doc, $matches);
        return !empty($matches[1]) ? $matches[1] : null;
    }
    
    private function extractLabel($doc)
    {
        preg_match('/@Label\("([^"]+)"\)/', $doc, $matches);
        return !empty($matches[1]) ? $matches[1] : null;
    }

    private function isSelfInstance($var, $objectTest)
    {
        return strtolower($var) != 'stdclass' && $objectTest instanceof self;
    }

    private function handleSelfInstance($source, $var, $propertyName)
    {
        if (strpos($source, "->") === false) {
            $value = isset($source) ? $this->dataSource->get($source) : $this->dataSource->get($propertyName);
            $objectValid = new $var($value);
            return $objectValid->value();
        } else {
            return $this->getNestedValue($source);
        }
    }

    private function isMagicObjectInstance($objectTest)
    {
        return $objectTest instanceof MagicObject || 
            $objectTest instanceof SetterGetter || 
            $objectTest instanceof SecretObject || 
            $objectTest instanceof PicoGenericObject;
    }

    private function handleMagicObject($source, $propertyName)
    {
        if (strpos($source, "->") === false) {
            $value = isset($source) ? $this->dataSource->get($source) : $this->dataSource->get($propertyName);
            return ($value instanceof MagicObject || $value instanceof SetterGetter || 
                    $value instanceof SecretObject || $value instanceof PicoGenericObject) 
                ? $value->value() 
                : json_decode(json_encode($value));
        } else {
            return $this->getNestedValue($source);
        }
    }

    private function handleDefaultCase($source, $key, $propertyName)
    {
        if (strpos($source, "->") === false) {
            return isset($source) ? $this->dataSource->get($source) : $this->dataSource->get($key);
        } else {
            return $this->getNestedValue($source);
        }
    }

    private function getNestedValue($source)
    {
        $currentVal = null;
        $arr = explode("->", $source);
        $fullKey = $arr[0];
        $currentVal = $this->dataSource->get($fullKey);
        for ($i = 1; $i < count($arr); $i++) {
            if (isset($currentVal) && $currentVal->get($arr[$i]) != null) {
                $currentVal = $currentVal->get($arr[$i]);
            } else {
                break;
            }
        }
        return $currentVal;
    }

    /**
     * Get the object value as a specified format
     *
     * @return stdClass An object representing the value of the instance
     */
    public function valueObject()
    {
        $obj = clone $this;
        foreach($obj as $key=>$value)
        {
            if($value instanceof self)
            {
                $value = $this->stringifyObject($value);
                $obj->set($key, $value);
            }
        }

        return $obj->value();
        
    }

    /**
     * Get the object value as an associative array
     *
     * @param bool $snakeCase Flag indicating whether to convert property names to snake case
     * @return array An associative array representing the object values
     */
    public function valueArray($snakeCase = false)
    {
        $value = $this->value($snakeCase);
        return json_decode(json_encode($value), true);
    }

    /**
     * Get the object value as an associative array with the first letter of each key in upper camel case
     *
     * @return array An associative array with keys in upper camel case
     */
    public function valueArrayUpperCamel()
    {
        $obj = clone $this;
        $array = (array) $obj->value();
        $renameMap = array();
        $keys = array_keys($array);
        foreach($keys as $key)
        {
            $renameMap[$key] = ucfirst($key);
        }
        $array = array_combine(array_map(function($el) use ($renameMap) {
            return $renameMap[$el];
        }, array_keys($array)), array_values($array));
        return $array;
    }

    /**
     * Check if the JSON naming strategy is snake case
     *
     * @return bool True if the naming strategy is snake case; otherwise, false
     */
    protected function _snakeJson()
    {
        return isset($this->_classParams[self::JSON])
            && isset($this->_classParams[self::JSON][self::PROPERTY_NAMING_STRATEGY])
            && strcasecmp($this->_classParams[self::JSON][self::PROPERTY_NAMING_STRATEGY], 'SNAKE_CASE') == 0
            ;
    }


    /**
     * Check if the JSON output should be prettified
     *
     * @return bool True if JSON output is set to be prettified; otherwise, false
     */
    protected function _pretty()
    {
        return isset($this->_classParams[self::JSON])
            && isset($this->_classParams[self::JSON]['prettify'])
            && strcasecmp($this->_classParams[self::JSON]['prettify'], 'true') == 0
            ;
    }

    /**
     * Check if a value is not null and not empty
     *
     * @param mixed $value The value to check
     * @return bool True if the value is not null and not empty; otherwise, false
     */
    private function _notNullAndNotEmpty($value)
    {
        return $value != null && !empty($value);
    }

    /**
     * Get a list of properties
     *
     * @param bool $reflectSelf Flag indicating whether to reflect properties of the current class
     * @param bool $asArrayProps Flag indicating whether to return properties as an array
     * @return array An array of property names or ReflectionProperty objects
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
     * Convert the result to an array of objects.
     *
     * @param array $result The result set to convert.
     * @return array An array of objects.
     */
    private function toArrayObject($result) // NOSONAR
    {
        $instance = array();
        $index = 0;
        if(isset($result) && is_array($result))
        {
            foreach($result as $value)
            {
                $className = get_class($this);
                $obj = new $className($value);
                $instance[$index] = $obj;
                $index++;
            }
        }
        return $instance;
    }

    /**
     * Get the number of properties of the object.
     *
     * @return int The number of properties.
     */
    public function size()
    {
        $parentProps = $this->propertyList(true, true);
        $length = 0;
        foreach ($this as $key => $val) {
            if(!in_array($key, $parentProps))
            {
                $length++;
            }
        }
        return $length;
    }

    /**
     * Recursively stringify an object or array of objects.
     *
     * @param self $value The object to stringify.
     * @return mixed The stringified object or array.
     */
    private function stringifyObject($value)
    {
        if(is_array($value))
        {
            foreach($value as $key2=>$val2)
            {
                if($val2 instanceof self)
                {
                    $value[$key2] = $val2->stringifyObject($val2);
                }
            }
        }
        else if(is_object($value))
        {
            foreach($value as $key2=>$val2)
            {
                if($val2 instanceof self)
                {

                    $value->{$key2} = $val2->stringifyObject($val2);
                }
            }
        }
        return $value->value();
    }

    /**
     * Magic method to convert the object to a string.
     *
     * @return string A JSON representation of the object.
     */
    public function __toString()
    {
        $pretty = $this->_pretty();
        $flag = $pretty ? JSON_PRETTY_PRINT : 0;
        $obj = clone $this;
        foreach($obj as $key=>$value)
        {
            if($value instanceof self)
            {
                $value = $this->stringifyObject($value);
                $obj->set($key, $value);
            }
        }
        return json_encode($obj->value(), $flag);
    }

    /**
     * Handles dynamic method calls for property access and manipulation.
     *
     * This method allows you to call methods that follow specific naming conventions
     * to interact with the object's properties. It supports operations such as 
     * checking existence, getting, setting, unsetting, and manipulating array properties.
     *
     * Supported method patterns:
     * - isset<PropertyName>(): bool
     * - is<PropertyName>(): bool
     * - equals<PropertyName>($value): bool
     * - get<PropertyName>(): mixed
     * - set<PropertyName>($value): self
     * - unset<PropertyName>(): self
     * - push<PropertyName>($value): self
     * - append<PropertyName>($value): self
     * - unshift<PropertyName>($value): self
     * - prepend<PropertyName>($value): self
     * - pop<PropertyName>(): mixed
     * - shift<PropertyName>(): mixed
     *
     * @param string $method The name of the method being called.
     * @param array $params The parameters passed to the method.
     * @return mixed The result of the dynamic method call.
     * @throws InvalidArgumentException If the method name does not match any supported pattern.
     */
    public function __call($method, $params) // NOSONAR
    {
        if (strncasecmp($method, "isset", 5) === 0) {
            $var = lcfirst(substr($method, 5));
            return isset($this->$var);
        }
        else if (strncasecmp($method, "is", 2) === 0) {
            $var = lcfirst(substr($method, 2));
            return isset($this->$var) ? $this->$var == 1 : false;
        }
        else if (strncasecmp($method, "equals", 6) === 0) {
            $var = lcfirst(substr($method, 6));
            return isset($this->$var) && $this->$var == $params[0];
        }
        else if (strncasecmp($method, "get", 3) === 0) {
            $var = lcfirst(substr($method, 3));
            return isset($this->$var) ? $this->$var : null;
        }
        else if (strncasecmp($method, "set", 3) === 0 && isset($params) && isset($params[0]) && !$this->_readonly) {
            $var = lcfirst(substr($method, 3));
            $this->$var = $params[0];
            $this->modifyNullProperties($var, $params[0]);
            return $this;
        }
        else if (strncasecmp($method, "unset", 5) === 0 && !$this->_readonly) {
            $var = lcfirst(substr($method, 5));
            $this->removeValue($var, $params[0]);
            return $this;
        }
        else if (strncasecmp($method, "push", 4) === 0 && isset($params) && is_array($params) && !$this->_readonly) {
            $var = lcfirst(substr($method, 4));
            return $this->push($var, isset($params) && is_array($params) && isset($params[0]) ? $params[0] : null);
        }
        else if (strncasecmp($method, "append", 6) === 0 && isset($params) && is_array($params) && !$this->_readonly) {
            $var = lcfirst(substr($method, 6));
            return $this->append($var, isset($params) && is_array($params) && isset($params[0]) ? $params[0] : null);
        }
        else if (strncasecmp($method, "unshift", 7) === 0 && isset($params) && is_array($params) && !$this->_readonly) {
            $var = lcfirst(substr($method, 7));
            return $this->unshift($var, isset($params) && is_array($params) && isset($params[0]) ? $params[0] : null);
        }
        else if (strncasecmp($method, "prepend", 7) === 0 && isset($params) && is_array($params) && !$this->_readonly) {
            $var = lcfirst(substr($method, 7));
            return $this->prepend($var, isset($params) && is_array($params) && isset($params[0]) ? $params[0] : null);
        }
        else if (strncasecmp($method, "pop", 3) === 0) {
            $var = lcfirst(substr($method, 3));
            return $this->pop($var);
        }
        else if (strncasecmp($method, "shift", 5) === 0) {
            $var = lcfirst(substr($method, 5));
            return $this->shift($var);
        }
    }
}
