<?php

use MagicObject\MagicObject;

require_once dirname(__DIR__) . "/vendor/autoload.php";

class CountryValidator extends MagicObject
{
    /**
     * @Size(min=2, max=50)
     * @var string
     */
    protected $name;

    /**
     * @Size(min=2, max=3)
     * @var string
     */
    protected $code;
}

class Country extends MagicObject
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $code;
}

class AddressValidator extends MagicObject
{
    /**
     * @Size(min=5, max=100)
     * @var string
     */
    protected $street;

    /**
     * @Size(min=2, max=50)
     * @var string
     */
    protected $city;

    /**
     * @Valid
     * @var CountryValidator
     */
    protected $country;
}

class Address extends MagicObject
{
    /**
     * @var string
     */
    protected $street;

    /**
     * @var string
     */
    protected $city;

    /**
     * @var Country
     */
    protected $country;
    
}

class RegistrationValidator extends MagicObject
{
    /**
     * @Size(min=5, max=50)
     * @var string
     */
    protected $name;

    /**
     * @Email
     * @var string
     */
    protected $email;

    /**
     * @Range(min=22.6, max=80.5)
     * @var float
     */
    protected $age;

    /**
     * @Enum(allowedValues={"male", "female", "other"}, message="Gender must be 'male', 'female', or 'other'", caseSensitive=false)
     * @var string
     */
    protected $gender;
    
    /**
     * @Required
     * @Valid
     * @var AddressValidator
     */
    protected $address;
}


/**
 * Registration class
 *
 * This class represents a registration form with various fields that are validated
 * using annotations. It extends the MagicObject class to leverage
 */
class Registration extends MagicObject
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $email;

    /**
     * @var float
     */
    protected $age;

    /**
     * @var string
     */
    protected $gender;
    
    /**
     * @var Address
     */
    protected $address;
}

$input = new Registration();
$input->setName('John Doe'); // This will fail the Size validation (min 15)
$input->setEmail('any');     // This will fail the Email validation
$input->setAge(25.5);
$input->setEmail("any@any.any"); // This will pass the Email validation
$input->setGender('Male'); // If you uncomment this, it will fail Enum validation
/*
$input->setAddress(new Address()); // This will pass the Valid annotation
$input->getAddress()->setStreet('123 Main St'); // This will pass the Size validation
$input->getAddress()->setCity('Springfield'); // This will pass the Size validation
$input->getAddress()->setCountry(new Country()); // This will pass the Size validation
$input->getAddress()->getCountry()->setName('USA'); // This will pass the Size validation
$input->getAddress()->getCountry()->setCode('US'); // This will pass the Size validation
*/

// Validate the input using the annotations defined in the Registration class itself
try {
    $input->validate(null, null, new RegistrationValidator()); // Call validate on the object that has the annotations
    echo "Validation successful!\n";
} catch (Exception $e) {
    echo "Validation failed: " . $e->getMessage() . "\n";
}
