<?php

namespace PhpProjects\AuthDev\Model;

/**
 * Stores validation messages and results for an entity.
 * 
 * @package PhpProjects\AuthDev\Model
 */
class ValidationResults
{
    /**
     * @var array
     */
    private $validationMessages;

    /**
     * @param array $validationMessages
     */
    public function __construct(array $validationMessages)
    {
        $this->validationMessages = $validationMessages;
    }

    /**
     * Returns an array of errors for a given field
     * 
     * @param string $field
     * @return array
     */
    public function getValidationErrorsForField(string $field) : array
    {
        return $this->validationMessages[$field] ?? [];
    }

    /**
     * Returns true if the entity was valid
     * 
     * @return bool
     */
    public function isValid() : bool 
    {
        return empty($this->validationMessages);
    }

    /**
     * Adds an error to a specific field in the validation results.
     * 
     * @param string $field
     * @param string $message
     */
    public function addErrorForField(string $field, string $message)
    {
        $this->validationMessages[$field][] = $message;
    }

    /**
     * Returns all error messages as an array.
     * @return array
     */
    public function getAllErrorMessages() : array
    {
        return array_merge(...array_values($this->validationMessages));
    }
}