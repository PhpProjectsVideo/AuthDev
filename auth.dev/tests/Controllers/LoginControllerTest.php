<?php

namespace PhpProjects\AuthDev\Controllers;

use Phake;
use PhpProjects\AuthDev\Authentication\LoginService;
use PhpProjects\AuthDev\Model\Csrf\CsrfService;
use PhpProjects\AuthDev\Model\DuplicateEntityException;
use PhpProjects\AuthDev\Model\Group\GroupEntity;
use PhpProjects\AuthDev\Model\Group\GroupRepository;
use PhpProjects\AuthDev\Model\User\UserEntity;
use PhpProjects\AuthDev\Model\User\UserRepository;
use PhpProjects\AuthDev\Model\User\UserValidation;
use PhpProjects\AuthDev\Model\ValidationResults;
use PhpProjects\AuthDev\Views\ViewService;
use PHPUnit\Framework\TestCase;

class LoginControllerTest extends TestCase
{
    /**
     * @var LoginController
     */
    private $loginController;

    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var GroupRepository
     */
    private $groupRepository;

    /**
     * @var \ArrayIterator
     */
    private $userList;

    /**
     * @var \ArrayIterator
     */
    private $groupList;

    /**
     * @var ViewService
     */
    private $viewService;

    /**
     * @var UserValidation
     */
    private $userValidation;

    /**
     * @var CsrfService
     */
    private $csrfService;

    /**
     * @var LoginService
     */
    private $loginService;

    protected function setUp()
    {
        $this->viewService = Phake::mock(ViewService::class);
        $this->csrfService = Phake::mock(CsrfService::class);
        Phake::when($this->csrfService)->validateToken->thenReturn(true);
        Phake::when($this->csrfService)->getNewToken->thenReturn('mytoken');

        $this->loginService = Phake::mock(LoginService::class);
        Phake::when($this->loginService)->isSessionAuthenticated->thenReturn(false);
        Phake::when($this->loginService)->attemptAuthentication->thenReturn(new ValidationResults([]));

        $this->loginController = new LoginController($this->viewService, $this->loginService, $this->csrfService);
    }
    
    public function testGetLogin()
    {
        $this->loginController->getLogin('/url');
        
        Phake::verify($this->viewService)->renderView('auth/login', [
            'originalUrl' => '/url',
            'validationResults' => new ValidationResults([]),
            'token' => 'mytoken',
        ]);
    }
    
    public function testGetLoginWhenAlreadyLoggedIn()
    {
        Phake::when($this->loginService)->isSessionAuthenticated->thenReturn(true);
        
        $this->loginController->getLogin('/url');
        
        Phake::verify($this->viewService)->redirect('/url');
    }
    
    public function testPostLogin()
    {
        $this->loginController->postLogin([
            'username' => 'user',
            'password' => 'pass',
            'originalUrl' => '/url',
        ]);
        
        Phake::verify($this->loginService)->attemptAuthentication('user', 'pass');
        Phake::verify($this->viewService)->redirect('/url');
    }
    
    public function testPostLoginInvalidUserAndPass()
    {
        $failedLoginResult = new ValidationResults(['login' => ['Login Failed']]);
        Phake::when($this->loginService)->attemptAuthentication->thenReturn($failedLoginResult);

        $this->loginController->postLogin([
            'username' => 'user',
            'password' => 'pass',
            'originalUrl' => '/url',
        ]);
        
        Phake::verify($this->viewService)->renderView('auth/login', [
            'originalUrl' => '/url',
            'validationResults' => $failedLoginResult,
            'token' => 'mytoken',
        ]);
    }
    
    public function testPostLoginInvalidCsrfToken()
    {
        Phake::when($this->csrfService)->validateToken->thenReturn(false);


        $this->loginController->postLogin([
            'username' => 'user',
            'password' => 'pass',
            'originalUrl' => '/url',
        ]);

        Phake::verify($this->viewService)->renderView('auth/login', Phake::capture($templateData));
        
        $this->assertEquals('/url', $templateData['originalUrl']);
        $this->assertEquals('mytoken', $templateData['token']);
        
        $this->assertFalse($templateData['validationResults']->isValid());
        $this->assertNotEmpty($templateData['validationResults']->getValidationErrorsForField('login'));
    }
    
    public function testPostLogout()
    {
        $this->loginController->postLogout([
            'originalUrl' => '/url'
        ]);
        
        Phake::verify($this->loginService)->removeAuthentication();
        Phake::verify($this->viewService)->redirect('/url');
    }
}