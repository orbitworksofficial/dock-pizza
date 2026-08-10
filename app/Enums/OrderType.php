<?php

declare(strict_types=1);

namespace App\Enums;

enum OrderType: string
{
    case DELIVERY = 'delivery';
    case PICKUP = 'pickup';

    public function label(): string
    {
        return match ($this) {
            self::DELIVERY => 'Delivery',
            self::PICKUP => 'Pickup',
        };
    }
}
