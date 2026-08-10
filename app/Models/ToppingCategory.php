<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ToppingCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'sort_order',
        'is_active',
        'max_selections',
        'is_required',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_active' => 'boolean',
        'max_selections' => 'integer',
        'is_required' => 'boolean',
    ];

    public function toppings(): HasMany
    {
        return $this->hasMany(Topping::class)->orderBy('sort_order');
    }
}
