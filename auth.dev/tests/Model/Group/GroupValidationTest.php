<?php

namespace PhpProjects\AuthDev\Model\Group;

use PHPUnit\Framework\TestCase;

class GroupValidationTest extends TestCase
{
    /**
     * @var GroupValidation
     */
    private $groupValidation;

    /**
     * @var GroupEntity
     */
    private $group;

    protected function setUp()
    {
        $this->groupValidation = new GroupValidation();
        $this->group = new GroupEntity();
        $this->group->setName('Test Group');
    }

    public function testValidateCanBeTrue()
    {
        $result = $this->groupValidation->validate($this->group);
        $this->assertTrue($result->isValid());
    }
    
    public function testValidateOnEmptyName()
    {
        $this->group->setName('');

        $result = $this->groupValidation->validate($this->group);
        $this->assertFalse($result->isValid());

        $errors = $result->getValidationErrorsForField('name');
        $this->assertEquals(['Name is required'], $errors);
    }

    public function testValidateOnBadName()
    {
        $this->group->setName(str_repeat('abc', 100));

        $result = $this->groupValidation->validate($this->group);
        $this->assertFalse($result->isValid());

        $errors = $result->getValidationErrorsForField('name');
        $this->assertEquals(['Names can only be up to 100 characters long.'], $errors);
    }
}