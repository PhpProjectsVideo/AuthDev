<?php

namespace PhpProjects\AuthDev\Model\Group;
use PhpProjects\AuthDev\Model\Permission\PermissionEntity;

/**
 * Represents a group in the system.
 */
class GroupEntity
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var array
     */
    private $permissionIds = [];


    public function __construct(int $id = 0)
    {
        $this->id = $id;
    }

    /**
     *
     * @param array $data
     * @return GroupEntity
     */
    public static function createFromArray(array $data) : GroupEntity
    {
        $group = new GroupEntity();
        $group->updateFromArray($data);
        return $group;
    }

    /**
     * Update a group from an array of data. Commonly used in the repository.
     *
     * @param array $data
     */
    public function updateFromArray(array $data)
    {
        if (isset($data['id']))
        {
            $this->setId($data['id']);
        }
        if (isset($data['name']))
        {
            $this->setName($data['name']);
        }
    }

    /**
     * Returns the id of the group.
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Sets the id of the group.
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
     * Determines if the group owns a permission
     *
     * @param PermissionEntity $permission
     * @return bool
     */
    public function isOwnerOfPermission(PermissionEntity $permission) : bool
    {
        return isset($this->permissionIds[$permission->getId()]);
    }

    /**
     * Adds the list of permission ids to the group
     *
     * @param array $permissionIds
     */
    public function addPermissions(array $permissionIds)
    {
        foreach ($permissionIds as $id)
        {
            if (!empty($id))
            {
                $this->permissionIds[$id] = true;
            }
        }
    }

    /**
     * Removes the list of permissions ids from the group
     *
     * @param array $permissionIds
     */
    public function removePermissions(array $permissionIds)
    {
        foreach ($permissionIds as $id)
        {
            if (!empty($id))
            {
                unset($this->permissionIds[$id]);
            }
        }
    }

    /**
     * Returns an array of permissions ids assigned to the group
     *
     * @return array
     */
    public function getPermissionIds() : array
    {
        return array_keys($this->permissionIds);
    }
}