<?php

declare(strict_types = 1);

namespace Example\Attributes;

use Attribute;

#[Attribute]
final class Route
{

    public function __construct(public string $path, public array $methods = ['GET'])
    {
    }

}

#[Attribute]
final class Security
{

    public function __construct(public string $expression)
    {
    }

}

#[Attribute]
final class Validate
{

    public function __construct(public string $validator)
    {
    }

}

final class AttributeAndTargetSpacing
{

    #[Route('/api/users', methods: ['GET'])]
    public function getUsers(): array
    {
        return [];
    }

    #[Route('/api/users/{id}', methods: ['PUT'])]
    #[Security("is_granted('ROLE_ADMIN')")]
    public function updateUser(int $id): void
    {
        $userId = $id;
        unset($userId);
    }

    #[Validate('user')]
    public function validateUser(): void
    {
        // Implementation
    }

}

#[Route('/api/users', methods: ['GET'])]
final class UserController
{

    public function index(): void
    {
        // Implementation
    }

}

final class UserEntity
{

    #[Route('/api/users', methods: ['GET'])]
    #[Security("is_granted('ROLE_USER')")]
    private string $name;

}
