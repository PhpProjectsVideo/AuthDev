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

    protected function setUp()
    {
        $this->userList = new \ArrayIterator([
            UserEntity::createFromArray([ 'username' => 'taken.user01', 'email' => 'taken1@digitalsandwich.com', 'name' => 'Existing User 1' ]),
            UserEntity::createFromArray([ 'username' => 'taken.user02', 'email' => 'taken2@digitalsandwich.com', 'name' => 'Existing User 2' ]),
            UserEntity::createFromArray([ 'username' => 'taken.user03', 'email' => 'taken3@digitalsandwich.com', 'name' => 'Existing User 3' ]),
        ]);
        
        $this->viewService = Phake::mock(ViewService::class);

        $this->userRepository = Phake::mock(UserRepository::class);
        Phake::when($this->userRepository)->getSortedList->thenReturn($this->userList);
        Phake::when($this->userRepository)->getCount->thenReturn(30);
        Phake::when($this->userRepository)->getListMatchingFriendlyName->thenReturn($this->userList);
        Phake::when($this->userRepository)->getCountMatchingFriendlyName->thenReturn(30);

        $this->groupRepository = Phake::mock(GroupRepository::class);
        $this->groupList = new \ArrayIterator([
            GroupEntity::createFromArray(['id' => 1, 'name' => 'Group 1']),
            GroupEntity::createFromArray(['id' => 2, 'name' => 'Group 2']),
            GroupEntity::createFromArray(['id' => 3, 'name' => 'Group 3']),
        ]);
        Phake::when($this->groupRepository)->getSortedList->thenReturn($this->groupList);


        $this->userValidation = Phake::mock(UserValidation::class);
        Phake::when($this->userValidation)->validate->thenReturn(new ValidationResults([]));
        
        $this->csrfService = Phake::mock(CsrfService::class);
        Phake::when($this->csrfService)->validateToken->thenReturn(true);
        
        $this->userController = Phake::partialMock(UserController::class, $this->viewService, $this->userRepository, $this->userValidation, $this->groupRepository, $this->csrfService);
        Phake::when($this->userController)->checkForPermission->thenReturn(true);
    }

    public function testGetListPage1()
    {
        $this->userController->getList(1);

        Phake::verify($this->userController)->checkForPermission('Administrator');
        Phake::verify($this->userRepository)->getSortedList(10, 0);
        Phake::verify($this->userRepository)->getCount();
        Phake::verify($this->viewService)->renderView('users/list', [
            'entities' => $this->userList,
            'currentPage' => 1,
            'totalPages' => 3,
            'term' => '',
        ]);
    }

    public function testGetListPage2()
    {
        $this->userController->getList(2);

        Phake::verify($this->userRepository)->getSortedList(10, 10);
        Phake::verify($this->userRepository)->getCount();
        Phake::verify($this->viewService)->renderView('users/list', [
            'entities' => $this->userList,
            'currentPage' => 2,
            'totalPages' => 3,
            'term' => '',
        ]);
    }

    public function testGetListWithSearchPage1()
    {
        $this->userController->getList(1, 'user0');

        Phake::verify($this->userRepository, Phake::never())->getSortedList;
        Phake::verify($this->userRepository, Phake::never())->getCount;
        Phake::verify($this->userRepository)->getListMatchingFriendlyName('user0', 10, 0);
        Phake::verify($this->userRepository)->getCountMatchingFriendlyName('user0');
        Phake::verify($this->viewService)->renderView('users/list', [
            'entities' => $this->userList,
            'currentPage' => 1,
            'totalPages' => 3,
            'term' => 'user0',
        ]);
    }

    public function testGetListWithSearchPage2()
    {
        $this->userController->getList(2, 'user0');

        Phake::verify($this->userRepository, Phake::never())->getSortedList;
        Phake::verify($this->userRepository, Phake::never())->getCount;
        Phake::verify($this->userRepository)->getListMatchingFriendlyName('user0', 10, 10);
        Phake::verify($this->userRepository)->getCountMatchingFriendlyName('user0');
        Phake::verify($this->viewService)->renderView('users/list', [
            'entities' => $this->userList,
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

        Phake::verify($this->userController)->checkForPermission('Administrator');
        Phake::verify($this->viewService)->renderView('users/form', Phake::capture($templateData));
        Phake::verify($this->csrfService)->getNewToken();

        $this->assertArrayHasKey('entity', $templateData);
        $this->assertInstanceOf(UserEntity::class, $templateData['entity']);

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

        Phake::verify($this->userController)->checkForPermission('Administrator');
        /* @var $user UserEntity */
        Phake::verify($this->userValidation)->validate(Phake::capture($user));
        $this->assertEquals('mike.lively', $user->getUsername());
        $this->assertEquals('Mike Lively', $user->getName());
        $this->assertEquals('m@digitalsandwich.com', $user->getEmail());
        $this->assertEquals('P@ssw0rd', $user->getClearTextPassword());
        $this->assertTrue(password_verify('P@ssw0rd', $user->getPasswordHash()));

        Phake::verify($this->csrfService)->validateToken('123456');
        Phake::verify($this->userRepository)->saveEntity($user);


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

        Phake::verify($this->userRepository, Phake::never())->saveEntity;
        Phake::verify($this->viewService, Phake::never())->redirect;

        Phake::verify($this->viewService)->renderView('users/form', Phake::capture($templateData));
        $this->assertArrayHasKey('entity', $templateData);
        $this->assertEquals('', $templateData['entity']->getUsername());
        $this->assertEquals('Mike Lively', $templateData['entity']->getName());
        $this->assertEquals('m@digitalsandwich.com', $templateData['entity']->getEmail());

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

        Phake::verify($this->userRepository, Phake::never())->saveEntity;
        Phake::verify($this->viewService, Phake::never())->redirect;

        Phake::verify($this->viewService)->renderView('users/form', Phake::capture($templateData));
        $this->assertArrayHasKey('entity', $templateData);
        $this->assertEquals('', $templateData['entity']->getClearTextPassword());

        $this->assertArrayHasKey('validationResults', $templateData);
        $this->assertEquals($validationResult, $templateData['validationResults']);
    }

    public function testPostNewDuplicateUser()
    {
        Phake::when($this->userRepository)->saveEntity->thenThrow(new DuplicateEntityException('username', new \Exception()));

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
        Phake::when($this->userRepository)->getByFriendlyName->thenReturn($user);

        $this->userController->getDetail('mike.lively');

        Phake::verify($this->userController)->checkForPermission('Administrator');
        Phake::verify($this->groupRepository)->getSortedList();
        Phake::verify($this->userRepository)->getByFriendlyName('mike.lively');
        Phake::verify($this->viewService)->renderView('users/form', [
            'entity' => $user,
            'groups' => iterator_to_array($this->groupList),
            'validationResults' => new ValidationResults([]),
            'token' => '1itfuefduyp9h',
        ]);
    }

    public function testGetDetailNoUser()
    {
        Phake::when($this->userRepository)->getByFriendlyName->thenReturn(null);

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
        Phake::when($this->userRepository)->getByFriendlyName->thenReturn($existingUser);
        
        $this->userController->postDetail('mike.lively', [
            'username' => 'new.name',
            'name' => 'Mike Lively',
            'email' => 'm@digitalsandwich.com',
            'token' => '123456',
        ]);

        Phake::verify($this->userController)->checkForPermission('Administrator');

        Phake::verify($this->csrfService)->validateToken('123456');

        Phake::verify($this->userRepository)->getByFriendlyName('mike.lively');
        /* @var $user UserEntity */
        Phake::verify($this->userValidation)->validate($existingUser);
        $this->assertEquals('new.name', $existingUser->getUsername());

        Phake::verify($this->userRepository)->saveEntity($existingUser);

        Phake::verify($this->viewService)->redirect('/users/', 303, 'User new.name successfully edited!');
    }
    
    public function testGetRemove()
    {
        Phake::when($this->csrfService)->getNewToken->thenReturn('1itfuefduyp9h');

        $user = UserEntity::createFromArray([
            'username' => 'mike.lively',
            'name' => 'Mike Lively',
            'email' => 'm@digitalsandwich.com',
            'password' => 'hashedpassword'
        ]);
        Phake::when($this->userRepository)->getListByFriendlyNames->thenReturn(new \ArrayIterator([$user]));

        $_SERVER['HTTP_REFERER'] = '/mytest/';

        $this->userController->getRemove([
            'entities' => [
                'mike.lively',
                'user2'
            ],
        ]);

        Phake::verify($this->userController)->checkForPermission('Administrator');
        Phake::verify($this->userRepository)->getListByFriendlyNames(['mike.lively', 'user2']);
        Phake::verify($this->viewService)->renderView('users/removeList', Phake::capture($templateData));
        
        $this->assertEquals('1itfuefduyp9h', $templateData['token']);
        $this->assertEquals([ $user ], iterator_to_array($templateData['entities']));
        $this->assertEquals('/mytest/', $templateData['originalUrl']);
    }

    public function testPostRemove()
    {
        $this->userController->postRemove([
            'entities' => [
                'mike.lively',
                'user2'
            ],
            'token' => '1itfuefduyp9h',
            'originalUrl' => '/mytest/',
        ]);

        Phake::verify($this->userController)->checkForPermission('Administrator');
        Phake::verify($this->userRepository)->deleteByFriendlyNames(['mike.lively', 'user2']);
        Phake::verify($this->viewService)->redirect('/mytest/', 303, 'Users successfully removed: mike.lively, user2');
    }

    public function testPostRemoveInvalidToken()
    {
        Phake::when($this->csrfService)->validateToken->thenReturn(false);

        $this->userController->postRemove([
            'entities' => [
                'mike.lively',
                'user2'
            ],
            'originalUrl' => '/mytest/',
        ]);

        Phake::verify($this->userRepository, Phake::never())->deleteByFriendlyNames;
        Phake::verify($this->viewService)->redirect('/mytest/', 303, "Your session has expired, please try deleting those users again");
    }

    public function testUpdateGroupsAddGroups()
    {
        $user = UserEntity::createFromArray([
            'username' => 'mike.lively',
            'name' => 'Mike Lively',
            'email' => 'm@digitalsandwich.com',
            'password' => 'hashedpassword'
        ]);
        Phake::when($this->userRepository)->getByFriendlyName->thenReturn($user);

        $this->userController->postUpdateGroups('mike.lively', [
            'token' => '1itfuefduyp9h',
            'groupIds' => [1, 2, 3],
            'operation' => 'add'
        ]);

        Phake::verify($this->userController)->checkForPermission('Administrator');

        Phake::verify($this->userRepository)->getByFriendlyName('mike.lively');
        $this->assertEquals([1, 2, 3], $user->getGroupIds());

        Phake::verify($this->userRepository)->saveEntity($user);

        Phake::verify($this->viewService)->redirect('/users/detail/mike.lively', 303, "Your groups have been updated", 'success');
    }

    public function testUpdateGroupsRemovesGroups()
    {
        $user = UserEntity::createFromArray([
            'username' => 'mike.lively',
            'name' => 'Mike Lively',
            'email' => 'm@digitalsandwich.com',
            'password' => 'hashedpassword'
        ]);
        $user->addGroups([1, 2, 3]);
        Phake::when($this->userRepository)->getByFriendlyName->thenReturn($user);

        $this->userController->postUpdateGroups('mike.lively', [
            'token' => '1itfuefduyp9h',
            'groupIds' => [2],
            'operation' => 'remove'
        ]);

        Phake::verify($this->userRepository)->getByFriendlyName('mike.lively');
        $this->assertEquals([1, 3], $user->getGroupIds());

        Phake::verify($this->userRepository)->saveEntity($user);

        Phake::verify($this->viewService)->redirect('/users/detail/mike.lively', 303, "Your groups have been updated", 'success');
    }

    public function testUpdateGroupsInvalidToken()
    {
        Phake::when($this->csrfService)->validateToken->thenReturn(false);

        $user = UserEntity::createFromArray([
            'username' => 'mike.lively',
            'name' => 'Mike Lively',
            'email' => 'm@digitalsandwich.com',
            'password' => 'hashedpassword'
        ]);
        $user->addGroups([1, 2, 3]);
        Phake::when($this->userRepository)->getByFriendlyName->thenReturn($user);

        $this->userController->postUpdateGroups('mike.lively', [
            'token' => '1itfuefduyp9h',
            'groupIds' => [2],
            'operation' => 'remove'
        ]);

        Phake::verify($this->userRepository, Phake::never())->saveEntity($user);

        Phake::verify($this->viewService)->redirect('/users/detail/mike.lively', 303, "Your session has expired, please try updating groups again", 'danger');
    }
}