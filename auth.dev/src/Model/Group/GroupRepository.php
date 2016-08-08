<?php

namespace PhpProjects\AuthDev\Model\Group;
use PhpProjects\AuthDev\Model\SimpleCrudRepository;

/**
 * Used to load and store groups to and from the system.
 */
class GroupRepository extends SimpleCrudRepository
{
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
        return GroupEntity::createFromArray($rowData);
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
        return 'groups';
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