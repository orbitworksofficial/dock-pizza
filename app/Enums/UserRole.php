<?php

declare(strict_types=1);

namespace App\Enums;

enum UserRole: string
{
    case SUPER_ADMIN = 'super_admin';
    case ADMIN = 'admin';
    case AUTHOR = 'author';
    case STORE_MANAGER = 'store_manager';
    case STAFF = 'staff';
    case CUSTOMER = 'customer';

    public function label(): string
    {
        return match ($this) {
            self::SUPER_ADMIN => 'Super Administrator',
            self::ADMIN => 'Administrator',
            self::AUTHOR => 'Author',
            self::STORE_MANAGER => 'Store Manager',
            self::STAFF => 'Staff',
            self::CUSTOMER => 'Customer',
        };
    }

    public function isAdmin(): bool
    {
        return in_array($this, [self::SUPER_ADMIN, self::ADMIN]);
    }

    /**
     * Roles allowed into the CMS at all. Authors reach it too, but every
     * controller still checks ownership before acting on a record.
     */
    public function canAuthor(): bool
    {
        return in_array($this, [self::SUPER_ADMIN, self::ADMIN, self::AUTHOR]);
    }

    public function isStaff(): bool
    {
        return in_array($this, [self::SUPER_ADMIN, self::ADMIN, self::STORE_MANAGER, self::STAFF]);
    }

    /**
     * Roles assignable from the CMS user manager.
     *
     * @return array<int, self>
     */
    public static function cmsRoles(): array
    {
        return [self::ADMIN, self::AUTHOR];
    }
}
