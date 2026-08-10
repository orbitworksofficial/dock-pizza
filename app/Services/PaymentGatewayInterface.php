<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Order;

interface PaymentGatewayInterface
{
    /**
     * Charge a payment source (card nonce/token).
     *
     * @param float $amount The amount to charge
     * @param string $sourceId The payment token or card source
     * @param array $options Additional options (e.g., currency, order reference)
     * @return array Returns an array with 'success', 'transaction_id', and 'raw_response'
     */
    public function charge(float $amount, string $sourceId, array $options = []): array;

    /**
     * Sync the local order to the POS system (kitchen/terminal).
     *
     * @param Order $order The local database order
     * @return string|null Returns the POS order ID if successful, null otherwise
     */
    public function syncOrder(Order $order): ?string;
    
    /**
     * Get the gateway name for identification.
     */
    public function getGatewayName(): string;
}
