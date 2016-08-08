<?php

namespace PhpProjects\AuthDev\Model\Group;

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
}