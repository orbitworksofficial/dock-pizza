<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\DeliveryZone;
use App\Models\Store;
use App\Models\StoreHour;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class StoreSeeder extends Seeder
{
    public function run(): void
    {
        $stores = [
            [
                'name' => 'Dock Pizza — Shady Side',
                'slug' => 'dock-pizza-shady-side',
                'description' => 'Our flagship dock-side location serving Shady Side with fresh, hot pizzas fresh off the dock.',
                'address' => '1484 Snug Harbor Road',
                'city' => 'Shady Side',
                'state' => 'MD',
                'zip_code' => '20764',
                'country' => 'US',
                'latitude' => 39.327376,
                'longitude' => -76.616333,
                'phone' => '443-203-6404',
                'email' => 'info@dockpizza.com',
                'delivery_radius' => 5.00,
                'minimum_order' => 12.00,
                'delivery_fee' => 3.99,
                'tax_rate' => 0.0600,
                'estimated_delivery_time' => 35,
                'estimated_pickup_time' => 15,
                'is_active' => true,
                'accepts_delivery' => true,
                'accepts_pickup' => true,
                'accepts_catering' => true,
                'features' => ['Wifi', 'Outdoor Seating', 'Wheelchair Accessible'],
            ],
            [
                'name' => 'Dock Pizza — Annapolis',
                'slug' => 'dock-pizza-annapolis',
                'description' => 'Located near the harbor, perfect for a quick lunch slice or late-night delivery.',
                'address' => '100 Dock St',
                'city' => 'Annapolis',
                'state' => 'MD',
                'zip_code' => '21401',
                'country' => 'US',
                'latitude' => 39.287236,
                'longitude' => -76.612845,
                'phone' => '410-555-0188',
                'email' => 'annapolis@dockpizza.com',
                'delivery_radius' => 3.00,
                'minimum_order' => 10.00,
                'delivery_fee' => 4.99,
                'tax_rate' => 0.0600,
                'estimated_delivery_time' => 40,
                'estimated_pickup_time' => 20,
                'is_active' => false,
                'accepts_delivery' => true,
                'accepts_pickup' => true,
                'accepts_catering' => false,
                'features' => ['Late Night Delivery', 'Counter Service'],
            ],
        ];

        foreach ($stores as $storeData) {
            $store = Store::create($storeData);

            // Printed hours: Sun–Thu 10:30am–10pm | Fri–Sat 10:30am–12am
            for ($day = 0; $day <= 6; $day++) {
                $isLateNight = ($day === 5 || $day === 6); // Friday & Saturday
                StoreHour::create([
                    'store_id' => $store->id,
                    'day_of_week' => $day,
                    'open_time' => '10:30:00',
                    'close_time' => $isLateNight ? '00:00:00' : '22:00:00',
                    'is_closed' => false,
                ]);
            }

            // Add delivery zones (Zip codes)
            $zips = $storeData['zip_code'] === '20764'
                ? ['20764' => 3.99, '20765' => 4.99, '20776' => 5.99]
                : ['21401' => 4.99, '21402' => 4.99, '21403' => 5.99];

            foreach ($zips as $zip => $fee) {
                DeliveryZone::create([
                    'store_id' => $store->id,
                    'zone_name' => "Zone {$zip}",
                    'zip_code' => $zip,
                    'delivery_fee' => $fee,
                    'estimated_time' => $store->estimated_delivery_time,
                    'is_active' => true,
                ]);
            }
        }
    }
}
