<?php

namespace MagicObject\Database;

/**
 * Class for sorting database queries.
 *
 * This class allows you to define sorting criteria for database queries,
 * including the field to sort by and the direction of sorting.
 *
 * @link https://github.com/Planetbiru/MagicObject
 */
class PicoSort
{
    const ORDER_TYPE_ASC  = "asc";
    const ORDER_TYPE_DESC = "desc";
    const SORT_BY         = "sortBy";

    /**
     * The field to sort by.
     *
     * @var string
     */
    private $sortBy = "";

    /**
     * The type of sorting (ascending or descending).
     *
     * @var string
     */
    private $sortType = "";

    /**
     * Constructor to initialize sorting criteria.
     *
     * @param string|null $sortBy The field to sort by.
     * @param string|null $sortType The type of sorting.
     */
    public function __construct($sortBy = null, $sortType = null)
    {
        $this->setSortBy($sortBy);
        $this->setSortType($sortType);
    }

    /**
     * Get the field to sort by.
     *
     * @return string The field to sort by.
     */
    public function getSortBy()
    {
        return $this->sortBy;
    }

    /**
     * Set the field to sort by.
     *
     * @param string $sortBy The field to sort by.
     *
     * @return self
     */
    public function setSortBy($sortBy)
    {
        $this->sortBy = $sortBy;
        return $this;
    }

    /**
     * Get the type of sorting.
     *
     * @return string The type of sorting.
     */
    public function getSortType()
    {
        return $this->sortType;
    }

    /**
     * Set the type of sorting.
     *
     * @param string $sortType The type of sorting.
     *
     * @return self
     */
    public function setSortType($sortType)
    {
        $this->sortType = $sortType;
        return $this;
    }

    /**
     * Magic method for dynamic method calls.
     *
     * This method allows the setting of sorting criteria through
     * dynamically named methods.
     *
     * @param string $method The method name.
     * @param array $params The parameters passed to the method.
     * @return self|null
     */
    public function __call($method, $params)
    {
        if (strncasecmp($method, self::SORT_BY, 6) === 0 && isset($params[0])) {
            $field = lcfirst(substr($method, 6));
            $value = $params[0];
            $this->setSortBy($field);
            $this->setSortType($value);
            return $this;
        }
        return null; // Added return for undefined methods
    }

    /**
     * Get an instance of PicoSort.
     *
     * @return self A new instance of PicoSort.
     */
    public static function getInstance()
    {
        return new self;
    }

    /**
     * Normalize the sort type to either ascending or descending.
     *
     * @param string $type The desired sort type.
     * @return string The normalized sort type.
     */
    public static function fixSortType($type)
    {
        return strcasecmp($type, self::ORDER_TYPE_DESC) == 0 ? self::ORDER_TYPE_DESC : self::ORDER_TYPE_ASC;
    }

    /**
     * Convert the object to a JSON string representation for debugging.
     *
     * This method is intended for debugging purposes only.
     *
     * @return string The JSON representation of the object.
     */
    public function __toString()
    {
        return json_encode(array(
            'sortBy' => $this->sortBy,
            'sortType' => $this->sortType
        ));
    }
}
