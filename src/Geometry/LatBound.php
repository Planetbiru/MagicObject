<?php

namespace MagicObject\Geometry;

/**
 * Class LatBounds
 *
 * Represents a latitude bounding box defined by southwest and northeast latitude values.
 *
 * @package MagicObject\Geometry
 */
class LatBounds
{
    /**
     * @var float The southwestern latitude value.
     */
    protected $_swLat; // NOSONAR

    /**
     * @var float The northeastern latitude value.
     */
    protected $_neLat; // NOSONAR

    /**
     * LatBounds constructor.
     *
     * @param float $swLat The southwestern latitude.
     * @param float $neLat The northeastern latitude.
     */
    public function __construct($swLat, $neLat) 
    {
        $this->_swLat = $swLat;
        $this->_neLat = $neLat;
    }

    /**
     * Get the southwestern latitude.
     *
     * @return float The southwestern latitude.
     */
    public function getSw()
    {
        return $this->_swLat;
    }

    /**
     * Get the northeastern latitude.
     *
     * @return float The northeastern latitude.
     */
    public function getNe()
    {
        return $this->_neLat;
    }

    /**
     * Calculate the midpoint latitude between the southwestern and northeastern latitudes.
     *
     * @return float The midpoint latitude.
     */
    public function getMidpoint()
    {
        return ($this->_swLat + $this->_neLat) / 2;
    }

    /**
     * Check if the latitude bounds are empty.
     *
     * @return bool True if the bounds are empty, false otherwise.
     */
    public function isEmpty()
    {
        return $this->_swLat > $this->_neLat;
    }

    /**
     * Determine if this LatBounds intersects with another LatBounds.
     *
     * @param LatBounds $LatBounds The other LatBounds to check for intersection.
     * @return bool True if there is an intersection, false otherwise.
     */
    public function intersects($LatBounds)
    {
        return $this->_swLat <= $LatBounds->getSw() 
            ? $LatBounds->getSw() <= $this->_neLat && $LatBounds->getSw() <= $LatBounds->getNe() 
            : $this->_swLat <= $LatBounds->getNe() && $this->_swLat <= $this->_neLat;
    }

    /**
     * Check if this LatBounds is equal to another LatBounds within a certain margin of error.
     *
     * @param LatBounds $LatBounds The other LatBounds to compare.
     * @return bool True if they are equal, false otherwise.
     */
    public function equals($LatBounds)
    {
        return $this->isEmpty() 
            ? $LatBounds->isEmpty() 
            : abs($LatBounds->getSw() - $this->_swLat) 
                + abs($this->_neLat - $LatBounds->getNe()) 
                <= SphericalGeometry::EQUALS_MARGIN_ERROR;
    }

    /**
     * Check if a given latitude is contained within the bounds.
     *
     * @param float $lat The latitude to check.
     * @return bool True if the latitude is contained, false otherwise.
     */
    public function contains($lat)
    {
        return $lat >= $this->_swLat && $lat <= $this->_neLat;
    }

    /**
     * Extend the bounds to include a new latitude.
     *
     * @param float $lat The latitude to extend the bounds with.
     */
    public function extend($lat)
    {
        if ($this->isEmpty()) 
        {
            $this->_neLat = $this->_swLat = $lat;
        }
        else if ($lat < $this->_swLat) 
        { 
            $this->_swLat = $lat;
        }
        else if ($lat > $this->_neLat) 
        {
            $this->_neLat = $lat;
        }
    }
}
