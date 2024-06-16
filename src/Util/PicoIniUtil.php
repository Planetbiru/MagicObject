<?php

namespace MagicObject\Util;

class PicoIniUtil
{
    public static function writeIniFile($array, $path) {
        $arrayMulti = false;
        
        # See if the array input is multidimensional.
        foreach($array as $arrayTest){
            if(is_array($arrayTest)) {
              $arrayMulti = true;
            }
        }
        
        $content = "";
    
        # Use categories in the INI file for multidimensional array OR use basic INI file:
        if ($arrayMulti) 
        {
            $content = self::getContentMulti($content, $array);
        } 
        else 
        {
            $content = self::getContent($content, $array);
        }
    
        if (!$handle = fopen($path, 'w')) {
            return false;
        }
        if (!fwrite($handle, $content)) {
            return false;
        }
        fclose($handle);
        return true;
    }
    
    /**
     * Get INI content
     *
     * @param string $content
     * @param array $array
     * @return string
     */
    private static function getContent($content, $array)
    {
        foreach ($array as $key2 => $elem2) {
            if (is_array($elem2)) {
                for ($i = 0; $i < count($elem2); $i++) {
                    $content .= $key2 . "[] = \"" . $elem2[$i] . "\"\n";
                }
            } else if ($elem2 == "") {
                $content .= $key2 . " = \n";
            } else {
                $content .= $key2 . " = \"" . $elem2 . "\"\n";
            }
        }
        return $content;
    }
    
    /**
     * Get INI content from multiple
     *
     * @param string $content
     * @param array $array
     * @return string
     */
    private static function getContentMulti($content, $array)
    {
        foreach ($array as $key => $elem) {
            $content .= "[" . $key . "]\n";
            foreach ($elem as $key2 => $elem2) {
                if (is_array($elem2)) {
                    for ($i = 0; $i < count($elem2); $i++) {
                        $content .= $key2 . "[] = \"" . $elem2[$i] . "\"\n";
                    }
                } else if ($elem2 == "") {
                    $content .= $key2 . " = \n";
                } else {
                    $content .= $key2 . " = \"" . $elem2 . "\"\n";
                }
            }
        }
        
        return $content;
    }
}