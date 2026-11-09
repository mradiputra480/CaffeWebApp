<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use App\Services\QRISPaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    protected QRISPaymentService $qrisService;

    public function __construct(QRISPaymentService $qrisService)
    {
        $this->qrisService = $qrisService;
    }

    /**
     * Generate Dynamic QRIS QR Code for payment.
     */
    public function generateQR(Request $request): JsonResponse
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'order_reference' => 'required|string',
        ]);

        $result = $this->qrisService->generateDynamicQR(
            (float) $request->amount,
            $request->order_reference
        );

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }

    /**
     * Check QRIS payment status (polling endpoint).
     */
    public function checkStatus(string $transactionId): JsonResponse
    {
        $result = $this->qrisService->checkStatus($transactionId);

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }

    /**
     * Simulate QRIS payment success (demo/testing endpoint).
     */
    public function simulateSuccess(Request $request): JsonResponse
    {
        $request->validate([
            'transaction_id' => 'required|string',
        ]);

        $result = $this->qrisService->simulatePaymentSuccess($request->transaction_id);

        return response()->json($result);
    }
}
