<?php

namespace PhpProjects\AuthDev\Model\Group;
use PhpProjects\AuthDev\Model\SimpleCrudRepository;

/**
 * Used to load and store groups to and from the system.
 */
class GroupRepository extends SimpleCrudRepository
{
    protected function onSingleEntityLoad($entity)
    {

        $sql = "SELECT permissions_id FROM groups_permissions WHERE groups_id = ?";
        $stm = $this->pdo->prepare($sql);
        $stm->execute([$entity->getId()]);

        $permissions = [];
        foreach ($stm as $row)
        {
            $permissions[] = $row['permissions_id'];
        }
        $entity->addPermissions($permissions);
    }

    protected function onSingleEntitySave($entity)
    {
        $groupPerimssionRemoveStm = $this->pdo->prepare("DELETE FROM groups_permissions WHERE groups_id = ?");
        $groupPerimssionRemoveStm->execute([$entity->getId()]);
        
        $groupPermissionInsertStm = $this->pdo->prepare("
            INSERT INTO groups_permissions (groups_id, permissions_id) VALUES (?, ?)
        ");
        foreach ($entity->getPermissionIds() as $id)
        {
            $groupPermissionInsertStm->execute([$entity->getId(), $id]);
        }
    }

    protected function onPreMultiEntityDelete($names)
    {
        $parms = rtrim(str_repeat('?,', count($names)), ',');
        $groupPermissionsStm = $this->pdo->prepare(
            "DELETE FROM groups_permissions WHERE groups_id IN (SELECT id FROM groups WHERE name IN ($parms))"
        );
        $groupPermissionsStm->execute($names);
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

    /**
     * The name of the column that holds the id.
     * @return string
     */
    protected function getIdColumn() : string
    {
        return 'id';
    }
}