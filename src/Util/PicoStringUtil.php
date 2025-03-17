<?php
namespace MagicObject\Util;

use stdClass;

/**
 * Class PicoStringUtil
 *
 * A utility class for performing various string manipulations and transformations.
 *
 * This class provides static methods for converting between different string case formats (snake case, camel case, kebab case),
 * validating string contents, and manipulating strings (trimming, checking for null/empty values, etc.).
 *
 * The methods are designed to be used statically, allowing for convenient access without needing to instantiate the class.
 *
 * Example usage:
 * ```
 * <?php
 * $camelCase = PicoStringUtil::camelize('example_string');
 * $kebabCase = PicoStringUtil::kebapize('exampleString');
 * $isNotEmpty = PicoStringUtil::isNotNullAndNotEmpty('Some Value');
 * ```
 * 
 * @author Kamshory
 * @package MagicObject\Util
 * @link https://github.com/Planetbiru/MagicObject
 */
class PicoStringUtil
{
    private function __construct()
    {
        // prevent object construction from outside the class
    }
    
    /**
     * Convert snake case to camel case
     *
     * @param string $input Input string in snake case format.
     * @param string $glue Optional. The glue character used in the input string (default is '_').
     * @return string Converted string in camel case format.
     */
    public static function camelize($input, $glue = '_')
    {
        $str = str_replace(array(' ', '-'), $glue, $input);
        $str = preg_replace('/_+/', $glue, $str);
        $str = preg_replace_callback('/_([a-z])/', function ($matches) {
            return strtoupper($matches[1]);
        }, $str);
        return lcfirst($str);
    }

    /**
     * Convert snake case to upper camel case
     *
     * @param string $input Input string in snake case format.
     * @param string $glue Optional. The glue character used in the input string (default is '_').
     * @return string Converted string in upper camel case format.
     */
    public static function upperCamelize($input, $glue = '_')
    {
        $input = self::camelize($input, $glue);
        return ucfirst($input);
    }

    /**
     * Convert camel case to snake case
     *
     * Converts a string from camel case (e.g., exampleString) to snake case (e.g., example_string).
     *
     * @param string $input Input string in camel case format.
     * @param string $glue Optional. The glue character used in the input string (default is '_').
     * @return string Converted string in snake case format.
     */
    public static function snakeize($input, $glue = '_') {
        return ltrim(
            preg_replace_callback('/[A-Z]/', function ($matches) use ($glue) {
                return $glue . strtolower($matches[0]);
            }, $input),
            $glue
        );
    }

    /**
     * Snakeize object
     *
     * Converts all keys of an object or an array to snake case.
     * This is useful for normalizing data structures when working with APIs or databases.
     *
     * @param mixed $object Object or array to be converted.
     * @return mixed The input object/array with keys converted to snake case.
     */
    public static function snakeizeObject($object)
    {
        if(is_array($object))
        {
            $array = array();
            foreach($object as $key=>$value)
            {
                $array[self::snakeize($key)] = self::snakeizeObject($value);
            }
            return $array;
        }
        else if($object instanceof stdClass)
        {
            $stdClass = new stdClass;
            foreach($object as $key=>$value)
            {
                $stdClass->{self::snakeize($key)} = self::snakeizeObject($value);
            }
            return $stdClass;
        }
        else
        {
            return $object;
        }
    }

    /**
     * Convert snake case to title case
     *
     * Converts a snake case string (e.g., example_string) to title case (e.g., Example String).
     * Words are separated by spaces, and the first letter of each word is capitalized.
     *
     * @param string $input Input string in snake case format.
     * @param string $glue Optional. The glue character used in the input string (default is '_').
     * @return string Converted string in title case format.
     */
    public static function snakeToTitle($input, $glue = '_')
    {
        $str = str_replace([' ', '-'], $glue, $input);
        $str = preg_replace('/_+/', $glue, $str);
        $words = explode($glue, $str);
        $words = array_map('ucwords', $words);
        return implode(' ', $words);
    }

    /**
     * Convert camel case to title case
     *
     * Converts a camel case string (e.g., exampleString) to title case (e.g., Example String).
     *
     * @param string $input Input string in camel case format.
     * @return string Converted string in title case format.
     */
    public static function camelToTitle($input)
    {
        $snake = self::snakeize($input);
        return self::snakeToTitle($snake);
    }

    /**
     * Convert to kebab case
     *
     * Converts a string to kebab case (e.g., example_string becomes example-string).
     * Useful for URL slugs or CSS class names.
     *
     * @param string $input Input string in any case format.
     * @return string Converted string in kebab case format.
     */
    public static function kebapize($input)
    {
        $snake = self::snakeize($input, '-');
        return str_replace('_', '-', $snake);
    }

    /**
     * Create constant key
     *
     * Converts a string to a constant key format (e.g., example_string becomes EXAMPLE_STRING).
     *
     * @param string $input Input string in snake case format.
     * @return string Converted string in uppercase snake case format.
     */
    public function constantKey($input)
    {
        return strtoupper(self::snakeize($input, '-'));
    }

    /**
     * Check if string starts with a substring
     *
     * Determines if the given string starts with the specified substring.
     * Comparison can be case-sensitive or case-insensitive.
     *
     * @param string $haystack The string to check.
     * @param string $value The substring to look for at the start.
     * @param bool $caseSensitive Optional. Flag to indicate if the comparison is case-sensitive (default is false).
     * @return bool True if the string starts with the substring, false otherwise.
     */
    public static function startsWith($haystack, $value, $caseSensitive = false)
    {
        if($caseSensitive)
        {
            return isset($haystack) && substr($haystack, 0, strlen($value)) == $value;
        }
        else
        {
            return isset($haystack) && strtolower(substr($haystack, 0, strlen($value))) == strtolower($value);
        }
    }

    /**
     * Check if string ends with a substring
     *
     * Determines if the given string ends with the specified substring.
     * Comparison can be case-sensitive or case-insensitive.
     *
     * @param string $haystack The string to check.
     * @param string $value The substring to look for at the end.
     * @param bool $caseSensitive Optional. Flag to indicate if the comparison is case-sensitive (default is false).
     * @return bool True if the string ends with the substring, false otherwise.
     */
    public static function endsWith($haystack, $value, $caseSensitive = false)
    {
        if($caseSensitive)
        {
            return isset($haystack) && substr($haystack, strlen($haystack) - strlen($value)) == $value;
        }
        else
        {
            return isset($haystack) && strtolower(substr($haystack, strlen($haystack) - strlen($value))) == strtolower($value);
        }
    }

    /**
     * Left trim a string
     *
     * Trims the specified substring from the start of the string for a defined number of times.
     * If count is -1, it trims until the substring no longer occurs at the start.
     *
     * @param string $haystack The string to trim.
     * @param string $substring The substring to trim from the start.
     * @param int $count Optional. Number of times to trim (default is -1).
     * @return string The trimmed string.
     */
    public static function lTrim($haystack, $substring, $count = -1)
    {
        $i = 0;
        $found = false;
        do
        {
            if(PicoStringUtil::startsWith($haystack, $substring))
            {
                $haystack = trim(substr($haystack, 1));
                $found = true;
                $i++;
            }
            else
            {
                $found = false;
            }
        }
        while($found && ($count == -1 || $count > $i));
        return $haystack;
    }

    /**
     * Right trim a string
     *
     * Trims the specified substring from the end of the string for a defined number of times.
     * If count is -1, it trims until the substring no longer occurs at the end.
     *
     * @param string $haystack The string to trim.
     * @param string $substring The substring to trim from the end.
     * @param int $count Optional. Number of times to trim (default is -1).
     * @return string The trimmed string.
     */
    public static function rTrim($haystack, $substring, $count = -1)
    {
        $i = 0;
        $found = false;
        do
        {
            if(PicoStringUtil::endsWith($haystack, $substring))
            {
                $haystack = trim(substr($haystack, 0, strlen($haystack) - 1));
                $found = true;
                $i++;
            }
            else
            {
                $found = false;
            }
        }
        while($found && ($count == -1 || $count > $i));
        return $haystack;
    }

    /**
     * Check if string is not null and not empty
     *
     * Determines if the given string is neither null nor empty.
     *
     * @param string $value The string to check.
     * @return bool True if the string is not null and not empty, false otherwise.
     */
    public static function isNotNullAndNotEmpty($value)
    {
        return isset($value) && !empty($value);
    }

    /**
     * Check if string is null or empty
     *
     * Determines if the given string is either null or empty.
     *
     * @param string $value The string to check.
     * @return bool True if the string is null or empty, false otherwise.
     */
    public static function isNullOrEmpty($value)
    {
        return !isset($value) || empty($value);
    }

    /**
     * Select not null value
     *
     * Returns the first value that is not null from the two provided values.
     *
     * @param mixed $value1 The first value to check.
     * @param mixed $value2 The second value to check.
     * @return mixed The first non-null value.
     */
    public static function selectNotNull($value1, $value2)
    {
        return isset($value1) ? $value1 : $value2;
    }

    /**
     * Fix carriage returns in a string
     *
     * Normalizes line endings in a string to Windows-style carriage return line feed (CRLF).
     *
     * @param string $input The input string to fix.
     * @return string The modified string with normalized line endings.
     */
    public static function windowsCariageReturn($input)
    {
        $input = str_replace("\n", "\r\n", $input);
        $input = str_replace("\r\r\n", "\r\n", $input);
        $input = str_replace("\r", "\r\n", $input);
        $input = str_replace("\r\n\n", "\r\n", $input);
        return $input;
    }

    /**
     * Splits a string into chunks of a specified length without breaking words.
     *
     * This method ensures that words are not split between chunks, making it
     * ideal for scenarios where maintaining the integrity of words is crucial.
     * The chunks are separated by the specified delimiter.
     *
     * @param string $input The input string to be chunked.
     * @param int $length The maximum length of each chunk. Defaults to 76.
     * @param string $delimiter The delimiter to append between chunks. Defaults to "\n".
     * @return string The chunked string with the specified delimiter between chunks.
     */
    public static function wordChunk($input, $length = 76, $delimiter = "\n") {
        $words = explode(' ', $input); // Split the input string into words based on spaces
        $currentChunk = ''; // The current chunk being built
        $result = []; // Array to hold all chunks

        foreach ($words as $word) {
            // If adding the current word exceeds the chunk length, save the current chunk
            if (strlen($currentChunk) + strlen($word) + 1 > $length) {
                $result[] = trim($currentChunk); // Add the completed chunk to the result
                $currentChunk = ''; // Reset the current chunk
            }

            // Append the word to the current chunk
            $currentChunk .= ($currentChunk === '' ? '' : ' ') . $word;
        }

        // Add the last chunk if it exists
        if (!empty($currentChunk)) {
            $result[] = trim($currentChunk);
        }

        // Combine all chunks with the specified delimiter and return the result
        return implode($delimiter, $result);
    }
    
    /**
     * Masks a string by replacing certain characters with a masking character.
     *
     * @param string $string The original string to be masked.
     * @param int $position The position where the masking should occur. Positive integers start from the beginning, negative integers start from the end.
     * @param int $maskLength The number of characters to mask.
     * @param string $maskChar The character to use for masking. Defaults to '*'.
     * @return string The masked string.
     */
    public static function maskString($string, $position, $maskLength, $maskChar = '*') {
        $stringLength = strlen($string);

        // Ensure $maskLength is a positive integer
        if ($maskLength < 0) {
            $maskLength = abs($maskLength); // Convert to positive if negative
        }

        // If maskLength exceeds the string length, adjust it
        if ($maskLength > $stringLength) {
            $maskLength = $stringLength;
        }

        // Case 1: Positive position (masking starts from the beginning of the string)
        if ($position > 0) {
            // If position exceeds the string length, adjust to string length
            if ($position > $stringLength) {
                $position = $stringLength;
            }

            // Part 1: The portion before the mask starts
            $part1 = substr($string, 0, $position - 1);

            // Part 2: The masked portion
            $part2 = str_repeat($maskChar, $maskLength);

            // Part 3: The remaining portion after the mask ends
            $part3 = substr($string, $position + $maskLength - 1);
            
            // Ensuring the final string has the correct length by adjusting Part 3
            $part3 = substr($part3, 0, $stringLength - strlen($part1) - strlen($part2));

            return $part1 . $part2 . $part3;
        }

        // Case 2: Negative position (masking starts from the end of the string)
        if ($position < 0) {
            // Convert negative position to the correct start index from the end
            $start = $stringLength + $position - $maskLength;

            // Ensure that start is not less than -1
            if ($start < -1) {
                $start = -1;
            }

            // Part 3: The portion after the mask starts (after the negative index)
            $part3 = substr($string, $start + $maskLength + 1);

            // Part 2: The masked portion
            $part2 = str_repeat($maskChar, $maskLength);

            // Part 1: The portion before the masking starts
            $part1 = substr($string, 0, $start + 1);

            // Ensuring the final string has the correct length by adjusting Part 3
            $part3 = substr($part3, 0, $stringLength - strlen($part1) - strlen($part2));

            return $part1 . $part2 . $part3;
        }

        // Default case: mask the entire string
        return str_repeat($maskChar, $stringLength);
    }
}