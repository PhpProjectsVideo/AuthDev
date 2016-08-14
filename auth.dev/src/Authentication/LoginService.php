<?php

namespace PhpProjects\AuthDev\Authentication;

use PhpProjects\AuthDev\Model\Permission\PermissionEntity;
use PhpProjects\AuthDev\Model\Permission\PermissionRepository;
use PhpProjects\AuthDev\Model\User\UserEntity;
use PhpProjects\AuthDev\Model\User\UserRepository;
use PhpProjects\AuthDev\Model\ValidationResults;

/**
 * A service to help handle authentication. 
 * 
 * Requires a UserRepository and PermissionRepository from which to check authentication data against.
 * 
 * Also requires a reference to the active $_SESSION.
 */
class LoginService
{
    /**
     * @var array
     */
    private $session;

    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var PermissionRepository
     */
    private $permissionRepository;

    /**
     * @param array $session a reference to the current session store for the user
     * @param UserRepository $userRepository
     * @param PermissionRepository $permissionRepository
     */
    public function __construct(array &$session, UserRepository $userRepository, PermissionRepository $permissionRepository)
    {
        $this->session =& $session;
        $this->userRepository = $userRepository;
        $this->permissionRepository = $permissionRepository;
    }

    /**
     * @return LoginService
     */
    public static function create() : LoginService
    {
        return new LoginService($_SESSION, UserRepository::create(), PermissionRepository::create());
    }

    /**
     * Returns true if the current $this->session is authenticated, false otherwise
     * 
     * @return bool
     */
    public function isSessionAuthenticated() : bool
    {
        return isset($this->session['LoginService']['username']);
    }

    /**
     * Attempts to authenticate a user with the given $username and $password
     * 
     * If valid the session with be marked as authenticated and a valid ValidationResults object returned.
     * 
     * If not valid, the method will return an invalid ValidationResults object.
     * 
     * @param $username
     * @param $password
     * @return ValidationResults
     */
    public function attemptAuthentication($username, $password) : ValidationResults
    {
        /* @var $user UserEntity */
        $user = $this->userRepository->getByFriendlyName($username);
        
        if($user && $user->passwordMatches($password))
        {
            $this->session['LoginService']['username'] = $username;
            return new ValidationResults([]);
        }
        else
        {
            return new ValidationResults([
                'login' => [
                    'Incorrect username or password, please try again'
                ],
            ]);
        }
    }

    /**
     * Removes authentication information from the session
     */
    public function removeAuthentication()
    {
        unset($this->session['LoginService']);
    }

    /**
     * Returns true if the session has the requested permission, false otherwise.
     * 
     * @param string $permission The name of the required permission
     * @return bool
     */
    public function sessionHasPermission(string $permission) : bool 
    {
        /* @var $user UserEntity */
        $user = $this->userRepository->getByFriendlyName($this->session['LoginService']['username']);
        
        if (!empty($user))
        {
            $permissions = $this->permissionRepository->getByGroupIds($user->getGroupIds());
            
            /* @var PermissionEntity $p */
            foreach ($permissions as $p)
            {
                if ($p->getName() == $permission)
                {
                    return true;
                }
            }
        }
        
        return false;
    }
}