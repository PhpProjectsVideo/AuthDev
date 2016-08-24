<?php

namespace PhpProjects\AuthDev\Model\Group;

use Phake;
use PhpProjects\AuthDev\DatabaseTestCaseTrait;
use PhpProjects\AuthDev\Memcache\MemcacheService;
use PhpProjects\AuthDev\Model\DuplicateEntityException;
use PhpProjects\AuthDev\Model\Permission\PermissionEntity;
use PHPUnit\Framework\TestCase;
use PHPUnit_Extensions_Database_DataSet_IDataSet;

class GroupRepositoryTest extends TestCase
{
    use DatabaseTestCaseTrait {
        DatabaseTestCaseTrait::setUp as dbSetup;
    }

    /**
     * @var GroupRepository
     */
    private $groupRepository;

    /**
     * Returns the test dataset.
     *
     * @return PHPUnit_Extensions_Database_DataSet_IDataSet
     */
    protected function getDataSet()
    {
        return new \PHPUnit_Extensions_Database_DataSet_ArrayDataSet([
            'groups' => [
                [ 'id' => 1, 'name' => 'Group1'],
                [ 'id' => 2, 'name' => 'Group5'],
                [ 'id' => 3, 'name' => 'Group2'],
                [ 'id' => 4, 'name' => 'Group7'],
                [ 'id' => 5, 'name' => 'Group3'],
                [ 'id' => 6, 'name' => 'Group4'],
                [ 'id' => 7, 'name' => 'Group6'],
                [ 'id' => 8, 'name' => 'Group8'],
                [ 'id' => 9, 'name' => 'Group11'],
                [ 'id' => 10, 'name' => 'Group10'],
                [ 'id' => 11, 'name' => 'Group9'],
            ],
            'groups_permissions' => [
                [ 'groups_id' => 1, 'permissions_id' => 1 ],
                [ 'groups_id' => 1, 'permissions_id' => 2 ],
            ]
        ]);
    }

    protected function setUp()
    {
        $this->dbSetup();

        $memcacheService = Phake::mock(MemcacheService::class);
        Phake::when($memcacheService)->nsGet->thenReturn(false);

        $this->groupRepository = new GroupRepository($this->getPdo(), $memcacheService);
    }

    public function testGetSortedGroupList()
    {
        $groupList = $this->groupRepository->getSortedList(5);

        $groupList = iterator_to_array($groupList);
        $this->assertCount(5, $groupList);
        $this->assertEquals('Group1', $groupList[0]->getName());
        $this->assertEquals(1, $groupList[0]->getId());

        $this->assertEquals('Group10', $groupList[1]->getName());
        $this->assertEquals('Group11', $groupList[2]->getName());
        $this->assertEquals('Group2', $groupList[3]->getName());
        $this->assertEquals('Group3', $groupList[4]->getName());
    }

    public function testGetSortedGroupListWithOffset()
    {
        $groupList = $this->groupRepository->getSortedList(5, 5);

        $groupList = iterator_to_array($groupList);
        $this->assertCount(5, $groupList);
        $this->assertEquals('Group4', $groupList[0]->getName());
        $this->assertEquals(6, $groupList[0]->getId());

        $this->assertEquals('Group5', $groupList[1]->getName());
        $this->assertEquals('Group6', $groupList[2]->getName());
        $this->assertEquals('Group7', $groupList[3]->getName());
        $this->assertEquals('Group8', $groupList[4]->getName());
    }

    public function testGetGroupListByNames()
    {
        $groupList = $this->groupRepository->getListByFriendlyNames(['Group1', 'Group6']);

        $groupList = iterator_to_array($groupList);
        $this->assertCount(2, $groupList);
        $this->assertEquals('Group1', $groupList[0]->getName());
        $this->assertEquals('Group6', $groupList[1]->getName());

    }

    public function testGetGroupCount()
    {
        $groupCount = $this->groupRepository->getCount();
        $this->assertEquals(11, $groupCount);
    }

    public function testGetSearchResult()
    {
        $groupList = $this->groupRepository->getListMatchingFriendlyName('group1', 5);

        $groupList = iterator_to_array($groupList);
        $this->assertCount(3, $groupList);
        $this->assertEquals('Group1', $groupList[0]->getName());
        $this->assertEquals('Group10', $groupList[1]->getName());
        $this->assertEquals('Group11', $groupList[2]->getName());
    }

    public function testGetSearchResultWithOffset()
    {
        $groupList = $this->groupRepository->getListMatchingFriendlyName('Group1', 1, 1);

        $groupList = iterator_to_array($groupList);
        $this->assertCount(1, $groupList);
        $this->assertEquals('Group10', $groupList[0]->getName());
    }

    public function testGetGroupCountMatchingName()
    {
        $groupCount = $this->groupRepository->getCountMatchingFriendlyName('Group1');
        $this->assertEquals(3, $groupCount);
    }

    public function testSaveGroup()
    {
        $group = new GroupEntity();
        $group->setName('Test Group 1');

        $this->groupRepository->saveEntity($group);

        $queryTable = $this->getConnection()->createQueryTable('groups',
            "SELECT * FROM groups WHERE name = 'Test Group 1'"
        );
        $this->assertEquals(1, $queryTable->getRowCount());

        $this->assertEquals($group->getName(), $queryTable->getValue(0, 'name'));
        $this->assertNotEmpty($group->getId());
    }

    public function testSaveGroupWrapsUniqueConstraintForNames()
    {
        $group = new GroupEntity();
        $group->setName('Group1');

        try
        {
            $this->groupRepository->saveEntity($group);
            $this->fail("Exception never thrown");
        }
        catch (DuplicateEntityException $e)
        {
            $this->assertEquals('name', $e->getField());
            $this->assertInstanceOf(\PDOException::class, $e->getPrevious());
        }
    }

    public function testGetGroupByName()
    {
        $group = $this->groupRepository->getByFriendlyName('Group1');

        $this->assertEquals('Group1', $group->getName());
        $this->assertEquals(1, $group->getId());
    }

    public function testGetGroupByNameReturnsNullOnNoGroup()
    {
        $group = $this->groupRepository->getByFriendlyName('nothere');
        $this->assertNull($group);
    }

    public function testSaveExistingGroup()
    {
        $group = new GroupEntity(1);
        $group->setName('Test Group');

        $this->groupRepository->saveEntity($group);

        $queryTable = $this->getConnection()->createQueryTable('groups',
            "SELECT * FROM groups WHERE name = 'Test Group'"
        );
        $this->assertEquals(1, $queryTable->getRowCount());

        $this->assertEquals($group->getName(), $queryTable->getValue(0, 'name'));

        $queryTable = $this->getConnection()->createQueryTable('groups',
            "SELECT * FROM groups WHERE name = 'Group1'"
        );
        $this->assertEquals(0, $queryTable->getRowCount());
    }

    public function testDeleteGroupsByNames()
    {
        $this->groupRepository->deleteByFriendlyNames(['Group1', 'Group2']);
        $queryTable = $this->getConnection()->createQueryTable('groups',
            "SELECT * FROM groups WHERE name IN ('Group1', 'Group2')"
        );
        $this->assertEquals(0, $queryTable->getRowCount());
    }

    public function testGetGroupByGroupnameLoadsPermissions()
    {
        $group = $this->groupRepository->getByFriendlyName('Group1');
        $permission1 = new PermissionEntity(1);
        $permission2 = new PermissionEntity(2);
        $permission3 = new PermissionEntity(3);

        $this->assertTrue($group->isOwnerOfPermission($permission1));
        $this->assertTrue($group->isOwnerOfPermission($permission2));
        $this->assertFalse($group->isOwnerOfPermission($permission3));
    }

    public function testSavingPermissions()
    {
        $group = $this->groupRepository->getByFriendlyName('Group1');
        $group->addPermissions([3]);
        $group->removePermissions([1]);

        $this->groupRepository->saveEntity($group);

        $queryTable = $this->getConnection()->createQueryTable('groups_permissions',
            "SELECT permissions_id FROM groups_permissions WHERE groups_id = 1 ORDER BY permissions_id"
        );
        $this->assertEquals(2, $queryTable->getRowCount());
        $this->assertEquals(2, $queryTable->getValue(0, 'permissions_id'));
        $this->assertEquals(3, $queryTable->getValue(1, 'permissions_id'));
    }

}