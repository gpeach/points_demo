<?php

use App\Services\Container;

$container = new Container();

$container->set('pdo', function () {
    $dbHost = "127.0.0.1";
    $dbName = "points_demo";
    $dbUser = "root";
    $dbPassword = "";
    try{
        $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName", $dbUser, $dbPassword);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        die("Connection failed: " . $e->getMessage());
    }

    return $pdo;
});

return $container;
