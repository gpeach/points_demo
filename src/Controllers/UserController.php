<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Validators\UserValidator;
use App\Exceptions\ValidationException;
use Exception;
use PDOException;
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
        try{
            $users = $this->userModel->getAllUsers();

            if (empty($users)) {
                return $response->withStatus(204);
            }

            $payload = json_encode($users);
            $response->getBody()->write($payload);

            return $response->withHeader('Content-Type', 'application/json');
        } catch (PDOException $e) {
            return $this->jsonResponse($response, [
                'error' => true,
                'message' => 'Database Error: ' . $e->getMessage()
            ], 500);
        } catch (Exception $e) {
            return $this->jsonResponse($response, [
                'error' => true,
                'message' => 'An unexpected error occurred.'
            ], 500);
        }
    }

    public function createUser(Request $request, Response $response): Response
    {
        try {
            $data = $request->getParsedBody();
            UserValidator::validateName($data['name'] ?? '');
            UserValidator::validateEmail($data['email'] ?? '');

            $this->userModel->addUser($data['name'], $data['email']);
            return $this->jsonResponse($response, ["success" => true, "message" => "User added successfully."], 201);
        } catch (ValidationException $e) {
            return $this->jsonResponse($response, [
                'error' => true,
                'message' => $e->getMessage(),
                'validationErrors' => $e->getErrors()
            ], 400);
        } catch (PDOException $e) {
            return $this->jsonResponse($response, [
                'error' => true,
                'message' => 'Database Error: ' . $e->getMessage()
            ], 500);
        } catch (Exception $e) {
            return $this->jsonResponse($response, [
                'error' => true,
                'message' => 'An unexpected error occurred.'
            ], 500);
        }
    }

    public function earnPoints(Request $request, Response $response, array $args): Response
    {
        try {
            $userId = (int)$args['id'];
            $data = $request->getParsedBody();
            UserValidator::validatePoints($data['points'] ?? 0);
            UserValidator::validateDescription($data['description'] ?? '');

            $points = $data['points'];
            $description = $data['description'];

            $success = $this->userModel->earnPoints($userId, $points);
            if (!$success) {
                return $this->jsonResponse(
                    $response,
                    ["error" => true, "message" => "User not found or update failed."],
                    500
                );
            }

            return $this->jsonResponse(
                $response,
                ["success" => true, "message" => "User $userId earned $points points for $description."],
                200
            );
        } catch (ValidationException $e) {
            return $this->jsonResponse($response, [
                'error' => true,
                'message' => $e->getMessage(),
                'validationErrors' => $e->getErrors()
            ], 400);
        } catch (PDOException $e) {
            return $this->jsonResponse($response, [
                'error' => true,
                'message' => 'Database Error: ' . $e->getMessage()
            ], 500);
        } catch (Exception $e) {
            return $this->jsonResponse($response, [
                'error' => true,
                'message' => 'An unexpected error occurred.'
            ], 500);
        }
    }

    public function redeemPoints(Request $request, Response $response, array $args): Response
    {
        try {
            $userId = (int)$args['id'];
            $data = $request->getParsedBody();
            UserValidator::validatePoints((int)$data['points'] ?? 0);
            UserValidator::validateDescription($data['description'] ?? '');

            $points = $data['points'];
            $description = $data['description'];
            $success = $this->userModel->redeemPoints($userId, $points);

            if (!$success) {
                return $this->jsonResponse(
                    $response,
                    ["error" => true, "message" => "User not found or insufficient points."],
                    400
                );
            }

            return $this->jsonResponse(
                $response,
                ["success" => true, "message" => "User $userId redeemed $points points for $description."],
                200
            );
        } catch (ValidationException $e) {
            return $this->jsonResponse($response, [
                'error' => true,
                'message' => $e->getMessage(),
                'validationErrors' => $e->getErrors()
            ], 400);
        } catch (PDOException $e) {
            return $this->jsonResponse($response, [
                'error' => true,
                'message' => 'Database Error: ' . $e->getMessage()
            ], 500);
        } catch (Exception $e) {
            return $this->jsonResponse($response, [
                'error' => true,
                'message' => 'An unexpected error occurred.'
            ], 500);
        }
    }

    public function deleteUser(Request $request, Response $response, array $args): Response
    {
        $userId = (int)$args['id'];
        try {
            $success = $this->userModel->deleteUser($userId);
            if (!$success) {
                return $this->jsonResponse($response, ["error" => true, "message" => "User not found."], 404);
            }

            return $this->jsonResponse(
                $response,
                ["success" => true, "message" => "User $userId deleted successfully."],
                200
            );
        } catch (PDOException $e) {
            return $this->jsonResponse($response, [
                'error' => true,
                'message' => 'Database Error: ' . $e->getMessage()
            ], 500);
        } catch (Exception $e) {
            return $this->jsonResponse($response, [
                'error' => true,
                'message' => 'An unexpected error occurred.'
            ], 500);
        }


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
