<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CateringRequest extends Model
{
    protected $fillable = [
        'user_id',
        'catering_package_id',
        'name',
        'email',
        'phone',
        'company',
        'event_date',
        'event_time',
        'guest_count',
        'event_type',
        'delivery_address',
        'special_requests',
        'estimated_budget',
        'status',
        'admin_notes',
        'quoted_amount',
        'responded_at',
    ];

    protected $casts = [
        'event_date' => 'date',
        'guest_count' => 'integer',
        'estimated_budget' => 'decimal:2',
        'quoted_amount' => 'decimal:2',
        'responded_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(CateringPackage::class, 'catering_package_id');
    }
}
