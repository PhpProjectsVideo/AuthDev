<?php

namespace PhpProjects\AuthDev\Model\Permission;

/**
 * Represents a permission in the system.
 */
class PermissionEntity
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    public function __construct(int $id = 0)
    {
        $this->id = $id;
    }

    /**
     *
     * @param array $data
     * @return PermissionEntity
     */
    public static function createFromArray(array $data) : PermissionEntity
    {
        $permission = new PermissionEntity();
        $permission->updateFromArray($data);
        return $permission;
    }

    /**
     * Update a permission from an array of data. Commonly used in the repository.
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
     * Returns the id of the permission.
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Sets the id of the permission.
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
}