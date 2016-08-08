<?php

namespace PhpProjects\AuthDev\Controllers;

use PhpProjects\AuthDev\Model\Csrf\CsrfService;
use PhpProjects\AuthDev\Model\DuplicateEntityException;
use PhpProjects\AuthDev\Model\Group\GroupRepository;
use PhpProjects\AuthDev\Model\User\UserEntity;
use PhpProjects\AuthDev\Model\User\UserRepository;
use PhpProjects\AuthDev\Model\User\UserValidation;
use PhpProjects\AuthDev\Model\ValidationResults;
use PhpProjects\AuthDev\Views\ViewService;

/**
 * Manages operations against the user portion of our domain model
 */
class UserController extends SimpleCrudController
{
    /**
     * @var UserValidation
     */
    private $userValidation;

    /**
     * @var GroupRepository
     */
    private $groupRepository;

    /**
     * @param ViewService $viewService
     * @param UserRepository $userRepository
     * @param UserValidation $userValidation
     * @param GroupRepository $groupRepository
     * @param CsrfService $csrfService
     */
    public function __construct(ViewService $viewService, UserRepository $userRepository, UserValidation $userValidation, GroupRepository $groupRepository, CsrfService $csrfService)
    {
        $this->userValidation = $userValidation;
        $this->groupRepository = $groupRepository;
        parent::__construct($viewService, $userRepository, $csrfService);
    }

    /**
     * Convenience Constructor
     *
     * @return UserController
     */
    public static function create() : UserController
    {
        return new self(ViewService::create(), UserRepository::create(), new UserValidation(), GroupRepository::create(), CsrfService::create());
    }

    /**
     * The folder in views containing the crud templates:
     * * form.php
     * * list.php
     * * removeList.php
     *
     * @return string
     */
    protected function getTemplateFolder() : string
    {
        return 'users';
    }

    /**
     * The base url for the crud pages in this controller.
     *
     * @return string
     */
    protected function getBaseUrl() : string
    {
        return '/users/';
    }

    /**
     * Creates a new entity with no data
     *
     * @return mixed
     */
    protected function getNewEntity()
    {
        return new UserEntity();
    }

    /**
     * The name that the entity will be referred to in this controller.
     *
     * @return string
     */
    protected function getEntityTitle() : string
    {
        return 'User';
    }

    /**
     * Create an entity based off of the array of data
     *
     * @param array $entityData
     * @return mixed
     */
    protected function getEntityFromData(array $entityData)
    {
        return UserEntity::createFromArray($entityData);
    }

    /**
     * Returns a validation result for the given entity.
     *
     * @param $entity
     * @return ValidationResults
     */
    protected function validateEntity($entity) : ValidationResults
    {
        return $this->userValidation->validate($entity);
    }

    /**
     * Returns a friendly name by which to refer to a given entity.
     *
     * @param mixed $entity
     * @return string
     */
    protected function getEntityFriendlyName($entity) : string
    {
        return $entity->getUsername();
    }
    
    protected function onGetDetailRender(array $templateData) : array
    {
        if (!empty($redirectMessage = $this->viewService->getRedirectMessage()))
        {
            $templateData['message'] = $redirectMessage;
            $templateData['messageStatus'] =  $this->viewService->getRedirectStatus() ?? 'default';
        }
        $templateData['groups'] = iterator_to_array($this->groupRepository->getSortedList());
        return $templateData;
    }

    protected function onSaveValidation(ValidationResults $validationResults, $entity, array $entityData) : bool
    {
        if (!empty($entityData['clear-password']) && $entityData['clear-password'] != $entityData['clear-password-confirm'])
        {
            $validationResults->addErrorForField('password', 'Passwords must match');
            $entity->setClearTextPassword('');
            return false;
        }

        return true;
    }

    /**
     * Handles updating a user's group memberships
     * 
     * @param string $username
     * @param array $postData
     */
    public function postUpdateGroups(string $username, array $postData)
    {
        if (!$this->csrfService->validateToken($postData['token'] ?? ''))
        {
            $this->viewService->redirect('/users/detail/' . urlencode($username), 303, "Your session has expired, please try updating groups again", 'danger');
        }
        else
        {
            $user = $this->crudRepository->getByFriendlyName($username);

            if (empty($user))
            {
                throw (new ContentNotFoundException("I could not locate the user {$username}."))
                    ->setTitle('User Not Found')
                    ->setRecommendedUrl('/users/')
                    ->setRecommendedAction('View All Users');
            }
            if ($postData['operation'] == 'add')
            {
                $user->addGroups($postData['groupIds'] ?? []);
            }
            elseif ($postData['operation'] == 'remove')
            {
                $user->removeGroups($postData['groupIds'] ?? []);
            }

            $this->crudRepository->saveEntity($user);

            $this->viewService->redirect('/users/detail/' . urlencode($username), 303, "Your groups have been updated", 'success');
        }
    }


}