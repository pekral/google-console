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

#[Attribute]
final class Cache
{

    public function __construct(public int $ttl = 3_600)
    {
    }

}

final class AttributesOrder
{

    #[Cache(ttl: 1_800)]
    #[Route('/api/users', methods: ['GET'])]
    #[Security("is_granted('ROLE_USER')")]
    #[Validate('user')]
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
    #[Validate('user')]
    private string $name;

}
