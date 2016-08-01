<?php

namespace PhpProjects\AuthDev\Model\User;
use PhpProjects\AuthDev\Database\DatabaseService;

/**
 * Used to load and store users to and from the system.
 */
class UserRepository
{
    /**
     * @var \PDO
     */
    private $pdo;

    /**
     * @param \PDO $pdo
     */
    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * @return UserRepository
     */
    public static function create() : UserRepository
    {
        return new UserRepository(DatabaseService::getInstance()->getPdo());
    }

    /**
     * Returns a sorted list of up to $limit users with an offset of $offset.
     *
     * Users will be sorted by their username.
     *
     * @param int $limit
     * @param int $offset
     * @return \Traversable
     */
    public function getSortedUserList(int $limit, int $offset = 0) : \Traversable
    {
        $sql = "SELECT id, username, email, name, password FROM users ORDER BY username LIMIT {$offset}, {$limit}";
        $stm = $this->pdo->query($sql);

        foreach ($stm as $rowData)
        {
            yield UserEntity::createFromArray($rowData);
        }
    }

    /**
     * Returns the number of users in the table
     * 
     * @return int
     */
    public function getUserCount() : int
    {
        $sql = "SELECT COUNT(*) FROM users";
        
        $stm = $this->pdo->query($sql);
        
        return $stm->fetchColumn();
    }

    /**
     * Returns a sorted list of up to $limit users whose username begin with $query with an offset of $offset.
     * 
     * Users will be sorted by their username.
     * 
     * @param string $query
     * @param int $limit
     * @param int $offset
     * @return \Traversable
     */
    public function getUsersMatchingUsername(string $query, int $limit, int $offset = 0) : \Traversable
    {
        $sql = "SELECT id, username, email, name, password FROM users WHERE username LIKE ? ORDER BY username LIMIT {$offset}, {$limit}";
        $stm = $this->pdo->prepare($sql);
        $stm->execute([ $query . '%' ]);

        foreach ($stm as $rowData)
        {
            yield UserEntity::createFromArray($rowData);
        }
    }

    /**
     * Returns the number of users whose username begin with $query
     * 
     * @param string $query
     * @return int
     */
    public function getUserCountMatchingUsername(string $query) : int
    {
        $sql = "SELECT COUNT(*) FROM users WHERE username LIKE ?";
        $stm = $this->pdo->prepare($sql);
        $stm->execute([ $query . '%' ]);
        return $stm->fetchColumn();
    }

    /**
     * Returns the user specified by $username or null if the user does not exist.
     * 
     * @param string $username
     * @return null|UserEntity
     */
    public function getUserByUsername(string $username)
    {
        $sql = "SELECT * FROM users WHERE username = ?";
        $stm = $this->pdo->prepare($sql);
        $stm->execute([ $username ]);
        
        $row = $stm->fetch();
        if (empty($row))
        {
            return null;
        }
        else
        {
            return UserEntity::createFromArray($row);
        }
    }

    /**
     * Saves a new or existing user entity to the repository
     *
     * @param UserEntity $user
     * @throws DuplicateUserException when a user with the same email or username already exists
     */
    public function saveUser(UserEntity $user)
    {
        if (empty($user->getId()))
        {
            $sql = "INSERT INTO users (username, email, name, password) VALUES (?, ?, ?, ?)";
            $stm = $this->pdo->prepare($sql);
            $sqlParameters = [$user->getUsername(), $user->getEmail(), $user->getName(), $user->getPasswordHash()];
        }
        else
        {
            $sql = "UPDATE users SET username = ?, email = ?, name = ?, password = ? WHERE id = ?";
            $stm = $this->pdo->prepare($sql);
            $sqlParameters = [$user->getUsername(), $user->getEmail(), $user->getName(), $user->getPasswordHash(), $user->getId()];
        }

        try
        {
            $stm->execute($sqlParameters);
            if (empty($user->getId()))
            {
                $user->setId($this->pdo->lastInsertId());
            }
        }
        catch (\PDOException $e)
        {
            if (preg_match('/UNIQUE constraint failed: users\.([^ ]+)/', $e->getMessage(), $matches))
            {
                throw new DuplicateUserException($matches[1], $e);
            }
            else
            {
                throw $e;
            }
        }
    }
}