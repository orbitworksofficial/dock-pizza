<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SquareService implements PaymentGatewayInterface
{
    protected string $accessToken;
    protected string $locationId;
    protected string $baseUrl;
    protected bool $isMock;

    public function __construct()
    {
        $this->accessToken = config('payment.gateways.square.access_token');
        $this->locationId  = config('payment.gateways.square.location_id');
        $environment       = config('payment.gateways.square.environment');

        $this->baseUrl = $environment === 'production'
            ? 'https://connect.squareup.com/v2'
            : 'https://connect.squareupsandbox.com/v2';

        // Only mock if the keys are truly the default placeholder values
        $this->isMock = ($this->accessToken === 'mock-square-access-token');
    }

    /**
     * Charge a payment source (nonce or card token).
     */
    public function charge(float $amount, string $sourceId, array $options = []): array
    {
        if ($this->isMock) {
            Log::info('SquareService: Mocking payment charge (no real keys configured)', ['amount' => $amount]);
            return [
                'success'        => true,
                'transaction_id' => 'sq_mock_' . Str::random(10),
                'raw_response'   => ['mock' => true, 'status' => 'COMPLETED'],
            ];
        }

        try {
            $payload = [
                'source_id'       => $sourceId,
                'idempotency_key' => (string) Str::uuid(),
                'amount_money'    => [
                    'amount'   => (int) round($amount * 100), // Square uses cents
                    'currency' => $options['currency'] ?? 'USD',
                ],
                'location_id'  => $this->locationId,
                'reference_id' => $options['reference_id'] ?? null,
                'autocomplete' => true,
            ];

            if (!empty($options['order_id'])) {
                $payload['order_id'] = $options['order_id'];
            }

            Log::info('Square: Sending payment request', ['url' => "{$this->baseUrl}/payments", 'amount_cents' => $payload['amount_money']['amount'], 'order_id' => $payload['order_id'] ?? null]);

            $response = Http::withHeaders([
                    'Square-Version' => '2024-01-18',
                    'Authorization'  => 'Bearer ' . $this->accessToken,
                    'Content-Type'   => 'application/json',
                ])
                ->post("{$this->baseUrl}/payments", $payload);

            $data = $response->json();

            if ($response->successful() && isset($data['payment'])) {
                Log::info('Square: Payment successful', ['payment_id' => $data['payment']['id']]);
                return [
                    'success'        => true,
                    'transaction_id' => $data['payment']['id'],
                    'raw_response'   => $data,
                ];
            }

            Log::error('Square: Payment failed', ['status' => $response->status(), 'response' => $data]);
            return [
                'success'        => false,
                'transaction_id' => null,
                'raw_response'   => $data,
            ];
        } catch (\Exception $e) {
            Log::error('Square: Payment exception — ' . $e->getMessage());
            return [
                'success'        => false,
                'transaction_id' => null,
                'raw_response'   => ['error' => $e->getMessage()],
            ];
        }
    }

    /**
     * Sync a completed order to the Square POS dashboard.
     */
    public function syncOrder(Order $order): ?string
    {
        if ($this->isMock) {
            Log::info('SquareService: Mocking order sync (no real keys configured)', ['order' => $order->order_number]);
            return 'sq_order_mock_' . Str::random(10);
        }

        try {
            // Load items if not loaded
            $order->loadMissing('items');

            $lineItems = [];
            foreach ($order->items as $item) {
                $lineItems[] = [
                    'name'             => $item->name . ' (' . $item->variation_name . ')',
                    'quantity'         => (string) $item->quantity,
                    'base_price_money' => [
                        'amount'   => (int) round($item->unit_price * 100),
                        'currency' => 'USD',
                    ],
                ];
            }

            if ($order->delivery_fee > 0) {
                $lineItems[] = [
                    'name'             => 'Delivery Fee',
                    'quantity'         => '1',
                    'base_price_money' => [
                        'amount'   => (int) round($order->delivery_fee * 100),
                        'currency' => 'USD',
                    ],
                ];
            }

            if ($order->tax_amount > 0) {
                $lineItems[] = [
                    'name'             => 'Estimated Tax',
                    'quantity'         => '1',
                    'base_price_money' => [
                        'amount'   => (int) round($order->tax_amount * 100),
                        'currency' => 'USD',
                    ],
                ];
            }

            $orderData = [
                'location_id'  => $this->locationId,
                'reference_id' => $order->order_number,
                'line_items'   => $lineItems,
                'state'        => 'OPEN',
            ];

            if ($order->discount_amount > 0) {
                $orderData['discounts'] = [
                    [
                        'name' => 'Order Discount',
                        'amount_money' => [
                            'amount' => (int) round($order->discount_amount * 100),
                            'currency' => 'USD',
                        ],
                        'scope' => 'ORDER'
                    ]
                ];
            }

            $payload = [
                'idempotency_key' => (string) Str::uuid(),
                'order'           => $orderData,
            ];

            Log::info('Square: Syncing order to POS', ['order_number' => $order->order_number, 'items_count' => count($lineItems)]);

            $response = Http::withHeaders([
                    'Square-Version' => '2024-01-18',
                    'Authorization'  => 'Bearer ' . $this->accessToken,
                    'Content-Type'   => 'application/json',
                ])
                ->post("{$this->baseUrl}/orders", $payload);

            $data = $response->json();

            if ($response->successful() && isset($data['order'])) {
                $squareOrderId = $data['order']['id'];
                Log::info('Square: Order synced to POS', ['square_order_id' => $squareOrderId]);
                return $squareOrderId;
            }

            Log::error('Square: Order sync failed', ['status' => $response->status(), 'response' => $data]);
            return null;
        } catch (\Exception $e) {
            Log::error('Square: Order sync exception — ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Return the gateway name for DB records.
     */
    public function getGatewayName(): string
    {
        return 'square';
    }
}
