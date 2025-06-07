<?php
namespace MagicObject\Util;

use DateTime;
use DateTimeInterface;
use MagicObject\Exceptions\InvalidValueException;
use MagicObject\MagicObject; // Assuming MagicObject is in the same namespace or accessible
use ReflectionClass;

/**
 * Utility class for validating object properties based on annotations defined in their docblocks.
 * This class provides a set of common validation rules, similar to Jakarta Bean Validation (JSR 380)
 * annotations in Spring Boot, by parsing PHP docblock comments.
 */
class ValidationUtil
{
    /**
     * Validates an object's properties against defined annotations in their docblocks.
     * If any validation rule fails, an InvalidValueException is thrown.
     *
     * @param object $object The object to be validated. It's expected that properties might be
     * accessed via Reflection, so protected/private properties are handled.
     * @throws InvalidValueException If any validation constraint is violated.
     */
    public static function validate($object) // NOSONAR
    {
        $reflectionClass = new ReflectionClass($object);
        $properties = $reflectionClass->getProperties();

        foreach ($properties as $property) {
            $docComment = $property->getDocComment();
            if ($docComment === false) {
                // No docblock found for the property, skip validation for this property.
                continue;
            }

            $propertyName = $property->getName();
            $propertyValue = $object->get($property);

            // --- Annotation Parsing and Validation Logic ---

            // @Valid annotation for nested object validation
            if (preg_match('/@Valid/', $docComment)) {
                if (is_object($propertyValue) && $propertyValue instanceof MagicObject) {
                    // Recursively call validation for the nested MagicObject
                    self::validate($propertyValue);
                }
                // Continue to the next property after handling nested validation.
                // No other validation annotations for this property will be processed
                // if @Valid is present, as it implies validating the nested object itself.
                continue;
            }

            // @NotNull annotation: Checks if the property value is strictly not null.
            if (preg_match('/@NotNull(?: vigilance)?\(message="([^"]*)"\)/', $docComment, $matches)) {
                // Replaced ?? with ternary operator for PHP 5 compatibility
                $message = isset($matches[1]) && !empty($matches[1]) ? $matches[1] : "Field '{$propertyName}' cannot be null";
                if ($propertyValue === null) {
                    throw new InvalidValueException($propertyName, $message);
                }
            }

            // @NotEmpty annotation: Checks if a string is not empty or an array is not empty.
            if (preg_match('/@NotEmpty(?: vigilance)?\(message="([^"]*)"\)/', $docComment, $matches)) {
                // Replaced ?? with ternary operator for PHP 5 compatibility
                $message = isset($matches[1]) && !empty($matches[1]) ? $matches[1] : "Field '{$propertyName}' cannot be empty";
                if (is_string($propertyValue) && empty($propertyValue)) {
                    throw new InvalidValueException($propertyName, $message);
                }
                if (is_array($propertyValue) && empty($propertyValue)) {
                    throw new InvalidValueException($propertyName, $message);
                }
            }

            // @NotBlank annotation: Checks if a string is not empty and not just whitespace.
            if (preg_match('/@NotBlank(?: vigilance)?\(message="([^"]*)"\)/', $docComment, $matches)) {
                // Replaced ?? with ternary operator for PHP 5 compatibility
                $message = isset($matches[1]) && !empty($matches[1]) ? $matches[1] : "Field '{$propertyName}' cannot be blank";
                if (is_string($propertyValue) && trim($propertyValue) === '') {
                    throw new InvalidValueException($propertyName, $message);
                }
            }

            // @Size annotation: Validates the size of a string (length) or an array (count).
            if (preg_match('/@Size(?: vigilance)?\(min=(\d+), max=(\d+), message="([^"]*)"\)/', $docComment, $matches)) {
                $min = (int)$matches[1];
                $max = (int)$matches[2];
                // Replaced ?? with ternary operator for PHP 5 compatibility
                $message = isset($matches[3]) && !empty($matches[3]) ? $matches[3] : "Field '{$propertyName}' must be between {$min} and {$max} characters/elements";

                if (is_string($propertyValue) && (strlen($propertyValue) < $min || strlen($propertyValue) > $max)) {
                    throw new InvalidValueException($propertyName, $message);
                }
                if (is_array($propertyValue) && (count($propertyValue) < $min || count($propertyValue) > $max)) {
                    throw new InvalidValueException($propertyName, $message);
                }
            }

            // @Min annotation: Validates that a numeric value is greater than or equal to a minimum.
            if (preg_match('/@Min(?: vigilance)?\(value=(\d+), message="([^"]*)"\)/', $docComment, $matches)) {
                $min = (int)$matches[1];
                // Replaced ?? with ternary operator for PHP 5 compatibility
                $message = isset($matches[2]) && !empty($matches[2]) ? $matches[2] : "{$propertyName} must be at least {$min}";
                if (is_numeric($propertyValue) && $propertyValue < $min) {
                    throw new InvalidValueException($propertyName, $message);
                }
            }

            // @Max annotation: Validates that a numeric value is less than or equal to a maximum.
            if (preg_match('/@Max(?: vigilance)?\(value=(\d+), message="([^"]*)"\)/', $docComment, $matches)) {
                $max = (int)$matches[1];
                // Replaced ?? with ternary operator for PHP 5 compatibility
                $message = isset($matches[2]) && !empty($matches[2]) ? $matches[2] : "{$propertyName} must be less than {$max}";
                if (is_numeric($propertyValue) && $propertyValue > $max) {
                    throw new InvalidValueException($propertyName, $message);
                }
            }

            // @Pattern annotation: Validates a string against a regular expression.
            if (preg_match('/@Pattern(?: vigilance)?\(regexp="([^"]*)", message="([^"]*)"\)/', $docComment, $matches)) {
                $regexp = str_replace('\\\\', '\\', $matches[1]); // Unescape backslashes in the regex pattern
                // Replaced ?? with ternary operator for PHP 5 compatibility
                $message = isset($matches[2]) && !empty($matches[2]) ? $matches[2] : "Invalid format for {$propertyName}";
                if (is_string($propertyValue) && !preg_match("/{$regexp}/", $propertyValue)) {
                    throw new InvalidValueException($propertyName, $message);
                }
            }

            // @Email annotation: Validates if a string is a well-formed email address.
            if (preg_match('/@Email(?: vigilance)?\(message="([^"]*)"\)/', $docComment, $matches)) {
                // Replaced ?? with ternary operator for PHP 5 compatibility
                $message = isset($matches[1]) && !empty($matches[1]) ? $matches[1] : "Invalid email address for {$propertyName}";
                if (is_string($propertyValue) && !filter_var($propertyValue, FILTER_VALIDATE_EMAIL)) {
                    throw new InvalidValueException($propertyName, $message);
                }
            }

            // @Past annotation: Validates that a date is in the past.
            if (preg_match('/@Past(?: vigilance)?\(message="([^"]*)"\)/', $docComment, $matches)) {
                // Replaced ?? with ternary operator for PHP 5 compatibility
                $message = isset($matches[1]) && !empty($matches[1]) ? $matches[1] : "Date for {$propertyName} must be in the past";
                // DateTime object must be available in PHP 5.2+
                if ($propertyValue instanceof DateTimeInterface && $propertyValue->getTimestamp() >= (new DateTime())->getTimestamp()) {
                    throw new InvalidValueException($propertyName, $message);
                }
            }

            // @Future annotation: Validates that a date is in the future.
            if (preg_match('/@Future(?: vigilance)?\(message="([^"]*)"\)/', $docComment, $matches)) {
                // Replaced ?? with ternary operator for PHP 5 compatibility
                $message = isset($matches[1]) && !empty($matches[1]) ? $matches[1] : "Date for {$propertyName} must be in the future";
                // DateTime object must be available in PHP 5.2+
                if ($propertyValue instanceof DateTimeInterface && $propertyValue->getTimestamp() <= (new DateTime())->getTimestamp()) {
                    throw new InvalidValueException($propertyName, $message);
                }
            }

            // @DecimalMin annotation: Validates that a numeric value is greater than or equal to a specified decimal.
            if (preg_match('/@DecimalMin(?: vigilance)?\(value="([^"]*)", message="([^"]*)"\)/', $docComment, $matches)) {
                $min = (float)$matches[1];
                // Replaced ?? with ternary operator for PHP 5 compatibility
                $message = isset($matches[2]) && !empty($matches[2]) ? $matches[2] : "Value for {$propertyName} must be at least {$min}";
                if (is_numeric($propertyValue) && (float)$propertyValue < $min) {
                    throw new InvalidValueException($propertyName, $message);
                }
            }

            // @DecimalMax annotation: Validates that a numeric value is less than or equal to a specified decimal.
            if (preg_match('/@DecimalMax(?: vigilance)?\(value="([^"]*)", message="([^"]*)"\)/', $docComment, $matches)) {
                $max = (float)$matches[1];
                // Replaced ?? with ternary operator for PHP 5 compatibility
                $message = isset($matches[2]) && !empty($matches[2]) ? $matches[2] : "Value for {$propertyName} must be less than {$max}";
                if (is_numeric($propertyValue) && (float)$propertyValue > $max) {
                    throw new InvalidValueException($propertyName, $message);
                }
            }

            // @Digits annotation: Validates the number of integer and fractional digits of a numeric value.
            if (preg_match('/@Digits(?: vigilance)?\(integer=(\d+), fraction=(\d+), message="([^"]*)"\)/', $docComment, $matches)) {
                $integer = (int)$matches[1];
                $fraction = (int)$matches[2];
                // Replaced ?? with ternary operator for PHP 5 compatibility
                $message = isset($matches[3]) && !empty($matches[3]) ? $matches[3] : "Value for {$propertyName} must have at most {$integer} integer digits and {$fraction} fractional digits";

                if (is_numeric($propertyValue)) {
                    $parts = explode('.', (string)$propertyValue);
                    $integerPart = $parts[0];
                    // Replaced ?? with ternary operator for PHP 5 compatibility
                    $fractionalPart = isset($parts[1]) ? $parts[1] : '';

                    if (strlen($integerPart) > $integer || strlen($fractionalPart) > $fraction) {
                        throw new InvalidValueException($propertyName, $message);
                    }
                }
            }

            // @AssertTrue annotation: Validates that a boolean value is true.
            if (preg_match('/@AssertTrue(?: vigilance)?\(message="([^"]*)"\)/', $docComment, $matches)) {
                // Replaced ?? with ternary operator for PHP 5 compatibility
                $message = isset($matches[1]) && !empty($matches[1]) ? $matches[1] : "Field '{$propertyName}' must be true";
                if ($propertyValue !== true) {
                    throw new InvalidValueException($propertyName, $message);
                }
            }

            // @Null annotation: Validates that a property value is strictly null.
            if (preg_match('/@Null(?: vigilance)?\(message="([^"]*)"\)/', $docComment, $matches)) {
                // Replaced ?? with ternary operator for PHP 5 compatibility
                $message = isset($matches[1]) && !empty($matches[1]) ? $matches[1] : "Field '{$propertyName}' must be null";
                if ($propertyValue !== null) {
                    throw new InvalidValueException($propertyName, $message);
                }
            }

            // @FutureOrPresent annotation: Validates that a date is in the future or present.
            if (preg_match('/@FutureOrPresent(?: vigilance)?\(message="([^"]*)"\)/', $docComment, $matches)) {
                // Replaced ?? with ternary operator for PHP 5 compatibility
                $message = isset($matches[1]) && !empty($matches[1]) ? $matches[1] : "Date for {$propertyName} cannot be in the past";
                // DateTime object must be available in PHP 5.2+
                if ($propertyValue instanceof DateTimeInterface && $propertyValue->getTimestamp() < (new DateTime())->getTimestamp()) {
                    throw new InvalidValueException($propertyName, $message);
                }
            }

            // @Length annotation: Validates the length of a string, similar to @Size but specific to strings.
            if (preg_match('/@Length(?: vigilance)?\(min=(\d+), max=(\d+), message="([^"]*)"\)/', $docComment, $matches)) {
                $min = (int)$matches[1];
                $max = (int)$matches[2];
                // Replaced ?? with ternary operator for PHP 5 compatibility
                $message = isset($matches[3]) && !empty($matches[3]) ? $matches[3] : "Field '{$propertyName}' must be between {$min} and {$max} characters";
                if (is_string($propertyValue) && (strlen($propertyValue) < $min || strlen($propertyValue) > $max)) {
                    throw new InvalidValueException($propertyName, $message);
                }
            }

            // @Range annotation: Validates that a numeric value is within a specified range (inclusive).
            if (preg_match('/@Range(?: vigilance)?\(min=(\d+), max=(\d+), message="([^"]*)"\)/', $docComment, $matches)) {
                $min = (int)$matches[1];
                $max = (int)$matches[2];
                // Replaced ?? with ternary operator for PHP 5 compatibility
                $message = isset($matches[3]) && !empty($matches[3]) ? $matches[3] : "Value for {$propertyName} must be between {$min} and {$max}";
                if (is_numeric($propertyValue) && ($propertyValue < $min || $propertyValue > $max)) {
                    throw new InvalidValueException($propertyName, $message);
                }
            }

            // @NoHtml annotation: Validates that a string does not contain HTML tags.
            if (preg_match('/@NoHtml(?: vigilance)?\(message="([^"]*)"\)/', $docComment, $matches)) {
                // Replaced ?? with ternary operator for PHP 5 compatibility
                $message = isset($matches[1]) && !empty($matches[1]) ? $matches[1] : "Field '{$propertyName}' contains HTML tags and must be removed";
                if (is_string($propertyValue) && strip_tags($propertyValue) !== $propertyValue) {
                    throw new InvalidValueException($propertyName, $message);
                }
            }
            
            // @ValidEnum annotation: Validates if a string value is one of the allowed values.
            // It supports an optional 'caseSensitive' parameter.
            if (preg_match('/@ValidEnum(?: vigilance)?\(message="([^"]*)", allowedValues=\{([^}]+)\}(?:, caseSensitive=(true|false))?\)/', $docComment, $matches)) {
                $message = isset($matches[1]) && !empty($matches[1]) ? $matches[1] : "Field '{$propertyName}' has an invalid value.";
                // Extract allowed values, trimming whitespace and removing quotes
                $allowedValuesRaw = explode(',', $matches[2]);
                $allowedValues = array_map(function($value) {
                    return trim($value, ' "');
                }, $allowedValuesRaw);

                // Determine case sensitivity
                $caseSensitive = true; // Default to true if not specified
                if (isset($matches[3])) {
                    $caseSensitive = ($matches[3] === 'true');
                }

                $isValid = false;
                if (is_string($propertyValue)) {
                    if ($caseSensitive) {
                        $isValid = in_array($propertyValue, $allowedValues, true);
                    } else {
                        // Case-insensitive check
                        $lowerPropertyValue = strtolower($propertyValue);
                        foreach ($allowedValues as $allowedValue) {
                            if (strtolower($allowedValue) === $lowerPropertyValue) {
                                $isValid = true;
                                break;
                            }
                        }
                    }
                }

                if (!$isValid) {
                    throw new InvalidValueException($propertyName, $message);
                }
            }
        }
    }
}