<?php

namespace PhpProjects\AuthDev\Model\User;

use PhpProjects\AuthDev\DatabaseTestCaseTrait;
use PhpProjects\AuthDev\Model\DuplicateEntityException;
use PhpProjects\AuthDev\Model\Group\GroupEntity;
use PHPUnit\Framework\TestCase;
use PHPUnit_Extensions_Database_DataSet_IDataSet;

class UserRepositoryTest extends TestCase
{
    use DatabaseTestCaseTrait {
        DatabaseTestCaseTrait::setUp as dbSetup;
    }

    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var string
     */
    private $password;

    /**
     * Returns the test dataset.
     *
     * @return PHPUnit_Extensions_Database_DataSet_IDataSet
     */
    protected function getDataSet()
    {
        return new \PHPUnit_Extensions_Database_DataSet_ArrayDataSet([
            'users' => [
                [ 'id' => 1, 'username' => 'taken.user02', 'email' => 'taken2@digitalsandwich.com', 'name' => 'Existing User 2', 'password' => $this->password ],
                [ 'id' => 2, 'username' => 'taken.user07', 'email' => 'taken7@digitalsandwich.com', 'name' => 'Existing User 7', 'password' => $this->password ],
                [ 'id' => 3, 'username' => 'taken.user05', 'email' => 'taken5@digitalsandwich.com', 'name' => 'Existing User 5', 'password' => $this->password ],
                [ 'id' => 4, 'username' => 'taken.user10', 'email' => 'taken10@digitalsandwich.com', 'name' => 'Existing User 10', 'password' => $this->password ],
                [ 'id' => 5, 'username' => 'taken.user06', 'email' => 'taken6@digitalsandwich.com', 'name' => 'Existing User 6', 'password' => $this->password ],
                [ 'id' => 6, 'username' => 'taken.user01', 'email' => 'taken1@digitalsandwich.com', 'name' => 'Existing User 1', 'password' => $this->password ],
                [ 'id' => 7, 'username' => 'taken.user04', 'email' => 'taken4@digitalsandwich.com', 'name' => 'Existing User 4', 'password' => $this->password ],
                [ 'id' => 8, 'username' => 'taken.user09', 'email' => 'taken9@digitalsandwich.com', 'name' => 'Existing User 9', 'password' => $this->password ],
                [ 'id' => 9, 'username' => 'taken.user03', 'email' => 'taken3@digitalsandwich.com', 'name' => 'Existing User 3', 'password' => $this->password ],
                [ 'id' => 10, 'username' => 'taken.user08', 'email' => 'taken8@digitalsandwich.com', 'name' => 'Existing User 8', 'password' => $this->password ],
                [ 'id' => 11, 'username' => 'taken.user11', 'email' => 'taken11@digitalsandwich.com', 'name' => 'Existing User 11', 'password' => $this->password ],
            ],
            'users_groups' => [
                [ 'users_id' => 6, 'groups_id' => 1 ],
                [ 'users_id' => 6, 'groups_id' => 2 ],
            ]
        ]);
    }

    protected function setUp()
    {
        $this->password = password_hash('P@ssw0rd', PASSWORD_BCRYPT, ['cost' => 10 ]);
        $this->dbSetup();
        
        $this->userRepository = new UserRepository($this->getPdo());
    }

    public function testgetSortedList()
    {
        $userList = $this->userRepository->getSortedList(5);

        $userList = iterator_to_array($userList);
        $this->assertCount(5, $userList);
        $this->assertEquals('taken.user01', $userList[0]->getUsername());
        $this->assertEquals('taken1@digitalsandwich.com', $userList[0]->getEmail());
        $this->assertEquals('Existing User 1', $userList[0]->getName());
        $this->assertEquals($this->password, $userList[0]->getPasswordHash());
        $this->assertEquals(6, $userList[0]->getId());

        $this->assertEquals('taken.user02', $userList[1]->getUsername());
        $this->assertEquals('taken.user03', $userList[2]->getUsername());
        $this->assertEquals('taken.user04', $userList[3]->getUsername());
        $this->assertEquals('taken.user05', $userList[4]->getUsername());
    }

    public function testgetSortedListWithOffset()
    {
        $userList = $this->userRepository->getSortedList(5, 5);

        $userList = iterator_to_array($userList);
        $this->assertCount(5, $userList);
        $this->assertEquals('taken.user06', $userList[0]->getUsername());
        $this->assertEquals('taken6@digitalsandwich.com', $userList[0]->getEmail());
        $this->assertEquals('Existing User 6', $userList[0]->getName());
        $this->assertEquals($this->password, $userList[0]->getPasswordHash());

        $this->assertEquals('taken.user07', $userList[1]->getUsername());
        $this->assertEquals('taken.user08', $userList[2]->getUsername());
        $this->assertEquals('taken.user09', $userList[3]->getUsername());
        $this->assertEquals('taken.user10', $userList[4]->getUsername());
    }

    public function testGetUserListByUsernames()
    {
        $userList = $this->userRepository->getListByFriendlyNames(['taken.user01', 'taken.user06']);

        $userList = iterator_to_array($userList);
        $this->assertCount(2, $userList);
        $this->assertEquals('taken.user01', $userList[0]->getUsername());
        $this->assertEquals('taken1@digitalsandwich.com', $userList[0]->getEmail());
        $this->assertEquals('Existing User 1', $userList[0]->getName());
        $this->assertEquals($this->password, $userList[0]->getPasswordHash());
        $this->assertEquals('taken.user06', $userList[1]->getUsername());
        $this->assertEquals('taken6@digitalsandwich.com', $userList[1]->getEmail());
        $this->assertEquals('Existing User 6', $userList[1]->getName());
        $this->assertEquals($this->password, $userList[1]->getPasswordHash());

    }

    public function testGetUserCount()
    {
        $userCount = $this->userRepository->getCount();
        $this->assertEquals(11, $userCount);
    }

    public function testGetSearchResult()
    {
        $userList = $this->userRepository->getListMatchingFriendlyName('taken.user1', 5);

        $userList = iterator_to_array($userList);
        $this->assertCount(2, $userList);
        $this->assertEquals('taken.user10', $userList[0]->getUsername());
        $this->assertEquals('taken10@digitalsandwich.com', $userList[0]->getEmail());
        $this->assertEquals('Existing User 10', $userList[0]->getName());
        $this->assertEquals($this->password, $userList[0]->getPasswordHash());
        $this->assertEquals(4, $userList[0]->getId());

        $this->assertEquals('taken.user11', $userList[1]->getUsername());
    }

    public function testGetSearchResultWithOffset()
    {
        $userList = $this->userRepository->getListMatchingFriendlyName('taken.user1', 1, 1);

        $userList = iterator_to_array($userList);
        $this->assertCount(1, $userList);
        $this->assertEquals('taken.user11', $userList[0]->getUsername());
        $this->assertEquals('taken11@digitalsandwich.com', $userList[0]->getEmail());
        $this->assertEquals('Existing User 11', $userList[0]->getName());
        $this->assertEquals($this->password, $userList[0]->getPasswordHash());
    }
    
    public function testGetUserCountMatchingUsername()
    {
        $userCount = $this->userRepository->getCountMatchingFriendlyName('taken.user1');
        $this->assertEquals(2, $userCount);
    }

    public function testSaveUser()
    {
        $user = new UserEntity();
        $user->setUsername('mike.lively');
        $user->setEmail('m@digitalsandwich.com');
        $user->setName('Mike Lively');
        $user->setPassword($this->password);

        $this->userRepository->saveEntity($user);

        $queryTable = $this->getConnection()->createQueryTable('users',
            "SELECT * FROM users WHERE username = 'mike.lively'"
        );
        $this->assertEquals(1, $queryTable->getRowCount());

        $this->assertEquals($user->getUsername(), $queryTable->getValue(0, 'username'));
        $this->assertEquals($user->getEmail(), $queryTable->getValue(0, 'email'));
        $this->assertEquals($user->getName(), $queryTable->getValue(0, 'name'));
        $this->assertEquals($user->getPasswordHash(), $queryTable->getValue(0, 'password'));
        $this->assertNotEmpty($user->getId());
    }
    
    public function testSaveUserWrapsUniqueConstraintForUsernames()
    {
        $user = new UserEntity();
        $user->setUsername('taken.user01');
        $user->setEmail('m@digitalsandwich.com');
        $user->setName('Mike Lively');
        $user->setPassword($this->password);

        try
        {
            $this->userRepository->saveEntity($user);
            $this->fail("Exception never thrown");
        }
        catch (DuplicateEntityException $e)
        {
            $this->assertEquals('username', $e->getField());
            $this->assertInstanceOf(\PDOException::class, $e->getPrevious());
        }
    }
    
    public function testSaveUserWrapsUniqueConstraintForEmails()
    {
        $user = new UserEntity();
        $user->setUsername('mike.lively');
        $user->setEmail('taken1@digitalsandwich.com');
        $user->setName('Mike Lively');
        $user->setPassword($this->password);

        try
        {
            $this->userRepository->saveEntity($user);
            $this->fail("Exception never thrown");
        }
        catch (DuplicateEntityException $e)
        {
            $this->assertEquals('email', $e->getField());
            $this->assertInstanceOf(\PDOException::class, $e->getPrevious());
        }
    }

    public function testGetUserByUsername()
    {
        $user = $this->userRepository->getByFriendlyName('taken.user01');

        $this->assertEquals('taken.user01', $user->getUsername());
        $this->assertEquals('taken1@digitalsandwich.com', $user->getEmail());
        $this->assertEquals('Existing User 1', $user->getName());
        $this->assertEquals(6, $user->getId());
        $this->assertTrue(password_verify('P@ssw0rd', password_hash('P@ssw0rd', PASSWORD_BCRYPT, ['cost' => 10 ])));
        $this->assertTrue(password_verify('P@ssw0rd', $user->getPasswordHash()));
    }

    public function testGetUserByUsernameReturnsNullOnNoUser()
    {
        $user = $this->userRepository->getByFriendlyName('nothere');
        $this->assertNull($user);
    }

    public function testSaveExistingUser()
    {
        $user = new UserEntity(6);
        $user->setUsername('mike.lively');
        $user->setEmail('m@digitalsandwich.com');
        $user->setName('Mike Lively');
        $user->setPassword($this->password);

        $this->userRepository->saveEntity($user);

        $queryTable = $this->getConnection()->createQueryTable('users',
            "SELECT * FROM users WHERE username = 'mike.lively'"
        );
        $this->assertEquals(1, $queryTable->getRowCount());

        $this->assertEquals($user->getUsername(), $queryTable->getValue(0, 'username'));
        $this->assertEquals($user->getEmail(), $queryTable->getValue(0, 'email'));
        $this->assertEquals($user->getName(), $queryTable->getValue(0, 'name'));
        $this->assertEquals($user->getPasswordHash(), $queryTable->getValue(0, 'password'));

        $queryTable = $this->getConnection()->createQueryTable('users',
            "SELECT * FROM users WHERE username = 'taken.user01'"
        );
        $this->assertEquals(0, $queryTable->getRowCount());
    }

    public function testDeleteUsersByUsernames()
    {
        $this->userRepository->deleteByFriendlyNames(['taken.user01', 'taken.user02']);
        $queryTable = $this->getConnection()->createQueryTable('users',
            "SELECT * FROM users WHERE username IN ('taken.user01', 'taken.user02')"
        );
        $this->assertEquals(0, $queryTable->getRowCount());
    }

    public function testGetUserByUsernameLoadsGroups()
    {
        $user = $this->userRepository->getByFriendlyName('taken.user01');
        $group1 = new GroupEntity(1);
        $group2 = new GroupEntity(2);
        $group3 = new GroupEntity(3);
        
        $this->assertTrue($user->isMemberOfGroup($group1));
        $this->assertTrue($user->isMemberOfGroup($group2));
        $this->assertFalse($user->isMemberOfGroup($group3));
    }
    
    public function testSavingGroups()
    {
        $user = $this->userRepository->getByFriendlyName('taken.user01');
        $user->addGroups([3]);
        $user->removeGroups([1]);
        
        $this->userRepository->saveEntity($user);

        $queryTable = $this->getConnection()->createQueryTable('users_groups',
            "SELECT groups_id FROM users_groups WHERE users_id = 6 ORDER BY groups_id"
        );
        $this->assertEquals(2, $queryTable->getRowCount());
        $this->assertEquals(2, $queryTable->getValue(0, 'groups_id'));
        $this->assertEquals(3, $queryTable->getValue(1, 'groups_id'));
    }
        
}