<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Delivery eligibility moved from ZIP-code zones to a distance radius
     * measured from each store, so bump existing stores to the 7-mile
     * service area and make that the default for new locations.
     */
    public function up(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->decimal('delivery_radius', 5, 2)->default(7.00)->change();
        });

        DB::table('stores')->update(['delivery_radius' => 7.00]);

        // The seeded coordinates for both locations pointed at Baltimore
        // (~34 miles off for Shady Side). Harmless while delivery was gated by
        // ZIP, but fatal now that distance decides eligibility.
        $corrections = [
            'dock-pizza-shady-side' => ['latitude' => 38.8411850, 'longitude' => -76.5100040],
            'dock-pizza-annapolis' => ['latitude' => 38.9775941, 'longitude' => -76.4858178],
        ];

        foreach ($corrections as $slug => $coordinates) {
            DB::table('stores')->where('slug', $slug)->update($coordinates);
        }
    }

    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->decimal('delivery_radius', 5, 2)->default(5.00)->change();
        });

        DB::table('stores')->update(['delivery_radius' => 5.00]);
    }
};
