<?php

namespace PhpProjects\AuthDev\Model\Group;

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
}