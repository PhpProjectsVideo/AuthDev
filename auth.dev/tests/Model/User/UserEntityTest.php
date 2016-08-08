<?php

namespace PhpProjects\AuthDev\Model\User;

use PhpProjects\AuthDev\Model\Group\GroupEntity;
use PHPUnit\Framework\TestCase;

class UserEntityTest extends TestCase
{
    /**
     * @var UserEntity
     */
    private $user;
    
    protected function setUp()
    {
        $this->user = new UserEntity(1);
        $this->user->setUsername('mike.lively');
        $this->user->setEmail('m@digitalsandwich.com');
        $this->user->setName('Michael Lively');
    }
    
    public function testEntityCreation()
    {
        $this->assertEquals(1, $this->user->getId());
        $this->assertEquals('mike.lively', $this->user->getUsername());
        $this->assertEquals('m@digitalsandwich.com', $this->user->getEmail());
        $this->assertEquals('Michael Lively', $this->user->getName());
    }

    public function testEntityUpdateFromArray()
    {
        $this->user->updateFromArray([
            'id' => 2,
            'username' => 'mike.lively2',
            'email' => 'm2@digitalsandwich.com',
        ]);

        $this->assertEquals(2, $this->user->getId());
        $this->assertEquals('mike.lively2', $this->user->getUsername());
        $this->assertEquals('m2@digitalsandwich.com', $this->user->getEmail());
        $this->assertEquals('Michael Lively', $this->user->getName());
    }
    
    public function testPasswordHashing()
    {
        $this->user->setClearTextPassword('P@ssw0rd');
        
        $this->assertEquals('P@ssw0rd', $this->user->getClearTextPassword());
        $this->assertTrue(password_verify('P@ssw0rd', $this->user->getPasswordHash()));
    }

    public function testClearTextPasswordResetOnPasswordHashChange()
    {
        $this->user->setClearTextPassword('P@ssw0rd');
        $this->user->setPassword('passwordhash');

        $this->assertEquals('passwordhash', $this->user->getPasswordHash());
        $this->assertEmpty($this->user->getClearTextPassword());
    }

    public function testEntityUpdateFromArrayWithHashedPassword()
    {
        $this->user->updateFromArray([
            'clear-password' => 'oldpass',
            'password' => password_hash('P@ssw0rd', PASSWORD_BCRYPT, ['cost' => 10 ]),
        ]);
        
        $this->assertTrue(password_verify('P@ssw0rd', $this->user->getPasswordHash()));
        $this->assertEmpty($this->user->getClearTextPassword());
    }

    public function testEntityCreationFromArrayWithClearTextPassword()
    {
        $this->user->updateFromArray([
            'clear-password' => 'P@ssw0rd',
        ]);

        $this->assertTrue(password_verify('P@ssw0rd', $this->user->getPasswordHash()));
        $this->assertEquals('P@ssw0rd', $this->user->getClearTextPassword());
    }

    public function testEntityCreationFromArrayWithEmptyClearTextPassword()
    {
        $this->user->setPassword('hashedPassword');
        $this->user->updateFromArray([
            'username' => 'new.user',
            'clear-password' => '',
        ]);

        $this->assertEquals('hashedPassword', $this->user->getPasswordHash());
    }

    public function testAddingGroups()
    {
        $group1 = new GroupEntity(1);
        $group2 = new GroupEntity(2);

        $this->assertFalse($this->user->isMemberOfGroup($group1));
        $this->assertFalse($this->user->isMemberOfGroup($group2));

        $this->user->addGroups([1, 2]);

        $this->assertTrue($this->user->isMemberOfGroup($group1));
        $this->assertTrue($this->user->isMemberOfGroup($group2));
    }

    public function testRemovingGroups()
    {
        $group1 = new GroupEntity(1);
        $group2 = new GroupEntity(2);
        $this->user->addGroups([1, 2]);
        
        $this->user->removeGroups([1, 2]);

        $this->assertFalse($this->user->isMemberOfGroup($group1));
        $this->assertFalse($this->user->isMemberOfGroup($group2));
    }
    
    public function testGetGroupIds()
    {
        $this->user->addGroups([1, 2]);
        
        $this->assertEquals([1, 2], $this->user->getGroupIds());
    }
}