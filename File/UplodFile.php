<?php

namespace MagicObject\File;

use MagicObject\Util\StringUtil;

class UplodFile
{
    private $map = array();

    /**
     * Uploaded file
     *
     * @var UploadFileObject[]
     */
    private $values = array();

    public function __construct()
    {
        /*
        Debug
        $_FILES = array(
            'test' => array(
                'error' => 1, 
                'tmp_name' => 'apa saja',
                'name' => 'coba.mp3'
            )
        );
        */
        $this->initMap();
    }

    public function __call($method, $arguments)
    {
        if (strncasecmp($method, "get", 3) === 0) {
            $var = lcfirst(substr($method, 3));
            $camel = StringUtil::camelize($var);
            $key = $this->map[$camel];
            return isset($this->values[$key]) ? new UploadFileObject($this->values[$key]) : new UploadFileObject();
        }
    }

    /**
     * Get uploaded file by key
     *
     * @param string $name
     * @return UploadFileObject|mixed
     */
    public function __get($name)
    {
        $camel = StringUtil::camelize($name);
        if (isset($this->map[$camel])) {
            $key = $this->map[$camel];
            if (isset($this->values[$key])) {
                return $this->values[$key];
            }
        }
        return new UploadFileObject();
    }

    /**
     * Init map
     *
     * @return void
     */
    private function initMap()
    {
        $keys = array_keys($_FILES);
        foreach ($keys as $key) {
            $camel = StringUtil::camelize($key);
            $this->map[$camel] = $key;
            $this->values[$key] = new UploadFileObject($_FILES[$key]);
        }
    }
}
