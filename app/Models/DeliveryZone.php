<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryZone extends Model
{
    protected $fillable = [
        'store_id',
        'zone_name',
        'zip_code',
        'delivery_fee',
        'estimated_time',
        'is_active',
    ];

    protected $casts = [
        'delivery_fee' => 'decimal:2',
        'estimated_time' => 'integer',
        'is_active' => 'boolean',
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }
}
