<?php

namespace App\Services;

use Midtrans\Config;
use Midtrans\Snap;

class MidtransService
{
    public function __construct()
    {
        Config::$serverKey = config('midtrans.server_key');
        Config::$clientKey = config('midtrans.client_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = config('midtrans.is_sanitized');
        Config::$is3ds = config('midtrans.is_3ds');
    }

    public function createTransaction(array $transactionDetails)
    {
        $params = [
            'transaction_details' => [
                'order_id' => $transactionDetails['order_id'],
                'gross_amount' => $transactionDetails['gross_amount'],
            ],
            'item_details' => $transactionDetails['item_details'],
            'customer_details' => $transactionDetails['customer_details'],
            'enabled_payments' => $transactionDetails['enabled_payments'] ?? $this->getEnabledPayments(),
            'callbacks' => [
                'finish' => route('payment.success')
            ]
        ];

        // Tambahkan expiry jika ada
        if (isset($transactionDetails['expiry'])) {
            $params['expiry'] = $transactionDetails['expiry'];
        }

        try {
            $snapToken = Snap::getSnapToken($params);
            return $snapToken;
        } catch (\Exception $e) {
            throw new \Exception('Midtrans Error: ' . $e->getMessage());
        }
    }

    private function getEnabledPayments()
    {
        return [
            'gopay', 
            'shopeepay', 
            'qris',
            'bank_transfer',
            'credit_card'
        ];
    }
}