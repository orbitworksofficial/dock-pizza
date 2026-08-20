<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class SeoMeta extends Model
{
    protected $table = 'seo_meta';

    protected $fillable = [
        'page_key',
        'page_name',
        'seoable_type',
        'seoable_id',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'canonical_url',
        'robots',
        'og_title',
        'og_description',
        'og_image',
        'og_type',
        'twitter_title',
        'twitter_description',
        'twitter_image',
        'twitter_card',
        'schema_markup',
        'faq_schema',
    ];

    /**
     * schema_markup and faq_schema are intentionally NOT cast to array — they
     * hold hand-pasted JSON-LD that must be stored verbatim so a validation
     * error can quote it back, and re-encoded only on output.
     */
    protected $casts = [];

    public function seoable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * FAQs shown on this page. The same rows generate the FAQPage JSON-LD.
     */
    public function faqs(): HasMany
    {
        return $this->hasMany(Faq::class, 'seo_meta_id')
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    /**
     * Rows addressed by route rather than by an owning model.
     */
    public function scopeForPages($query)
    {
        return $query->whereNotNull('page_key');
    }
}
