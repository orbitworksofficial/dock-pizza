<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CloverService implements PaymentGatewayInterface
{
    protected string $accessToken;

    protected string $ecommercePrivateKey;

    protected string $merchantId;

    protected string $publicKey;

    protected string $environment;

    protected string $posBaseUrl;

    protected string $ecommerceBaseUrl;

    protected bool $isMock;

    public function __construct()
    {
        $this->accessToken = (string) config('payment.gateways.clover.access_token', '');
        $this->ecommercePrivateKey = (string) config('payment.gateways.clover.ecommerce_private_key', $this->accessToken);
        $this->merchantId = (string) config('payment.gateways.clover.merchant_id', '');
        $this->publicKey = (string) config('payment.gateways.clover.public_key', '');
        $this->environment = (string) config('payment.gateways.clover.environment', 'sandbox');

        $isProduction = $this->environment === 'production';

        // POS / Inventory REST API
        $this->posBaseUrl = $isProduction
            ? 'https://api.clover.com/v3'
            : 'https://sandbox.dev.clover.com/v3';

        // Ecommerce (charges / PAKMS) API
        $this->ecommerceBaseUrl = $isProduction
            ? 'https://scl.clover.com'
            : 'https://scl-sandbox.dev.clover.com';

        $this->isMock = $this->shouldUseMock();
    }

    public function isMock(): bool
    {
        return $this->isMock;
    }

    public function getPublicKey(): string
    {
        return $this->publicKey;
    }

    public function getMerchantId(): string
    {
        return $this->merchantId;
    }

    public function getEnvironment(): string
    {
        return $this->environment;
    }

    /**
     * True when the merchant token is live (orders can sync to Clover POS).
     */
    public function isLiveMerchant(): bool
    {
        return ! $this->isMock;
    }

    /**
     * Online card payments require a PAKMS public key for Clover.js tokenization.
     */
    public function canProcessCards(): bool
    {
        if ($this->isMock) {
            return true; // mock path accepts simulated card tokens
        }

        $key = trim($this->publicKey);

        return $key !== '' && ! str_starts_with($key, 'mock-');
    }

    /**
     * Charge a Clover card token (clv_...).
     */
    public function charge(float $amount, string $sourceId, array $options = []): array
    {
        if ($this->isMock) {
            Log::info('CloverService: Mocking payment charge (localhost / no real keys)', [
                'amount' => $amount,
                'source' => $sourceId,
            ]);

            return [
                'success' => true,
                'transaction_id' => 'clv_mock_' . Str::random(12),
                'raw_response' => [
                    'mock' => true,
                    'status' => 'succeeded',
                    'paid' => true,
                    'amount' => (int) round($amount * 100),
                    'source' => $sourceId,
                ],
            ];
        }

        // Guard against frontend mock tokens hitting a live gateway
        if (str_starts_with($sourceId, 'clv_mock_') || str_starts_with($sourceId, 'mock_')) {
            return [
                'success' => false,
                'transaction_id' => null,
                'raw_response' => [
                    'error' => 'Card form is not fully configured. Add CLOVER_PUBLIC_KEY (PAKMS apiAccessKey) to process real card payments.',
                ],
            ];
        }

        if (! $this->canProcessCards()) {
            return [
                'success' => false,
                'transaction_id' => null,
                'raw_response' => [
                    'error' => 'Clover ecommerce public key is missing. Set CLOVER_PUBLIC_KEY in .env after generating a PAKMS key in Clover.',
                ],
            ];
        }

        try {
            $amountCents = (int) round($amount * 100);

            $payload = [
                'amount' => $amountCents,
                'currency' => strtolower((string) ($options['currency'] ?? 'usd')),
                'source' => $sourceId,
                'capture' => true,
                'ecomind' => 'ecom',
            ];

            if (! empty($options['reference_id'])) {
                // Clover enforces max length 12 on external_reference_id.
                $ref = preg_replace('/[^A-Za-z0-9]/', '', (string) $options['reference_id']) ?: 'ORDER';
                $payload['external_reference_id'] = substr($ref, -12);
            }

            if (! str_starts_with($sourceId, 'clv_')) {
                return [
                    'success' => false,
                    'transaction_id' => null,
                    'raw_response' => [
                        'error' => 'Invalid card token from the payment form. Please re-enter the card and try again.',
                    ],
                ];
            }

            // When an ecommerce order id is available, prefer paying that order.
            $endpoint = "{$this->ecommerceBaseUrl}/v1/charges";
            if (! empty($options['ecommerce_order_id'])) {
                $endpoint = "{$this->ecommerceBaseUrl}/v1/orders/{$options['ecommerce_order_id']}/pay";
                unset($payload['amount'], $payload['currency']);
            }

            Log::info('Clover: Sending charge request', [
                'endpoint' => $endpoint,
                'amount_cents' => $amountCents,
                'reference' => $options['reference_id'] ?? null,
                'source_prefix' => substr($sourceId, 0, 12),
                'using_dedicated_ecom_key' => $this->ecommercePrivateKey !== $this->accessToken,
            ]);

            // Cardholder IP is required by Ecommerce; 127.0.0.1 is often unusable.
            $clientIp = request()->ip() ?? '127.0.0.1';
            if (in_array($clientIp, ['127.0.0.1', '::1'], true)
                || ! filter_var($clientIp, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                $clientIp = '203.0.113.10';
            }

            $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $this->ecommercePrivateKey,
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'Idempotency-Key' => (string) Str::uuid(),
                    'X-Forwarded-For' => $clientIp,
                ])
                ->timeout(30)
                ->post($endpoint, $payload);

            $data = $response->json() ?? [];
            if (! is_array($data)) {
                $data = ['message' => is_string($data) ? $data : 'Unexpected Clover response'];
            }

            $paid = (bool) ($data['paid'] ?? false);
            $statusOk = in_array(($data['status'] ?? ''), ['succeeded', 'paid'], true);
            $hasId = ! empty($data['id']);

            if ($response->successful() && ($paid || $statusOk || $hasId)) {
                $chargeId = $data['id'] ?? null;

                // Best-effort: record payment against the POS order so the terminal/kitchen sees it paid.
                if ($chargeId && ! empty($options['order_id'])) {
                    $this->recordPosPayment((string) $options['order_id'], $amountCents, (string) $chargeId);
                }

                Log::info('Clover: Payment successful', ['charge_id' => $chargeId]);

                return [
                    'success' => true,
                    'transaction_id' => $chargeId,
                    'raw_response' => $data,
                ];
            }

            $errorMessage = $this->extractCloverErrorMessage($data, $response->status());

            Log::error('Clover: Payment failed', [
                'status' => $response->status(),
                'response' => $data,
                'source_prefix' => substr($sourceId, 0, 12),
                'amount_cents' => $amountCents,
            ]);

            return [
                'success' => false,
                'transaction_id' => null,
                'raw_response' => array_merge($data, [
                    'error' => $errorMessage,
                ]),
            ];
        } catch (\Throwable $e) {
            Log::error('Clover: Payment exception — ' . $e->getMessage());

            return [
                'success' => false,
                'transaction_id' => null,
                'raw_response' => ['error' => $e->getMessage()],
            ];
        }
    }

    /**
     * Sync a completed online order into Clover POS (kitchen / merchant dashboard).
     */
    public function syncOrder(Order $order): ?string
    {
        if ($this->isMock) {
            Log::info('CloverService: Mocking order sync (localhost / no real keys)', [
                'order' => $order->order_number,
            ]);

            return 'clv_order_mock_' . Str::random(12);
        }

        try {
            $order->loadMissing(['items.toppings']);

            $lineItems = [];

            foreach ($order->items as $item) {
                $name = $item->name;
                if (!empty($item->variation_name)) {
                    $name .= ' (' . $item->variation_name . ')';
                }

                $noteParts = [];
                if ($item->relationLoaded('toppings') && $item->toppings->isNotEmpty()) {
                    $noteParts[] = 'Toppings: ' . $item->toppings->pluck('name')->implode(', ');
                }
                if (!empty($item->special_instructions)) {
                    $noteParts[] = $item->special_instructions;
                }

                $qty = max(1, (int) $item->quantity);
                for ($i = 0; $i < $qty; $i++) {
                    $line = [
                        'name' => $name,
                        'price' => (int) round(((float) $item->unit_price) * 100),
                        'unitQty' => 1,
                    ];
                    if ($noteParts !== []) {
                        $line['note'] = implode(' | ', $noteParts);
                    }
                    $lineItems[] = $line;
                }
            }

            if ((float) $order->delivery_fee > 0) {
                $lineItems[] = [
                    'name' => 'Delivery Fee',
                    'price' => (int) round(((float) $order->delivery_fee) * 100),
                    'unitQty' => 1,
                ];
            }

            if ((float) $order->tax_amount > 0) {
                $lineItems[] = [
                    'name' => 'Estimated Tax',
                    'price' => (int) round(((float) $order->tax_amount) * 100),
                    'unitQty' => 1,
                ];
            }

            if ((float) $order->discount_amount > 0) {
                $lineItems[] = [
                    'name' => 'Order Discount',
                    'price' => -1 * (int) round(((float) $order->discount_amount) * 100),
                    'unitQty' => 1,
                ];
            }

            $note = trim(implode(' | ', array_filter([
                'Online order ' . $order->order_number,
                $order->type?->value ?? (string) $order->type,
                $order->customer_name,
                $order->customer_phone,
                $order->delivery_instructions,
            ])));

            $payload = [
                'orderCart' => [
                    'groupLineItems' => false,
                    'lineItems' => $lineItems,
                    'note' => $note,
                ],
            ];

            Log::info('Clover: Syncing order to POS', [
                'order_number' => $order->order_number,
                'items_count' => count($lineItems),
            ]);

            $response = Http::withToken($this->accessToken)
                ->acceptJson()
                ->timeout(30)
                ->post("{$this->posBaseUrl}/merchants/{$this->merchantId}/atomic_order/orders", $payload);

            $data = $response->json() ?? [];

            if ($response->successful()) {
                $cloverOrderId = $data['id'] ?? $data['href'] ?? null;
                if (is_string($cloverOrderId) && str_contains($cloverOrderId, '/')) {
                    $cloverOrderId = basename($cloverOrderId);
                }

                if ($cloverOrderId) {
                    // Keep the order open/visible on POS; title helps staff find it.
                    Http::withToken($this->accessToken)
                        ->acceptJson()
                        ->timeout(15)
                        ->post("{$this->posBaseUrl}/merchants/{$this->merchantId}/orders/{$cloverOrderId}", [
                            'title' => $order->order_number,
                            'note' => $note,
                            'state' => 'open',
                        ]);

                    Log::info('Clover: Order synced to POS', ['clover_order_id' => $cloverOrderId]);

                    return (string) $cloverOrderId;
                }
            }

            // Fallback: classic create-order + line_items if atomic endpoint is unavailable
            Log::warning('Clover: Atomic order failed, trying classic order create', [
                'status' => $response->status(),
                'response' => $data,
            ]);

            return $this->syncOrderClassic($order, $lineItems, $note);
        } catch (\Throwable $e) {
            Log::error('Clover: Order sync exception — ' . $e->getMessage());

            return null;
        }
    }

    public function getGatewayName(): string
    {
        return 'clover';
    }

    /**
     * Prefer nested Clover error details over the generic "400 Bad Request" shell message.
     *
     * @param  array<string, mixed>  $data
     */
    protected function extractCloverErrorMessage(array $data, int $status): string
    {
        if ($status === 401) {
            return 'Clover rejected the charge credentials (401). '
                . 'Use the Ecommerce private API key from Settings → Ecommerce → API Tokens '
                . 'as CLOVER_ECOMMERCE_PRIVATE_KEY.';
        }

        $candidates = [
            $data['error']['message'] ?? null,
            $data['error']['code'] ?? null,
            is_string($data['error'] ?? null) ? $data['error'] : null,
            $data['message'] ?? null,
        ];

        foreach ($candidates as $candidate) {
            if (! is_string($candidate) || trim($candidate) === '') {
                continue;
            }
            // Skip unhelpful HTTP status echoes.
            if (preg_match('/^\d{3}\s+Bad Request$/i', $candidate)) {
                continue;
            }
            return $candidate;
        }

        return match ($status) {
            400 => 'Clover rejected the card charge (bad request). Check card details, amount, and that the card was tokenized correctly.',
            402 => 'Card was declined by Clover / the card issuer.',
            429 => 'Too many payment attempts. Please wait a moment and try again.',
            default => 'Clover payment failed (HTTP ' . $status . ').',
        };
    }

    /**
     * Mock unless real sandbox/production credentials are configured.
     * This lets localhost work fully without Clover account keys.
     */
    protected function shouldUseMock(): bool
    {
        $placeholders = [
            '',
            'mock-clover-access-token',
            'your-clover-access-token',
        ];

        $merchantPlaceholders = [
            '',
            'mock-clover-merchant-id',
            'your-clover-merchant-id',
        ];

        return in_array($this->accessToken, $placeholders, true)
            || in_array($this->merchantId, $merchantPlaceholders, true);
    }

    /**
     * Classic order + line_items fallback for POS sync.
     *
     * @param  array<int, array<string, mixed>>  $lineItems
     */
    protected function syncOrderClassic(Order $order, array $lineItems, string $note): ?string
    {
        $create = Http::withToken($this->accessToken)
            ->acceptJson()
            ->timeout(30)
            ->post("{$this->posBaseUrl}/merchants/{$this->merchantId}/orders", [
                'state' => 'open',
                'title' => $order->order_number,
                'note' => $note,
            ]);

        if (!$create->successful()) {
            Log::error('Clover: Classic order creation failed', [
                'status' => $create->status(),
                'response' => $create->json(),
            ]);

            return null;
        }

        $cloverOrderId = $create->json('id');
        if (!$cloverOrderId) {
            return null;
        }

        foreach ($lineItems as $line) {
            Http::withToken($this->accessToken)
                ->acceptJson()
                ->timeout(15)
                ->post("{$this->posBaseUrl}/merchants/{$this->merchantId}/orders/{$cloverOrderId}/line_items", [
                    'name' => $line['name'],
                    'price' => $line['price'],
                    'note' => $line['note'] ?? null,
                ]);
        }

        Log::info('Clover: Classic order synced to POS', ['clover_order_id' => $cloverOrderId]);

        return (string) $cloverOrderId;
    }

    /**
     * Best-effort tender on the POS order after an ecommerce charge succeeds.
     */
    protected function recordPosPayment(string $cloverOrderId, int $amountCents, string $externalPaymentId): void
    {
        try {
            $response = Http::withToken($this->accessToken)
                ->acceptJson()
                ->timeout(15)
                ->post("{$this->posBaseUrl}/merchants/{$this->merchantId}/orders/{$cloverOrderId}/payments", [
                    'amount' => $amountCents,
                    'tipAmount' => 0,
                    'taxAmount' => 0,
                    'externalPaymentId' => $externalPaymentId,
                    'note' => 'Online card payment',
                ]);

            if (!$response->successful()) {
                Log::warning('Clover: Could not attach POS payment (non-fatal)', [
                    'status' => $response->status(),
                    'response' => $response->json(),
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning('Clover: POS payment attach exception (non-fatal) — ' . $e->getMessage());
        }
    }
}
