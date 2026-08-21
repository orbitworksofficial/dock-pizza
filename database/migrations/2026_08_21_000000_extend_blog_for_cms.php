<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Brings the existing blog tables up to what the CMS needs: a three-state
 * status, real many-to-many tags, and the per-post SEO fields.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            // draft / published / archived replaces the is_published boolean,
            // which cannot express "archived".
            $table->string('status', 20)->default('draft')->after('content');
            $table->string('featured_image_alt')->nullable()->after('featured_image');
            $table->boolean('is_featured')->default(false)->after('status');
            $table->boolean('allow_comments')->default(true)->after('is_featured');
            // Derived from word count on save, never edited by hand.
            $table->unsignedSmallInteger('reading_minutes')->default(1)->after('allow_comments');

            // Per-post SEO — same field set as page_seo.
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->string('seo_keywords')->nullable();
            $table->string('canonical_url')->nullable();
            $table->string('robots')->nullable();
            $table->string('og_title')->nullable();
            $table->text('og_description')->nullable();
            $table->string('og_image')->nullable();
            $table->string('og_type')->nullable();
            $table->string('twitter_title')->nullable();
            $table->text('twitter_description')->nullable();
            $table->string('twitter_image')->nullable();
            $table->string('twitter_card')->nullable();
            $table->longText('schema_markup')->nullable();
            $table->longText('faq_schema')->nullable();

            $table->index(['status', 'published_at']);
        });

        // Carry the old boolean across so nothing silently unpublishes.
        DB::table('blog_posts')->where('is_published', true)->update(['status' => 'published']);

        Schema::table('blog_categories', function (Blueprint $table) {
            $table->integer('sort_order')->default(0)->after('description');
            $table->boolean('is_active')->default(true)->after('sort_order');
        });

        Schema::create('blog_tags', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->timestamps();
        });

        Schema::create('blog_post_tag', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blog_post_id')->constrained()->cascadeOnDelete();
            $table->foreignId('blog_tag_id')->constrained()->cascadeOnDelete();
            $table->unique(['blog_post_id', 'blog_tag_id']);
        });

        // Per-post FAQs reuse the faqs table, exactly as page SEO does.
        Schema::table('faqs', function (Blueprint $table) {
            $table->foreignId('blog_post_id')->nullable()->after('seo_meta_id')
                ->constrained()->cascadeOnDelete();
            $table->index(['blog_post_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::table('faqs', function (Blueprint $table) {
            $table->dropForeign(['blog_post_id']);
            $table->dropIndex(['blog_post_id', 'sort_order']);
            $table->dropColumn('blog_post_id');
        });

        Schema::dropIfExists('blog_post_tag');
        Schema::dropIfExists('blog_tags');

        Schema::table('blog_categories', function (Blueprint $table) {
            $table->dropColumn(['sort_order', 'is_active']);
        });

        Schema::table('blog_posts', function (Blueprint $table) {
            $table->dropIndex(['status', 'published_at']);
            $table->dropColumn([
                'status', 'featured_image_alt', 'is_featured', 'allow_comments', 'reading_minutes',
                'seo_title', 'seo_description', 'seo_keywords', 'canonical_url', 'robots',
                'og_title', 'og_description', 'og_image', 'og_type',
                'twitter_title', 'twitter_description', 'twitter_image', 'twitter_card',
                'schema_markup', 'faq_schema',
            ]);
        });
    }
};
