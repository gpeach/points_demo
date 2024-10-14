<?php

namespace App\Validators;

use App\Exceptions\ValidationException;

class UserValidator
{
    /**
     * @throws ValidationException
     */
    public static function validateName(string $name): void
    {
        $sanitized = filter_var($name, FILTER_SANITIZE_SPECIAL_CHARS);
        if (empty($sanitized)) {
            throw new ValidationException("Invalid Name", ['name' => 'Name cannot be empty.']);
        }
        if (strlen($sanitized) > 255) {
            throw new ValidationException("Invalid Name", ['name' => 'Name cannot be more than 255 characters.']);
        }
    }

    /**
     * @throws ValidationException
     */
    public static function validateEmail(string $email): void
    {
        $sanitized = filter_var($email, FILTER_SANITIZE_EMAIL);
        if (empty($sanitized)) {
            throw new ValidationException("Invalid Email", ['email' => 'Email cannot be empty.']);
        }
        if (strlen($sanitized) > 255) {
            throw new ValidationException("Invalid Email", ['email' => 'Email cannot be more than 255 characters.']);
        }
        if (!filter_var($sanitized, FILTER_VALIDATE_EMAIL)) {
            throw new ValidationException("Invalid Email", ['email' => 'Invalid email address.']);
        }
    }

    /**
     * @throws ValidationException
     */
    public static function validatePoints($points): void
    {
        if (!filter_var($points, FILTER_VALIDATE_INT, ["options" => ["min_range" => 0, "max_range" => 1000000]])) {
            throw new ValidationException("Invalid Points", ['points' => 'Points must be a positive integer not more than 1,000,000.']);
        }
    }

    /**
     * @throws ValidationException
     */
    public static function validateDescription(string $description): void
    {
        $sanitized = filter_var($description, FILTER_SANITIZE_SPECIAL_CHARS);
        if (empty($sanitized)) {
            throw new ValidationException("Invalid Description", ['description' => 'Description cannot be empty.']);
        }
        if (strlen($sanitized) > 255) {
            throw new ValidationException("Invalid Description", ['description' => 'Description cannot be more than 255 characters.']);
        }
    }
}
