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

final class RequireAttributeAfterDocComment
{

    /**
     * Get users list
     */
    #[Route('/api/users', methods: ['GET'])]
    #[Security("is_granted('ROLE_USER')")]
    public function getUsers(): array
    {
        return [];
    }

    /**
     * Update user
     * 
     * @param int $id User ID
     */
    #[Route('/api/users/{id}', methods: ['PUT'])]
    #[Security("is_granted('ROLE_ADMIN')")]
    #[Validate('user')]
    public function updateUser(int $id): void
    {
        $userId = $id;
        unset($userId);
    }

}
