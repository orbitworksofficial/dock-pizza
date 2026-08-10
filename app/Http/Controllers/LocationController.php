<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\DeliveryZone;
use App\Models\Store;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LocationController extends Controller
{
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
            // Delivery flow: Find store serving this zip code
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

            $zone = DeliveryZone::where('zip_code', $zip)
                ->where('is_active', true)
                ->first();

            if (!$zone) {
                if ($request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Sorry, we do not deliver to ZIP code ' . $zip . ' yet. Try ordering for pickup!'
                    ], 422);
                }

                return back()->withInput()->withErrors([
                    'zip_code' => 'Sorry, we do not deliver to ZIP code ' . $zip . ' yet. Try ordering for pickup!'
                ]);
            }

            $store = $zone->store;

            session([
                'order_location' => [
                    'type' => 'delivery',
                    'store_id' => $store->id,
                    'store_name' => $store->name,
                    'store_slug' => $store->slug,
                    'zip_code' => $zip,
                    'address' => $request->input('address', ''),
                ]
            ]);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Delivery location set!',
                    'redirect' => route('menu.index')
                ]);
            }

            return redirect()->route('menu.index')->with('success', 'Delivery set to ' . $zip . ' from ' . $store->name);
        }
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
