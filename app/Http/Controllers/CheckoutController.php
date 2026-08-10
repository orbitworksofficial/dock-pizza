<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Enums\PaymentStatus;
use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemTopping;
use App\Models\OrderStatusHistory;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\Store;
use App\Models\Topping;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function showCheckout(): View
    {
        $stores = Store::query()
            ->where('is_active', true)
            ->orderByRaw("CASE WHEN slug = 'dock-pizza-shady-side' THEN 0 ELSE 1 END")
            ->orderBy('name')
            ->get();

        $paymentGateway = config('payment.default', 'clover');
        $cloverPublicKey = '';
        $cloverIsMock = true;
        $cloverCanProcessCards = false;
        $cloverEnvironment = 'sandbox';

        if ($paymentGateway === 'clover') {
            $gateway = app(\App\Services\PaymentGatewayInterface::class);
            if ($gateway instanceof \App\Services\CloverService) {
                $cloverPublicKey = $gateway->getPublicKey();
                $cloverIsMock = $gateway->isMock();
                $cloverCanProcessCards = $gateway->canProcessCards();
                $cloverEnvironment = $gateway->getEnvironment();
            }
        }

        return view('checkout', compact(
            'stores',
            'paymentGateway',
            'cloverPublicKey',
            'cloverIsMock',
            'cloverCanProcessCards',
            'cloverEnvironment'
        ));
    }

    public function placeOrder(Request $request): JsonResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'order_type' => ['required', 'in:delivery,pickup'],
            'payment_method' => ['required', 'in:cod,card'],
            'payment_token' => ['required_if:payment_method,card', 'nullable', 'string'],
            'cart' => ['required', 'array', 'min:1'],
            'cart.*.product.id' => ['required', 'integer', 'exists:products,id'],
            'cart.*.variation.id' => ['required', 'integer', 'exists:product_variations,id'],
            'cart.*.quantity' => ['required', 'integer', 'min:1'],
            'cart.*.toppings' => ['nullable', 'array'],
            // Delivery fields
            'address' => ['required_if:order_type,delivery', 'nullable', 'string', 'max:500'],
            'city' => ['required_if:order_type,delivery', 'nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:50'],
            'zip_code' => ['required_if:order_type,delivery', 'nullable', 'string', 'max:20'],
            'delivery_instructions' => ['nullable', 'string', 'max:1000'],
            // Pickup fields
            'store_id' => ['required_if:order_type,pickup', 'nullable', 'exists:stores,id'],
            // Optional
            'coupon_code' => ['nullable', 'string', 'max:50'],
        ]);

        try {
            $paymentGateway = app(\App\Services\PaymentGatewayInterface::class);

            return DB::transaction(function () use ($request, $paymentGateway) {
                // 1. Determine the store
                $store = $this->resolveStore($request);

                if (!$store) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Could not determine the store for your order. Please select a store or enter a valid delivery address.',
                    ], 422);
                }

                // 2. Build order items and calculate totals server-side
                $cartItems = $request->input('cart');
                $orderItemsData = [];
                $subtotal = 0;

                foreach ($cartItems as $cartItem) {
                    $product = Product::find($cartItem['product']['id']);
                    $variation = ProductVariation::find($cartItem['variation']['id']);

                    if (!$product || !$variation) {
                        return response()->json([
                            'success' => false,
                            'message' => 'One or more items in your cart are no longer available.',
                        ], 422);
                    }

                    $quantity = (int) $cartItem['quantity'];
                    $unitPrice = (float) $variation->price;

                    // Calculate topping prices
                    $toppingsData = [];
                    $toppingsTotal = 0;

                    if (!empty($cartItem['toppings'])) {
                        foreach ($cartItem['toppings'] as $toppingId) {
                            $topping = Topping::find($toppingId);
                            if ($topping) {
                                $toppingPrice = (float) $topping->price;
                                $toppingsTotal += $toppingPrice;
                                $toppingsData[] = [
                                    'topping_id' => $topping->id,
                                    'name' => $topping->name,
                                    'price' => $toppingPrice,
                                    'portion' => 'whole',
                                ];
                            }
                        }
                    }

                    $itemUnitPrice = $unitPrice + $toppingsTotal;
                    $itemTotalPrice = $itemUnitPrice * $quantity;
                    $subtotal += $itemTotalPrice;

                    $orderItemsData[] = [
                        'product' => $product,
                        'variation' => $variation,
                        'quantity' => $quantity,
                        'unit_price' => $itemUnitPrice,
                        'total_price' => $itemTotalPrice,
                        'toppings_data' => $toppingsData,
                        'special_instructions' => $cartItem['special_instructions'] ?? null,
                    ];
                }

                // 3. Calculate tax
                $taxRate = (float) $store->tax_rate;
                $taxAmount = round($subtotal * $taxRate, 2);

                // 4. Calculate delivery fee
                $deliveryFee = 0;
                $orderType = $request->input('order_type');

                if ($orderType === 'delivery') {
                    $deliveryFee = (float) $store->delivery_fee;
                }

                // 5. Handle coupon discount
                $coupon = null;
                $discountAmount = 0;

                if ($request->filled('coupon_code')) {
                    $coupon = Coupon::where('code', $request->input('coupon_code'))
                        ->where('is_active', true)
                        ->where(function ($q) {
                            $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
                        })
                        ->where(function ($q) {
                            $q->whereNull('expires_at')->orWhere('expires_at', '>=', now());
                        })
                        ->first();

                    if ($coupon) {
                        if ($subtotal >= (float) $coupon->minimum_order) {
                            if ($coupon->type === 'percentage') {
                                $discountAmount = round($subtotal * ($coupon->value / 100), 2);
                                if ($coupon->maximum_discount) {
                                    $discountAmount = min($discountAmount, (float) $coupon->maximum_discount);
                                }
                            } else {
                                $discountAmount = min((float) $coupon->value, $subtotal);
                            }
                        }
                    }
                }

                // 6. Calculate grand total
                $total = round($subtotal + $taxAmount + $deliveryFee - $discountAmount, 2);

                // 7. Generate unique order number
                $orderNumber = $this->generateOrderNumber();

                // 8. Create the Order
                $order = Order::create([
                    'order_number' => $orderNumber,
                    'user_id' => auth()->id(),
                    'store_id' => $store->id,
                    'coupon_id' => $coupon?->id,
                    'type' => $orderType,
                    'status' => OrderStatus::PENDING->value,
                    'payment_status' => PaymentStatus::PENDING->value, // Will update to completed if card succeeds
                    'payment_method' => $request->input('payment_method'),
                    'subtotal' => $subtotal,
                    'tax_amount' => $taxAmount,
                    'delivery_fee' => $deliveryFee,
                    'discount_amount' => $discountAmount,
                    'tip_amount' => 0,
                    'total' => $total,
                    'customer_name' => $request->input('name'),
                    'customer_email' => $request->input('email'),
                    'customer_phone' => $request->input('phone'),
                    'delivery_address' => $request->input('address'),
                    'delivery_city' => $request->input('city'),
                    'delivery_state' => $request->input('state'),
                    'delivery_zip' => $request->input('zip_code'),
                    'delivery_instructions' => $request->input('delivery_instructions'),
                    'estimated_minutes' => $orderType === 'delivery'
                        ? $store->estimated_delivery_time
                        : $store->estimated_pickup_time,
                ]);

                // 9. Create Order Items and Toppings
                foreach ($orderItemsData as $itemData) {
                    $orderItem = OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $itemData['product']->id,
                        'product_variation_id' => $itemData['variation']->id,
                        'name' => $itemData['product']->name,
                        'variation_name' => $itemData['variation']->name,
                        'quantity' => $itemData['quantity'],
                        'unit_price' => $itemData['unit_price'],
                        'total_price' => $itemData['total_price'],
                        'special_instructions' => $itemData['special_instructions'],
                    ]);

                    // Create toppings for this item
                    foreach ($itemData['toppings_data'] as $toppingData) {
                        OrderItemTopping::create([
                            'order_item_id' => $orderItem->id,
                            'topping_id' => $toppingData['topping_id'],
                            'name' => $toppingData['name'],
                            'price' => $toppingData['price'],
                            'portion' => $toppingData['portion'],
                        ]);
                    }

                    // Update product orders_count
                    $itemData['product']->increment('orders_count');
                }

                // 10. Handle coupon usage tracking
                if ($coupon && $discountAmount > 0) {
                    CouponUsage::create([
                        'coupon_id' => $coupon->id,
                        'user_id' => auth()->id(),
                        'order_id' => $order->id,
                        'discount_amount' => $discountAmount,
                    ]);

                    $coupon->increment('used_count');
                }

                // 11. Sync to POS System FIRST (so we have a Square Order ID)
                try {
                    $posOrderId = $paymentGateway->syncOrder($order);
                    if ($posOrderId) {
                        $order->update(['pos_order_id' => $posOrderId]);
                    }
                } catch (\Exception $e) {
                    Log::error('POS Sync failed', ['order' => $order->id, 'error' => $e->getMessage()]);
                    // Don't fail the transaction if POS sync fails, we can retry later or it's non-fatal to the customer.
                }

                // 12. Process Payment if card
                $paymentResponse = null;
                if ($request->input('payment_method') === 'card') {
                    if ($paymentGateway instanceof \App\Services\CloverService
                        && ! $paymentGateway->canProcessCards()) {
                        DB::rollBack();

                        return response()->json([
                            'success' => false,
                            'message' => 'Online card payments are not fully configured for Clover yet. Please pay cash on delivery or contact the restaurant.',
                        ], 422);
                    }

                    $paymentToken = $request->input('payment_token');
                    $paymentOptions = [
                        'currency' => 'USD',
                        'reference_id' => $order->order_number,
                    ];
                    // Link charge to Clover POS order when available
                    if ($order->pos_order_id) {
                        $paymentOptions['order_id'] = $order->pos_order_id;
                    }

                    $paymentResponse = $paymentGateway->charge($total, $paymentToken, $paymentOptions);

                    if (!$paymentResponse['success']) {
                        DB::rollBack();
                        $failMessage = $paymentResponse['raw_response']['error']
                            ?? $paymentResponse['raw_response']['message']
                            ?? 'Unknown error';

                        return response()->json([
                            'success' => false,
                            'message' => 'Payment failed: ' . (is_string($failMessage) ? $failMessage : 'Unknown error'),
                        ], 422);
                    }

                    // Payment succeeded, update order
                    $order->update(['payment_status' => PaymentStatus::COMPLETED->value]);
                }

                // 13. Create Payment record
                Payment::create([
                    'order_id' => $order->id,
                    'payment_method' => $request->input('payment_method') === 'cod' ? 'cash_on_delivery' : 'credit_card',
                    'gateway' => $request->input('payment_method') === 'card' ? $paymentGateway->getGatewayName() : null,
                    'transaction_id' => $paymentResponse['transaction_id'] ?? null,
                    'amount' => $total,
                    'currency' => 'USD',
                    'status' => $request->input('payment_method') === 'card'
                        ? PaymentStatus::COMPLETED->value
                        : PaymentStatus::PENDING->value,
                    'gateway_response' => $paymentResponse['raw_response'] ?? null,
                ]);

                // 14. Create Order Status History
                OrderStatusHistory::create([
                    'order_id' => $order->id,
                    'status' => OrderStatus::PENDING->value,
                    'changed_by_type' => auth()->check() ? 'App\\Models\\User' : null,
                    'changed_by_id' => auth()->id(),
                    'notes' => 'Order placed by customer.',
                ]);

                // 16. Clear session order location
                session()->forget('order_location');

                return response()->json([
                    'success' => true,
                    'message' => 'Thank you! Your order has been placed successfully!',
                    'order_number' => $orderNumber,
                    'order_id' => $order->id,
                    'redirect' => route('order.confirmation', $orderNumber),
                ]);
            });
        } catch (\Exception $e) {
            Log::error('Order placement failed', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while placing your order. Please try again.',
            ], 500);
        }
    }

    /**
     * Resolve the store for this order from request or session.
     */
    private function resolveStore(Request $request): ?Store
    {
        $orderType = $request->input('order_type');

        if ($orderType === 'pickup' && $request->filled('store_id')) {
            return Store::where('is_active', true)->find($request->input('store_id'));
        }

        // For delivery, check session first
        $orderLocation = session('order_location');
        if ($orderLocation && isset($orderLocation['store_id'])) {
            return Store::where('is_active', true)->find($orderLocation['store_id']);
        }

        // Fallback: find any active store
        return Store::where('is_active', true)->first();
    }

    /**
     * Generate a unique, human-readable order number.
     */
    private function generateOrderNumber(): string
    {
        $date = now()->format('ymd');
        $lastOrder = Order::whereDate('created_at', today())
            ->orderByDesc('id')
            ->first();

        $sequence = $lastOrder ? ((int) substr($lastOrder->order_number, -4)) + 1 : 1;

        return 'DOCKPIZZA-' . $date . '-' . str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }
}
