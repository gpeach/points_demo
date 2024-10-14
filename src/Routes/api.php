<?php

use Slim\App;
use App\Controllers\HomeController;
use App\Controllers\UserController;
use App\Models\UserModel;

return function (App $app, $container) {

    $pdo = $container->get('pdo');

    $homeController = new HomeController();

    $userModel = new UserModel($pdo);

    $userController = new UserController($userModel);

    $app->get('/', [$homeController, 'index']);

    $app->get('/users', [$userController, 'getAllUsers']);

    $app->post('/users', [$userController, 'createUser']);

    $app->post('/users/{id}/earn', [$userController, 'earnPoints']);

    $app->post('/users/{id}/redeem', [$userController, 'redeemPoints']);

    $app->delete('/users/{id}', [$userController, 'deleteUser']);
};
