<?php

namespace App\Services;

use Exception;

class Container
{
    /**
     * Array to hold the registered services.
     *
     * @var array
     */
    private array $services = [];

    /**
     * Register a service in the container.
     *
     * @param string $name The name of the service.
     * @param callable $service A callable that returns the service.
     */
    public function set(string $name, callable $service): void
    {
        $this->services[$name] = $service;
    }

    /**
     * Retrieve a service from the container.
     *
     * @param string $name The name of the service to retrieve.
     * @return mixed The service (e.g., PDO instance).
     * @throws Exception If the service is not found.
     */
    public function get(string $name)
    {
        if (!isset($this->services[$name])) {
            throw new Exception("Service {$name} not found.");
        }

        return $this->services[$name]();
    }

    /**
     * Check if the container has a service registered.
     *
     * @param string $name The name of the service to check.
     * @return bool True if the service exists, false otherwise.
     */
    public function has(string $name): bool
    {
        return isset($this->services[$name]);
    }
}
