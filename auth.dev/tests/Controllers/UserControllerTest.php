<?php

namespace PhpProjects\AuthDev\Controllers;

use Phake;
use PhpProjects\AuthDev\Model\Csrf\CsrfService;
use PhpProjects\AuthDev\Model\User\DuplicateUserException;
use PhpProjects\AuthDev\Model\User\UserEntity;
use PhpProjects\AuthDev\Model\User\UserRepository;
use PhpProjects\AuthDev\Model\User\UserValidation;
use PhpProjects\AuthDev\Model\ValidationResults;
use PhpProjects\AuthDev\Views\ViewService;
use PHPUnit\Framework\TestCase;

class UserControllerTest extends TestCase
{
    /**
     * @var UserController
     */
    private $userController;

    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var \ArrayIterator
     */
    private $userList;
    
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

    protected function setUp()
    {
        $this->userList = new \ArrayIterator([
            UserEntity::createFromArray([ 'username' => 'taken.user01', 'email' => 'taken1@digitalsandwich.com', 'name' => 'Existing User 1' ]),
            UserEntity::createFromArray([ 'username' => 'taken.user02', 'email' => 'taken2@digitalsandwich.com', 'name' => 'Existing User 2' ]),
            UserEntity::createFromArray([ 'username' => 'taken.user03', 'email' => 'taken3@digitalsandwich.com', 'name' => 'Existing User 3' ]),
        ]);
        
        $this->viewService = Phake::mock(ViewService::class);

        $this->userRepository = Phake::mock(UserRepository::class);
        Phake::when($this->userRepository)->getSortedUserList->thenReturn($this->userList);
        Phake::when($this->userRepository)->getUserCount->thenReturn(30);
        Phake::when($this->userRepository)->getUsersMatchingUsername->thenReturn($this->userList);
        Phake::when($this->userRepository)->getUserCountMatchingUsername->thenReturn(30);

        $this->userValidation = Phake::mock(UserValidation::class);
        Phake::when($this->userValidation)->validate->thenReturn(new ValidationResults([]));
        
        $this->csrfService = Phake::mock(CsrfService::class);
        Phake::when($this->csrfService)->validateToken->thenReturn(true);

        $this->userController = new UserController($this->viewService, $this->userRepository, $this->userValidation, $this->csrfService);
    }

    public function testGetListPage1()
    {
        $this->userController->getList(1);

        Phake::verify($this->userRepository)->getSortedUserList(10, 0);
        Phake::verify($this->userRepository)->getUserCount();
        Phake::verify($this->viewService)->renderView('users/list', [
            'users' => $this->userList,
            'currentPage' => 1,
            'totalPages' => 3,
            'term' => '',
        ]);
    }

    public function testGetListPage2()
    {
        $this->userController->getList(2);

        Phake::verify($this->userRepository)->getSortedUserList(10, 10);
        Phake::verify($this->userRepository)->getUserCount();
        Phake::verify($this->viewService)->renderView('users/list', [
            'users' => $this->userList,
            'currentPage' => 2,
            'totalPages' => 3,
            'term' => '',
        ]);
    }

    public function testGetListWithSearchPage1()
    {
        $this->userController->getList(1, 'user0');

        Phake::verify($this->userRepository, Phake::never())->getSortedUserList;
        Phake::verify($this->userRepository, Phake::never())->getUserCount;
        Phake::verify($this->userRepository)->getUsersMatchingUsername('user0', 10, 0);
        Phake::verify($this->userRepository)->getUserCountMatchingUsername('user0');
        Phake::verify($this->viewService)->renderView('users/list', [
            'users' => $this->userList,
            'currentPage' => 1,
            'totalPages' => 3,
            'term' => 'user0',
        ]);
    }

    public function testGetListWithSearchPage2()
    {
        $this->userController->getList(2, 'user0');

        Phake::verify($this->userRepository, Phake::never())->getSortedUserList;
        Phake::verify($this->userRepository, Phake::never())->getUserCount;
        Phake::verify($this->userRepository)->getUsersMatchingUsername('user0', 10, 10);
        Phake::verify($this->userRepository)->getUserCountMatchingUsername('user0');
        Phake::verify($this->viewService)->renderView('users/list', [
            'users' => $this->userList,
            'currentPage' => 2,
            'totalPages' => 3,
            'term' => 'user0',
        ]);
    }

    public function testGetListChecksRedirectMessage()
    {
        Phake::when($this->viewService)->getRedirectMessage->thenReturn('My flash message');

        $this->userController->getList();

        Phake::verify($this->viewService)->getRedirectMessage();
        Phake::verify($this->viewService)->renderView($this->anything(), Phake::capture($templateData));

        $this->assertArrayHasKey('message', $templateData);
        $this->assertEquals('My flash message', $templateData['message']);
    }
    
    public function testGetNew()
    {
        Phake::when($this->csrfService)->getNewToken->thenReturn('1itfuefduyp9h');
        
        $this->userController->getNew();
        
        Phake::verify($this->viewService)->renderView('users/form', Phake::capture($templateData));
        Phake::verify($this->csrfService)->getNewToken();

        $this->assertArrayHasKey('user', $templateData);
        $this->assertInstanceOf(UserEntity::class, $templateData['user']);

        $this->assertArrayHasKey('validationResults', $templateData);
        $this->assertInstanceOf(ValidationResults::class, $templateData['validationResults']);
        $this->assertTrue($templateData['validationResults']->isValid());

        $this->assertArrayHasKey('token', $templateData);
        $this->assertEquals('1itfuefduyp9h', $templateData['token']);
    }
    
    public function testPostNew()
    {
        $this->userController->postNew([
            'username' => 'mike.lively',
            'name' => 'Mike Lively',
            'email' => 'm@digitalsandwich.com',
            'clear-password' => 'P@ssw0rd',
            'clear-password-confirm' => 'P@ssw0rd',
            'token' => '123456',
        ]);

        /* @var $user UserEntity */
        Phake::verify($this->userValidation)->validate(Phake::capture($user));
        $this->assertEquals('mike.lively', $user->getUsername());
        $this->assertEquals('Mike Lively', $user->getName());
        $this->assertEquals('m@digitalsandwich.com', $user->getEmail());
        $this->assertEquals('P@ssw0rd', $user->getClearTextPassword());
        $this->assertTrue(password_verify('P@ssw0rd', $user->getPasswordHash()));

        Phake::verify($this->csrfService)->validateToken('123456');
        Phake::verify($this->userRepository)->saveUser($user);


        Phake::verify($this->viewService)->redirect('/users/', 303, 'User mike.lively successfully edited!');
    }

    public function testPostNewInvalid()
    {
        $validationResult = new ValidationResults(['username' => [ 'username is empty' ]]);
        Phake::when($this->userValidation)->validate->thenReturn($validationResult);

        $this->userController->postNew([
            'username' => '',
            'name' => 'Mike Lively',
            'email' => 'm@digitalsandwich.com',
            'clear-password' => 'P@ssw0rd',
            'clear-password-confirm' => 'P@ssw0rd',
            'token' => '123456',
        ]);

        Phake::verify($this->userRepository, Phake::never())->saveUser;
        Phake::verify($this->viewService, Phake::never())->redirect;

        Phake::verify($this->viewService)->renderView('users/form', Phake::capture($templateData));
        $this->assertArrayHasKey('user', $templateData);
        $this->assertEquals('', $templateData['user']->getUsername());
        $this->assertEquals('Mike Lively', $templateData['user']->getName());
        $this->assertEquals('m@digitalsandwich.com', $templateData['user']->getEmail());

        $this->assertArrayHasKey('validationResults', $templateData);
        $this->assertEquals($validationResult, $templateData['validationResults']);
    }

    public function testPostNewMismatchedPasswords()
    {
        $validationResult = new ValidationResults(['password' => [ 'Passwords don\'t match' ]]);
        Phake::when($this->userValidation)->validate->thenReturn($validationResult);

        $this->userController->postNew([
            'username' => '',
            'name' => 'Mike Lively',
            'email' => 'm@digitalsandwich.com',
            'clear-password' => 'P@ssw0rd',
            'clear-password-confirm' => 'Passw0rd',
            'token' => '123456',
        ]);

        Phake::verify($this->userRepository, Phake::never())->saveUser;
        Phake::verify($this->viewService, Phake::never())->redirect;

        Phake::verify($this->viewService)->renderView('users/form', Phake::capture($templateData));
        $this->assertArrayHasKey('user', $templateData);
        $this->assertEquals('', $templateData['user']->getClearTextPassword());

        $this->assertArrayHasKey('validationResults', $templateData);
        $this->assertEquals($validationResult, $templateData['validationResults']);
    }

    public function testPostNewDuplicateUser()
    {
        Phake::when($this->userRepository)->saveUser->thenThrow(new DuplicateUserException('username', new \Exception()));

        $this->userController->postNew([
            'username' => 'mike.lively',
            'name' => 'Mike Lively',
            'email' => 'm@digitalsandwich.com',
            'clear-password' => 'P@ssw0rd',
            'clear-password-confirm' => 'P@ssw0rd',
            'token' => '123456',
        ]);

        Phake::verify($this->viewService, Phake::never())->redirect;

        Phake::verify($this->viewService)->renderView('users/form', Phake::capture($templateData));

        $this->assertArrayHasKey('validationResults', $templateData);
        $this->assertEquals(['This username is already registered. Please try another.'], $templateData['validationResults']->getValidationErrorsForField('username'));
    }

    public function testPostNewMismatchedCsrfToken()
    {
        Phake::when($this->csrfService)->validateToken->thenReturn(false);

        $this->userController->postNew([
            'username' => 'mike.lively',
            'name' => 'Mike Lively',
            'email' => 'm@digitalsandwich.com',
            'clear-password' => 'P@ssw0rd',
            'clear-password-confirm' => 'P@ssw0rd',
            'token' => '123456',
        ]);

        Phake::verify($this->viewService, Phake::never())->redirect;

        Phake::verify($this->viewService)->renderView('users/form', Phake::capture($templateData));

        $this->assertArrayHasKey('validationResults', $templateData);
        $this->assertEquals(['Your session has expired, please try again'], $templateData['validationResults']->getValidationErrorsForField('form'));
    }

    public function testGetDetail()
    {
        Phake::when($this->csrfService)->getNewToken->thenReturn('1itfuefduyp9h');

        $user = UserEntity::createFromArray([
            'username' => 'mike.lively',
            'name' => 'Mike Lively',
            'email' => 'm@digitalsandwich.com',
            'password' => 'hashedpassword'
        ]);
        Phake::when($this->userRepository)->getUserByUsername->thenReturn($user);

        $this->userController->getDetail('mike.lively');

        Phake::verify($this->userRepository)->getUserByUsername('mike.lively');
        Phake::verify($this->viewService)->renderView('users/form', [
            'user' => $user,
            'validationResults' => new ValidationResults([]),
            'token' => '1itfuefduyp9h',
        ]);
    }

    public function testGetDetailNoUser()
    {
        Phake::when($this->userRepository)->getUserByUsername->thenReturn(null);

        try
        {
            $this->userController->getDetail('mike.lively');
            $this->fail('A ContentNotFoundException exception should have been thrown');
        }
        catch (ContentNotFoundException $e)
        {
            Phake::verify($this->viewService, Phake::never())->renderView;
            $this->assertEquals('User Not Found', $e->getTitle());
            $this->assertEquals('I could not locate the user mike.lively.', $e->getMessage());
            $this->assertEquals('/users/', $e->getRecommendedUrl());
            $this->assertEquals('View All Users', $e->getRecommendedAction());
        }
    }
    
    public function testPostDetail()
    {
        $existingUser = UserEntity::createFromArray([
            'username' => 'mike.lively',
            'name' => 'Mike Lively',
            'email' => 'm@digitalsandwich.com',
            'password' => 'hashedPassword'
        ]);
        Phake::when($this->userRepository)->getUserByUsername->thenReturn($existingUser);
        
        $this->userController->postDetail('mike.lively', [
            'username' => 'new.name',
            'name' => 'Mike Lively',
            'email' => 'm@digitalsandwich.com',
            'token' => '123456',
        ]);

        Phake::verify($this->csrfService)->validateToken('123456');

        Phake::verify($this->userRepository)->getUserByUsername('mike.lively');
        /* @var $user UserEntity */
        Phake::verify($this->userValidation)->validate($existingUser);
        $this->assertEquals('new.name', $existingUser->getUsername());

        Phake::verify($this->userRepository)->saveUser($existingUser);

        Phake::verify($this->viewService)->redirect('/users/', 303, 'User new.name successfully edited!');
    }
}