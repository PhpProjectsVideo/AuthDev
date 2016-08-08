<?php

namespace PhpProjects\AuthDev\Controllers;

use Phake;
use PhpProjects\AuthDev\Model\Csrf\CsrfService;
use PhpProjects\AuthDev\Model\DuplicateEntityException;
use PhpProjects\AuthDev\Model\Group\GroupEntity;
use PhpProjects\AuthDev\Model\Group\GroupRepository;
use PhpProjects\AuthDev\Model\Group\GroupValidation;
use PhpProjects\AuthDev\Model\ValidationResults;
use PhpProjects\AuthDev\Views\ViewService;
use PHPUnit\Framework\TestCase;

class GroupControllerTest extends TestCase
{
    /**
     * @var GroupController
     */
    private $groupController;

    /**
     * @var GroupRepository
     */
    private $groupRepository;

    /**
     * @var \ArrayIterator
     */
    private $groupList;

    /**
     * @var ViewService
     */
    private $viewService;

    /**
     * @var GroupValidation
     */
    private $groupValidation;

    /**
     * @var CsrfService
     */
    private $csrfService;

    protected function setUp()
    {
        $this->groupList = new \ArrayIterator([
            GroupEntity::createFromArray([ 'name' => 'taken.group01' ]),
            GroupEntity::createFromArray([ 'name' => 'taken.group02' ]),
            GroupEntity::createFromArray([ 'name' => 'taken.group03' ]),
        ]);

        $this->viewService = Phake::mock(ViewService::class);

        $this->groupRepository = Phake::mock(GroupRepository::class);
        Phake::when($this->groupRepository)->getSortedGroupList->thenReturn($this->groupList);
        Phake::when($this->groupRepository)->getGroupCount->thenReturn(30);
        Phake::when($this->groupRepository)->getGroupsMatchingName->thenReturn($this->groupList);
        Phake::when($this->groupRepository)->getGroupCountMatchingName->thenReturn(30);

        $this->groupValidation = Phake::mock(GroupValidation::class);
        Phake::when($this->groupValidation)->validate->thenReturn(new ValidationResults([]));

        $this->csrfService = Phake::mock(CsrfService::class);
        Phake::when($this->csrfService)->validateToken->thenReturn(true);

        $this->groupController = new GroupController($this->viewService, $this->groupRepository, $this->groupValidation, $this->csrfService);
    }

    public function testGetListPage1()
    {
        $this->groupController->getList(1);

        Phake::verify($this->groupRepository)->getSortedGroupList(10, 0);
        Phake::verify($this->groupRepository)->getGroupCount();
        Phake::verify($this->viewService)->renderView('groups/list', [
            'groups' => $this->groupList,
            'currentPage' => 1,
            'totalPages' => 3,
            'term' => '',
        ]);
    }

    public function testGetListPage2()
    {
        $this->groupController->getList(2);

        Phake::verify($this->groupRepository)->getSortedGroupList(10, 10);
        Phake::verify($this->groupRepository)->getGroupCount();
        Phake::verify($this->viewService)->renderView('groups/list', [
            'groups' => $this->groupList,
            'currentPage' => 2,
            'totalPages' => 3,
            'term' => '',
        ]);
    }

    public function testGetListWithSearchPage1()
    {
        $this->groupController->getList(1, 'group0');

        Phake::verify($this->groupRepository, Phake::never())->getSortedGroupList;
        Phake::verify($this->groupRepository, Phake::never())->getGroupCount;
        Phake::verify($this->groupRepository)->getGroupsMatchingName('group0', 10, 0);
        Phake::verify($this->groupRepository)->getGroupCountMatchingName('group0');
        Phake::verify($this->viewService)->renderView('groups/list', [
            'groups' => $this->groupList,
            'currentPage' => 1,
            'totalPages' => 3,
            'term' => 'group0',
        ]);
    }

    public function testGetListWithSearchPage2()
    {
        $this->groupController->getList(2, 'group0');

        Phake::verify($this->groupRepository, Phake::never())->getSortedGroupList;
        Phake::verify($this->groupRepository, Phake::never())->getGroupCount;
        Phake::verify($this->groupRepository)->getGroupsMatchingName('group0', 10, 10);
        Phake::verify($this->groupRepository)->getGroupCountMatchingName('group0');
        Phake::verify($this->viewService)->renderView('groups/list', [
            'groups' => $this->groupList,
            'currentPage' => 2,
            'totalPages' => 3,
            'term' => 'group0',
        ]);
    }

    public function testGetListChecksRedirectMessage()
    {
        Phake::when($this->viewService)->getRedirectMessage->thenReturn('My flash message');

        $this->groupController->getList();

        Phake::verify($this->viewService)->getRedirectMessage();
        Phake::verify($this->viewService)->renderView($this->anything(), Phake::capture($templateData));

        $this->assertArrayHasKey('message', $templateData);
        $this->assertEquals('My flash message', $templateData['message']);
    }

    public function testGetNew()
    {
        Phake::when($this->csrfService)->getNewToken->thenReturn('1itfuefduyp9h');

        $this->groupController->getNew();

        Phake::verify($this->viewService)->renderView('groups/form', Phake::capture($templateData));
        Phake::verify($this->csrfService)->getNewToken();

        $this->assertArrayHasKey('group', $templateData);
        $this->assertInstanceOf(GroupEntity::class, $templateData['group']);

        $this->assertArrayHasKey('validationResults', $templateData);
        $this->assertInstanceOf(ValidationResults::class, $templateData['validationResults']);
        $this->assertTrue($templateData['validationResults']->isValid());

        $this->assertArrayHasKey('token', $templateData);
        $this->assertEquals('1itfuefduyp9h', $templateData['token']);
    }

    public function testPostNew()
    {
        $this->groupController->postNew([
            'name' => 'Test Group',
            'token' => '123456',
        ]);

        /* @var $group GroupEntity */
        Phake::verify($this->groupValidation)->validate(Phake::capture($group));
        $this->assertEquals('Test Group', $group->getName());

        Phake::verify($this->csrfService)->validateToken('123456');
        Phake::verify($this->groupRepository)->saveGroup($group);


        Phake::verify($this->viewService)->redirect('/groups/', 303, 'Group Test Group successfully edited!');
    }

    public function testPostNewInvalid()
    {
        $validationResult = new ValidationResults(['name' => [ 'name is empty' ]]);
        Phake::when($this->groupValidation)->validate->thenReturn($validationResult);

        $this->groupController->postNew([
            'name' => '',
        ]);

        Phake::verify($this->groupRepository, Phake::never())->saveGroup;
        Phake::verify($this->viewService, Phake::never())->redirect;

        Phake::verify($this->viewService)->renderView('groups/form', Phake::capture($templateData));
        $this->assertArrayHasKey('group', $templateData);
        $this->assertEquals('', $templateData['group']->getName());

        $this->assertArrayHasKey('validationResults', $templateData);
        $this->assertEquals($validationResult, $templateData['validationResults']);
    }
    public function testPostNewDuplicateGroup()
    {
        Phake::when($this->groupRepository)->saveEntity->thenThrow(new DuplicateEntityException('name', new \Exception()));

        $this->groupController->postNew([
            'name' => 'Test Group',
        ]);

        Phake::verify($this->viewService, Phake::never())->redirect;

        Phake::verify($this->viewService)->renderView('groups/form', Phake::capture($templateData));

        $this->assertArrayHasKey('validationResults', $templateData);
        $this->assertEquals(['This name is already registered. Please try another.'], $templateData['validationResults']->getValidationErrorsForField('name'));
    }

    public function testPostNewMismatchedCsrfToken()
    {
        Phake::when($this->csrfService)->validateToken->thenReturn(false);

        $this->groupController->postNew([
            'name' => 'Test Group',
            'token' => '123456',
        ]);

        Phake::verify($this->viewService, Phake::never())->redirect;

        Phake::verify($this->viewService)->renderView('groups/form', Phake::capture($templateData));

        $this->assertArrayHasKey('validationResults', $templateData);
        $this->assertEquals(['Your session has expired, please try again'], $templateData['validationResults']->getValidationErrorsForField('form'));
    }

    public function testGetDetail()
    {
        Phake::when($this->csrfService)->getNewToken->thenReturn('1itfuefduyp9h');

        $group = GroupEntity::createFromArray([
            'name' => 'Test Group',
        ]);
        Phake::when($this->groupRepository)->getGroupByName->thenReturn($group);

        $this->groupController->getDetail('Test Group');

        Phake::verify($this->groupRepository)->getGroupByName('Test Group');
        Phake::verify($this->viewService)->renderView('groups/form', [
            'group' => $group,
            'validationResults' => new ValidationResults([]),
            'token' => '1itfuefduyp9h',
        ]);
    }

    public function testGetDetailNoGroup()
    {
        Phake::when($this->groupRepository)->getGroupByName->thenReturn(null);

        try
        {
            $this->groupController->getDetail('Test Group');
            $this->fail('A ContentNotFoundException exception should have been thrown');
        }
        catch (ContentNotFoundException $e)
        {
            Phake::verify($this->viewService, Phake::never())->renderView;
            $this->assertEquals('Group Not Found', $e->getTitle());
            $this->assertEquals('I could not locate the group Test Group.', $e->getMessage());
            $this->assertEquals('/groups/', $e->getRecommendedUrl());
            $this->assertEquals('View All Groups', $e->getRecommendedAction());
        }
    }

    public function testPostDetail()
    {
        $existingGroup = GroupEntity::createFromArray([
            'name' => 'Test Group',
        ]);
        Phake::when($this->groupRepository)->getGroupByName->thenReturn($existingGroup);

        $this->groupController->postDetail('Test Group', [
            'name' => 'Test Group 2',
            'token' => '123456'
        ]);

        Phake::verify($this->csrfService)->validateToken('123456');

        Phake::verify($this->groupRepository)->getGroupByName('Test Group');
        /* @var $group GroupEntity */
        Phake::verify($this->groupValidation)->validate($existingGroup);
        $this->assertEquals('Test Group 2', $existingGroup->getName());

        Phake::verify($this->groupRepository)->saveGroup($existingGroup);

        Phake::verify($this->viewService)->redirect('/groups/', 303, 'Group Test Group 2 successfully edited!');
    }

    public function testGetRemove()
    {
        Phake::when($this->csrfService)->getNewToken->thenReturn('1itfuefduyp9h');

        $group = GroupEntity::createFromArray([
            'name' => 'Test Group',
        ]);
        Phake::when($this->groupRepository)->getGroupListByNames->thenReturn(new \ArrayIterator([$group]));

        $_SERVER['HTTP_REFERER'] = '/mytest/';

        $this->groupController->getRemove([
            'groups' => [
                'Test Group',
                'group2'
            ],
        ]);

        Phake::verify($this->groupRepository)->getGroupListByNames(['Test Group', 'group2']);
        Phake::verify($this->viewService)->renderView('groups/removeList', Phake::capture($templateData));

        $this->assertEquals('1itfuefduyp9h', $templateData['token']);
        $this->assertEquals([ $group ], iterator_to_array($templateData['groups']));
        $this->assertEquals('/mytest/', $templateData['originalUrl']);
    }

    public function testPostRemove()
    {
        $this->groupController->postRemove([
            'groups' => [
                'Test Group',
                'group2'
            ],
            'token' => '1itfuefduyp9h',
            'originalUrl' => '/mytest/',
        ]);

        Phake::verify($this->groupRepository)->deleteGroupsByNames(['Test Group', 'group2']);
        Phake::verify($this->viewService)->redirect('/mytest/', 303, 'Groups successfully removed: Test Group, group2');
    }

    public function testPostRemoveInvalidToken()
    {
        Phake::when($this->csrfService)->validateToken->thenReturn(false);

        $this->groupController->postRemove([
            'groups' => [
                'Test Group',
                'group2'
            ],
            'originalUrl' => '/mytest/',
        ]);

        Phake::verify($this->groupRepository, Phake::never())->deleteGroupsByNames;
        Phake::verify($this->viewService)->redirect('/mytest/', 303, "Your session has expired, please try deleting those groups again");
    }
}