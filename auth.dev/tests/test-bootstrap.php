<?php

require __DIR__ . '/../vendor/autoload.php';

$pdo = new PDO('mysql:host=localhost', 'auth', 'auth123');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$pdo->exec("DROP DATABASE IF EXISTS auth_test");
$pdo->exec("CREATE DATABASE auth_test");
$pdo->exec("USE auth_test");

$contents = file_get_contents(__DIR__ . '/../schema/schema.sql');

$pdo->exec($contents);


\PhpProjects\AuthDev\DatabaseTestCaseTrait::setPdo($pdo);