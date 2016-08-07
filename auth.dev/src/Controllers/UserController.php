<?php

namespace PhpProjects\AuthDev\Controllers;

use PhpProjects\AuthDev\Model\Csrf\CsrfService;
use PhpProjects\AuthDev\Model\User\DuplicateUserException;
use PhpProjects\AuthDev\Model\User\UserEntity;
use PhpProjects\AuthDev\Model\User\UserRepository;
use PhpProjects\AuthDev\Model\User\UserValidation;
use PhpProjects\AuthDev\Model\ValidationResults;
use PhpProjects\AuthDev\Views\ViewService;

/**
 * Manages operations against the user portion of our domain model
 */
class UserController
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
     * @var CsrfService
     */
    private $csrfService;

    /**
     * @param ViewService $viewService
     * @param UserRepository $userRepository
     * @param UserValidation $userValidation
     * @param CsrfService $csrfService
     */
    public function __construct(ViewService $viewService, UserRepository $userRepository, UserValidation $userValidation, CsrfService $csrfService)
    {
        $this->viewService = $viewService;
        $this->userRepository = $userRepository;
        $this->userValidation = $userValidation;
        $this->csrfService = $csrfService;
    }

    /**
     * Convenience Constructor
     *
     * @return UserController
     */
    public static function create() : UserController
    {
        return new self(ViewService::create(), UserRepository::create(), new UserValidation(), CsrfService::create());
    }

    /**
     * Displays a list of users in the system
     * 
     * @param int $currentPage
     * @param string $query
     */
    public function getList(int $currentPage = 1, string $query = '')
    {
        if (empty($query))
        {
            $userList = $this->userRepository->getSortedUserList(10, ($currentPage - 1) * 10);
            $userCount = $this->userRepository->getUserCount();
        }
        else
        {
            $userList = $this->userRepository->getUsersMatchingUsername($query, 10, ($currentPage - 1) * 10);
            $userCount = $this->userRepository->getUserCountMatchingUsername($query);
        }

        $templateData = [
            'users' => $userList,
            'currentPage' => $currentPage,
            'totalPages' => $userCount > 0 ? (int)(($userCount - 1) / 10) + 1 : 1,
            'term' => $query
        ];
        
        if (!empty($redirectMessage = $this->viewService->getRedirectMessage()))
        {
            $templateData['message'] = $redirectMessage;
        }
        $this->viewService->renderView('users/list', $templateData);
    }

    /**
     * Displays a form to add a new user.
     */
    public function getNew()
    {
        $userEntity = new UserEntity();
        $this->viewService->renderView('users/form', [
            'user' => $userEntity,
            'validationResults' => new ValidationResults([]),
            'token' => $this->csrfService->getNewToken(),
        ]);
    }

    /**
     * Displays a form for editing $username
     * 
     * @param string $username
     */
    public function getDetail(string $username)
    {
        $user = $this->userRepository->getUserByUsername($username);
        
        if (empty($user))
        {
            throw (new ContentNotFoundException("I could not locate the user {$username}."))
                ->setTitle('User Not Found')
                ->setRecommendedUrl('/users/')
                ->setRecommendedAction('View All Users');
        }
        
        $this->viewService->renderView('users/form', [
            'user' => $user,
            'validationResults' => new ValidationResults([]),
            'token' => $this->csrfService->getNewToken(),
        ]);
    }

    /**
     * Posts a form for a new user.
     *
     * @param array $userData
     */
    public function postNew(array $userData)
    {
        $user = UserEntity::createFromArray($userData);

        $this->saveUser($user, $userData);
    }

    /**
     * Posts a form editing an existing user
     * 
     * @param string $username
     * @param array $userData
     */
    public function postDetail(string $username, array $userData)
    {
        $user = $this->userRepository->getUserByUsername($username);
        $user->updateFromArray($userData);

        $this->saveUser($user, $userData);
    }

    /**
     * Handles saving new or old users to the system
     * 
     * @param UserEntity $user
     */
    protected function saveUser(UserEntity $user, array $userData)
    {
        $validationResults = $this->userValidation->validate($user);

        if (!$this->csrfService->validateToken($userData['token'] ?? ''))
        {
            $validationResults->addErrorForField('form', 'Your session has expired, please try again');
        }
        elseif (!empty($userData['clear-password']) && $userData['clear-password'] != $userData['clear-password-confirm'])
        {
            $validationResults->addErrorForField('password', 'Passwords must match');
            $user->setClearTextPassword('');
        }
        elseif ($validationResults->isValid())
        {
            try
            {
                $this->userRepository->saveUser($user);
                $this->viewService->redirect('/users/', 303, "User {$user->getUsername()} successfully edited!");
                return;
            }
            catch (DuplicateUserException $e)
            {
                $validationResults = new ValidationResults([ $e->getField() => [ "This {$e->getField()} is already registered. Please try another." ] ]);
            }
        }
        $this->viewService->renderView('users/form', [
            'user' => $user,
            'validationResults' => $validationResults,
            'token' => $this->csrfService->getNewToken(),
        ]);
    }

    /**
     * Displays a removal confirmation for the usernames provided. 
     * @param array $getData
     */
    public function getRemove(array $getData)
    {
        $users = $this->userRepository->getUserListByUsernames($getData['users'] ?? []);
        $this->viewService->renderView('users/removeList', [
            'users' => $users,
            'token' => $this->csrfService->getNewToken(),
            'originalUrl' => $_SERVER['HTTP_REFERER'] ?? '/users/'
        ]);
    }

    /**
     * Removes the users provided
     * @param $_POST
     */
    public function postRemove(array $postData)
    {
        if (!$this->csrfService->validateToken($postData['token'] ?? ''))
        {
            $this->viewService->redirect($postData['originalUrl'], 303, "Your session has expired, please try deleting those users again");
        }
        else
        {
            $users = $postData['users'] ?? [];
            $this->userRepository->deleteUsersByUsernames($users);
            
            $this->viewService->redirect($postData['originalUrl'], 303, 'Users successfully removed: ' . implode(', ', $users));
        }
    }
}