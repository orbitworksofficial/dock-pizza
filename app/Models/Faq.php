<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Faq extends Model
{
    protected $fillable = [
        'seo_meta_id',
        'blog_post_id',
        'question',
        'answer',
        'category',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function seoMeta(): BelongsTo
    {
        return $this->belongsTo(SeoMeta::class, 'seo_meta_id');
    }
}
