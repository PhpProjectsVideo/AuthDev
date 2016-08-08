<?php

namespace PhpProjects\AuthDev\Model\Group;
use PhpProjects\AuthDev\Model\ValidationResults;

/**
 * A service to ensure that a group is valid;
 */
class GroupValidation
{

    /**
     * Returns the validation results for a given group.
     *
     * @param GroupEntity $group
     * @return ValidationResults
     */
    public function validate(GroupEntity $group) : ValidationResults
    {
        $validationMessages = [];

        if (empty($group->getName()))
        {
            $validationMessages['name'][] = 'Name is required';
        }
        elseif (strlen($group->getName()) > 100)
        {
            $validationMessages['name'][] = 'Names can only be up to 100 characters long.';
        }

        return new ValidationResults($validationMessages);
    }
}