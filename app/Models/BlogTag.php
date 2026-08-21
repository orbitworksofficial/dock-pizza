<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class BlogTag extends Model
{
    protected $fillable = ['name', 'slug'];

    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(BlogPost::class, 'blog_post_tag');
    }

    /**
     * Find or create by name, so tags can be added inline while writing.
     */
    public static function fromName(string $name): self
    {
        $name = trim($name);

        return static::firstOrCreate(
            ['slug' => Str::slug($name)],
            ['name' => $name]
        );
    }
}
