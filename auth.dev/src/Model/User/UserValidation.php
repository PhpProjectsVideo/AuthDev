<?php

namespace PhpProjects\AuthDev\Model\User;
use PhpProjects\AuthDev\Model\ValidationResults;

/**
 * A service to ensure that a user is valid;
 */
class UserValidation
{

    /**
     * Returns the validation results for a given user.
     * 
     * @param UserEntity $user
     * @return ValidationResults
     */
    public function validate(UserEntity $user) : ValidationResults
    {
        $validationMessages = [];

        if (empty($user->getUsername()))
        {
            $validationMessages['username'][] = 'Username is required';
        }
        elseif (!preg_match('/^[a-zA-Z0-9._-]{1,50}$/', $user->getUsername()))
        {
            $validationMessages['username'][] = 'Usernames must be less than 50 characters and can only contain a-z, A-Z, 0-9 or the characters . _ and -.';
        }

        if (empty($user->getEmail()))
        {
            $validationMessages['email'][] = 'Email is required';
        }
        elseif (!filter_var($user->getEmail(), FILTER_VALIDATE_EMAIL))
        {
            $validationMessages['email'][] = 'You must enter a valid email. Please try another.';
        }

        if (empty($user->getName()))
        {
            $validationMessages['name'][] = 'Name is required';
        }
        elseif (strlen($user->getName()) > 100)
        {
            $validationMessages['name'][] = 'Names can only be up to 100 characters long.';
        }
        
        if (empty($user->getPasswordHash()))
        {
            $validationMessages['password'][] = 'Password is required';
        }
        
        return new ValidationResults($validationMessages);
    }
}