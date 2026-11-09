<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class QRISPaymentService
{
    /**
     * QRIS payment timeout in seconds (3 minutes).
     */
    public const TIMEOUT_SECONDS = 180;

    /**
     * Generate a simulated Dynamic QRIS payment payload.
     *
     * @return array{transaction_id: string, qr_payload: string, qr_image_url: string, amount: float, expires_at: string}
     */
    public function generateDynamicQR(float $amount, string $orderReference): array
    {
        $transactionId = 'QRIS-SYNC-'.strtoupper(Str::random(8));
        $expiresAt = now()->addSeconds(self::TIMEOUT_SECONDS);

        // Build QRIS-compliant payload string (simulated EMVCo format)
        $qrPayload = implode('.', [
            '00020101021226',
            '0016COM.CAFESYNC.WWW',
            '0118MERCHANT.CAFESYNC',
            '5204581253033605802ID',
            '5925CafeSync Artisanal Cafe',
            '6007Jakarta',
            '54'.str_pad(number_format($amount, 2, '', ''), 13, '0', STR_PAD_LEFT),
            '62'.strlen($transactionId).$transactionId,
            '6304',
        ]);

        // Generate QR code image URL via public QR API
        $qrImageUrl = 'https://api.qrserver.com/v1/create-qr-code/?'.http_build_query([
            'size' => '300x300',
            'data' => $qrPayload,
            'format' => 'svg',
            'margin' => 10,
        ]);

        // Store transaction status in cache for polling
        Cache::put("qris_status_{$transactionId}", [
            'status' => 'PENDING',
            'transaction_id' => $transactionId,
            'amount' => $amount,
            'order_reference' => $orderReference,
            'created_at' => now()->toIso8601String(),
            'expires_at' => $expiresAt->toIso8601String(),
        ], self::TIMEOUT_SECONDS + 60);

        return [
            'transaction_id' => $transactionId,
            'qr_payload' => $qrPayload,
            'qr_image_url' => $qrImageUrl,
            'amount' => $amount,
            'expires_at' => $expiresAt->toIso8601String(),
        ];
    }

    /**
     * Check the status of a QRIS transaction.
     */
    public function checkStatus(string $transactionId): array
    {
        $cacheKey = "qris_status_{$transactionId}";
        $data = Cache::get($cacheKey);

        if (! $data) {
            return [
                'status' => 'NOT_FOUND',
                'transaction_id' => $transactionId,
                'message' => 'Transaksi QRIS tidak ditemukan atau sudah kedaluwarsa.',
            ];
        }

        // Check if expired
        if (now()->isAfter($data['expires_at'])) {
            Cache::put($cacheKey, array_merge($data, ['status' => 'EXPIRED']), 300);

            return [
                'status' => 'EXPIRED',
                'transaction_id' => $transactionId,
                'message' => 'Waktu pembayaran QRIS telah habis (timeout 3 menit).',
            ];
        }

        return $data;
    }

    /**
     * Simulate confirming a QRIS payment (for demo/testing purposes).
     * In production, this would be triggered by a webhook from the QRIS gateway.
     */
    public function simulatePaymentSuccess(string $transactionId): array
    {
        $cacheKey = "qris_status_{$transactionId}";
        $data = Cache::get($cacheKey);

        if (! $data) {
            return [
                'success' => false,
                'message' => 'Transaksi QRIS tidak ditemukan.',
            ];
        }

        if ($data['status'] === 'EXPIRED') {
            return [
                'success' => false,
                'message' => 'Transaksi QRIS sudah kedaluwarsa.',
            ];
        }

        $data['status'] = 'SUCCESS';
        $data['paid_at'] = now()->toIso8601String();
        Cache::put($cacheKey, $data, 3600);

        return [
            'success' => true,
            'status' => 'SUCCESS',
            'transaction_id' => $transactionId,
            'message' => 'Pembayaran QRIS berhasil dikonfirmasi.',
        ];
    }
}
