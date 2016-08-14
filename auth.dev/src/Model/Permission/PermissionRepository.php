<?php

namespace PhpProjects\AuthDev\Model\Permission;
use PhpProjects\AuthDev\Model\SimpleCrudRepository;

/**
 * Used to load and store permissions to and from the system.
 */
class PermissionRepository extends SimpleCrudRepository
{
    public function getByGroupIds(array $getGroupIds) : \Traversable
    {
        if (empty($getGroupIds))
        {
            return new \ArrayIterator(array());
        }
        
        $paramList = rtrim(str_repeat('?,', count($getGroupIds)), ',');
        $stm = $this->pdo->prepare("
            SELECT p.id, p.name FROM permissions p 
            JOIN groups_permissions pg ON pg.permissions_id = p.id 
            WHERE pg.groups_id IN ({$paramList})
        ");
        $stm->execute($getGroupIds);
        
        foreach ($stm as $row)
        {
            yield $this->getEntityFromRow($row);
        }
    }

    /**
     * Returns a map of column names to entity values from the given $entity
     *
     * @param mixed $entity
     * @return array
     */
    protected function getRowFromEntity($entity)
    {
        return [
            'name' => $entity->getName(),
        ];
    }

    /**
     * Creates an entity based on a map of column names to entity values.
     *
     * @param array $rowData
     * @return mixed
     */
    protected function getEntityFromRow(array $rowData)
    {
        return PermissionEntity::createFromArray($rowData);
    }

    /**
     * Returns the database id of the given entity.
     *
     * @param mixed $entity
     * @return mixed
     */
    protected function getEntityId($entity)
    {
        return $entity->getId();
    }

    /**
     * Sets the database id of the given entity to $lastInsertId
     * @param mixed $entity
     * @param int $lastInsertId
     */
    protected function setEntityId($entity, int $lastInsertId)
    {
        $entity->setId($lastInsertId);
    }

    /**
     * Returns the name of the table this repository works with.
     *
     * @return string
     */
    protected function getTable() : string
    {
        return 'permissions';
    }

    /**
     * Returns a list of columns that should be loaded whenever list calls are made to the repo.
     *
     * @return array
     */
    protected function getColumnList() : array
    {
        return [
            'id',
            'name'
        ];
    }

    /**
     * The name of the column we typically sort results by.
     *
     * @return string
     */
    protected function getDefaultSortColumn() : string
    {
        return 'name';
    }

    /**
     * The name of the column that recieves the majority of our lookups.
     *
     * @return string
     */
    protected function getFriendlyLookupColumn() : string
    {
        return 'name';
    }
}