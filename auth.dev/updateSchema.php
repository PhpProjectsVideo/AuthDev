<?php

$pdo = new PDO('mysql:host=localhost', 'auth', 'auth123');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$pdo->exec("DROP DATABASE IF EXISTS auth");
$pdo->exec("CREATE DATABASE auth");
$pdo->exec("USE auth");

$contents = file_get_contents(__DIR__ . '/schema/schema.sql');
$pdo->exec($contents);
