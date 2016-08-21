<?php

$response['users'] = [];
/* @var \PhpProjects\AuthDev\Model\User\UserEntity $user */
foreach ($users as $user)
{
    $response['users'][] = [
        'user' => [
            'username' => $user->getUsername(),
            'name' => $user->getName(),
            'email' => $user->getEmail(),
        ],
        'links' => [
            'self' => '/api/users/user/' . urlencode($user->getId()),
            'edit' => '/api/users/user/' . urlencode($user->getId())
        ],
    ];
}

$response['links']['search'] = '/api/users?q={searchTerm}';
$response['links']['list'] = '/api/users';
$response['links']['create'] = '/api/users/user';
if ($currentPage < $totalPages)
{
    $response['links']['next'] = '/api/users?page=' . urlencode($currentPage + 1);
}

if ($currentPage > 1)
{
    $response['links']['prev'] = '/api/users?page=' . urlencode($currentPage - 1);
}

echo json_encode($response);