<?php

declare(strict_types = 1);

namespace Example\ControlStructures;

final class RequireNullSafeObjectOperator
{

    public function exampleWithNullSafeOperator(): void
    {
        $user = $this->getUser();
        $name = $user?->getName();
        $email = $user?->getProfile()?->getEmail();
        
        echo "Name: " . ($name ?? 'null') . "\n";
        echo "Email: " . ($email ?? 'null') . "\n";
    }

    public function exampleWithComplexChain(): void
    {
        $user = $this->getUser();
        $street = $user?->getProfile()?->getAddress()?->getStreet();
        $city = $user?->getProfile()?->getAddress()?->getCity();
        
        echo "Street: " . ($street ?? 'null') . "\n";
        echo "City: " . ($city ?? 'null') . "\n";
    }

    public function exampleWithNullCheck(): void
    {
        $user = $this->getUser();
        
        if ($user === null) {
            return;
        }

        $name = $user->getName();
        $email = $user->getProfile()->getEmail();
        echo "Name: " . $name . ", Email: " . $email . "\n";
    }

    private function getUser(): ?User
    {
        return new User();
    }

}

final class User
{

    public function getName(): ?string
    {
        return 'John Doe';
    }

    public function getProfile(): ?Profile
    {
        return new Profile();
    }

}

final class Profile
{

    public function getEmail(): ?string
    {
        return 'john@example.com';
    }

    public function getAddress(): ?Address
    {
        return new Address();
    }

}

final class Address
{

    public function getStreet(): ?string
    {
        return '123 Main St';
    }

    public function getCity(): ?string
    {
        return 'New York';
    }

}
