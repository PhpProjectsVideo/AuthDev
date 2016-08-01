<?php

namespace PhpProjects\AuthDev\Model\User;

use Exception;
use SebastianBergmann\GlobalState\RuntimeException;

/**
 * Thrown when an operation in UserRepository would result in a duplicate user in our database.
 * 
 * @package PhpProjects\AuthDev\Model\User
 */
class DuplicateUserException extends RuntimeException
{
    /**
     * @var string
     */
    private $field;
    
    public function __construct(string $field, Exception $exception = null)
    {
        parent::__construct("The given {$field} already exists", 0, $exception);
        $this->field = $field;
    }

    /**
     * The field where the duplicate existed.
     * 
     * @return string
     */
    public function getField() : string
    {
        return $this->field;
    }
}