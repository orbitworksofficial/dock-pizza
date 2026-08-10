<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('franchise_inquiries', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email');
            $table->string('phone', 20);
            $table->string('city')->nullable();
            $table->string('state', 50)->nullable();
            $table->string('zip_code', 20)->nullable();
            $table->decimal('investment_budget', 12, 2)->nullable();
            $table->string('experience_level')->nullable();
            $table->text('message')->nullable();
            $table->string('status')->default('new');
            $table->text('admin_notes')->nullable();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('contacted_at')->nullable();
            $table->dateTime('followed_up_at')->nullable();
            $table->timestamps();

            $table->index('status');
        });

        Schema::create('catering_packages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->decimal('starting_price', 10, 2);
            $table->integer('min_people')->nullable();
            $table->integer('max_people')->nullable();
            $table->string('image')->nullable();
            $table->json('includes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('catering_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('catering_package_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('email');
            $table->string('phone', 20);
            $table->string('company')->nullable();
            $table->date('event_date');
            $table->time('event_time')->nullable();
            $table->integer('guest_count');
            $table->string('event_type')->nullable();
            $table->text('delivery_address')->nullable();
            $table->text('special_requests')->nullable();
            $table->decimal('estimated_budget', 10, 2)->nullable();
            $table->string('status')->default('pending');
            $table->text('admin_notes')->nullable();
            $table->decimal('quoted_amount', 10, 2)->nullable();
            $table->dateTime('responded_at')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('event_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catering_requests');
        Schema::dropIfExists('catering_packages');
        Schema::dropIfExists('franchise_inquiries');
    }
};
