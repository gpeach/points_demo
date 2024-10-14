<?php

namespace App\Models;

use PDO;
use PDOException;

class UserModel
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getAllUsers(): array
    {
        try {
            $sql = "SELECT * FROM users";
            $stmt = $this->pdo->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new PDOException("Error fetching all users: " . $e->getMessage(), (int)$e->getCode(), $e);
        }
    }

    public function addUser(string $name, string $email): bool
    {
        try {
            $sql = "INSERT INTO users (name, email, points_balance) VALUES (:name, :email, :points_balance)";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                'name' => $name,
                'email' => $email,
                'points_balance' => 0
            ]);
        } catch (PDOException $e) {
            throw new PDOException("Error adding user: " . $e->getMessage(), (int)$e->getCode(), $e);
        }
    }

    public function earnPoints(int $userId, int $points): bool
    {
        try {
            $sql = "SELECT points_balance FROM users WHERE id = :userId";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['userId' => $userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                return false;
            }

            $newPoints = $user['points_balance'] + $points;

            $sql = "UPDATE users SET points_balance = :points WHERE id = :userId";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute(['points' => $newPoints, 'userId' => $userId]);
        } catch (PDOException $e) {
            throw new PDOException("Error updating points: " . $e->getMessage(), (int)$e->getCode(), $e);
        }
    }

    public function redeemPoints(int $userId, int $points): bool
    {
        try {
            $sql = "SELECT points_balance FROM users WHERE id = :userId";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['userId' => $userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user || $user['points_balance'] < $points) {
                return false;
            }

            $newPoints = $user['points_balance'] - $points;

            $sql = "UPDATE users SET points_balance = :points WHERE id = :userId";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute(['points' => $newPoints, 'userId' => $userId]);
        } catch (PDOException $e) {
            throw new PDOException("Error redeeming points: " . $e->getMessage(), (int)$e->getCode(), $e);
        }
    }

    public function deleteUser(int $userId): bool
    {
        try {
            $sql = "DELETE FROM users WHERE id = :userId";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['userId' => $userId]);
            if ($stmt->rowCount() === 0) {
                return false;
            }
            return true;
        } catch (PDOException $e) {
            throw new PDOException("Error deleting user: " . $e->getMessage(), (int)$e->getCode(), $e);
        }
    }

    public function getUserById(int $userId): ?array
    {
        try {
            $sql = "SELECT * FROM users WHERE id = :userId";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['userId' => $userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            return $user ?: null;
        } catch (PDOException $e) {
            throw new PDOException("Error fetching user by ID: " . $e->getMessage(), (int)$e->getCode(), $e);
        }
    }
}
