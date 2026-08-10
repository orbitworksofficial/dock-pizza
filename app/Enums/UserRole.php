<?php

declare(strict_types=1);

namespace App\Enums;

enum UserRole: string
{
    case SUPER_ADMIN = 'super_admin';
    case ADMIN = 'admin';
    case STORE_MANAGER = 'store_manager';
    case STAFF = 'staff';
    case CUSTOMER = 'customer';

    public function label(): string
    {
        return match ($this) {
            self::SUPER_ADMIN => 'Super Administrator',
            self::ADMIN => 'Administrator',
            self::STORE_MANAGER => 'Store Manager',
            self::STAFF => 'Staff',
            self::CUSTOMER => 'Customer',
        };
    }

    public function isAdmin(): bool
    {
        return in_array($this, [self::SUPER_ADMIN, self::ADMIN]);
    }

    public function isStaff(): bool
    {
        return in_array($this, [self::SUPER_ADMIN, self::ADMIN, self::STORE_MANAGER, self::STAFF]);
    }
}
