<?php

namespace PhpProjects\AuthDev\Model\Permission;

use PHPUnit\Framework\TestCase;

class PermissionValidationTest extends TestCase
{
    /**
     * @var PermissionValidation
     */
    private $permissionValidation;

    /**
     * @var PermissionEntity
     */
    private $permission;

    protected function setUp()
    {
        $this->permissionValidation = new PermissionValidation();
        $this->permission = new PermissionEntity();
        $this->permission->setName('Test Permission');
    }

    public function testValidateCanBeTrue()
    {
        $result = $this->permissionValidation->validate($this->permission);
        $this->assertTrue($result->isValid());
    }

    public function testValidateOnEmptyName()
    {
        $this->permission->setName('');

        $result = $this->permissionValidation->validate($this->permission);
        $this->assertFalse($result->isValid());

        $errors = $result->getValidationErrorsForField('name');
        $this->assertEquals(['Name is required'], $errors);
    }

    public function testValidateOnBadName()
    {
        $this->permission->setName(str_repeat('abc', 100));

        $result = $this->permissionValidation->validate($this->permission);
        $this->assertFalse($result->isValid());

        $errors = $result->getValidationErrorsForField('name');
        $this->assertEquals(['Names can only be up to 100 characters long.'], $errors);
    }
}