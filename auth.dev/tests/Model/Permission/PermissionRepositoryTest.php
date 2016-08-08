<?php

namespace PhpProjects\AuthDev\Model\Permission;

use PhpProjects\AuthDev\DatabaseTestCaseTrait;
use PhpProjects\AuthDev\Model\DuplicateEntityException;
use PHPUnit\Framework\TestCase;
use PHPUnit_Extensions_Database_DataSet_IDataSet;

class PermissionRepositoryTest extends TestCase
{
    use DatabaseTestCaseTrait {
        DatabaseTestCaseTrait::setUp as dbSetup;
    }

    /**
     * @var PermissionRepository
     */
    private $permissionRepository;

    /**
     * Returns the test dataset.
     *
     * @return PHPUnit_Extensions_Database_DataSet_IDataSet
     */
    protected function getDataSet()
    {
        return new \PHPUnit_Extensions_Database_DataSet_ArrayDataSet([
            'permissions' => [
                [ 'id' => 1, 'name' => 'Permission1'],
                [ 'id' => 2, 'name' => 'Permission5'],
                [ 'id' => 3, 'name' => 'Permission2'],
                [ 'id' => 4, 'name' => 'Permission7'],
                [ 'id' => 5, 'name' => 'Permission3'],
                [ 'id' => 6, 'name' => 'Permission4'],
                [ 'id' => 7, 'name' => 'Permission6'],
                [ 'id' => 8, 'name' => 'Permission8'],
                [ 'id' => 9, 'name' => 'Permission11'],
                [ 'id' => 10, 'name' => 'Permission10'],
                [ 'id' => 11, 'name' => 'Permission9'],
            ],
        ]);
    }

    protected function setUp()
    {
        $this->dbSetup();

        $this->permissionRepository = new PermissionRepository($this->getPdo());
    }

    public function testGetSortedPermissionList()
    {
        $permissionList = $this->permissionRepository->getSortedList(5);

        $permissionList = iterator_to_array($permissionList);
        $this->assertCount(5, $permissionList);
        $this->assertEquals('Permission1', $permissionList[0]->getName());
        $this->assertEquals(1, $permissionList[0]->getId());

        $this->assertEquals('Permission10', $permissionList[1]->getName());
        $this->assertEquals('Permission11', $permissionList[2]->getName());
        $this->assertEquals('Permission2', $permissionList[3]->getName());
        $this->assertEquals('Permission3', $permissionList[4]->getName());
    }

    public function testGetSortedPermissionListWithOffset()
    {
        $permissionList = $this->permissionRepository->getSortedList(5, 5);

        $permissionList = iterator_to_array($permissionList);
        $this->assertCount(5, $permissionList);
        $this->assertEquals('Permission4', $permissionList[0]->getName());
        $this->assertEquals(6, $permissionList[0]->getId());

        $this->assertEquals('Permission5', $permissionList[1]->getName());
        $this->assertEquals('Permission6', $permissionList[2]->getName());
        $this->assertEquals('Permission7', $permissionList[3]->getName());
        $this->assertEquals('Permission8', $permissionList[4]->getName());
    }

    public function testGetPermissionListByNames()
    {
        $permissionList = $this->permissionRepository->getListByFriendlyNames(['Permission1', 'Permission6']);

        $permissionList = iterator_to_array($permissionList);
        $this->assertCount(2, $permissionList);
        $this->assertEquals('Permission1', $permissionList[0]->getName());
        $this->assertEquals('Permission6', $permissionList[1]->getName());

    }

    public function testGetPermissionCount()
    {
        $permissionCount = $this->permissionRepository->getCount();
        $this->assertEquals(11, $permissionCount);
    }

    public function testGetSearchResult()
    {
        $permissionList = $this->permissionRepository->getListMatchingFriendlyName('permission1', 5);

        $permissionList = iterator_to_array($permissionList);
        $this->assertCount(3, $permissionList);
        $this->assertEquals('Permission1', $permissionList[0]->getName());
        $this->assertEquals('Permission10', $permissionList[1]->getName());
        $this->assertEquals('Permission11', $permissionList[2]->getName());
    }

    public function testGetSearchResultWithOffset()
    {
        $permissionList = $this->permissionRepository->getListMatchingFriendlyName('Permission1', 1, 1);

        $permissionList = iterator_to_array($permissionList);
        $this->assertCount(1, $permissionList);
        $this->assertEquals('Permission10', $permissionList[0]->getName());
    }

    public function testGetPermissionCountMatchingName()
    {
        $permissionCount = $this->permissionRepository->getCountMatchingFriendlyName('Permission1');
        $this->assertEquals(3, $permissionCount);
    }

    public function testSavePermission()
    {
        $permission = new PermissionEntity();
        $permission->setName('Test Permission 1');

        $this->permissionRepository->saveEntity($permission);

        $queryTable = $this->getConnection()->createQueryTable('permissions',
            "SELECT * FROM permissions WHERE name = 'Test Permission 1'"
        );
        $this->assertEquals(1, $queryTable->getRowCount());

        $this->assertEquals($permission->getName(), $queryTable->getValue(0, 'name'));
        $this->assertNotEmpty($permission->getId());
    }

    public function testSavePermissionWrapsUniqueConstraintForNames()
    {
        $permission = new PermissionEntity();
        $permission->setName('Permission1');

        try
        {
            $this->permissionRepository->saveEntity($permission);
            $this->fail("Exception never thrown");
        }
        catch (DuplicateEntityException $e)
        {
            $this->assertEquals('name', $e->getField());
            $this->assertInstanceOf(\PDOException::class, $e->getPrevious());
        }
    }

    public function testGetPermissionByName()
    {
        $permission = $this->permissionRepository->getByFriendlyName('Permission1');

        $this->assertEquals('Permission1', $permission->getName());
        $this->assertEquals(1, $permission->getId());
    }

    public function testGetPermissionByNameReturnsNullOnNoPermission()
    {
        $permission = $this->permissionRepository->getByFriendlyName('nothere');
        $this->assertNull($permission);
    }

    public function testSaveExistingPermission()
    {
        $permission = new PermissionEntity(1);
        $permission->setName('Test Permission');

        $this->permissionRepository->saveEntity($permission);

        $queryTable = $this->getConnection()->createQueryTable('permissions',
            "SELECT * FROM permissions WHERE name = 'Test Permission'"
        );
        $this->assertEquals(1, $queryTable->getRowCount());

        $this->assertEquals($permission->getName(), $queryTable->getValue(0, 'name'));

        $queryTable = $this->getConnection()->createQueryTable('permissions',
            "SELECT * FROM permissions WHERE name = 'Permission1'"
        );
        $this->assertEquals(0, $queryTable->getRowCount());
    }

    public function testDeletePermissionsByNames()
    {
        $this->permissionRepository->deleteByFriendlyNames(['Permission1', 'Permission2']);
        $queryTable = $this->getConnection()->createQueryTable('permissions',
            "SELECT * FROM permissions WHERE name IN ('Permission1', 'Permission2')"
        );
        $this->assertEquals(0, $queryTable->getRowCount());
    }
}