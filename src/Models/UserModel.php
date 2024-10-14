<?php

namespace App\Models;

use PDO;

class UserModel
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getAllUsers(): array
    {
        $sql = "SELECT * FROM users";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addUser(string $name, string $email): bool
    {
        $sql = "INSERT INTO users (name, email, points_balance) VALUES (:name, :email, :points_balance)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            'name' => $name,
            'email' => $email,
            'points_balance' => 0
        ]);
    }

    public function earnPoints(int $userId, int $points): bool
    {
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
    }

    public function redeemPoints(int $userId, int $points): bool
    {
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
    }

    public function deleteUser(int $userId): bool
    {
        $sql = "SELECT * FROM users WHERE id = :userId";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['userId' => $userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            return false;
        }

        $sql = "DELETE FROM users WHERE id = :userId";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute(['userId' => $userId]);
    }
}
