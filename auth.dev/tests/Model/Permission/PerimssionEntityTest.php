<?php

namespace PhpProjects\AuthDev\Model\Permission;

use PHPUnit\Framework\TestCase;

class PermissionEntityTest extends TestCase
{
    /**
     * @var PermissionEntity
     */
    private $permission;

    protected function setUp()
    {
        $this->permission = new PermissionEntity(1);
        $this->permission->setName('Test Permission');
    }

    public function testEntityCreation()
    {
        $this->assertEquals(1, $this->permission->getId());
        $this->assertEquals('Test Permission', $this->permission->getName());
    }

    public function testEntityUpdateFromArray()
    {
        $this->permission->updateFromArray([
            'id' => 2,
            'name' => 'Test Permission 2',
        ]);

        $this->assertEquals(2, $this->permission->getId());
        $this->assertEquals('Test Permission 2', $this->permission->getName());
    }
}