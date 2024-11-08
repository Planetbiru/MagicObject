<?php

namespace MagicObject;

/**
 * Class Txt
 *
 * A utility class that provides dynamic handling of static method calls and dynamic property access.
 * This class allows for flexible interaction by returning the names of methods and properties 
 * that are called statically or accessed dynamically but are not explicitly defined within the class.
 * It can be useful for implementing dynamic behavior or creating a fluent interface.
 */
class Txt
{
    /**
     * Handles static calls to undefined methods.
     *
     * This method intercepts calls to static methods that are not explicitly defined in the class 
     * and returns the name of the method being called.
     *
     * @param string $name The name of the method being called.
     * @param array $arguments An array of arguments passed to the method.
     * @return string The name of the called method.
     */
    public static function __callStatic($name, $arguments)
    {
        return $name;
    }
    
    /**
     * Returns a new instance of the Txt class.
     *
     * This method allows you to retrieve an instance of the Txt class to perform non-static operations,
     * such as dynamic property access using the __get() magic method.
     *
     * @return self A new instance of the Txt class.
     */
    public function getInstance()
    {
        return new self;
    }
    
    /**
     * Handles dynamic access to undefined properties.
     *
     * This method is invoked when an undefined property is accessed on an instance of the Txt class.
     * It returns the name of the property being accessed.
     *
     * @param string $name The name of the property being accessed.
     * @return string The name of the accessed property.
     */
    public function __get($name) {
        return $name;
    }
}
