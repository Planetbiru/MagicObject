<?php
namespace MagicObject\Util;

use DateTime;
use DateTimeInterface;
use Exception;
use MagicObject\Exceptions\InvalidValueException;
use MagicObject\MagicObject;
use ReflectionClass;

/**
 * Utility class for validating object properties based on annotations defined in their docblocks.
 * This class provides a set of common validation rules, similar to Jakarta Bean Validation (JSR 380)
 * annotations in Spring Boot, by parsing PHP docblock comments.
 */
class ValidationUtil // NOSONAR
{
    /**
     * Default validation message templates.
     *
     * @var array
     */
    private $validationMessageTemplate = array();
    
    /**
     * Get a new instance of ValidationUtil.
     *
     * @param array $customTemplates Optional. An associative array of custom message templates.
     * These templates will override or extend the default messages.
     * @return self A new instance of ValidationUtil.
     */
    public static function getInstance($customTemplates = array())
    {
        return new self($customTemplates);
    }
    
    /**
     * ValidationUtil constructor.
     * Initializes default message templates and allows overriding/extending them.
     *
     * @param array $customTemplates Optional. An associative array of custom message templates.
     */
    public function __construct($customTemplates = array())
    {
        $this->init($customTemplates);
    }
    
    /**
     * Initializes default validation message templates.
     * Can be used to set or override templates.
     *
     * @param array $customTemplates Optional. An associative array of custom message templates
     * to override or extend the defaults.
     * @return self
     */
    public function init($customTemplates = array())
    {
        $this->validationMessageTemplate = array(
            'required' => "Field '%s' cannot be null",
            'notEmpty' => "Field '%s' cannot be empty",
            'notBlank' => "Field '%s' cannot be blank",
            'size' => "Field '%s' must be between %f and %f characters/elements",
            'min' => "Field '%s' must be at least %f",
            'max' => "Field '%s' must be less than %f",
            'pattern' => "Invalid format for field '%s'",
            'email' => "Invalid email address for field '%s'",
            'past' => "Date for field '%s' must be in the past",
            'future' => "Date for field '%s' must be in the future",
            'decimalMin' => "Value for field '%s' must be at least %f",
            'decimalMax' => "Value for field '%s' must be less than %f",
            'digits' => "Value for field '%s' must have at most %d integer digits and %d fractional digits",
            'assertTrue' => "Field '%s' must be true",
            'null' => "Field '%s' must be null",
            'futureOrPresent' => "Date for field '%s' cannot be in the past",
            'length' => "Field '%s' must be between %d and %d characters",
            'range' => "Value for field '%s' must be between %d and %d",
            'noHtml' => "Field '%s' contains HTML tags and must be removed",
            'validEnum' => "Field '%s' has an invalid value.",
        );

        // Process custom templates to camelize keys
        if (!empty($customTemplates)) {
            $camelizedCustomTemplates = array();
            foreach ($customTemplates as $key => $value) {
                // Pastikan PicoStringUtil di-import atau berikan namespace lengkap
                $camelizedKey = PicoStringUtil::camelize($key); 
                $camelizedCustomTemplates[$camelizedKey] = $value;
            }
            $this->validationMessageTemplate = array_merge($this->validationMessageTemplate, $camelizedCustomTemplates);
        }
        return $this; // Allow method chaining
    }
    
    /**
     * Attempts to convert the given value to a DateTime object.
     *
     * Supports values of type:
     * - DateTimeInterface: returned as-is.
     * - int: treated as a UNIX timestamp.
     * - string: parsed using PHP's DateTime constructor.
     *
     * If the value cannot be converted (e.g., invalid date string), null is returned.
     *
     * @param mixed $value The value to convert (DateTimeInterface, int, or string).
     * @return DateTime|null The corresponding DateTime object, or null on failure.
     */
    public function convertToDateTime($value) // NOSONAR
    {
        if ($value instanceof DateTimeInterface) {
            return $value;
        }

        if (is_int($value)) {
            $dateTime = new DateTime();
            $dateTime->setTimestamp($value);
            return $dateTime;
        }

        if (is_string($value)) {
            try {
                return new DateTime($value);
            } catch (Exception $e) {
                return null;
            }
        }

        return null;
    }

    /**
     * Validates an object's properties against defined annotations in their docblocks.
     * If any validation rule fails, an InvalidValueException is thrown.
     *
     * @param object $object The object to be validated. It's expected that properties might be
     * accessed via Reflection, so protected/private properties are handled.
     * @param string|null $parentPropertyName Optional. The name of the parent property, used for nested validation messages.
     * @throws InvalidValueException If any validation constraint is violated.
     */
    public function validate($object, $parentPropertyName = null) // NOSONAR
    {
        $reflectionClass = new ReflectionClass($object);
        $properties = $reflectionClass->getProperties();

        foreach ($properties as $property) {
            $docComment = $property->getDocComment();
            if ($docComment === false) {
                continue;
            }

            $propertyName = $property->getName();
            // Build the full property path for better error messages
            $fullPropertyName = $parentPropertyName ? $parentPropertyName . '.' . $propertyName : $propertyName;

            $propertyValue = $object->get($property);

            // The order of validation matters: @Valid should usually be processed first
            // to allow nested object validation before individual property validations.
            if ($this->validateValidAnnotation($fullPropertyName, $propertyValue, $docComment)) {
                continue; // If @Valid is present and handled, skip other validations for this property.
            }

            $this->validateRequiredAnnotation($fullPropertyName, $propertyValue, $docComment);
            $this->validateNotEmptyAnnotation($fullPropertyName, $propertyValue, $docComment);
            $this->validateNotBlankAnnotation($fullPropertyName, $propertyValue, $docComment);
            $this->validateSizeAnnotation($fullPropertyName, $propertyValue, $docComment);
            $this->validateMinAnnotation($fullPropertyName, $propertyValue, $docComment);
            $this->validateMaxAnnotation($fullPropertyName, $propertyValue, $docComment);
            $this->validatePatternAnnotation($fullPropertyName, $propertyValue, $docComment);
            $this->validateEmailAnnotation($fullPropertyName, $propertyValue, $docComment);
            $this->validatePastAnnotation($fullPropertyName, $propertyValue, $docComment);
            $this->validateFutureAnnotation($fullPropertyName, $propertyValue, $docComment);
            $this->validateDecimalMinAnnotation($fullPropertyName, $propertyValue, $docComment);
            $this->validateDecimalMaxAnnotation($fullPropertyName, $propertyValue, $docComment);
            $this->validateDigitsAnnotation($fullPropertyName, $propertyValue, $docComment);
            $this->validateAssertTrueAnnotation($fullPropertyName, $propertyValue, $docComment);
            $this->validateNullAnnotation($fullPropertyName, $propertyValue, $docComment);
            $this->validateFutureOrPresentAnnotation($fullPropertyName, $propertyValue, $docComment);
            $this->validateLengthAnnotation($fullPropertyName, $propertyValue, $docComment);
            $this->validateRangeAnnotation($fullPropertyName, $propertyValue, $docComment);
            $this->validateNoHtmlAnnotation($fullPropertyName, $propertyValue, $docComment);
            $this->validateValidEnumAnnotation($fullPropertyName, $propertyValue, $docComment);
        }
    }
    
    /**
     * Creates a validation message using a predefined template.
     *
     * @param string $validationType The type of validation (e.g., 'required', 'size').
     * @param array $values An array of values to be inserted into the message template (e.g., [$propertyName, $min, $max]).
     * @return string The formatted validation message.
     */
    private function createMessage($validationType, $values)
    {
        if (!isset($this->validationMessageTemplate[$validationType])) {
            return "Invalid value for unknown validation type: " . $validationType;
        }

        $template = $this->validationMessageTemplate[$validationType];
        
        try {
            // Attempt to format the message using vsprintf
            return vsprintf($template, $values);
        } catch (Exception $e) {
            // Catch any exception (e.g., TypeError if arguments don't match placeholders)
            // Log the error if a logging mechanism is available
            error_log(
                "ValidationUtil: Failed to format message for type '{$validationType}'. " .
                "Template: '{$template}', Values: [" . implode(', ', array_map(function($v) {
                    return is_scalar($v) ? (string)$v : gettype($v);
                }, $values)) . "]. Error: " . $e->getMessage()
            );
            
            // Return a generic fallback message to avoid breaking the application
            return "Validation error for field. (Internal message formatting issue for type: {$validationType})";
        }
    }

    /**
     * Validates the @Valid annotation for nested objects.
     * This annotation triggers recursive validation for MagicObject instances.
     *
     * @param string $propertyName The name of the property being validated, potentially including parent path.
     * @param mixed $propertyValue The current value of the property.
     * @param string $docComment The docblock comment of the property.
     * @return bool True if @Valid is present and handled (i.e., further validation for this property should be skipped).
     * @throws InvalidValueException If a nested object validation fails.
     */
    private function validateValidAnnotation($propertyName, $propertyValue, $docComment)
    {
        if (preg_match('/@Valid/', $docComment)) {
            if (is_object($propertyValue) && $propertyValue instanceof MagicObject) {
                // Pass the current full property name to the recursive call
                $this->validate($propertyValue, $propertyName);
            }
            return true; // Indicates that @Valid was processed
        }
        return false;
    }

    /**
     * Validates the @Required annotation.
     * Ensures the property value is not strictly null.
     *
     * @param string $propertyName The name of the property being validated, potentially including parent path.
     * @param mixed $propertyValue The current value of the property.
     * @param string $docComment The docblock comment of the property.
     * @throws InvalidValueException If the property value is null.
     */
    private function validateRequiredAnnotation($propertyName, $propertyValue, $docComment)
    {
        if (preg_match('/@Required(?: vigilance)?\(message="([^"]*)"\)/', $docComment, $matches)) {
            $message = isset($matches[1]) && !empty($matches[1]) ? $matches[1] : $this->createMessage('required', array($propertyName));
            if ($propertyValue === null) {
                throw new InvalidValueException($propertyName, $message);
            }
        }
    }

    /**
     * Validates the @NotEmpty annotation.
     * Ensures a string is not empty or an array is not empty.
     *
     * @param string $propertyName The name of the property being validated, potentially including parent path.
     * @param mixed $propertyValue The current value of the property.
     * @param string $docComment The docblock comment of the property.
     * @throws InvalidValueException If the string or array property value is empty.
     */
    private function validateNotEmptyAnnotation($propertyName, $propertyValue, $docComment)
    {
        if (preg_match('/@NotEmpty(?: vigilance)?\(message="([^"]*)"\)/', $docComment, $matches)) {
            $message = isset($matches[1]) && !empty($matches[1]) ? $matches[1] : $this->createMessage('notEmpty', array($propertyName));
            if ((is_string($propertyValue) && empty($propertyValue)) || (is_array($propertyValue) && empty($propertyValue))) {
                throw new InvalidValueException($propertyName, $message);
            }
        }
    }

    /**
     * Validates the @NotBlank annotation.
     * Ensures a string is not empty and not just whitespace.
     *
     * @param string $propertyName The name of the property being validated, potentially including parent path.
     * @param mixed $propertyValue The current value of the property.
     * @param string $docComment The docblock comment of the property.
     * @throws InvalidValueException If the string property value is blank.
     */
    private function validateNotBlankAnnotation($propertyName, $propertyValue, $docComment)
    {
        if (preg_match('/@NotBlank(?: vigilance)?\(message="([^"]*)"\)/', $docComment, $matches)) {
            $message = isset($matches[1]) && !empty($matches[1]) ? $matches[1] : $this->createMessage('notBlank', array($propertyName));
            if (is_string($propertyValue) && trim($propertyValue) === '') {
                throw new InvalidValueException($propertyName, $message);
            }
        }
    }

    /**
     * Validates the @Size annotation.
     * Ensures the size of a string (length) or an array (count) is within a specified range.
     *
     * @param string $propertyName The name of the property being validated, potentially including parent path.
     * @param mixed $propertyValue The current value of the property.
     * @param string $docComment The docblock comment of the property.
     * @throws InvalidValueException If the size is outside the specified range.
     */
    private function validateSizeAnnotation($propertyName, $propertyValue, $docComment)
    {
        if (preg_match('/@Size(?: vigilance)?\(min=(\d+), max=(\d+), message="([^"]*)"\)/', $docComment, $matches)) {
            $min = (int)$matches[1];
            $max = (int)$matches[2];
            $message = isset($matches[3]) && !empty($matches[3]) ? $matches[3] : $this->createMessage('size', array($propertyName, $min, $max));

            if ((is_string($propertyValue) && (strlen($propertyValue) < $min || strlen($propertyValue) > $max)) ||
                (is_array($propertyValue) && (count($propertyValue) < $min || count($propertyValue) > $max))) {
                throw new InvalidValueException($propertyName, $message);
            }
        }
    }

    /**
     * Validates the @Min annotation.
     * Ensures a numeric value is greater than or equal to a specified minimum.
     *
     * @param string $propertyName The name of the property being validated, potentially including parent path.
     * @param mixed $propertyValue The current value of the property.
     * @param string $docComment The docblock comment of the property.
     * @throws InvalidValueException If the numeric value is less than the minimum.
     */
    private function validateMinAnnotation($propertyName, $propertyValue, $docComment)
    {
        if (preg_match('/@Min(?: vigilance)?\(value=(\d+), message="([^"]*)"\)/', $docComment, $matches)) {
            $min = (int)$matches[1];
            $message = isset($matches[2]) && !empty($matches[2]) ? $matches[2] : $this->createMessage('min', array($propertyName, $min));
            if (is_numeric($propertyValue) && $propertyValue < $min) {
                throw new InvalidValueException($propertyName, $message);
            }
        }
    }

    /**
     * Validates the @Max annotation.
     * Ensures a numeric value is less than or equal to a specified maximum.
     *
     * @param string $propertyName The name of the property being validated, potentially including parent path.
     * @param mixed $propertyValue The current value of the property.
     * @param string $docComment The docblock comment of the property.
     * @throws InvalidValueException If the numeric value is greater than the maximum.
     */
    private function validateMaxAnnotation($propertyName, $propertyValue, $docComment)
    {
        if (preg_match('/@Max(?: vigilance)?\(value=(\d+), message="([^"]*)"\)/', $docComment, $matches)) {
            $max = (int)$matches[1];
            $message = isset($matches[2]) && !empty($matches[2]) ? $matches[2] : $this->createMessage('max', array($propertyName, $max));
            if (is_numeric($propertyValue) && $propertyValue > $max) {
                throw new InvalidValueException($propertyName, $message);
            }
        }
    }

    /**
     * Validates the @Pattern annotation.
     * Ensures a string matches a specified regular expression.
     *
     * @param string $propertyName The name of the property being validated, potentially including parent path.
     * @param mixed $propertyValue The current value of the property.
     * @param string $docComment The docblock comment of the property.
     * @throws InvalidValueException If the string does not match the pattern.
     */
    private function validatePatternAnnotation($propertyName, $propertyValue, $docComment)
    {
        if (preg_match('/@Pattern(?: vigilance)?\(regexp="([^"]*)", message="([^"]*)"\)/', $docComment, $matches)) {
            $regexp = str_replace('\\\\', '\\', $matches[1]);
            $message = isset($matches[2]) && !empty($matches[2]) ? $matches[2] : $this->createMessage('pattern', array($propertyName));
            if (is_string($propertyValue) && !preg_match("/{$regexp}/", $propertyValue)) {
                throw new InvalidValueException($propertyName, $message);
            }
        }
    }

    /**
     * Validates the @Email annotation.
     * Ensures a string is a well-formed email address.
     *
     * @param string $propertyName The name of the property being validated, potentially including parent path.
     * @param mixed $propertyValue The current value of the property.
     * @param string $docComment The docblock comment of the property.
     * @throws InvalidValueException If the string is not a valid email address.
     */
    private function validateEmailAnnotation($propertyName, $propertyValue, $docComment)
    {
        if (preg_match('/@Email(?: vigilance)?\(message="([^"]*)"\)/', $docComment, $matches)) {
            $message = isset($matches[1]) && !empty($matches[1]) ? $matches[1] : $this->createMessage('email', array($propertyName));
            if (is_string($propertyValue) && !filter_var($propertyValue, FILTER_VALIDATE_EMAIL)) {
                throw new InvalidValueException($propertyName, $message);
            }
        }
    }

    /**
     * Validates the @Past annotation.
     * Ensures a date is in the past. Supports DateTimeInterface, int (timestamp), and string date formats.
     *
     * @param string $propertyName The name of the property being validated, potentially including parent path.
     * @param mixed $propertyValue The current value of the property.
     * @param string $docComment The docblock comment of the property.
     * @throws InvalidValueException If the date is not in the past.
     */
    private function validatePastAnnotation($propertyName, $propertyValue, $docComment)
    {
        if (preg_match('/@Past(?: vigilance)?\(message="([^"]*)"\)/', $docComment, $matches)) {
            $message = isset($matches[1]) && !empty($matches[1]) ? $matches[1] : $this->createMessage('past', array($propertyName));
            $date = $this->convertToDateTime($propertyValue);
            if ($date instanceof DateTimeInterface && $date->getTimestamp() >= (new DateTime())->getTimestamp()) {
                throw new InvalidValueException($propertyName, $message);
            }
        }
    }

    /**
     * Validates the @Future annotation.
     * Ensures a date is in the future. Supports DateTimeInterface, int (timestamp), and string date formats.
     *
     * @param string $propertyName The name of the property being validated, potentially including parent path.
     * @param mixed $propertyValue The current value of the property.
     * @param string $docComment The docblock comment of the property.
     * @throws InvalidValueException If the date is not in the future.
     */
    private function validateFutureAnnotation($propertyName, $propertyValue, $docComment)
    {
        if (preg_match('/@Future(?: vigilance)?\(message="([^"]*)"\)/', $docComment, $matches)) {
            $message = isset($matches[1]) && !empty($matches[1]) ? $matches[1] : $this->createMessage('future', array($propertyName));
            $date = $this->convertToDateTime($propertyValue);
            if ($date instanceof DateTimeInterface && $date->getTimestamp() <= (new DateTime())->getTimestamp()) {
                throw new InvalidValueException($propertyName, $message);
            }
        }
    }

    /**
     * Validates the @DecimalMin annotation.
     * Ensures a numeric value is greater than or equal to a specified decimal minimum.
     *
     * @param string $propertyName The name of the property being validated, potentially including parent path.
     * @param mixed $propertyValue The current value of the property.
     * @param string $docComment The docblock comment of the property.
     * @throws InvalidValueException If the numeric value is less than the specified decimal minimum.
     */
    private function validateDecimalMinAnnotation($propertyName, $propertyValue, $docComment)
    {
        if (preg_match('/@DecimalMin(?: vigilance)?\(value="([^"]*)", message="([^"]*)"\)/', $docComment, $matches)) {
            $min = (float)$matches[1];
            $message = isset($matches[2]) && !empty($matches[2]) ? $matches[2] : $this->createMessage('decimalMin', array($propertyName, $min));
            if (is_numeric($propertyValue) && (float)$propertyValue < $min) {
                throw new InvalidValueException($propertyName, $message);
            }
        }
    }

    /**
     * Validates the @DecimalMax annotation.
     * Ensures a numeric value is less than or equal to a specified decimal maximum.
     *
     * @param string $propertyName The name of the property being validated, potentially including parent path.
     * @param mixed $propertyValue The current value of the property.
     * @param string $docComment The docblock comment of the property.
     * @throws InvalidValueException If the numeric value is greater than the specified decimal maximum.
     */
    private function validateDecimalMaxAnnotation($propertyName, $propertyValue, $docComment)
    {
        if (preg_match('/@DecimalMax(?: vigilance)?\(value="([^"]*)", message="([^"]*)"\)/', $docComment, $matches)) {
            $max = (float)$matches[1];
            $message = isset($matches[2]) && !empty($matches[2]) ? $matches[2] : $this->createMessage('decimalMax', array($propertyName, $max));
            if (is_numeric($propertyValue) && (float)$propertyValue > $max) {
                throw new InvalidValueException($propertyName, $message);
            }
        }
    }

    /**
     * Validates the @Digits annotation.
     * Ensures a numeric value has at most a specified number of integer and fractional digits.
     *
     * @param string $propertyName The name of the property being validated, potentially including parent path.
     * @param mixed $propertyValue The current value of the property.
     * @param string $docComment The docblock comment of the property.
     * @throws InvalidValueException If the number of digits exceeds the specified limits.
     */
    private function validateDigitsAnnotation($propertyName, $propertyValue, $docComment)
    {
        if (preg_match('/@Digits(?: vigilance)?\(integer=(\d+), fraction=(\d+), message="([^"]*)"\)/', $docComment, $matches)) {
            $integer = (int)$matches[1];
            $fraction = (int)$matches[2];
            $message = isset($matches[3]) && !empty($matches[3]) ? $matches[3] : $this->createMessage('digits', array($propertyName, $integer, $fraction));

            if (is_numeric($propertyValue)) {
                $parts = explode('.', (string)$propertyValue);
                $integerPart = $parts[0];
                $fractionalPart = isset($parts[1]) ? $parts[1] : '';

                if (strlen($integerPart) > $integer || strlen($fractionalPart) > $fraction) {
                    throw new InvalidValueException($propertyName, $message);
                }
            }
        }
    }

    /**
     * Validates the @AssertTrue annotation.
     * Ensures a boolean property value is strictly true.
     *
     * @param string $propertyName The name of the property being validated, potentially including parent path.
     * @param mixed $propertyValue The current value of the property.
     * @param string $docComment The docblock comment of the property.
     * @throws InvalidValueException If the boolean value is not true.
     */
    private function validateAssertTrueAnnotation($propertyName, $propertyValue, $docComment)
    {
        if (preg_match('/@AssertTrue(?: vigilance)?\(message="([^"]*)"\)/', $docComment, $matches)) {
            $message = isset($matches[1]) && !empty($matches[1]) ? $matches[1] : $this->createMessage('assertTrue', array($propertyName));
            if ($propertyValue !== true) {
                throw new InvalidValueException($propertyName, $message);
            }
        }
    }

    /**
     * Validates the @Null annotation.
     * Ensures a property value is strictly null.
     *
     * @param string $propertyName The name of the property being validated, potentially including parent path.
     * @param mixed $propertyValue The current value of the property.
     * @param string $docComment The docblock comment of the property.
     * @throws InvalidValueException If the property value is not null.
     */
    private function validateNullAnnotation($propertyName, $propertyValue, $docComment)
    {
        if (preg_match('/@Null(?: vigilance)?\(message="([^"]*)"\)/', $docComment, $matches)) {
            $message = isset($matches[1]) && !empty($matches[1]) ? $matches[1] : $this->createMessage('null', array($propertyName));
            if ($propertyValue !== null) {
                throw new InvalidValueException($propertyName, $message);
            }
        }
    }

    /**
     * Validates the @FutureOrPresent annotation.
     * Ensures a date is in the future or the present. Supports DateTimeInterface, int (timestamp), and string date formats.
     *
     * @param string $propertyName The name of the property being validated, potentially including parent path.
     * @param mixed $propertyValue The current value of the property.
     * @param string $docComment The docblock comment of the property.
     * @throws InvalidValueException If the date is in the past.
     */
    private function validateFutureOrPresentAnnotation($propertyName, $propertyValue, $docComment)
    {
        if (preg_match('/@FutureOrPresent(?: vigilance)?\(message="([^"]*)"\)/', $docComment, $matches)) {
            $message = isset($matches[1]) && !empty($matches[1]) ? $matches[1] : $this->createMessage('futureOrPresent', array($propertyName));
            $date = $this->convertToDateTime($propertyValue);
            if ($date instanceof DateTimeInterface && $date->getTimestamp() < (new DateTime())->getTimestamp()) {
                throw new InvalidValueException($propertyName, $message);
            }
        }
    }

    /**
     * Validates the @Length annotation.
     * Ensures the length of a string is within a specified range. This is specific to strings.
     *
     * @param string $propertyName The name of the property being validated, potentially including parent path.
     * @param mixed $propertyValue The current value of the property.
     * @param string $docComment The docblock comment of the property.
     * @throws InvalidValueException If the string length is outside the specified range.
     */
    private function validateLengthAnnotation($propertyName, $propertyValue, $docComment)
    {
        if (preg_match('/@Length(?: vigilance)?\(min=(\d+), max=(\d+), message="([^"]*)"\)/', $docComment, $matches)) {
            $min = (int)$matches[1];
            $max = (int)$matches[2];
            $message = isset($matches[3]) && !empty($matches[3]) ? $matches[3] : $this->createMessage('length', array($propertyName, $min, $max));
            if (is_string($propertyValue) && (strlen($propertyValue) < $min || strlen($propertyValue) > $max)) {
                throw new InvalidValueException($propertyName, $message);
            }
        }
    }

    /**
     * Validates the @Range annotation.
     * Ensures a numeric value is within a specified inclusive range.
     *
     * @param string $propertyName The name of the property being validated, potentially including parent path.
     * @param mixed $propertyValue The current value of the property.
     * @param string $docComment The docblock comment of the property.
     * @throws InvalidValueException If the numeric value is outside the specified range.
     */
    private function validateRangeAnnotation($propertyName, $propertyValue, $docComment)
    {
        if (preg_match('/@Range(?: vigilance)?\(min=(\d+), max=(\d+), message="([^"]*)"\)/', $docComment, $matches)) {
            $min = (int)$matches[1];
            $max = (int)$matches[2];
            $message = isset($matches[3]) && !empty($matches[3]) ? $matches[3] : $this->createMessage('range', array($propertyName, $min, $max));
            if (is_numeric($propertyValue) && ($propertyValue < $min || $propertyValue > $max)) {
                throw new InvalidValueException($propertyName, $message);
            }
        }
    }

    /**
     * Validates the @NoHtml annotation.
     * Ensures a string does not contain any HTML tags.
     *
     * @param string $propertyName The name of the property being validated, potentially including parent path.
     * @param mixed $propertyValue The current value of the property.
     * @param string $docComment The docblock comment of the property.
     * @throws InvalidValueException If the string contains HTML tags.
     */
    private function validateNoHtmlAnnotation($propertyName, $propertyValue, $docComment)
    {
        if (preg_match('/@NoHtml(?: vigilance)?\(message="([^"]*)"\)/', $docComment, $matches)) {
            $message = isset($matches[1]) && !empty($matches[1]) ? $matches[1] : $this->createMessage('noHtml', array($propertyName));
            if (is_string($propertyValue) && strip_tags($propertyValue) !== $propertyValue) {
                throw new InvalidValueException($propertyName, $message);
            }
        }
    }

    /**
     * Validates the @ValidEnum annotation.
     * Ensures a string value is one of the allowed values. Supports case-sensitive or case-insensitive matching.
     *
     * @param string $propertyName The name of the property being validated, potentially including parent path.
     * @param mixed $propertyValue The current value of the property.
     * @param string $docComment The docblock comment of the property.
     * @throws InvalidValueException If the string value is not found in the allowed values list.
     */
    private function validateValidEnumAnnotation($propertyName, $propertyValue, $docComment) // NOSONAR
    {
        if (preg_match('/@ValidEnum(?: vigilance)?\(message="([^"]*)", allowedValues=\{([^}]+)\}(?:, caseSensitive=(true|false))?\)/', $docComment, $matches)) {
            $message = isset($matches[1]) && !empty($matches[1]) ? $matches[1] : $this->createMessage('validEnum', array($propertyName));
            $allowedValuesRaw = explode(',', $matches[2]);
            $allowedValues = array_map(function($value) {
                return trim($value, ' "');
            }, $allowedValuesRaw);

            $caseSensitive = true;
            if (isset($matches[3])) {
                $caseSensitive = ($matches[3] === 'true');
            }

            $isValid = false;
            if (is_string($propertyValue)) {
                if ($caseSensitive) {
                    $isValid = in_array($propertyValue, $allowedValues, true);
                } else {
                    $lowerPropertyValue = strtolower($propertyValue);
                    foreach ($allowedValues as $allowedValue) {
                        if (strtolower($allowedValue) === $lowerPropertyValue) {
                            $isValid = true;
                            break;
                        }
                        // Also check if numeric and matches any allowed value (e.g., "1" matches 1)
                        if (is_numeric($lowerPropertyValue) && is_numeric($allowedValue) && (float)$lowerPropertyValue === (float)$allowedValue) {
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