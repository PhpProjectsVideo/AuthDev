<?php

namespace PhpProjects\AuthDev\Model;
use PhpProjects\AuthDev\Database\DatabaseService;

/**
 * Base class for repositories that offer simple crud functionality
 * 
 */
abstract class SimpleCrudRepository 
{
    /**
     * @var \PDO
     */
    protected $pdo;

    /**
     * @param \PDO $pdo
     */
    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * @return static
     */
    public static function create() : SimpleCrudRepository
    {
        return new static(DatabaseService::getInstance()->getPdo());
    }

    /**
     * Returns a map of column names to entity values from the given $entity
     *
     * @param mixed $entity
     * @return array
     */
    abstract protected function getRowFromEntity($entity);

    /**
     * Creates an entity based on a map of column names to entity values.
     *
     * @param array $rowData
     * @return mixed
     */
    abstract protected function getEntityFromRow(array $rowData);

    /**
     * Returns the database id of the given entity.
     *
     * @param mixed $entity
     * @return mixed
     */
    abstract protected function getEntityId($entity);

    /**
     * Sets the database id of the given entity to $lastInsertId
     * @param mixed $entity
     * @param int $lastInsertId
     */
    abstract protected function setEntityId($entity, int $lastInsertId);

    /**
     * Returns the name of the table this repository works with.
     *
     * @return string
     */
    abstract protected function getTable() : string;

    /**
     * Returns a list of columns that should be loaded whenever list calls are made to the repo.
     *
     * @return array
     */
    abstract protected function getColumnList() : array;

    /**
     * The name of the column we typically sort results by.
     *
     * @return string
     */
    abstract protected function getDefaultSortColumn() : string;

    /**
     * The name of the column that recieves the majority of our lookups.
     *
     * @return string
     */
    abstract protected function getFriendlyLookupColumn() : string;

    /**
     * A helper method to make building the column list in sql easier.
     *
     * @return string
     */
    protected function getSqlColumnList() : string
    {
        return implode($this->getColumnList(), ',');
    }

    /**
     * Returns a sorted list of up to $limit groups with an offset of $offset.
     *
     * @param int $limit
     * @param int $offset
     * @return \Traversable
     */
    public function getSortedGroupList(int $limit = 0, int $offset = 0) : \Traversable
    {
        if (!empty($limit))
        {
            $sql = "SELECT {$this->getSqlColumnList()} FROM {$this->getTable()} ORDER BY {$this->getDefaultSortColumn()} LIMIT {$offset}, {$limit}";
        }
        else
        {
            $sql = "SELECT {$this->getSqlColumnList()} FROM {$this->getTable()} ORDER BY {$this->getDefaultSortColumn()}";
        }
        $stm = $this->pdo->query($sql);

        foreach ($stm as $rowData)
        {
            yield $this->getEntityFromRow($rowData);
        }
    }

    public function getGroupListByNames(array $names) : \Traversable
    {
        if (empty($names))
        {
            return new \ArrayIterator([]);
        }
        $parms = rtrim(str_repeat('?,', count($names)), ',');
        $sql = "SELECT {$this->getSqlColumnList()} FROM {$this->getTable()} WHERE {$this->getFriendlyLookupColumn()} IN ($parms) ORDER BY {$this->getDefaultSortColumn()}";
        $stm = $this->pdo->prepare($sql);
        $stm->execute($names);

        foreach ($stm as $rowData)
        {
            yield $this->getEntityFromRow($rowData);
        }
    }

    /**
     * Returns the number of groups in the table
     *
     * @return int
     */
    public function getGroupCount() : int
    {
        $sql = "SELECT COUNT(*) FROM {$this->getTable()}";

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
        $sql = "SELECT {$this->getSqlColumnList()} FROM {$this->getTable()} WHERE {$this->getFriendlyLookupColumn()} LIKE ? ORDER BY {$this->getDefaultSortColumn()} LIMIT {$offset}, {$limit}";
        $stm = $this->pdo->prepare($sql);
        $stm->execute([ $query . '%' ]);

        foreach ($stm as $rowData)
        {
            yield $this->getEntityFromRow($rowData);
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
        $sql = "SELECT COUNT(*) FROM {$this->getTable()} WHERE {$this->getFriendlyLookupColumn()} LIKE ?";
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
        $sql = "SELECT * FROM {$this->getTable()} WHERE {$this->getFriendlyLookupColumn()} = ?";
        $stm = $this->pdo->prepare($sql);
        $stm->execute([ $name ]);

        $row = $stm->fetch();
        if (empty($row))
        {
            return null;
        }
        else
        {
            return $this->getEntityFromRow($row);
        }
    }

    /**
     * Saves a new or existing group entity to the repository
     *
     * @param $group
     * @throws DuplicateEntityException when an entity with the same unique key already exists
     */
    public function saveGroup($group)
    {
        if (empty($group->getId()))
        {
            $stmPlaceholders = [];
            foreach ($this->getColumnList() as $column)
            {
                $stmPlaceholders[] = ':' . $column;
            }
            $stmPlaceholders = implode(',', $stmPlaceholders);
            $sql = "INSERT INTO {$this->getTable()} ({$this->getSqlColumnList()}) VALUES ({$stmPlaceholders})";
            $stm = $this->pdo->prepare($sql);
            $sqlParameters = $this->getRowFromEntity($group);
        }
        else
        {
            $stmPlaceholders = [];
            foreach ($this->getColumnList() as $column)
            {
                $stmPlaceholders[] = $column . ' = :' . $column;
            }
            $stmPlaceholders = implode(',', $stmPlaceholders);
            $sql = "UPDATE {$this->getTable()} SET {$stmPlaceholders} WHERE id = :id";
            $stm = $this->pdo->prepare($sql);
            $sqlParameters = $this->getRowFromEntity($group);
            $sqlParameters['id'] = $this->getEntityId($group);
        }

        try
        {
            $stm->execute($sqlParameters);
            if (empty($this->getEntityId($group)))
            {
                $this->setEntityId($group, $this->pdo->lastInsertId());
            }
        }
        catch (\PDOException $e)
        {
            if (preg_match("/UNIQUE constraint failed: {$this->getTable()}\\.([^ ]+)/", $e->getMessage(), $matches))
            {
                throw new DuplicateEntityException($matches[1], $e);
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
        $sql = "DELETE FROM {$this->getTable()} WHERE {$this->getFriendlyLookupColumn()} IN ($parms)";
        $stm = $this->pdo->prepare($sql);
        $stm->execute($names);
    }
}