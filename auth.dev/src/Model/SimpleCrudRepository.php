<?php

namespace PhpProjects\AuthDev\Model;
use PhpProjects\AuthDev\Controllers\SimpleCrudController;
use PhpProjects\AuthDev\Database\DatabaseService;
use PhpProjects\AuthDev\Memcache\MemcacheService;

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
     * @var MemcacheService
     */
    protected $memcacheService;

    /**
     * @param \PDO $pdo
     * @param MemcacheService $memcacheService
     */
    public function __construct(\PDO $pdo, MemcacheService $memcacheService)
    {
        $this->pdo = $pdo;
        $this->memcacheService = $memcacheService;
    }

    /**
     * @return static
     */
    public static function create() : SimpleCrudRepository
    {
        return new static(DatabaseService::getInstance()->getPdo(), MemcacheService::getInstance());
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
     * The name of the column that holds the id.
     * @return string
     */
    abstract protected function getIdColumn() : string;

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
        $entities = $this->memcacheService->nsGet($this->getTable(), "sortedList:{$limit}:{$offset}");

        if ($entities === false)
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

            $entities = [];
            foreach ($stm as $rowData)
            {
                $entities[] = $this->getEntityFromRow($rowData);
            }
            $this->memcacheService->nsSet($this->getTable(), "sortedList:{$limit}:{$offset}", $entities, 300);
        }
        
        return new \ArrayIterator($entities);
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
        $count = $this->memcacheService->nsGet($this->getTable(), "count");

        if ($count === false)
        {

            $sql = "SELECT COUNT(*) FROM {$this->getTable()}";

            $stm = $this->pdo->query($sql);

            $count = $stm->fetchColumn();

            $this->memcacheService->nsSet($this->getTable(), "count", $count, 300);
        }

        return $count;
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
        $entities = $this->memcacheService->nsGet($this->getTable(), "queryList:{$query}:{$limit}:{$offset}");

        if ($entities === false)
        {
            $sql = "SELECT {$this->getSqlColumnList()} FROM {$this->getTable()} WHERE {$this->getFriendlyLookupColumn()} LIKE ? ORDER BY {$this->getDefaultSortColumn()} LIMIT {$offset}, {$limit}";
            $stm = $this->pdo->prepare($sql);
            $stm->execute([ $query . '%' ]);

            $entities = [];
            foreach ($stm as $rowData)
            {
                $entities[] = $this->getEntityFromRow($rowData);
            }

            $this->memcacheService->nsSet($this->getTable(), "queryList:{$query}:{$limit}:{$offset}", $entities, 300);
        }

        return new \ArrayIterator($entities);
    }

    /**
     * Returns the number of entities whose friendly name begin with $query
     *
     * @param string $query
     * @return int
     */
    public function getCountMatchingFriendlyName(string $query) : int
    {
        $count = $this->memcacheService->nsGet($this->getTable(), "queryCount:{$query}");

        if ($count === false)
        {
            $sql = "SELECT COUNT(*) FROM {$this->getTable()} WHERE {$this->getFriendlyLookupColumn()} LIKE ?";
            $stm = $this->pdo->prepare($sql);
            $stm->execute([ $query . '%' ]);
            $count = $stm->fetchColumn();
            $this->memcacheService->nsSet($this->getTable(), "queryCount:{$query}", $count, 300);
        }

        return $count;
    }

    /**
     * Returns the entity specified by friendly name $name or null if the entity does not exist.
     *
     * @param string $name
     * @return mixed
     */
    public function getByFriendlyName(string $name)
    {
        $entity = $this->memcacheService->nsGet($this->getTable(), "byName:{$name}");

        if ($entity === false)
        {
            $sql = "SELECT * FROM {$this->getTable()} WHERE {$this->getFriendlyLookupColumn()} = ?";
            $stm = $this->pdo->prepare($sql);
            $stm->execute([ $name ]);

            $row = $stm->fetch();
            if (empty($row))
            {
                $entity = null;
            }
            else
            {
                $entity = $this->getEntityFromRow($row);
            }
            $this->memcacheService->nsSet($this->getTable(), "byName:{$name}", $entity, 300);
        }

        if (!empty($entity))
        {
            $this->onSingleEntityLoad($entity);
        }

        return $entity;
    }

    /**
     * Returns the entity specified by id $id or null if the entity does not exist.
     *
     * @param mixed $id
     * @return mixed
     */
    public function getById($id)
    {
        $entity = $this->memcacheService->nsGet($this->getTable(), "byId:{$id}");

        if ($entity === false)
        {
            $sql = "SELECT * FROM {$this->getTable()} WHERE {$this->getIdColumn()} = ?";
            $stm = $this->pdo->prepare($sql);
            $stm->execute([ $id ]);

            $row = $stm->fetch();
            if (empty($row))
            {
                $entity = null;
            }
            else
            {
                $entity = $this->getEntityFromRow($row);
            }
            $this->memcacheService->nsSet($this->getTable(), "byId:{$id}", $entity, 300);
        }

        if (!empty($entity))
        {
            $this->onSingleEntityLoad($entity);
        }

        return $entity;
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
            $sqlParameters = [];
            $row = $this->getRowFromEntity($entity);
            foreach ($this->getColumnList() as $column)
            {
                $stmPlaceholders[] = ':' . $column;
                $sqlParameters[$column] = $row[$column] ?? null;
            }
            $stmPlaceholders = implode(',', $stmPlaceholders);
            $sql = "INSERT INTO {$this->getTable()} ({$this->getSqlColumnList()}) VALUES ({$stmPlaceholders})";
            $stm = $this->pdo->prepare($sql);
        }
        else
        {
            $stmPlaceholders = [];
            $sqlParameters = [];
            $row = $this->getRowFromEntity($entity);
            foreach ($this->getColumnList() as $column)
            {
                $stmPlaceholders[] = $column . ' = :' . $column;
                $sqlParameters[$column] = $row[$column] ?? null;
            }
            $stmPlaceholders = implode(',', $stmPlaceholders);
            $sqlParameters['id'] = $this->getEntityId($entity);
            $sql = "UPDATE {$this->getTable()} SET {$stmPlaceholders} WHERE id = :id";
            $stm = $this->pdo->prepare($sql);
        }

        try
        {
            $stm->execute($sqlParameters);
            $this->memcacheService->nsFlush($this->getTable());
            if (empty($this->getEntityId($entity)))
            {
                $this->setEntityId($entity, $this->pdo->lastInsertId());
            }
            $this->onSingleEntitySave($entity);
        }
        catch (\PDOException $e)
        {
            if (preg_match("/Duplicate entry '[^']+' for key '{$this->getTable()}_([^ ]+)_uindex/", $e->getMessage(), $matches))
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
        
        $this->onPreMultiEntityDelete($names);
        
        $sql = "DELETE FROM {$this->getTable()} WHERE {$this->getFriendlyLookupColumn()} IN ($parms)";
        $stm = $this->pdo->prepare($sql);
        $stm->execute($names);
        $this->memcacheService->nsFlush($this->getTable());
    }

    /**
     * Override to perform some customization when a single entity is loaded.
     *
     * @param mixed $entity
     */
    protected function onSingleEntityLoad($entity)
    {
    }

    /**
     * Override to perform some customization when a single entity is saved.
     * 
     * @param mixed $entity
     */
    protected function onSingleEntitySave($entity)
    {
    }

    /**
     * Overrided to perform some customization prio to deleting an entity by friendly names
     * @param $names
     */
    protected function onPreMultiEntityDelete($names)
    {
    }
}