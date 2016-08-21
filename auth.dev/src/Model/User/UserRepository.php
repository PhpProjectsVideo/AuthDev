<?php

namespace PhpProjects\AuthDev\Model\User;
use PhpProjects\AuthDev\Model\SimpleCrudRepository;

/**
 * Used to load and store users to and from the system.
 */
class UserRepository extends SimpleCrudRepository
{
    protected function onSingleEntityLoad($entity)
    {

        $sql = "SELECT groups_id FROM users_groups WHERE users_id = ?";
        $stm = $this->pdo->prepare($sql);
        $stm->execute([$entity->getId()]);

        $groups = [];
        foreach ($stm as $row)
        {
            $groups[] = $row['groups_id'];
        }
        $entity->addGroups($groups);
    }

    protected function onSingleEntitySave($entity)
    {
        $userGroupRemoveSql = "DELETE FROM users_groups WHERE users_id = ?";
        $userGroupRemoveStm = $this->pdo->prepare($userGroupRemoveSql);
        $userGroupRemoveStm->execute([$entity->getId()]);

        $userGroupInsertSql = "INSERT INTO users_groups (users_id, groups_id) VALUES (?, ?)";
        $userGroupInsertStm = $this->pdo->prepare($userGroupInsertSql);
        foreach ($entity->getGroupIds() as $id)
        {
            $userGroupInsertStm->execute([$entity->getId(), $id]);
        }
    }

    protected function onPreMultiEntityDelete($names)
    {
        $parms = rtrim(str_repeat('?,', count($names)), ',');
        $userGroupSql = "DELETE FROM users_groups WHERE users_id IN (SELECT id FROM users WHERE username IN ($parms))";
        $userGroupStm = $this->pdo->prepare($userGroupSql);
        $userGroupStm->execute($names);
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
            'username' => $entity->getUsername(),
            'email' => $entity->getEmail(),
            'name' => $entity->getName(),
            'password' => $entity->getPasswordHash(),
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
        return UserEntity::createFromArray($rowData);
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
        return 'users';
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
            'username',
            'email',
            'name',
            'password',
        ];
    }

    /**
     * The name of the column we typically sort results by.
     *
     * @return string
     */
    protected function getDefaultSortColumn() : string
    {
        return 'username';
    }

    /**
     * The name of the column that recieves the majority of our lookups.
     *
     * @return string
     */
    protected function getFriendlyLookupColumn() : string
    {
        return 'username';
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