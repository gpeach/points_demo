<?php

namespace App\Validators;

class UserValidator
{
    public static function validateName(string $name): bool|array
    {
        $sanitized = filter_var($name, FILTER_SANITIZE_SPECIAL_CHARS);
        if (empty($sanitized)) {
            return ["error" => true, "message" => "Name cannot be empty."];
        }
        if (strlen($sanitized) > 255) {
            return ["error" => true, "message" => "Name cannot be more than 255 characters."];
        }
        return true;
    }

    public static function validateEmail(string $email): bool|array
    {
        $sanitized = filter_var($email, FILTER_SANITIZE_EMAIL);
        if (empty($sanitized)) {
            return ["error" => true, "message" => "Email cannot be empty."];
        }
        if (strlen($sanitized) > 255) {
            return ["error" => true, "message" => "Email cannot be more than 255 characters."];
        }
        if (!filter_var($sanitized, FILTER_VALIDATE_EMAIL)) {
            return ["error" => true, "message" => "Invalid email address."];
        }
        return true;
    }

    public static function validatePoints($points): bool|array
    {
        if (!filter_var($points, FILTER_VALIDATE_INT, ["options" => ["min_range" => 0, "max_range" => 1000000]])) {
            return ["error" => true, "message" => "Points must be a positive integer not more than 1,000,000."];
        }
        return true;
    }

    public static function validateDescription(string $description): bool|array
    {
        $sanitized = filter_var($description, FILTER_SANITIZE_SPECIAL_CHARS);
        if (empty($sanitized)) {
            return ["error" => true, "message" => "Description cannot be empty."];
        }
        if (strlen($sanitized) > 255) {
            return ["error" => true, "message" => "Description cannot be more than 255 characters."];
        }
        return true;
    }
}
