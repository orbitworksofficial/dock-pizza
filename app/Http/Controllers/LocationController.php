<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Store;
use App\Services\GeocodingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LocationController extends Controller
{
    public function __construct(private readonly GeocodingService $geocoder)
    {
    }

    /**
     * Set selected store and order type (delivery/pickup) in session.
     */
    public function selectStore(Request $request): RedirectResponse|JsonResponse
    {
        $request->validate([
            'order_type' => ['required', 'in:delivery,pickup'],
            'store_id' => ['required_if:order_type,pickup', 'exists:stores,id'],
            'zip_code' => ['required_if:order_type,delivery', 'string', 'max:10'],
            'address' => ['nullable', 'string', 'max:255'],
        ]);

        $orderType = $request->input('order_type');

        if ($orderType === 'pickup') {
            $store = Store::findOrFail($request->input('store_id'));
            
            session([
                'order_location' => [
                    'type' => 'pickup',
                    'store_id' => $store->id,
                    'store_name' => $store->name,
                    'store_slug' => $store->slug,
                    'address' => $store->address,
                ]
            ]);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Store selected for pickup!',
                    'redirect' => route('menu.index')
                ]);
            }

            return redirect()->route('menu.index')->with('success', 'Ordering pickup from ' . $store->name);
        } else {
            // Delivery flow: the ZIP only sharpens the geocoding lookup —
            // eligibility is decided purely by distance from a store.
            $zipRaw = trim((string) $request->input('zip_code'));
            $zipDigits = preg_replace('/\D+/', '', $zipRaw) ?? '';
            $zip = substr($zipDigits, 0, 5);

            if (strlen($zip) < 5) {
                if ($request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Please enter a valid 5-digit US ZIP code.',
                    ], 422);
                }

                return back()->withInput()->withErrors([
                    'zip_code' => 'Please enter a valid 5-digit US ZIP code.',
                ]);
            }

            $address = trim((string) $request->input('address', ''));

            if ($address === '') {
                return $this->deliveryUnavailable($request, 'Please enter your street address so we can check if you are in range.');
            }

            // Distance is the only thing that decides delivery. Geocoding runs
            // server-side so client-supplied coordinates cannot be spoofed to
            // force an out-of-range order through.
            $coordinates = $this->geocoder->geocode($address, $zip);

            if (!$coordinates) {
                Log::warning('Delivery address could not be geocoded', [
                    'address' => $address,
                    'zip' => $zip,
                ]);

                return $this->deliveryUnavailable(
                    $request,
                    'We could not locate that address. Please check the street and ZIP, or order for pickup.'
                );
            }

            $match = Store::findDeliveringTo($coordinates['latitude'], $coordinates['longitude']);

            if (!$match) {
                $nearest = $this->nearestDeliveryStore($coordinates);
                $message = $nearest
                    ? sprintf(
                        'Sorry, that address is about %s miles from our %s location, which delivers up to %s miles. Try ordering for pickup!',
                        number_format($nearest['distance'], 1),
                        $nearest['store']->name,
                        rtrim(rtrim(number_format((float) $nearest['store']->delivery_radius, 1), '0'), '.')
                    )
                    : 'Sorry, that address is outside our delivery area. Try ordering for pickup!';

                return $this->deliveryUnavailable($request, $message);
            }

            $store = $match['store'];
            $distance = round($match['distance'], 2);

            session([
                'order_location' => [
                    'type' => 'delivery',
                    'store_id' => $store->id,
                    'store_name' => $store->name,
                    'store_slug' => $store->slug,
                    'zip_code' => $zip,
                    'address' => $address,
                    'latitude' => $coordinates['latitude'] ?? null,
                    'longitude' => $coordinates['longitude'] ?? null,
                    'distance_miles' => $distance,
                ]
            ]);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Delivery location set!',
                    'redirect' => route('menu.index')
                ]);
            }

            $confirmation = $distance !== null
                ? sprintf('Delivery set — %s is %s miles away.', $store->name, number_format($distance, 1))
                : 'Delivery set to ' . $zip . ' from ' . $store->name;

            return redirect()->route('menu.index')->with('success', $confirmation);
        }
    }

    /**
     * Nearest delivery-capable store to a point, ignoring its radius.
     * Used only to explain how far out of range an address is.
     *
     * @param  array{latitude: float, longitude: float}  $coordinates
     * @return array{store: Store, distance: float}|null
     */
    private function nearestDeliveryStore(array $coordinates): ?array
    {
        $nearest = null;

        $stores = Store::where('is_active', true)
            ->where('accepts_delivery', true)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get();

        foreach ($stores as $store) {
            $distance = $store->distanceTo($coordinates['latitude'], $coordinates['longitude']);

            if ($distance !== null && ($nearest === null || $distance < $nearest['distance'])) {
                $nearest = ['store' => $store, 'distance' => $distance];
            }
        }

        return $nearest;
    }

    /**
     * Consistent rejection response for an out-of-range delivery address.
     */
    private function deliveryUnavailable(Request $request, string $message): RedirectResponse|JsonResponse
    {
        if ($request->wantsJson()) {
            return response()->json([
                'success' => false,
                'message' => $message,
            ], 422);
        }

        return back()->withInput()->withErrors(['zip_code' => $message]);
    }

    /**
     * Clear selected store/location from session.
     */
    public function clearStore(): RedirectResponse
    {
        session()->forget('order_location');
        return redirect()->route('home')->with('success', 'Location reset. Please choose your ordering method.');
    }
}
