<?php

namespace PhpProjects\AuthDev\Model\User;

use PHPUnit\Framework\TestCase;

class UserValidationTest extends TestCase
{
    /**
     * @var UserValidation
     */
    private $userValidation;

    /**
     * @var UserEntity
     */
    private $user;

    protected function setUp()
    {
        $this->userValidation = new UserValidation();
        $this->user = new UserEntity();
        $this->user->setUsername('mike.lively');
        $this->user->setEmail('m@digitalsandwich.com');
        $this->user->setName('mike.lively');
        $this->user->setPassword('hashedpassword');
    }

    public function testValidateCanBeTrue()
    {
        $result = $this->userValidation->validate($this->user);
        $this->assertTrue($result->isValid());
    }

    public function testValidateCanBeTrueWithClearTextPassword()
    {
        $this->user->setPassword('');
        $this->user->setClearTextPassword('password');

        $result = $this->userValidation->validate($this->user);
        $this->assertTrue($result->isValid());
    }

    public function testValidateOnEmptyUserName()
    {
        $this->user->setUsername('');

        $result = $this->userValidation->validate($this->user);
        $this->assertFalse($result->isValid());

        $this->assertEmpty($result->getValidationErrorsForField('email'));
        $this->assertEmpty($result->getValidationErrorsForField('name'));
        $this->assertEmpty($result->getValidationErrorsForField('password'));
        $errors = $result->getValidationErrorsForField('username');
        $this->assertEquals(['Username is required'], $errors);
    }

    public function testValidateOnBadUserName()
    {
        $this->user->setUsername('mike.lively~~~~~');

        $result = $this->userValidation->validate($this->user);
        $this->assertFalse($result->isValid());

        $this->assertEmpty($result->getValidationErrorsForField('email'));
        $this->assertEmpty($result->getValidationErrorsForField('name'));
        $this->assertEmpty($result->getValidationErrorsForField('password'));
        $errors = $result->getValidationErrorsForField('username');
        $this->assertEquals(['Usernames must be less than 50 characters and can only contain a-z, A-Z, 0-9 or the characters . _ and -.'], $errors);
    }

    public function testValidateOnBlankPassword()
    {
        $this->user->setPassword('');

        $result = $this->userValidation->validate($this->user);
        $this->assertFalse($result->isValid());

        $this->assertEmpty($result->getValidationErrorsForField('username'));
        $this->assertEmpty($result->getValidationErrorsForField('email'));
        $this->assertEmpty($result->getValidationErrorsForField('name'));
        $errors = $result->getValidationErrorsForField('password');
        $this->assertEquals(['Password is required'], $errors);
    }

    public function testValidateOnEmptyName()
    {
        $this->user->setName('');

        $result = $this->userValidation->validate($this->user);
        $this->assertFalse($result->isValid());

        $this->assertEmpty($result->getValidationErrorsForField('username'));
        $this->assertEmpty($result->getValidationErrorsForField('email'));
        $errors = $result->getValidationErrorsForField('name');
        $this->assertEquals(['Name is required'], $errors);
    }

    public function testValidateOnBadName()
    {
        $this->user->setName(str_repeat('abc', 100));

        $result = $this->userValidation->validate($this->user);
        $this->assertFalse($result->isValid());

        $this->assertEmpty($result->getValidationErrorsForField('username'));
        $this->assertEmpty($result->getValidationErrorsForField('email'));
        $this->assertEmpty($result->getValidationErrorsForField('password'));
        $errors = $result->getValidationErrorsForField('name');
        $this->assertEquals(['Names can only be up to 100 characters long.'], $errors);
    }

    public function testValidateOnEmptyEmail()
    {
        $this->user->setEmail('');

        $result = $this->userValidation->validate($this->user);
        $this->assertFalse($result->isValid());

        $this->assertEmpty($result->getValidationErrorsForField('username'));
        $this->assertEmpty($result->getValidationErrorsForField('password'));
        $this->assertEmpty($result->getValidationErrorsForField('name'));
        $errors = $result->getValidationErrorsForField('email');
        $this->assertEquals(['Email is required'], $errors);
    }

    public function testValidateOnBadEmail()
    {
        $this->user->setEmail('noemail');

        $result = $this->userValidation->validate($this->user);
        $this->assertFalse($result->isValid());

        $this->assertEmpty($result->getValidationErrorsForField('username'));
        $this->assertEmpty($result->getValidationErrorsForField('name'));
        $this->assertEmpty($result->getValidationErrorsForField('password'));
        $errors = $result->getValidationErrorsForField('email');
        $this->assertEquals(['You must enter a valid email. Please try another.'], $errors);
    }
}