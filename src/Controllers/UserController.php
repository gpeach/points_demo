<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Validators\UserValidator;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class UserController
{
    private UserModel $userModel;

    public function __construct(UserModel $userModel)
    {
        $this->userModel = $userModel;
    }

    public function getAllUsers(Request $request, Response $response): Response
    {
        $users = $this->userModel->getAllUsers();

        if (empty($users)) {
            return $response->withStatus(204);
        }

        $payload = json_encode($users);
        $response->getBody()->write($payload);

        return $response->withHeader('Content-Type', 'application/json');
    }

    public function createUser(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();

        $nameValidation = UserValidator::validateName($data['name'] ?? '');
        if ($nameValidation !== true) {
            return $this->jsonResponse($response, $nameValidation, 400);
        }

        $emailValidation = UserValidator::validateEmail($data['email'] ?? '');
        if ($emailValidation !== true) {
            return $this->jsonResponse($response, $emailValidation, 400);
        }

        $this->userModel->addUser($data['name'], $data['email']);
        return $this->jsonResponse($response, ["success" => true, "message" => "User added successfully."], 201);
    }

    public function earnPoints(Request $request, Response $response, array $args): Response
    {
        $userId = (int) $args['id'];
        $data = $request->getParsedBody();

        $pointsValidation = UserValidator::validatePoints($data['points'] ?? 0);
        if ($pointsValidation !== true) {
            return $this->jsonResponse($response, $pointsValidation, 400);
        }

        $descriptionValidation = UserValidator::validateDescription($data['description'] ?? '');
        if ($descriptionValidation !== true) {
            return $this->jsonResponse($response, $descriptionValidation, 400);
        }

        $points = $data['points'];
        $description = $data['description'];
        $success = $this->userModel->earnPoints($userId, $points);

        if (!$success) {
            return $this->jsonResponse($response, ["error" => true, "message" => "User not found or update failed."], 500);
        }

        return $this->jsonResponse($response, ["success" => true, "message" => "User $userId earned $points points for $description."], 200);
    }

    public function redeemPoints(Request $request, Response $response, array $args): Response
    {
        $userId = (int) $args['id'];
        $data = $request->getParsedBody();

        $pointsValidation = UserValidator::validatePoints($data['points'] ?? 0);
        if ($pointsValidation !== true) {
            return $this->jsonResponse($response, $pointsValidation, 400);
        }

        $descriptionValidation = UserValidator::validateDescription($data['description'] ?? '');
        if ($descriptionValidation !== true) {
            return $this->jsonResponse($response, $descriptionValidation, 400);
        }

        $points = $data['points'];
        $description = $data['description'];
        $success = $this->userModel->redeemPoints($userId, $points);

        if (!$success) {
            return $this->jsonResponse($response, ["error" => true, "message" => "User not found or insufficient points."], 400);
        }

        return $this->jsonResponse($response, ["success" => true, "message" => "User $userId redeemed $points points for $description."], 200);
    }

    public function deleteUser(Request $request, Response $response, array $args): Response
    {
        $userId = (int) $args['id'];

        $success = $this->userModel->deleteUser($userId);

        if (!$success) {
            return $this->jsonResponse($response, ["error" => true, "message" => "User not found."], 404);
        }

        return $this->jsonResponse($response, ["success" => true, "message" => "User $userId deleted successfully."], 200);
    }

    private function jsonResponse(Response $response, array $data, int $statusCode): Response
    {
        $payload = json_encode($data);
        $response->getBody()->write($payload);

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($statusCode);
    }
}
