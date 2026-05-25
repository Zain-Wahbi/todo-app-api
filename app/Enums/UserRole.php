<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case User  = 'user';

    public function label(): string
    {
        return match($this) {
            UserRole::Admin => 'Administrator',
            UserRole::User  => 'Regular User',
        };
    }

    public function isAdmin(): bool
    {
        return $this === UserRole::Admin;
    }
}