<?php

use App\Services\Container;

$container = new Container();

$container->set('pdo', function () {
    $dbHost = "127.0.0.1";
    $dbName = "points_demo";
    $dbUser = "root";
    $dbPassword = "";
    $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName", $dbUser, $dbPassword);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $pdo;
});

return $container;
