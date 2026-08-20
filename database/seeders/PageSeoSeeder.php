<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\SeoMeta;
use Illuminate\Database\Seeder;

/**
 * Creates a row per public route from the config fallbacks, so the dashboard
 * opens with something to edit rather than an empty list.
 *
 * Idempotent: existing rows are left untouched, never overwritten with defaults.
 */
class PageSeoSeeder extends Seeder
{
    public function run(): void
    {
        foreach (config('seo.pages') as $pageKey => $page) {
            // noindex utility routes are handled by config alone.
            if ($page['hidden'] ?? false) {
                continue;
            }

            SeoMeta::firstOrCreate(
                ['page_key' => $pageKey],
                [
                    'page_name' => $page['name'],
                    'meta_title' => $page['title'],
                    'meta_description' => $page['description'],
                    'meta_keywords' => $page['keywords'] ?? config('seo.defaults.keywords'),
                    'robots' => $page['robots'] ?? config('seo.defaults.robots'),
                    'og_type' => config('seo.defaults.og_type'),
                    'twitter_card' => config('seo.defaults.twitter_card'),
                ]
            );
        }
    }
}
