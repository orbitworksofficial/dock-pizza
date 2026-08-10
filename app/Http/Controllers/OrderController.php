<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    /**
     * Order confirmation page — accessible by anyone with the order number.
     */
    public function confirmation(string $orderNumber): View
    {
        $order = Order::where('order_number', $orderNumber)
            ->with(['items.product', 'items.variation', 'items.toppings', 'store', 'payment'])
            ->firstOrFail();

        return view('orders.confirmation', compact('order'));
    }

    /**
     * Order tracking page with visual timeline.
     */
    public function track(string $orderNumber): View
    {
        $order = Order::where('order_number', $orderNumber)
            ->with(['items.product', 'items.toppings', 'store', 'statusHistory' => function ($q) {
                $q->orderBy('created_at', 'asc');
            }])
            ->firstOrFail();

        $trackingSteps = $this->buildTrackingSteps($order);

        return view('orders.track', compact('order', 'trackingSteps'));
    }

    /**
     * Order history for logged-in customers.
     */
    public function history(Request $request): View
    {
        $orders = Order::where('user_id', auth()->id())
            ->with(['items', 'store'])
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('orders.history', compact('orders'));
    }

    /**
     * Build a structured tracking steps array for the visual timeline.
     */
    private function buildTrackingSteps(Order $order): array
    {
        $isDelivery = $order->type->value === 'delivery';

        $allSteps = [
            ['key' => 'pending',          'label' => 'Order Placed',      'icon' => 'fa-receipt'],
            ['key' => 'confirmed',        'label' => 'Confirmed',         'icon' => 'fa-circle-check'],
            ['key' => 'preparing',        'label' => 'Preparing',         'icon' => 'fa-fire-burner'],
            ['key' => 'ready',            'label' => 'Ready',             'icon' => 'fa-bell-concierge'],
        ];

        if ($isDelivery) {
            $allSteps[] = ['key' => 'out_for_delivery', 'label' => 'Out for Delivery', 'icon' => 'fa-motorcycle'];
            $allSteps[] = ['key' => 'delivered',        'label' => 'Delivered',         'icon' => 'fa-house-circle-check'];
        } else {
            $allSteps[] = ['key' => 'picked_up', 'label' => 'Picked Up', 'icon' => 'fa-bag-shopping'];
        }

        $currentStatus = $order->status->value;
        $statusOrder = array_column($allSteps, 'key');
        $currentIndex = array_search($currentStatus, $statusOrder);

        // Handle cancelled/refunded
        if ($currentStatus === 'cancelled' || $currentStatus === 'refunded') {
            $currentIndex = -1; // No step is active
        }

        foreach ($allSteps as $i => &$step) {
            if ($currentIndex === false || $currentIndex === -1) {
                $step['status'] = 'upcoming';
            } elseif ($i < $currentIndex) {
                $step['status'] = 'completed';
            } elseif ($i === $currentIndex) {
                $step['status'] = 'current';
            } else {
                $step['status'] = 'upcoming';
            }

            // Get timestamp from order record
            $timestampField = match ($step['key']) {
                'pending'          => 'created_at',
                'confirmed'        => 'confirmed_at',
                'preparing'        => 'preparing_at',
                'ready'            => 'ready_at',
                'out_for_delivery' => 'ready_at',
                'delivered'        => 'delivered_at',
                'picked_up'        => 'delivered_at',
                default            => null,
            };

            $step['timestamp'] = $timestampField && $order->{$timestampField}
                ? $order->{$timestampField}->format('g:i A')
                : null;
        }

        return $allSteps;
    }
}
