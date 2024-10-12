<?php

namespace MagicObject\File;

use MagicObject\Util\PicoStringUtil;

/**
 * Class representing an upload file tool.
 *
 * This class is designed to handle uploaded files. All attributes in this class are read-only.
 *
 * @link https://github.com/Planetbiru/MagicObject
 */
class PicoUploadFile
{
    /**
     * Mapping of uploaded file names to their keys.
     *
     * @var array
     */
    private $map = array();

    /**
     * Array of uploaded file containers.
     *
     * @var PicoUploadFileContainer[]
     */
    private $values = array();

    /**
     * Constructor.
     *
     * Initializes the mapping of uploaded file names and populates the values.
     */
    public function __construct()
    {
        $this->initMap();
    }

    /**
     * Magic method to handle dynamic getter calls.
     *
     * @param string $method The name of the method being called.
     * @param array $arguments The arguments passed to the method.
     * @return mixed The value of the requested property or an empty container.
     */
    public function __call($method, $arguments) //NOSONAR
    {
        if (strncasecmp($method, "get", 3) === 0) {
            $var = substr($method, 3);
            $camel = PicoStringUtil::camelize($var);
            $key = $this->map[$camel] ?? null;
            return isset($this->values[$key]) ? $this->values[$key] : new PicoUploadFileContainer();
        }
    }
    
    /**
     * Get an uploaded file by parameter name.
     *
     * @param string $name The parameter name.
     * @return PicoUploadFileContainer An instance of the uploaded file container or an empty container.
     */
    public function get($name)
    {
        return $this->__get($name);
    }

    /**
     * Magic method to handle dynamic property access.
     *
     * @param string $name The name of the property being accessed.
     * @return PicoUploadFileContainer An instance of the uploaded file container or an empty container.
     */
    public function __get($name)
    {
        $camel = PicoStringUtil::camelize($name);
        if (isset($this->map[$camel])) {
            $key = $this->map[$camel];
            return $this->values[$key] ?? new PicoUploadFileContainer();
        }
        return new PicoUploadFileContainer();
    }

    /**
     * Check if an uploaded file exists for the given parameter name.
     *
     * @param string $name The parameter name.
     * @return boolean True if the file exists; otherwise, false.
     */
    public function __isset($name)
    {
        $camel = PicoStringUtil::camelize($name);
        return isset($this->map[$camel]);
    }

    /**
     * Initialize the mapping of uploaded file names to their keys.
     *
     * @return void
     */
    private function initMap()
    {
        $keys = array_keys($_FILES);
        foreach ($keys as $key) {
            $camel = PicoStringUtil::camelize(str_replace("-", "_", $key));
            $this->map[$camel] = $key;
            $this->values[$key] = new PicoUploadFileContainer($_FILES[$key]);
        }
    }

    /**
     * Convert the object to a string representation for debugging.
     *
     * @return string JSON-encoded string of the uploaded file data.
     */
    public function __toString()
    {
        $arr = array();
        foreach ($this->values as $key => $value) {
            $arr[$key] = json_decode($value); // Assuming PicoUploadFileContainer has a valid __toString() method
        }
        return json_encode($arr);
    }
}
