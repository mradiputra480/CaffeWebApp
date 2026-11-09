<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Services\AnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AnalyticsController extends Controller
{
    public function __construct(
        protected AnalyticsService $analyticsService
    ) {}

    /**
     * Display Manager Executive Analytics & Intelligence Dashboard.
     */
    public function index(Request $request): View
    {
        $manager = Auth::user();

        $period = $request->input('period', 'today');
        $customStart = $request->input('custom_start');
        $customEnd = $request->input('custom_end');

        $metrics = $this->analyticsService->getExecutiveDashboardMetrics($period, $customStart, $customEnd);

        return view('manager.dashboard', array_merge($metrics, [
            'manager' => $manager,
        ]));
    }

    /**
     * Export Sales Audit records to CSV or PDF format.
     */
    public function exportAudit(Request $request): Response
    {
        $format = strtolower($request->input('format', 'csv'));
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $paymentMethod = $request->input('payment_method');
        $paymentStatus = $request->input('payment_status', 'PAID');

        $dateSuffix = $startDate && $endDate
            ? "{$startDate}_to_{$endDate}"
            : date('Ymd_His');

        if ($format === 'pdf') {
            $pdf = $this->analyticsService->generateSalesAuditPdf(
                $startDate,
                $endDate,
                $paymentMethod,
                $paymentStatus
            );

            $filename = "CafeSync_Sales_Audit_{$dateSuffix}.pdf";

            return response($pdf->output(), 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            ]);
        }

        // Default: Export to CSV
        $csvContent = $this->analyticsService->generateSalesAuditCsv(
            $startDate,
            $endDate,
            $paymentMethod,
            $paymentStatus
        );

        $filename = "CafeSync_Sales_Audit_{$dateSuffix}.csv";

        return response($csvContent, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }
}
