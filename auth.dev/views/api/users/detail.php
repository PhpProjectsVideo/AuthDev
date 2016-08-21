<?php

/* @var \PhpProjects\AuthDev\Model\User\UserEntity $user */
$response['user'] = [
    'username' => $user->getUsername(),
    'name' => $user->getName(),
    'email' => $user->getEmail(),
];

foreach ($user->getGroupIds() as $id)
{
    $response['user']['user_groups']['groups'][] = [
        'group' => ['id' => $id],
        'links' => [
            'self' => '/api/group/' . $id,
            'edit' => '/api/group/' . $id,
        ],
    ];
}
$response['user']['user_groups']['links']['list'] = '/api/groups';

$response['links'] = [
    'self' => '/api/users/user/' . urlencode($user->getId()),
    'edit' => '/api/users/user/' . urlencode($user->getId()),
    'list' => '/api/users',
    'create' => '/api/users/user',
];

echo json_encode($response);
