<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CateringPackage extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'starting_price',
        'min_people',
        'max_people',
        'image',
        'includes',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'starting_price' => 'decimal:2',
        'min_people' => 'integer',
        'max_people' => 'integer',
        'includes' => 'array',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function requests(): HasMany
    {
        return $this->hasMany(CateringRequest::class);
    }
}
