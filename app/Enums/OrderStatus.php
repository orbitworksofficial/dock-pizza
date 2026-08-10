<?php

declare(strict_types=1);

namespace App\Enums;

enum OrderStatus: string
{
    case PENDING = 'pending';
    case CONFIRMED = 'confirmed';
    case PREPARING = 'preparing';
    case READY = 'ready';
    case OUT_FOR_DELIVERY = 'out_for_delivery';
    case DELIVERED = 'delivered';
    case PICKED_UP = 'picked_up';
    case CANCELLED = 'cancelled';
    case REFUNDED = 'refunded';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::CONFIRMED => 'Confirmed',
            self::PREPARING => 'Preparing',
            self::READY => 'Ready for Pickup',
            self::OUT_FOR_DELIVERY => 'Out for Delivery',
            self::DELIVERED => 'Delivered',
            self::PICKED_UP => 'Picked Up',
            self::CANCELLED => 'Cancelled',
            self::REFUNDED => 'Refunded',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'yellow',
            self::CONFIRMED => 'blue',
            self::PREPARING => 'indigo',
            self::READY => 'emerald',
            self::OUT_FOR_DELIVERY => 'orange',
            self::DELIVERED => 'green',
            self::PICKED_UP => 'green',
            self::CANCELLED => 'red',
            self::REFUNDED => 'gray',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::PENDING => 'clock',
            self::CONFIRMED => 'check-circle',
            self::PREPARING => 'fire',
            self::READY => 'shopping-bag',
            self::OUT_FOR_DELIVERY => 'truck',
            self::DELIVERED => 'home',
            self::PICKED_UP => 'hand',
            self::CANCELLED => 'x-circle',
            self::REFUNDED => 'arrow-uturn-left',
        };
    }

    public function isActive(): bool
    {
        return in_array($this, [
            self::PENDING,
            self::CONFIRMED,
            self::PREPARING,
            self::READY,
            self::OUT_FOR_DELIVERY,
        ]);
    }

    public function isCompleted(): bool
    {
        return in_array($this, [self::DELIVERED, self::PICKED_UP]);
    }
}
