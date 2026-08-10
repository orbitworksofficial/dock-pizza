<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stores', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('address');
            $table->string('city');
            $table->string('state', 50);
            $table->string('zip_code', 20)->index();
            $table->string('country', 2)->default('US');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('email')->nullable();
            $table->string('image')->nullable();
            $table->decimal('delivery_radius', 5, 2)->default(5.00);
            $table->decimal('minimum_order', 8, 2)->default(10.00);
            $table->decimal('delivery_fee', 8, 2)->default(3.99);
            $table->decimal('tax_rate', 5, 4)->default(0.0600);
            $table->integer('estimated_delivery_time')->default(45);
            $table->integer('estimated_pickup_time')->default(20);
            $table->boolean('is_active')->default(true);
            $table->boolean('accepts_delivery')->default(true);
            $table->boolean('accepts_pickup')->default(true);
            $table->boolean('accepts_catering')->default(false);
            $table->json('features')->nullable();
            $table->integer('sort_order')->default(0);
            $table->softDeletes();
            $table->timestamps();

            $table->index(['latitude', 'longitude']);
            $table->index(['is_active', 'accepts_delivery']);
        });

        Schema::create('store_hours', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->tinyInteger('day_of_week');
            $table->time('open_time');
            $table->time('close_time');
            $table->boolean('is_closed')->default(false);
            $table->timestamps();

            $table->unique(['store_id', 'day_of_week']);
        });

        Schema::create('delivery_zones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->string('zone_name');
            $table->string('zip_code', 20)->index();
            $table->decimal('delivery_fee', 8, 2)->default(0.00);
            $table->integer('estimated_time')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['store_id', 'zip_code']);
        });

        Schema::create('store_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->string('key');
            $table->text('value')->nullable();
            $table->timestamps();

            $table->unique(['store_id', 'key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('store_settings');
        Schema::dropIfExists('delivery_zones');
        Schema::dropIfExists('store_hours');
        Schema::dropIfExists('stores');
    }
};
