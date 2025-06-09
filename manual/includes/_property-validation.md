## Property Validation

The `ValidationUtil` class has been significantly enhanced to provide a robust and flexible object property validation mechanism. Inspired by Jakarta Bean Validation (JSR 380), developers can now apply a comprehensive set of annotations directly in property docblocks to enforce data integrity.

The following validation annotations are now supported:

-   **`@Valid`**: Recursively validates nested `MagicObject` instances.
-   **`@Required(message="...")`**: Ensures the property value is not `null`.
-   **`@NotEmpty(message="...")`**: Checks if a string is not empty (`""`) or an array is not empty.
-   **`@NotBlank(message="...")`**: Validates that a string is not empty and not just whitespace characters.
-   **`@Size(min=X, max=Y, message="...")`**: Verifies that the length of a string or the count of an array is within a specified range.
-   **`@Min(value=X, message="...")`**: Asserts that a numeric property's value is greater than or equal to a minimum value.
-   **`@Max(value=X, message="...")`**: Asserts that a numeric property's value is less than or equal to a maximum value.
-   **`@Pattern(regexp="...", message="...")`**: Validates a string property against a specified regular expression.
-   **`@Email(message="...")`**: Checks if a string property is a well-formed email address.
-   **`@Past(message="...")`**: Ensures a `DateTimeInterface` property represents a date/time in the past.
-   **`@Future(message="...")`**: Ensures a `DateTimeInterface` property represents a date/time in the future.
-   **`@DecimalMin(value="...", message="...")`**: Validates that a numeric property (can be float/string) is greater than or equal to a specified decimal value.
-   **`@DecimalMax(value="...", message="...")`**: Validates that a numeric property (can be float/string) is less than or equal to a specified decimal value.
-   **`@Digits(integer=X, fraction=Y, message="...")`**: Checks that a numeric property has at most `X` integer digits and `Y` fractional digits.
-   **`@AssertTrue(message="...")`**: Asserts that a boolean property's value is strictly `true`.
-   **`@FutureOrPresent(message="...")`**: Ensures a `DateTimeInterface` property represents a date/time in the future or the present.
-   **`@Length(min=X, max=Y, message="...")`**: Similar to `@Size`, specifically for string lengths within a range.
-   **`@Range(min=X, max=Y, message="...")`**: Validates that a numeric property's value falls within an inclusive range.
-   **`@NoHtml(message="...")`**: Checks if a string property contains any HTML tags.
-   **`@Enum(message="...", allowedValues={...}, caseSensitive=true|false)`**: Ensures a string property's value is one of a predefined set of allowed values, with an option for case-sensitive or case-insensitive comparison.

These new validation capabilities provide a declarative and robust way to ensure data consistency and reduce boilerplate validation code in your MagicObject entities.


**Class `UserProfile`**

```php
// Define an example entity class demonstrating various validations
class UserProfile extends MagicObject
{
    /**
     * @Required(message="Username cannot be null")
     * @NotBlank(message="Username cannot be blank")
     * @Length(min=4, max=20, message="Username must be 4-20 characters long")
     * @Pattern(regexp="^[a-zA-Z0-9_]+$", message="Username can only contain letters, numbers, and underscores")
     * @var string
     */
    protected $username;

    /**
     * @Email(message="Invalid email address format")
     * @Required(message="Email cannot be null")
     * @var string
     */
    protected $email;

    /**
     * @Min(value=18, message="Age must be at least 18")
     * @Max(value=99, message="Age cannot exceed 99")
     * @var int
     */
    protected $age;

    /**
     * @NoHtml(message="About Me field contains unsupported HTML tags")
     * @Size(max=500, message="About Me cannot exceed 500 characters")
     * @var string
     */
    protected $aboutMe;

    /**
     * @Past(message="Birth date must be in the past")
     * @var DateTime
     */
    protected $birthDate;

    /**
     * @Enum(message="Gender must be 'Male' or 'Female'", allowedValues={"Male", "Female"})
     * @var string
     */
    protected $gender;

    /**
     * @Enum(message="Status must be 'active', 'inactive', or 'pending'", allowedValues={"active", "inactive", "pending"}, caseSensitive=false)
     * @var string
     */
    protected $status;

    // A nested object to demonstrate @Valid
    /**
     * @Valid
     * @var Address
     */
    protected $address;
```

**Class `Address`**

```php
class Address extends MagicObject
{
    /**
     * @Required(message="Street cannot be null")
     * @NotBlank(message="Street cannot be blank")
     * @var string
     */
    protected $street;

    /**
     * @Required(message="City cannot be null")
     * @NotBlank(message="City cannot be blank")
     * @var string
     */
    protected $city;
}
```

**Validation**

```php
// Test 1: Valid user profile
try {
    $user = new UserProfile();
    $user->setUsername("john_doe");
    $user->setEmail("john.doe@example.com");
    $user->setAge(30);
    $user->setAboutMe("Hello, I am John Doe. I like programming.");
    $user->setBirthDate(new DateTime('1995-01-15'));
    $user->setGender("Male");
    $user->setStatus("active");

    $address = new Address();
    $address->setStreet("123 Main St");
    $address->setCity("Anytown");
    $user->setAddress($address);

    ValidationUtil::getInstance()->validate($user);
    echo "Test 1: Valid User Profile - PASSED.\n";
} catch (InvalidValueException $e) {
    echo "Test 1: FAILED (Unexpected) - " . $e->getPropertyName() . ": " . $e->getValidationMessage() . "\n";
}

// Test 2: Invalid username (too short)
try {
    $user = new UserProfile();
    $user->setUsername("joh"); // Too short
    $user->setEmail("john.doe@example.com");
    $user->setAge(30);
    $user->setAboutMe("Hello, I am John Doe.");
    $user->setBirthDate(new DateTime('1995-01-15'));
    $user->setGender("Male");
    $user->setStatus("active");
    
    $address = new Address();
    $address->setStreet("123 Main St");
    $address->setCity("Anytown");
    $user->setAddress($address);

    ValidationUtil::getInstance()->validate($user);
    echo "Test 2: Valid User Profile (should have failed) - PASSED.\n";
} catch (InvalidValueException $e) {
    echo "Test 2: FAILED (Expected) - " . $e->getPropertyName() . ": " . $e->getValidationMessage() . "\n";
}

// Test 3: Invalid email format
try {
    $user = new UserProfile();
    $user->setUsername("jane_doe");
    $user->setEmail("invalid-email"); // Invalid format
    $user->setAge(25);
    $user->setAboutMe("Hello, I am Jane Doe.");
    $user->setBirthDate(new DateTime('1999-03-20'));
    $user->setGender("Female");
    $user->setStatus("inactive");

    $address = new Address();
    $address->setStreet("123 Main St");
    $address->setCity("Anytown");
    $user->setAddress($address);

    ValidationUtil::getInstance()->validate($user);
    echo "Test 3: Valid User Profile (should have failed) - PASSED.\n";
} catch (InvalidValueException $e) {
    echo "Test 3: FAILED (Expected) - " . $e->getPropertyName() . ": " . $e->getValidationMessage() . "\n";
}

// Test 4: Invalid Enum value (case-sensitive)
try {
    $user = new UserProfile();
    $user->setUsername("test_user");
    $user->setEmail("test@example.com");
    $user->setAge(40);
    $user->setAboutMe("Some text.");
    $user->setBirthDate(new DateTime('1980-05-01'));
    $user->setGender("male"); // Should fail due to case-sensitivity (expected "Male")
    $user->setStatus("pending");

    $address = new Address();
    $address->setStreet("123 Main St");
    $address->setCity("Anytown");
    $user->setAddress($address);

    ValidationUtil::getInstance()->validate($user);
    echo "Test 4: Valid User Profile (should have failed) - PASSED.\n";
} catch (InvalidValueException $e) {
    echo "Test 4: FAILED (Expected) - " . $e->getPropertyName() . ": " . $e->getValidationMessage() . "\n";
}

// Test 5: Nested validation failure
try {
    $user = new UserProfile();
    $user->setUsername("test_nest");
    $user->setEmail("nest@example.com");
    $user->setAge(22);
    $user->setAboutMe("Testing nested validation.");
    $user->setBirthDate(new DateTime('2000-01-01'));
    $user->setGender("Female");
    $user->setStatus("active");

    $address = new Address();
    $address->setStreet(""); // Blank street, should fail @NotBlank
    $address->setCity("Anytown");
    $user->setAddress($address);

    ValidationUtil::getInstance()->validate($user);
    echo "Test 5: Valid User Profile (should have failed due to nested object) - PASSED.\n";
} catch (InvalidValueException $e) {
    echo "Test 5: FAILED (Expected due to nested object) - " . $e->getPropertyName() . ": " . $e->getValidationMessage() . "\n";
}
```