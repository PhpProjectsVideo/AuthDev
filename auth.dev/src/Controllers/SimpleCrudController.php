<?php

namespace PhpProjects\AuthDev\Controllers;

use PhpProjects\AuthDev\Model\Csrf\CsrfService;
use PhpProjects\AuthDev\Model\DuplicateEntityException;
use PhpProjects\AuthDev\Model\SimpleCrudRepository;
use PhpProjects\AuthDev\Model\ValidationResults;
use PhpProjects\AuthDev\Views\ViewService;

/**
 * Provides basic funcationality for a simple crud controller.
 * 
 * When using this controller you will be able to add, remove, delete, and update your entites. To ensure that all 
 * fields are properly edited you will be responsible to ensure those fields are present in templates and in the 
 * entity, but so long as you have a SimpleCrudRepository for the entity as well, much of the boiler plate backend
 * code will be handled for you.
 */
abstract class SimpleCrudController
{
    /**
     * @var ViewService
     */
    protected $viewService;

    /**
     * @var SimpleCrudRepository
     */
    protected $crudRepository;

    /**
     * @var CsrfService
     */
    protected $csrfService;

    /**
     * @param ViewService $viewService
     * @param SimpleCrudRepository $crudRepository
     * @param CsrfService $csrfService
     */
    public function __construct(ViewService $viewService, SimpleCrudRepository $crudRepository, CsrfService $csrfService)
    {
        $this->viewService = $viewService;
        $this->crudRepository = $crudRepository;
        $this->csrfService = $csrfService;
    }

    /**
     * The folder in views containing the crud templates:
     * * form.php
     * * list.php
     * * removeList.php
     *
     * @return string
     */
    abstract protected function getTemplateFolder() : string;

    /**
     * The base url for the crud pages in this controller.
     *
     * @return string
     */
    abstract protected function getBaseUrl() : string;

    /**
     * Creates a new entity with no data
     *
     * @return mixed
     */
    abstract protected function getNewEntity();

    /**
     * The name that the entity will be referred to in this controller.
     *
     * @return string
     */
    abstract protected function getEntityTitle() : string;

    /**
     * Create an entity based off of the array of data
     *
     * @param array $entityData
     * @return mixed
     */
    abstract protected function getEntityFromData(array $entityData);

    /**
     * Returns a validation result for the given entity.
     *
     * @param $entity
     * @return ValidationResults
     */
    abstract protected function validateEntity($entity) : ValidationResults;

    /**
     * Returns a friendly name by which to refer to a given entity.
     *
     * @param mixed $entity
     * @return string
     */
    abstract protected function getEntityFriendlyName($entity) : string;

    /**
     * Displays a list of entities in the system
     *
     * @param int $currentPage
     * @param string $query
     */
    public function getList(int $currentPage = 1, string $query = '')
    {
        if (empty($query))
        {
            $entityList = $this->crudRepository->getSortedList(10, ($currentPage - 1) * 10);
            $entityCount = $this->crudRepository->getCount();
        }
        else
        {
            $entityList = $this->crudRepository->getListMatchingFriendlyName($query, 10, ($currentPage - 1) * 10);
            $entityCount = $this->crudRepository->getCountMatchingFriendlyName($query);
        }

        $templateData = [
            'entities' => $entityList,
            'currentPage' => $currentPage,
            'totalPages' => $entityCount > 0 ? (int)(($entityCount - 1) / 10) + 1 : 1,
            'term' => $query
        ];

        if (!empty($redirectMessage = $this->viewService->getRedirectMessage()))
        {
            $templateData['message'] = $redirectMessage;
        }
        $this->viewService->renderView($this->getTemplateFolder() . '/list', $templateData);
    }

    /**
     * Displays a form to add a new entity.
     */
    public function getNew()
    {
        $entity = $this->getNewEntity();
        $this->viewService->renderView($this->getTemplateFolder() . '/form', [
            'entity' => $entity,
            'validationResults' => new ValidationResults([]),
            'token' => $this->csrfService->getNewToken(),
        ]);
    }

    /**
     * Displays a form for editing $name
     *
     * @param string $name
     */
    public function getDetail(string $name)
    {
        $entity = $this->crudRepository->getByFriendlyName($name);

        if (empty($entity))
        {
            $entityTitle = $this->getEntityTitle();
            $entityTitleLower = strtolower($entityTitle);
            throw (new ContentNotFoundException("I could not locate the {$entityTitleLower} {$name}."))
                ->setTitle($entityTitle . ' Not Found')
                ->setRecommendedUrl($this->getBaseUrl())
                ->setRecommendedAction("View All {$entityTitle}s");
        }

        $templateData = [
            'entity' => $entity,
            'validationResults' => new ValidationResults([]),
            'token' => $this->csrfService->getNewToken(),
        ];
        
        $templateData = $this->onGetDetailRender($templateData);
        $this->viewService->renderView($this->getTemplateFolder() . '/form', $templateData);
    }

    /**
     * Posts a form for a new entity.
     *
     * @param array $entityData
     */
    public function postNew(array $entityData)
    {
        $entity = $this->getEntityFromData($entityData);

        $this->saveEntity($entity, $entityData);
    }

    /**
     * Posts a form editing an existing entity
     *
     * @param string $name
     * @param array $entityData
     */
    public function postDetail(string $name, array $entityData)
    {
        $entity = $this->crudRepository->getByFriendlyName($name);

        if (empty($entity))
        {
            $entityTitle = $this->getEntityTitle();
            $entityTitleLower = strtolower($entityTitle);
            throw (new ContentNotFoundException("I could not locate the {$entityTitleLower} {$name}."))
                ->setTitle($entityTitle . ' Not Found')
                ->setRecommendedUrl($this->getBaseUrl())
                ->setRecommendedAction("View All {$entityTitle}s");
        }

        $entity->updateFromArray($entityData);

        $this->saveEntity($entity, $entityData);
    }

    /**
     * Handles saving new or old entities to the system
     *
     * @param mixed $entity
     * @param array $entityData
     */
    protected function saveEntity($entity, array $entityData)
    {
        $validationResults = $this->validateEntity($entity);

        if (!$this->csrfService->validateToken($entityData['token'] ?? ''))
        {
            $validationResults->addErrorForField('form', 'Your session has expired, please try again');
        }
        elseif ($this->onSaveValidation($validationResults, $entity, $entityData) && $validationResults->isValid())
        {
            try
            {
                $this->crudRepository->saveEntity($entity);
                $this->viewService->redirect($this->getBaseUrl(), 303, "{$this->getEntityTitle()} {$this->getEntityFriendlyName($entity)} successfully edited!");
                return;
            }
            catch (DuplicateEntityException $e)
            {
                $validationResults = new ValidationResults([ $e->getField() => [ "This {$e->getField()} is already registered. Please try another." ] ]);
            }
        }
        $this->viewService->renderView($this->getTemplateFolder() . '/form', [
            'entity' => $entity,
            'validationResults' => $validationResults,
            'token' => $this->csrfService->getNewToken(),
        ]);
    }

    /**
     * Displays a removal confirmation for the names provided.
     * @param array $getData
     */
    public function getRemove(array $getData)
    {
        $entities = $this->crudRepository->getListByFriendlyNames($getData['entities'] ?? []);
        $this->viewService->renderView($this->getTemplateFolder() . '/removeList', [
            'entities' => $entities,
            'token' => $this->csrfService->getNewToken(),
            'originalUrl' => $_SERVER['HTTP_REFERER'] ?? $this->getBaseUrl(),
        ]);
    }

    /**
     * Removes the entity provided
     * @param array $postData
     */
    public function postRemove(array $postData)
    {
        if (!$this->csrfService->validateToken($postData['token'] ?? ''))
        {
            $entityTitle = strtolower($this->getEntityTitle()) . 's';
            $this->viewService->redirect($postData['originalUrl'], 303, "Your session has expired, please try deleting those {$entityTitle} again");
        }
        else
        {
            $entities = $postData['entities'] ?? [];
            $this->crudRepository->deleteByFriendlyNames($entities);

            $this->viewService->redirect($postData['originalUrl'], 303, "{$this->getEntityTitle()}s successfully removed: " . implode(', ', $entities));
        }
    }

    /**
     * Override to augment data for the getDetail page.
     * 
     * @param array $templateData
     * @return array
     */
    protected function onGetDetailRender(array $templateData) : array
    {
        return $templateData;
    }

    /**
     * Override to do additional validation outside of the entity. If entity level validation can continue return true.
     * 
     * @param ValidationResults $validationResults
     * @param $entity
     * @param array $entityData
     * @return bool
     */
    protected function onSaveValidation(ValidationResults$validationResults, $entity, array $entityData) : bool 
    {
        return true;
    }
}