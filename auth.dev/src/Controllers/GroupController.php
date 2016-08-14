<?php

namespace PhpProjects\AuthDev\Controllers;

use PhpProjects\AuthDev\Model\Csrf\CsrfService;
use PhpProjects\AuthDev\Model\Group\GroupRepository;
use PhpProjects\AuthDev\Model\Group\GroupValidation;
use PhpProjects\AuthDev\Model\Group\GroupEntity;
use PhpProjects\AuthDev\Model\Permission\PermissionRepository;
use PhpProjects\AuthDev\Model\ValidationResults;
use PhpProjects\AuthDev\Views\ViewService;

/**
 * Manages operations against the group portion of our domain model
 */
class GroupController extends SimpleCrudController
{
    use PermissionableControllerTrait;
    
    /**
     * @var GroupValidation
     */
    private $groupValidation;

    /**
     * @var PermissionRepository
     */
    private $permissionRepository;

    /**
     * @param ViewService $viewService
     * @param GroupRepository $groupRepository
     * @param GroupValidation $groupValidation
     * @param PermissionRepository $permissionRepository
     * @param CsrfService $csrfService
     */
    public function __construct(ViewService $viewService, GroupRepository $groupRepository, GroupValidation $groupValidation, PermissionRepository $permissionRepository, CsrfService $csrfService)
    {
        $this->groupValidation = $groupValidation;
        $this->permissionRepository = $permissionRepository;
        parent::__construct($viewService, $groupRepository, $csrfService);
    }

    /**
     * Convenience Constructor
     *
     * @return GroupController
     */
    public static function create() : GroupController
    {
        return new self(ViewService::create(), GroupRepository::create(), new GroupValidation(), PermissionRepository::create(), CsrfService::create());
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
        return 'groups';
    }

    /**
     * The base url for the crud pages in this controller.
     * 
     * @return string
     */
    protected function getBaseUrl() : string
    {
        return '/groups/';
    }

    /**
     * Creates a new entity with no data
     * 
     * @return mixed
     */
    protected function getNewEntity()
    {
        return new GroupEntity();
    }

    /**
     * The name that the entity will be referred to in this controller.
     * 
     * @return string
     */
    protected function getEntityTitle() : string
    {
        return 'Group';
    }

    /**
     * Create an entity based off of the array of data
     * 
     * @param array $entityData
     * @return mixed
     */
    protected function getEntityFromData(array $entityData)
    {
        return GroupEntity::createFromArray($entityData);
    }

    /**
     * Returns a validation result for the given entity.
     * 
     * @param $entity
     * @return ValidationResults
     */
    protected function validateEntity($entity) : ValidationResults
    {
        return $this->groupValidation->validate($entity);
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

    protected function onGetDetailRender(array $templateData) : array
    {
        if (!empty($redirectMessage = $this->viewService->getRedirectMessage()))
        {
            $templateData['message'] = $redirectMessage;
            $templateData['messageStatus'] =  $this->viewService->getRedirectStatus() ?? 'default';
        }
        $templateData['permissions'] = iterator_to_array($this->permissionRepository->getSortedList());
        return $templateData;
    }

    /**
     * Handles updating a groups permissions
     *
     * @param string $name
     * @param array $postData
     */
    public function postUpdatePermissions(string $name, array $postData)
    {
        if (!$this->checkForPermission('Administrator')) 
        {
            return;
        }

        if (!$this->csrfService->validateToken($postData['token'] ?? ''))
        {

            $this->viewService->redirect('/groups/detail/' . urlencode($name), 303, "Your session has expired, please try updating permissions again", 'danger');
        }
        else
        {
            $group = $this->crudRepository->getByFriendlyName($name);

            if (empty($group))
            {
                throw (new ContentNotFoundException("I could not locate the group {$name}."))
                    ->setTitle('Group Not Found')
                    ->setRecommendedUrl('/groups/')
                    ->setRecommendedAction('View All Groups');
            }
            if ($postData['operation'] == 'add')
            {
                $group->addPermissions($postData['permissionIds'] ?? []);
            }
            elseif ($postData['operation'] == 'remove')
            {
                $group->removePermissions($postData['permissionIds'] ?? []);
            }

            $this->crudRepository->saveEntity($group);

            $this->viewService->redirect('/groups/detail/' . urlencode($name), 303, "Your permissions have been updated", 'success');
        }
    }

    /**
     * @return ViewService
     */
    protected function getViewService() : ViewService
    {
        return $this->viewService;
        // TODO: Implement getViewService() method.
    }
}