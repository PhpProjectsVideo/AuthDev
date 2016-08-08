<?php

namespace PhpProjects\AuthDev\Model\Group;

use PhpProjects\AuthDev\Model\Permission\PermissionEntity;
use PHPUnit\Framework\TestCase;

class GroupEntityTest extends TestCase
{
    /**
     * @var GroupEntity
     */
    private $group;

    protected function setUp()
    {
        $this->group = new GroupEntity(1);
        $this->group->setName('Test Group');
    }

    public function testEntityCreation()
    {
        $this->assertEquals(1, $this->group->getId());
        $this->assertEquals('Test Group', $this->group->getName());
    }

    public function testEntityUpdateFromArray()
    {
        $this->group->updateFromArray([
            'id' => 2,
            'name' => 'Test Group 2',
        ]);

        $this->assertEquals(2, $this->group->getId());
        $this->assertEquals('Test Group 2', $this->group->getName());
    }

    public function testAddingPermissions()
    {
        $permission1 = new PermissionEntity(1);
        $permission2 = new PermissionEntity(2);

        $this->assertFalse($this->group->isOwnerOfPermission($permission1));
        $this->assertFalse($this->group->isOwnerOfPermission($permission2));

        $this->group->addPermissions([1, 2]);

        $this->assertTrue($this->group->isOwnerOfPermission($permission1));
        $this->assertTrue($this->group->isOwnerOfPermission($permission2));
    }

    public function testRemovingPermissions()
    {
        $permission1 = new PermissionEntity(1);
        $permission2 = new PermissionEntity(2);
        $this->group->addPermissions([1, 2]);

        $this->group->removePermissions([1, 2]);

        $this->assertFalse($this->group->isOwnerOfPermission($permission1));
        $this->assertFalse($this->group->isOwnerOfPermission($permission2));
    }

    public function testGetPermissionIds()
    {
        $this->group->addPermissions([1, 2]);

        $this->assertEquals([1, 2], $this->group->getPermissionIds());
    }
}