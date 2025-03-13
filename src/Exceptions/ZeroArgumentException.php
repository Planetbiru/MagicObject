<?php
namespace MagicObject\Exceptions;

use InvalidArgumentException;
use Throwable;

/**
 * Class ZeroArgumentException
 *
 * Custom exception class for handling cases where a method or function 
 * is called with zero arguments when at least one is expected. This 
 * exception extends the built-in InvalidArgumentException to provide 
 * more specific error handling for invalid input scenarios. 
 * It can help in identifying issues related to argument validation 
 * in function calls, ensuring that methods are invoked correctly 
 * with the necessary parameters.
 * 
 * @author Kamshory
 * @package MagicObject\Exceptions
 * @link https://github.com/Planetbiru/MagicObject
 */
class ZeroArgumentException extends InvalidArgumentException
{
    /**
     * Previous exception
     *
     * @var Throwable|null The previous exception
     */
    private $previous;

    /**
     * Constructor for ZeroArgumentException.
     *
     * @param string $message  Exception message
     * @param int $code        Exception code
     * @param Throwable|null $previous Previous exception
     */
    public function __construct($message, $code = 0, $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->previous = $previous;
    }

    /**
     * Get the previous exception.
     *
     * @return Throwable|null The previous exception
     */
    public function getPreviousException()
    {
        return $this->previous;
    }
}
