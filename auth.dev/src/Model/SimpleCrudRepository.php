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
     * Returns a sorted list of up to $limit entities with an offset of $offset.
     *
     * @param int $limit
     * @param int $offset
     * @return \Traversable
     */
    public function getSortedList(int $limit = 0, int $offset = 0) : \Traversable
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

    public function getListByFriendlyNames(array $names) : \Traversable
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
     * Returns the number of entities in the table
     *
     * @return int
     */
    public function getCount() : int
    {
        $sql = "SELECT COUNT(*) FROM {$this->getTable()}";

        $stm = $this->pdo->query($sql);

        return $stm->fetchColumn();
    }

    /**
     * Returns a sorted list of up to $limit entity whose friendly name begin with $query with an offset of $offset.
     *
     * @param string $query
     * @param int $limit
     * @param int $offset
     * @return \Traversable
     */
    public function getListMatchingFriendlyName(string $query, int $limit, int $offset = 0) : \Traversable
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
     * Returns the number of entities whose friendly name begin with $query
     *
     * @param string $query
     * @return int
     */
    public function getCountMatchingFriendlyName(string $query) : int
    {
        $sql = "SELECT COUNT(*) FROM {$this->getTable()} WHERE {$this->getFriendlyLookupColumn()} LIKE ?";
        $stm = $this->pdo->prepare($sql);
        $stm->execute([ $query . '%' ]);
        return $stm->fetchColumn();
    }

    /**
     * Returns the entity specified by friendly name $name or null if the entity does not exist.
     *
     * @param string $name
     * @return mixed
     */
    public function getByFriendlyName(string $name)
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
     * Saves a new or existing entity to the repository
     *
     * @param mixed $entity
     * @throws DuplicateEntityException when an entity with the same unique key already exists
     */
    public function saveEntity($entity)
    {
        if (empty($entity->getId()))
        {
            $stmPlaceholders = [];
            foreach ($this->getColumnList() as $column)
            {
                $stmPlaceholders[] = ':' . $column;
            }
            $stmPlaceholders = implode(',', $stmPlaceholders);
            $sql = "INSERT INTO {$this->getTable()} ({$this->getSqlColumnList()}) VALUES ({$stmPlaceholders})";
            $stm = $this->pdo->prepare($sql);
            $sqlParameters = $this->getRowFromEntity($entity);
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
            $sqlParameters = $this->getRowFromEntity($entity);
            $sqlParameters['id'] = $this->getEntityId($entity);
        }

        try
        {
            $stm->execute($sqlParameters);
            if (empty($this->getEntityId($entity)))
            {
                $this->setEntityId($entity, $this->pdo->lastInsertId());
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

    /**
     * Deletes entities with the given friendly names
     * @param array $names
     */
    public function deleteByFriendlyNames(array $names)
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