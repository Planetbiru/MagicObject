<?php

namespace MagicObject\File;

class UploadFileObject
{
    private $values = array();
    public function __construct($file = null)
    {
        if($file != null)
        {
            $this->values = $file;
        }
    }
    
    /**
     * Check if file is multiple upload or not
     *
     * @return boolean
     */
    public function isMultiple()
    {
        return isset($this->values['tmp_name']) && is_array($this->values['tmp_name']);
    }
    
    /**
     * Check if file is multiple upload or not
     *
     * @param integer $index
     * @return boolean
     */
    public function isExists($index)
    {
        return $this->isMultiple() && isset($this->values['tmp_name'][$index]);
    }
    
    /**
     * Get total file with similar key
     *
     * @return void
     */
    public function getFileCount()
    {
        if(empty($this->values))
        {
            return 0;
        }
        return $this->isMultiple() ? count($this->values['tmp_name']) : 1;
    }
    
    /**
     * Get one file
     *
     * @param integer $index
     * @return array
     */
    public function getItem($index)
    {
        $file = array(
            'tmp_name' => $this->values['tmp_name'][$index],
            'name' => $this->values['name'][$index]
        );
        
        if(isset($this->values['error'][$index]))
        {
            $file['error'] = $this->values['error'][$index];
        }
        if(isset($this->values['type'][$index]))
        {
            $file['type'] = $this->values['type'][$index];
        }
        if(isset($this->values['size'][$index]))
        {
            $file['size'] = $this->values['size'][$index];
        }

        // PHP 8
        if(isset($this->values['full_path'][$index]))
        {
            $file['full_path'] = $this->values['full_path'][$index];
        }
        
        return $file;
    }
    
    /**
     * Copy file to destination path
     *
     * @param integer $index
     * @param string $destination
     * @return self
     */
    public function copy($index, $userFunction)
    {
        $file = array();
        if($this->isMultiple() && $this->isExists($index))
        {
            $file = $this->getItem($index);
        }
        else
        {
            $file = $this->values;
        }
        if($userFunction != null && is_callable($userFunction))
        {
            call_user_func($userFunction, $file);
        }
        return $this;
    }
    
    /**
     * Magic object to convert object to string
     *
     * @return string
     */
    public function __toString()
    {
        return empty($this->values) ? "{}" : json_encode($this->values);
    }
}