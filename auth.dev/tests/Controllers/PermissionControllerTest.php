<?php

namespace PhpProjects\AuthDev\Controllers;

use Phake;
use PhpProjects\AuthDev\Model\Csrf\CsrfService;
use PhpProjects\AuthDev\Model\DuplicateEntityException;
use PhpProjects\AuthDev\Model\Permission\PermissionEntity;
use PhpProjects\AuthDev\Model\Permission\PermissionRepository;
use PhpProjects\AuthDev\Model\Permission\PermissionValidation;
use PhpProjects\AuthDev\Model\ValidationResults;
use PhpProjects\AuthDev\Views\ViewService;
use PHPUnit\Framework\TestCase;

class PermissionControllerTest extends TestCase
{
    /**
     * @var PermissionController
     */
    private $permissionController;

    /**
     * @var PermissionRepository
     */
    private $permissionRepository;

    /**
     * @var \ArrayIterator
     */
    private $permissionList;

    /**
     * @var ViewService
     */
    private $viewService;

    /**
     * @var PermissionValidation
     */
    private $permissionValidation;

    /**
     * @var CsrfService
     */
    private $csrfService;

    protected function setUp()
    {
        $this->permissionList = new \ArrayIterator([
            PermissionEntity::createFromArray([ 'name' => 'taken.permission01' ]),
            PermissionEntity::createFromArray([ 'name' => 'taken.permission02' ]),
            PermissionEntity::createFromArray([ 'name' => 'taken.permission03' ]),
        ]);

        $this->viewService = Phake::mock(ViewService::class);

        $this->permissionRepository = Phake::mock(PermissionRepository::class);
        Phake::when($this->permissionRepository)->getSortedList->thenReturn($this->permissionList);
        Phake::when($this->permissionRepository)->getCount->thenReturn(30);
        Phake::when($this->permissionRepository)->getListMatchingFriendlyName->thenReturn($this->permissionList);
        Phake::when($this->permissionRepository)->getCountMatchingFriendlyName->thenReturn(30);

        $this->permissionValidation = Phake::mock(PermissionValidation::class);
        Phake::when($this->permissionValidation)->validate->thenReturn(new ValidationResults([]));

        $this->csrfService = Phake::mock(CsrfService::class);
        Phake::when($this->csrfService)->validateToken->thenReturn(true);

        $this->permissionController = new PermissionController($this->viewService, $this->permissionRepository, $this->permissionValidation, $this->csrfService);
    }

    public function testGetListPage1()
    {
        $this->permissionController->getList(1);

        Phake::verify($this->permissionRepository)->getSortedList(10, 0);
        Phake::verify($this->permissionRepository)->getCount();
        Phake::verify($this->viewService)->renderView('permissions/list', [
            'entities' => $this->permissionList,
            'currentPage' => 1,
            'totalPages' => 3,
            'term' => '',
        ]);
    }

    public function testGetListPage2()
    {
        $this->permissionController->getList(2);

        Phake::verify($this->permissionRepository)->getSortedList(10, 10);
        Phake::verify($this->permissionRepository)->getCount();
        Phake::verify($this->viewService)->renderView('permissions/list', [
            'entities' => $this->permissionList,
            'currentPage' => 2,
            'totalPages' => 3,
            'term' => '',
        ]);
    }

    public function testGetListWithSearchPage1()
    {
        $this->permissionController->getList(1, 'permission0');

        Phake::verify($this->permissionRepository, Phake::never())->getSortedList;
        Phake::verify($this->permissionRepository, Phake::never())->getCount;
        Phake::verify($this->permissionRepository)->getListMatchingFriendlyName('permission0', 10, 0);
        Phake::verify($this->permissionRepository)->getCountMatchingFriendlyName('permission0');
        Phake::verify($this->viewService)->renderView('permissions/list', [
            'entities' => $this->permissionList,
            'currentPage' => 1,
            'totalPages' => 3,
            'term' => 'permission0',
        ]);
    }

    public function testGetListWithSearchPage2()
    {
        $this->permissionController->getList(2, 'permission0');

        Phake::verify($this->permissionRepository, Phake::never())->getSortedList;
        Phake::verify($this->permissionRepository, Phake::never())->getCount;
        Phake::verify($this->permissionRepository)->getListMatchingFriendlyName('permission0', 10, 10);
        Phake::verify($this->permissionRepository)->getCountMatchingFriendlyName('permission0');
        Phake::verify($this->viewService)->renderView('permissions/list', [
            'entities' => $this->permissionList,
            'currentPage' => 2,
            'totalPages' => 3,
            'term' => 'permission0',
        ]);
    }

    public function testGetListChecksRedirectMessage()
    {
        Phake::when($this->viewService)->getRedirectMessage->thenReturn('My flash message');

        $this->permissionController->getList();

        Phake::verify($this->viewService)->getRedirectMessage();
        Phake::verify($this->viewService)->renderView($this->anything(), Phake::capture($templateData));

        $this->assertArrayHasKey('message', $templateData);
        $this->assertEquals('My flash message', $templateData['message']);
    }

    public function testGetNew()
    {
        Phake::when($this->csrfService)->getNewToken->thenReturn('1itfuefduyp9h');

        $this->permissionController->getNew();

        Phake::verify($this->viewService)->renderView('permissions/form', Phake::capture($templateData));
        Phake::verify($this->csrfService)->getNewToken();

        $this->assertArrayHasKey('entity', $templateData);
        $this->assertInstanceOf(PermissionEntity::class, $templateData['entity']);

        $this->assertArrayHasKey('validationResults', $templateData);
        $this->assertInstanceOf(ValidationResults::class, $templateData['validationResults']);
        $this->assertTrue($templateData['validationResults']->isValid());

        $this->assertArrayHasKey('token', $templateData);
        $this->assertEquals('1itfuefduyp9h', $templateData['token']);
    }

    public function testPostNew()
    {
        $this->permissionController->postNew([
            'name' => 'Test Permission',
            'token' => '123456',
        ]);

        /* @var $permission PermissionEntity */
        Phake::verify($this->permissionValidation)->validate(Phake::capture($permission));
        $this->assertEquals('Test Permission', $permission->getName());

        Phake::verify($this->csrfService)->validateToken('123456');
        Phake::verify($this->permissionRepository)->saveEntity($permission);


        Phake::verify($this->viewService)->redirect('/permissions/', 303, 'Permission Test Permission successfully edited!');
    }

    public function testPostNewInvalid()
    {
        $validationResult = new ValidationResults(['name' => [ 'name is empty' ]]);
        Phake::when($this->permissionValidation)->validate->thenReturn($validationResult);

        $this->permissionController->postNew([
            'name' => '',
        ]);

        Phake::verify($this->permissionRepository, Phake::never())->saveEntity;
        Phake::verify($this->viewService, Phake::never())->redirect;

        Phake::verify($this->viewService)->renderView('permissions/form', Phake::capture($templateData));
        $this->assertArrayHasKey('entity', $templateData);
        $this->assertEquals('', $templateData['entity']->getName());

        $this->assertArrayHasKey('validationResults', $templateData);
        $this->assertEquals($validationResult, $templateData['validationResults']);
    }
    public function testPostNewDuplicatePermission()
    {
        Phake::when($this->permissionRepository)->saveEntity->thenThrow(new DuplicateEntityException('name', new \Exception()));

        $this->permissionController->postNew([
            'name' => 'Test Permission',
        ]);

        Phake::verify($this->viewService, Phake::never())->redirect;

        Phake::verify($this->viewService)->renderView('permissions/form', Phake::capture($templateData));

        $this->assertArrayHasKey('validationResults', $templateData);
        $this->assertEquals(['This name is already registered. Please try another.'], $templateData['validationResults']->getValidationErrorsForField('name'));
    }

    public function testPostNewMismatchedCsrfToken()
    {
        Phake::when($this->csrfService)->validateToken->thenReturn(false);

        $this->permissionController->postNew([
            'name' => 'Test Permission',
            'token' => '123456',
        ]);

        Phake::verify($this->viewService, Phake::never())->redirect;

        Phake::verify($this->viewService)->renderView('permissions/form', Phake::capture($templateData));

        $this->assertArrayHasKey('validationResults', $templateData);
        $this->assertEquals(['Your session has expired, please try again'], $templateData['validationResults']->getValidationErrorsForField('form'));
    }

    public function testGetDetail()
    {
        Phake::when($this->csrfService)->getNewToken->thenReturn('1itfuefduyp9h');

        $permission = PermissionEntity::createFromArray([
            'name' => 'Test Permission',
        ]);
        Phake::when($this->permissionRepository)->getByFriendlyName->thenReturn($permission);

        $this->permissionController->getDetail('Test Permission');

        Phake::verify($this->permissionRepository)->getByFriendlyName('Test Permission');
        Phake::verify($this->viewService)->renderView('permissions/form', [
            'entity' => $permission,
            'validationResults' => new ValidationResults([]),
            'token' => '1itfuefduyp9h',
        ]);
    }

    public function testGetDetailNoPermission()
    {
        Phake::when($this->permissionRepository)->getByFriendlyName->thenReturn(null);

        try
        {
            $this->permissionController->getDetail('Test Permission');
            $this->fail('A ContentNotFoundException exception should have been thrown');
        }
        catch (ContentNotFoundException $e)
        {
            Phake::verify($this->viewService, Phake::never())->renderView;
            $this->assertEquals('Permission Not Found', $e->getTitle());
            $this->assertEquals('I could not locate the permission Test Permission.', $e->getMessage());
            $this->assertEquals('/permissions/', $e->getRecommendedUrl());
            $this->assertEquals('View All Permissions', $e->getRecommendedAction());
        }
    }

    public function testPostDetail()
    {
        $existingPermission = PermissionEntity::createFromArray([
            'name' => 'Test Permission',
        ]);
        Phake::when($this->permissionRepository)->getByFriendlyName->thenReturn($existingPermission);

        $this->permissionController->postDetail('Test Permission', [
            'name' => 'Test Permission 2',
            'token' => '123456'
        ]);

        Phake::verify($this->csrfService)->validateToken('123456');

        Phake::verify($this->permissionRepository)->getByFriendlyName('Test Permission');
        /* @var $permission PermissionEntity */
        Phake::verify($this->permissionValidation)->validate($existingPermission);
        $this->assertEquals('Test Permission 2', $existingPermission->getName());

        Phake::verify($this->permissionRepository)->saveEntity($existingPermission);

        Phake::verify($this->viewService)->redirect('/permissions/', 303, 'Permission Test Permission 2 successfully edited!');
    }

    public function testGetRemove()
    {
        Phake::when($this->csrfService)->getNewToken->thenReturn('1itfuefduyp9h');

        $permission = PermissionEntity::createFromArray([
            'name' => 'Test Permission',
        ]);
        Phake::when($this->permissionRepository)->getListByFriendlyNames->thenReturn(new \ArrayIterator([$permission]));

        $_SERVER['HTTP_REFERER'] = '/mytest/';

        $this->permissionController->getRemove([
            'entities' => [
                'Test Permission',
                'permission2'
            ],
        ]);

        Phake::verify($this->permissionRepository)->getListByFriendlyNames(['Test Permission', 'permission2']);
        Phake::verify($this->viewService)->renderView('permissions/removeList', Phake::capture($templateData));

        $this->assertEquals('1itfuefduyp9h', $templateData['token']);
        $this->assertEquals([ $permission ], iterator_to_array($templateData['entities']));
        $this->assertEquals('/mytest/', $templateData['originalUrl']);
    }

    public function testPostRemove()
    {
        $this->permissionController->postRemove([
            'entities' => [
                'Test Permission',
                'permission2'
            ],
            'token' => '1itfuefduyp9h',
            'originalUrl' => '/mytest/',
        ]);

        Phake::verify($this->permissionRepository)->deleteByFriendlyNames(['Test Permission', 'permission2']);
        Phake::verify($this->viewService)->redirect('/mytest/', 303, 'Permissions successfully removed: Test Permission, permission2');
    }

    public function testPostRemoveInvalidToken()
    {
        Phake::when($this->csrfService)->validateToken->thenReturn(false);

        $this->permissionController->postRemove([
            'entities' => [
                'Test Permission',
                'permission2'
            ],
            'originalUrl' => '/mytest/',
        ]);

        Phake::verify($this->permissionRepository, Phake::never())->deleteByFriendlyNames;
        Phake::verify($this->viewService)->redirect('/mytest/', 303, "Your session has expired, please try deleting those permissions again");
    }
}