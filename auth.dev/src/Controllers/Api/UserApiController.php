<?php

namespace PhpProjects\AuthDev\Controllers\Api;

use PhpProjects\AuthDev\Authentication\LoginService;
use PhpProjects\AuthDev\Controllers\ContentNotFoundException;
use PhpProjects\AuthDev\Model\DuplicateEntityException;
use PhpProjects\AuthDev\Model\User\UserEntity;
use PhpProjects\AuthDev\Model\User\UserRepository;
use PhpProjects\AuthDev\Model\User\UserValidation;
use PhpProjects\AuthDev\Model\ValidationResults;
use PhpProjects\AuthDev\Views\ViewService;

/**
 * Provides basic funcationality for a simple crud controller.
 *
 * When using this controller you will be able to add, remove, delete, and update your entites. To ensure that all
 * fields are properly edited you will be responsible to ensure those fields are present in templates and in the
 * entity, but so long as you have a SimpleCrudRepository for the entity as well, much of the boiler plate backend
 * code will be handled for you.
 */
class UserApiController
{
    /**
     * @var ViewService
     */
    private $viewService;

    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var UserValidation
     */
    private $userValidation;

    /**
     * @var LoginService
     */
    private $loginService;

    /**
     * @param ViewService $viewService
     * @param UserRepository $userRepository
     * @param UserValidation $userValidation
     * @param LoginService $loginService
     */
    public function __construct(ViewService $viewService, UserRepository $userRepository, UserValidation $userValidation, LoginService $loginService)
    {
        $this->viewService = $viewService;
        $this->userRepository = $userRepository;
        $this->userValidation = $userValidation;
        $this->loginService = $loginService;
    }
    
    public static function create()
    {
        return new self(ViewService::create(), UserRepository::create(), new UserValidation(), LoginService::create());
    }

    /**
     * Displays a list of entities in the system
     *
     * @param int $currentPage
     * @param string $query
     */
    public function getList(int $currentPage = 1, string $query = '')
    {
        if (!$this->checkForPermission('Administrator'))
        {
            return;
        }

        if (empty($query))
        {
            $users = $this->userRepository->getSortedList(10, ($currentPage - 1) * 10);
            $userCount = $this->userRepository->getCount();
        }
        else
        {
            $users = $this->userRepository->getListMatchingFriendlyName($query, 10, ($currentPage - 1) * 10);
            $userCount = $this->userRepository->getCountMatchingFriendlyName($query);
        }

        $templateData = [
            'users' => $users,
            'currentPage' => $currentPage,
            'totalPages' => $userCount > 0 ? (int)(($userCount - 1) / 10) + 1 : 1,
            'term' => $query
        ];

        $this->viewService->renderView('api/users/list', $templateData);
    }
    /**
     * Displays info about $id
     *
     * @param mixed $id
     */
    public function getUser($id)
    {
        if (!$this->checkForPermission('Administrator'))
        {
            return;
        }

        $user = $this->userRepository->getById($id);

        if (empty($user))
        {
            throw (new ContentNotFoundException("I could not locate the user {$id}."))
                ->setTitle('User Not Found')
                ->setRecommendedUrl('/api/users')
                ->setRecommendedAction("View All Users");
        }

        $this->viewService->renderView('api/users/detail', [
            'user' => $user
        ]);
    }

    /**
     * Posts a form for a new entity.
     *
     * @param array $userData
     */
    public function createUser(array $userData)
    {
        if (!$this->checkForPermission('Administrator'))
        {
            return;
        }

        $user = UserEntity::createFromArray($userData);

        $this->saveEntity($user);
    }

    /**
     * Posts a form editing an existing entity
     *
     * @param mixed $id
     * @param array $userData
     */
    public function editUser($id, array $userData)
    {
        if (!$this->checkForPermission('Administrator'))
        {
            return;
        }

        $user = $this->userRepository->getById($id);

        if (empty($user))
        {
            throw (new ContentNotFoundException("I could not locate the user {$id}."))
                ->setTitle('User Not Found')
                ->setRecommendedUrl('/api/users')
                ->setRecommendedAction("View All Userss");
        }

        $user->updateFromArray($userData);

        $this->saveEntity($user);
    }

    /**
     * Handles saving new or old entities to the system
     *
     * @param mixed $user
     */
    protected function saveEntity(UserEntity $user)
    {
        $validationResults = $this->userValidation->validate($user);

        try
        {
            if ($validationResults->isValid())
            {
                $isNew = !$user->getId();
                $this->userRepository->saveEntity($user);
                if ($isNew)
                {
                    $this->viewService->redirect('/api/users/user/' . urlencode($user->getId()), 201);
                }
                $this->viewService->renderView('api/users/detail', [
                    'user' => $user
                ]);
                return;
            }
        }
        catch (DuplicateEntityException $e)
        {
            $validationResults = new ValidationResults([ $e->getField() => [ "This {$e->getField()} is already registered. Please try another." ] ]);
        }
        $this->viewService->renderHeader('HTTP/1.0 400 Bad Request');
        $allErrorMessages = $validationResults->getAllErrorMessages();
        echo json_encode([
            'errors' => $allErrorMessages
        ]);
    }

    /**
     * Handles updating a user's group memberships
     *
     * @param mixed $id
     * @param array $postData
     */
    public function editUserGroups($id, array $postData)
    {
        if (!$this->checkForPermission('Administrator'))
        {
            return;
        }

        $user = $this->userRepository->getById($id);

        if (empty($user))
        {
            throw (new ContentNotFoundException("I could not locate the user {$id}."))
                ->setTitle('User Not Found')
                ->setRecommendedUrl('/api/users')
                ->setRecommendedAction('View All Users');
        }
        
        $user->setGroups($postData['groupIds']);

        $this->userRepository->saveEntity($user);
        $this->viewService->redirect('/api/users/user/' . urlencode($user->getId()), 201);
        $this->viewService->renderView('api/users/detail', [
            'user' => $user
        ]);
    }
    
    /**
     * Removes the entity provided
     * @param mixed $id
     */
    public function deleteUser($id)
    {
        if (!$this->checkForPermission('Administrator'))
        {
            return;
        }


        $user = $this->userRepository->getById($id);

        if (empty($user))
        {
            throw (new ContentNotFoundException("I could not locate the user {$id}."))
                ->setTitle('User Not Found')
                ->setRecommendedUrl('/api/users')
                ->setRecommendedAction('View All Users');
        }
        
        $this->userRepository->deleteByFriendlyNames([ $user->getUsername() ]);

        echo json_encode([
            'links' => [
                'list' => '/api/users',
                'create' => '/api/users/user',
            ],
        ]);
    }

    /**
     * A null implementation of checkForPermission to always approve permission checks.
     *
     * @param string $permission
     * @return bool
     */
    protected function checkForPermission(string $permission) : bool
    {
        $result = $this->loginService->attemptAuthentication($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']);
        
        if (!$result->isValid())
        {
            $this->viewService->renderHeader('HTTP/1.0 401 Unauthorized');
            return false;
        }
        elseif (!$this->loginService->userHasPermission($_SERVER['PHP_AUTH_USER'], $permission))
        {
            $this->viewService->renderHeader('HTTP/1.0 403 Forbidden');
            return false;
        }
        return true;
    }
}