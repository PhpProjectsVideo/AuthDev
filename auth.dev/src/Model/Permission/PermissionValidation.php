<?php

namespace PhpProjects\AuthDev\Model\Permission;
use PhpProjects\AuthDev\Model\ValidationResults;

/**
 * A service to ensure that a permission is valid;
 */
class PermissionValidation
{

    /**
     * Returns the validation results for a given permission.
     *
     * @param PermissionEntity $permission
     * @return ValidationResults
     */
    public function validate(PermissionEntity $permission) : ValidationResults
    {
        $validationMessages = [];

        if (empty($permission->getName()))
        {
            $validationMessages['name'][] = 'Name is required';
        }
        elseif (strlen($permission->getName()) > 100)
        {
            $validationMessages['name'][] = 'Names can only be up to 100 characters long.';
        }

        return new ValidationResults($validationMessages);
    }
}