<?php

namespace App\Enums;

enum UserRole: string
{
    case CUSTOMER = 'customer';
    case ADMIN    = 'admin';

    public function label(): string
    {
        return match($this) {
            UserRole::CUSTOMER => 'Khách hàng',
            UserRole::ADMIN    => 'Quản trị viên',
        };
    }

    public function isAdmin(): bool
    {
        return $this === UserRole::ADMIN;
    }
}