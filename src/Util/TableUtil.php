<?php
namespace MagicObject\Util;

use DOMElement;

class TableUtil
{
    /**
     * Set class
     *
     * @param DOMElement $node
     * @param array $annotationClass
     * @return DOMElement
     */
    public static function setClassList($node, $annotationClass)
    {
        if(isset($annotationClass) && isset($annotationClass["content"]))
        {
            $classList = $annotationClass["content"];
            $node->setAttribute("class", $classList);
        }
        return $node;
    }

    /**
     * Set attributes
     *
     * @param DOMElement $node
     * @param array $annotationClass
     * @return DOMElement
     */
    public static function setAttributes($node, $annotationAttributes)
    {
        if(isset($annotationAttributes) && is_array($annotationAttributes))
        {
            foreach($annotationAttributes as $attributeName=>$attributeValue)
            {
                echo "$attributeName, $attributeValue\r\n";
                $node->setAttribute($attributeName, $attributeValue);
            }            
        }
        return $node;
    }

    /**
     * Set identity
     *
     * @param DOMElement $node
     * @param array $annotationIdentity
     * @return DOMElement
     */
    public static function setIdentity($node, $annotationIdentity)
    {
        if(isset($annotationIdentity) && is_array($annotationIdentity))
        {
            if(isset($annotationIdentity["name"]))
            {
                $node->setAttribute("name", $annotationIdentity["name"]);
            }      
        }
        return $node;
    }
}