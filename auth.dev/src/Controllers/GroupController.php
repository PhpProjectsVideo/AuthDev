<?php

namespace PhpProjects\AuthDev\Controllers;

use PhpProjects\AuthDev\Model\Csrf\CsrfService;
use PhpProjects\AuthDev\Model\DuplicateEntityException;
use PhpProjects\AuthDev\Model\Group\GroupRepository;
use PhpProjects\AuthDev\Model\Group\GroupValidation;
use PhpProjects\AuthDev\Model\Group\GroupEntity;
use PhpProjects\AuthDev\Model\ValidationResults;
use PhpProjects\AuthDev\Views\ViewService;

/**
 * Manages operations against the group portion of our domain model
 */
class GroupController
{
    /**
     * @var ViewService
     */
    private $viewService;

    /**
     * @var GroupRepository
     */
    private $groupRepository;

    /**
     * @var GroupValidation
     */
    private $groupValidation;

    /**
     * @var CsrfService
     */
    private $csrfService;

    /**
     * @param ViewService $viewService
     * @param GroupRepository $groupRepository
     * @param GroupValidation $groupValidation
     * @param CsrfService $csrfService
     */
    public function __construct(ViewService $viewService, GroupRepository $groupRepository, GroupValidation $groupValidation, CsrfService $csrfService)
    {
        $this->viewService = $viewService;
        $this->groupRepository = $groupRepository;
        $this->groupValidation = $groupValidation;
        $this->csrfService = $csrfService;
    }

    /**
     * Convenience Constructor
     *
     * @return GroupController
     */
    public static function create() : GroupController
    {
        return new self(ViewService::create(), GroupRepository::create(), new GroupValidation(), CsrfService::create());
    }


    /**
     * Displays a list of groups in the system
     *
     * @param int $currentPage
     * @param string $query
     */
    public function getList(int $currentPage = 1, string $query = '')
    {
        if (empty($query))
        {
            $groupList = $this->groupRepository->getSortedList(10, ($currentPage - 1) * 10);
            $groupCount = $this->groupRepository->getCount();
        }
        else
        {
            $groupList = $this->groupRepository->getListMatchingFriendlyName($query, 10, ($currentPage - 1) * 10);
            $groupCount = $this->groupRepository->getCountMatchingFriendlyName($query);
        }

        $templateData = [
            'groups' => $groupList,
            'currentPage' => $currentPage,
            'totalPages' => $groupCount > 0 ? (int)(($groupCount - 1) / 10) + 1 : 1,
            'term' => $query
        ];

        if (!empty($redirectMessage = $this->viewService->getRedirectMessage()))
        {
            $templateData['message'] = $redirectMessage;
        }
        $this->viewService->renderView('groups/list', $templateData);
    }

    /**
     * Displays a form to add a new group.
     */
    public function getNew()
    {
        $groupEntity = new GroupEntity();
        $this->viewService->renderView('groups/form', [
            'group' => $groupEntity,
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
        $group = $this->groupRepository->getByFriendlyName($name);

        if (empty($group))
        {
            throw (new ContentNotFoundException("I could not locate the group {$name}."))
                ->setTitle('Group Not Found')
                ->setRecommendedUrl('/groups/')
                ->setRecommendedAction('View All Groups');
        }

        $this->viewService->renderView('groups/form', [
            'group' => $group,
            'validationResults' => new ValidationResults([]),
            'token' => $this->csrfService->getNewToken(),
        ]);
    }

    /**
     * Posts a form for a new group.
     *
     * @param array $groupData
     */
    public function postNew(array $groupData)
    {
        $group = GroupEntity::createFromArray($groupData);

        $this->saveGroup($group, $groupData);
    }

    /**
     * Posts a form editing an existing group
     *
     * @param string $name
     * @param array $groupData
     */
    public function postDetail(string $name, array $groupData)
    {
        $group = $this->groupRepository->getByFriendlyName($name);
        $group->updateFromArray($groupData);

        $this->saveGroup($group, $groupData);
    }

    /**
     * Handles saving new or old groups to the system
     *
     * @param GroupEntity $group
     */
    protected function saveGroup(GroupEntity $group, array $groupData)
    {
        $validationResults = $this->groupValidation->validate($group);

        if (!$this->csrfService->validateToken($groupData['token'] ?? ''))
        {
            $validationResults->addErrorForField('form', 'Your session has expired, please try again');
        }
        elseif ($validationResults->isValid())
        {
            try
            {
                $this->groupRepository->saveEntity($group);
                $this->viewService->redirect('/groups/', 303, "Group {$group->getName()} successfully edited!");
                return;
            }
            catch (DuplicateEntityException $e)
            {
                $validationResults = new ValidationResults([ $e->getField() => [ "This {$e->getField()} is already registered. Please try another." ] ]);
            }
        }
        $this->viewService->renderView('groups/form', [
            'group' => $group,
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
        $groups = $this->groupRepository->getListByFriendlyNames($getData['groups'] ?? []);
        $this->viewService->renderView('groups/removeList', [
            'groups' => $groups,
            'token' => $this->csrfService->getNewToken(),
            'originalUrl' => $_SERVER['HTTP_REFERER'] ?? '/groups/'
        ]);
    }

    /**
     * Removes the groups provided
     * @param $_POST
     */
    public function postRemove(array $postData)
    {
        if (!$this->csrfService->validateToken($postData['token'] ?? ''))
        {
            $this->viewService->redirect($postData['originalUrl'], 303, "Your session has expired, please try deleting those groups again");
        }
        else
        {
            $groups = $postData['groups'] ?? [];
            $this->groupRepository->deleteByFriendlyNames($groups);

            $this->viewService->redirect($postData['originalUrl'], 303, 'Groups successfully removed: ' . implode(', ', $groups));
        }
    }
}