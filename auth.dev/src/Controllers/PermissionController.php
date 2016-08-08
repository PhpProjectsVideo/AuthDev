<?php

namespace PhpProjects\AuthDev\Controllers;

use PhpProjects\AuthDev\Model\Csrf\CsrfService;
use PhpProjects\AuthDev\Model\Permission\PermissionRepository;
use PhpProjects\AuthDev\Model\Permission\PermissionValidation;
use PhpProjects\AuthDev\Model\Permission\PermissionEntity;
use PhpProjects\AuthDev\Model\ValidationResults;
use PhpProjects\AuthDev\Views\ViewService;

/**
 * Manages operations against the permission portion of our domain model
 */
class PermissionController extends SimpleCrudController
{
    /**
     * @var PermissionValidation
     */
    private $permissionValidation;

    /**
     * @param ViewService $viewService
     * @param PermissionRepository $permissionRepository
     * @param PermissionValidation $permissionValidation
     * @param CsrfService $csrfService
     */
    public function __construct(ViewService $viewService, PermissionRepository $permissionRepository, PermissionValidation $permissionValidation, CsrfService $csrfService)
    {
        $this->permissionValidation = $permissionValidation;
        parent::__construct($viewService, $permissionRepository, $csrfService);
    }

    /**
     * Convenience Constructor
     *
     * @return PermissionController
     */
    public static function create() : PermissionController
    {
        return new self(ViewService::create(), PermissionRepository::create(), new PermissionValidation(), CsrfService::create());
    }

    /**
     * The folder in views containing the crud templates:
     * * form.php
     * * list.php
     * * removeList.php
     *
     * @return string
     */
    protected function getTemplateFolder() : string
    {
        return 'permissions';
    }

    /**
     * The base url for the crud pages in this controller.
     *
     * @return string
     */
    protected function getBaseUrl() : string
    {
        return '/permissions/';
    }

    /**
     * Creates a new entity with no data
     *
     * @return mixed
     */
    protected function getNewEntity()
    {
        return new PermissionEntity();
    }

    /**
     * The name that the entity will be referred to in this controller.
     *
     * @return string
     */
    protected function getEntityTitle() : string
    {
        return 'Permission';
    }

    /**
     * Create an entity based off of the array of data
     *
     * @param array $entityData
     * @return mixed
     */
    protected function getEntityFromData(array $entityData)
    {
        return PermissionEntity::createFromArray($entityData);
    }

    /**
     * Returns a validation result for the given entity.
     *
     * @param $entity
     * @return ValidationResults
     */
    protected function validateEntity($entity) : ValidationResults
    {
        return $this->permissionValidation->validate($entity);
    }

    /**
     * Returns a friendly name by which to refer to a given entity.
     *
     * @param mixed $entity
     * @return string
     */
    protected function getEntityFriendlyName($entity) : string
    {
        return $entity->getName();
    }
}