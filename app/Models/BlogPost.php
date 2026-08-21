<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class BlogPost extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title', 'slug', 'excerpt', 'content',
        'featured_image', 'featured_image_alt',
        'status', 'is_featured', 'allow_comments', 'reading_minutes',
        'blog_category_id', 'author_id', 'published_at', 'views_count',
        'seo_title', 'seo_description', 'seo_keywords', 'canonical_url', 'robots',
        'og_title', 'og_description', 'og_image', 'og_type',
        'twitter_title', 'twitter_description', 'twitter_image', 'twitter_card',
        'schema_markup', 'faq_schema',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'is_featured' => 'boolean',
        'allow_comments' => 'boolean',
        'views_count' => 'integer',
        'reading_minutes' => 'integer',
    ];

    public const STATUSES = ['draft', 'published', 'archived'];

    /**
     * Average adult reading speed. Used to derive reading_minutes, which is
     * never editable by hand.
     */
    private const WORDS_PER_MINUTE = 200;

    protected static function booted(): void
    {
        static::saving(function (BlogPost $post) {
            $post->reading_minutes = $post->calculateReadingMinutes();
        });
    }

    public function calculateReadingMinutes(): int
    {
        $text = trim(strip_tags((string) $this->content));
        $words = $text === '' ? 0 : str_word_count($text);

        return max(1, (int) ceil($words / self::WORDS_PER_MINUTE));
    }

    /**
     * Whether the slug may still track the title.
     *
     * Once a post has been published, or its slug edited by hand, the URL is
     * public: changing it silently breaks inbound links and loses rankings.
     */
    public function slugIsLocked(): bool
    {
        if ($this->exists && $this->published_at !== null) {
            return true;
        }

        // A slug that no longer matches its title was set deliberately.
        return $this->exists
            && $this->slug !== ''
            && $this->slug !== Str::slug((string) $this->getOriginal('title'));
    }

    /**
     * A slug unique across posts, ignoring this one.
     */
    public static function uniqueSlug(string $value, ?int $ignoreId = null): string
    {
        $base = Str::slug($value) ?: 'post';
        $slug = $base;
        $n = 2;

        while (static::withTrashed()
            ->where('slug', $slug)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists()
        ) {
            $slug = $base . '-' . $n++;
        }

        return $slug;
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(BlogCategory::class, 'blog_category_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(BlogTag::class, 'blog_post_tag');
    }

    public function faqs(): HasMany
    {
        return $this->hasMany(Faq::class, 'blog_post_id')
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    /**
     * Live posts only: published status and a publish date that has arrived,
     * so scheduling works without a cron job.
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function scopeVisibleTo(Builder $query, ?User $user): Builder
    {
        // Authors only ever see their own drafts.
        if ($user && !$user->role->isAdmin()) {
            return $query->where('author_id', $user->id);
        }

        return $query;
    }

    public function getUrlAttribute(): string
    {
        return url('/blog/' . $this->slug);
    }

    public function isLive(): bool
    {
        return $this->status === 'published'
            && $this->published_at !== null
            && $this->published_at->lte(now());
    }
}
