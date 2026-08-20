<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Widens the existing polymorphic seo_meta table so one row can describe
 * either a route (page_key = '/menu') or a model (seoable_type/seoable_id),
 * rather than standing up a second, competing SEO table.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('seo_meta', function (Blueprint $table) {
            // Route-addressed rows have no owning model, so the morph pair
            // must become optional. Laravel's morphs() made them NOT NULL.
            $table->string('seoable_type')->nullable()->change();
            $table->unsignedBigInteger('seoable_id')->nullable()->change();

            $table->string('page_key')->nullable()->unique()->after('id');
            $table->string('page_name')->nullable()->after('page_key');

            $table->string('og_type')->nullable()->after('og_image');
            $table->string('twitter_title')->nullable()->after('og_type');
            $table->text('twitter_description')->nullable()->after('twitter_title');
            $table->string('twitter_image')->nullable()->after('twitter_description');
            $table->string('twitter_card')->nullable()->after('twitter_image');

            $table->longText('faq_schema')->nullable()->after('schema_markup');
        });

        // schema_markup was cast to JSON, which rejects the hand-written
        // JSON-LD editors paste. LONGTEXT lets us store it verbatim and
        // validate in PHP, where we can report a useful error.
        DB::statement('ALTER TABLE `seo_meta` MODIFY `schema_markup` LONGTEXT NULL');

        // Per-page FAQs reuse the existing faqs table rather than a JSON
        // column, so they stay queryable and sortable.
        Schema::table('faqs', function (Blueprint $table) {
            $table->foreignId('seo_meta_id')->nullable()->after('id')
                ->constrained('seo_meta')->cascadeOnDelete();
            $table->index(['seo_meta_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::table('faqs', function (Blueprint $table) {
            $table->dropForeign(['seo_meta_id']);
            $table->dropIndex(['seo_meta_id', 'sort_order']);
            $table->dropColumn('seo_meta_id');
        });

        Schema::table('seo_meta', function (Blueprint $table) {
            $table->dropColumn([
                'page_key', 'page_name', 'og_type',
                'twitter_title', 'twitter_description', 'twitter_image', 'twitter_card',
                'faq_schema',
            ]);
        });

        DB::statement('ALTER TABLE `seo_meta` MODIFY `schema_markup` JSON NULL');
    }
};
