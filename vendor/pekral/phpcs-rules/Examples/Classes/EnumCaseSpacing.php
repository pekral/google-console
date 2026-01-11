<?php

declare(strict_types = 1);

namespace Example\Classes;

enum UserRole
{

    case Admin;
    case User;
    case Guest;

}

final class EnumCaseSpacing
{

    public function getRole(): UserRole
    {
        return UserRole::Admin;
    }

} 