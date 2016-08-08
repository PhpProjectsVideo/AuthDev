<?php

namespace PhpProjects\AuthDev\Model\Group;
use PhpProjects\AuthDev\Database\DatabaseService;

/**
 * Used to load and store groups to and from the system.
 */
class GroupRepository
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
     * @return GroupRepository
     */
    public static function create() : GroupRepository
    {
        return new GroupRepository(DatabaseService::getInstance()->getPdo());
    }

    /**
     * Returns a sorted list of up to $limit groups with an offset of $offset.
     *
     * Groups will be sorted by their name.
     *
     * @param int $limit
     * @param int $offset
     * @return \Traversable
     */
    public function getSortedGroupList(int $limit = 0, int $offset = 0) : \Traversable
    {
        if (!empty($limit))
        {
            $sql = "SELECT id, name FROM groups ORDER BY name LIMIT {$offset}, {$limit}";
        }
        else
        {
            $sql = "SELECT id, name FROM groups ORDER BY name";
        }
        $stm = $this->pdo->query($sql);

        foreach ($stm as $rowData)
        {
            yield GroupEntity::createFromArray($rowData);
        }
    }

    public function getGroupListByNames(array $names) : \Traversable
    {
        if (empty($names))
        {
            return new \ArrayIterator([]);
        }
        $parms = rtrim(str_repeat('?,', count($names)), ',');
        $sql = "SELECT id, name FROM groups WHERE name IN ($parms) ORDER BY name";
        $stm = $this->pdo->prepare($sql);
        $stm->execute($names);

        foreach ($stm as $rowData)
        {
            yield GroupEntity::createFromArray($rowData);
        }
    }

    /**
     * Returns the number of groups in the table
     *
     * @return int
     */
    public function getGroupCount() : int
    {
        $sql = "SELECT COUNT(*) FROM groups";

        $stm = $this->pdo->query($sql);

        return $stm->fetchColumn();
    }

    /**
     * Returns a sorted list of up to $limit groups whose name begin with $query with an offset of $offset.
     *
     * Groups will be sorted by their name.
     *
     * @param string $query
     * @param int $limit
     * @param int $offset
     * @return \Traversable
     */
    public function getGroupsMatchingName(string $query, int $limit, int $offset = 0) : \Traversable
    {
        $sql = "SELECT id, name FROM groups WHERE name LIKE ? ORDER BY name LIMIT {$offset}, {$limit}";
        $stm = $this->pdo->prepare($sql);
        $stm->execute([ $query . '%' ]);

        foreach ($stm as $rowData)
        {
            yield GroupEntity::createFromArray($rowData);
        }
    }

    /**
     * Returns the number of groups whose name begin with $query
     *
     * @param string $query
     * @return int
     */
    public function getGroupCountMatchingName(string $query) : int
    {
        $sql = "SELECT COUNT(*) FROM groups WHERE name LIKE ?";
        $stm = $this->pdo->prepare($sql);
        $stm->execute([ $query . '%' ]);
        return $stm->fetchColumn();
    }

    /**
     * Returns the group specified by $name or null if the group does not exist.
     *
     * @param string $name
     * @return null|GroupEntity
     */
    public function getGroupByName(string $name)
    {
        $sql = "SELECT * FROM groups WHERE name = ?";
        $stm = $this->pdo->prepare($sql);
        $stm->execute([ $name ]);

        $row = $stm->fetch();
        if (empty($row))
        {
            return null;
        }
        else
        {
            return GroupEntity::createFromArray($row);
        }
    }

    /**
     * Saves a new or existing group entity to the repository
     *
     * @param GroupEntity $group
     * @throws DuplicateGroupException when a group with the same email or name already exists
     */
    public function saveGroup(GroupEntity $group)
    {
        if (empty($group->getId()))
        {
            $sql = "INSERT INTO groups (name) VALUES (?)";
            $stm = $this->pdo->prepare($sql);
            $sqlParameters = [$group->getName()];
        }
        else
        {
            $sql = "UPDATE groups SET name = ? WHERE id = ?";
            $stm = $this->pdo->prepare($sql);
            $sqlParameters = [$group->getName(), $group->getId()];
        }

        try
        {
            $stm->execute($sqlParameters);
            if (empty($group->getId()))
            {
                $group->setId($this->pdo->lastInsertId());
            }
        }
        catch (\PDOException $e)
        {
            if (preg_match('/UNIQUE constraint failed: groups\.([^ ]+)/', $e->getMessage(), $matches))
            {
                throw new DuplicateGroupException($matches[1], $e);
            }
            else
            {
                throw $e;
            }
        }
    }

    public function deleteGroupsByNames(array $names)
    {
        if (empty($names))
        {
            return;
        }
        $parms = rtrim(str_repeat('?,', count($names)), ',');
        $sql = "DELETE FROM groups WHERE name IN ($parms)";
        $stm = $this->pdo->prepare($sql);
        $stm->execute($names);
    }
}