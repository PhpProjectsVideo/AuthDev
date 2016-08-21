<?php

namespace PhpProjects\AuthDev\Model\User;
use PhpProjects\AuthDev\Model\Group\GroupEntity;

/**
 * Represents a user in the system.
 */
class UserEntity
{
    /**
     * The type of hash to use with the password
     */
    const CONFIG_HASH_TYPE = PASSWORD_BCRYPT;

    /**
     * The cost factor for the password.
     */
    const CONFIG_COST = 10;

    /**
     * @var int
     */
    private $id;
    
    /**
     * @var string
     */
    private $username;

    /**
     * @var string
     */
    private $email;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $clearTextPassword;

    /**
     * @var string
     */
    private $passwordHash;

    /**
     * @var array
     */
    private $groupIds = [];
    
    public function __construct(int $id = 0)
    {
        $this->id = $id;
    }

    /**
     * 
     * @param array $data
     * @return UserEntity
     */
    public static function createFromArray(array $data) : UserEntity
    {
        $user = new UserEntity();
        $user->updateFromArray($data);
        return $user;
    }

    /**
     * Update a user from an array of data. Commonly used in the repository.
     * 
     * @param array $data
     */
    public function updateFromArray(array $data)
    {
        if (isset($data['id']))
        {
            $this->setId($data['id']);
        }
        if (isset($data['username']))
        {
            $this->setUsername($data['username']);
        }
        if (isset($data['email']))
        {
            $this->setEmail($data['email']);
        }
        if (isset($data['name']))
        {
            $this->setName($data['name']);
        }

        if (!empty($data['password']))
        {
            $this->setPassword($data['password'] ?? '');
            $this->setClearTextPassword('');
        }
        elseif (!empty($data['clear-password']))
        {
            $this->setClearTextPassword($data['clear-password']);
        }
    }
    
    /**
     * Returns the id of the user.
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Sets the id of the user.
     *
     * @param int $id
     */
    public function setId(int $id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param string $username
     */
    public function setUsername(string $username)
    {
        $this->username = $username;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail(string $email)
    {
        $this->email = $email;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name)
    {
        $this->name = $name;
    }

    /**
     * Sets the clear text password for a user.
     * 
     * Also resets the hash to match with the clear text password
     *
     * @param string $password
     */
    public function setClearTextPassword(string $password)
    {
        $this->clearTextPassword = $password;
        if (!empty($password))
        {
            $this->passwordHash = password_hash($password, self::CONFIG_HASH_TYPE, ['cost' => self::CONFIG_COST ]);
        }
    }

    /**
     * Returns the hashed version of the password.
     * 
     * @return string
     */
    public function getPasswordHash()
    {
        return $this->passwordHash;
    }

    /**
     * Returns the clear text version of the password or null if it cannot be determined.
     * 
     * @return string|null
     */
    public function getClearTextPassword()
    {
        return $this->clearTextPassword;
    }

    /**
     * @param string $passwordHash
     */
    public function setPassword(string $passwordHash)
    {
        $this->clearTextPassword = '';
        $this->passwordHash = $passwordHash;
    }

    /**
     * Determines if the user is in $group
     *
     * @param GroupEntity $group
     * @return bool
     */
    public function isMemberOfGroup(GroupEntity $group) : bool
    {
        return isset($this->groupIds[$group->getId()]);
    }

    /**
     * Adds the list of group ids to the member groups
     *
     * @param array $groupIds
     */
    public function addGroups(array $groupIds)
    {
        foreach ($groupIds as $id)
        {
            if (!empty($id))
            {
                $this->groupIds[$id] = true;
            }
        }
    }

    /**
     * Removes the list of group ids from the member groups
     *
     * @param array $groupIds
     */
    public function removeGroups(array $groupIds)
    {
        foreach ($groupIds as $id)
        {
            if (!empty($id))
            {
                unset($this->groupIds[$id]);
            }
        }
    }

    /**
     * Resets the group list
     *
     * @param array $groupIds
     */
    public function setGroups(array $groupIds)
    {
        $this->groupIds = [];
        $this->addGroups($groupIds);
    }

    /**
     * Returns an array of group ids
     * 
     * @return array
     */
    public function getGroupIds() : array
    {
        return array_keys($this->groupIds);
    }

    public function passwordMatches(string $password) : bool
    {
        return password_verify($password, $this->getPasswordHash());
    }
}