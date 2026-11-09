<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Audit Penjualan - CafeSync</title>
    <style>
        @page {
            margin: 12mm 15mm 15mm 15mm;
            size: a4 landscape;
        }

        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #1E1B18;
            font-size: 11px;
            line-height: 1.4;
            margin: 0;
            padding: 0;
        }

        .header-table {
            width: 100%;
            border-bottom: 2px solid #3B2314;
            padding-bottom: 12px;
            margin-bottom: 15px;
        }

        .brand-title {
            font-size: 20px;
            font-weight: bold;
            color: #3B2314;
            letter-spacing: -0.5px;
            margin: 0;
        }

        .brand-subtitle {
            font-size: 10px;
            color: #D97706;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 2px;
        }

        .report-title {
            font-size: 14px;
            font-weight: bold;
            color: #1E1B18;
            text-align: right;
            margin: 0;
            text-transform: uppercase;
        }

        .report-meta {
            font-size: 9px;
            color: #78716C;
            text-align: right;
            margin-top: 3px;
        }

        /* KPI Summary Grid */
        .kpi-table {
            width: 100%;
            margin-bottom: 15px;
            border-collapse: separate;
            border-spacing: 8px 0;
        }

        .kpi-card {
            background-color: #FAF2ED;
            border: 1px solid #E7E5E4;
            border-radius: 6px;
            padding: 8px 12px;
            text-align: left;
        }

        .kpi-label {
            font-size: 8px;
            text-transform: uppercase;
            color: #78716C;
            font-weight: bold;
            letter-spacing: 0.5px;
            margin-bottom: 3px;
        }

        .kpi-value {
            font-size: 15px;
            font-weight: bold;
            color: #3B2314;
        }

        .kpi-sub {
            font-size: 8px;
            color: #16A34A;
            font-weight: 600;
            margin-top: 2px;
        }

        /* Ledger Table */
        .ledger-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        .ledger-table th {
            background-color: #3B2314;
            color: #FFFFFF;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 7px 8px;
            text-align: left;
            border: 1px solid #3B2314;
        }

        .ledger-table td {
            padding: 6px 8px;
            border-bottom: 1px solid #E7E5E4;
            border-left: 1px solid #F4ECE7;
            border-right: 1px solid #F4ECE7;
            font-size: 9.5px;
        }

        .ledger-table tr:nth-child(even) td {
            background-color: #FDFBF7;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .badge-cash {
            background-color: #DCFCE7;
            color: #16A34A;
            padding: 2px 5px;
            border-radius: 4px;
            font-size: 8px;
            font-weight: bold;
        }

        .badge-qris {
            background-color: #FFEDD5;
            color: #D97706;
            padding: 2px 5px;
            border-radius: 4px;
            font-size: 8px;
            font-weight: bold;
        }

        .badge-paid {
            background-color: #DCFCE7;
            color: #16A34A;
            padding: 2px 5px;
            border-radius: 4px;
            font-size: 8px;
            font-weight: bold;
        }

        .total-row td {
            background-color: #FAF2ED !important;
            border-top: 2px solid #D97706 !important;
            border-bottom: 2px solid #3B2314 !important;
            font-weight: bold;
            font-size: 10px;
            color: #3B2314;
        }

        /* Sign-off Block */
        .signoff-table {
            width: 100%;
            margin-top: 20px;
            page-break-inside: avoid;
        }

        .signoff-box {
            width: 45%;
            border: 1px solid #E7E5E4;
            background-color: #FFFFFF;
            border-radius: 6px;
            padding: 10px 14px;
            text-align: center;
        }

        .signoff-title {
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            color: #78716C;
            margin-bottom: 35px;
        }

        .signoff-name {
            font-size: 11px;
            font-weight: bold;
            color: #3B2314;
            border-top: 1px dashed #78716C;
            padding-top: 5px;
            margin-top: 10px;
        }

        .signoff-role {
            font-size: 8px;
            color: #78716C;
        }

        .footer-note {
            margin-top: 15px;
            font-size: 8px;
            color: #78716C;
            text-align: center;
            border-top: 1px solid #E7E5E4;
            padding-top: 8px;
        }
    </style>
</head>
<body>
    <!-- Top Report Header -->
    <table class="header-table">
        <tr>
            <td style="width: 55%; vertical-align: top;">
                <div class="brand-title">CafeSync &bull; Executive Suite</div>
                <div class="brand-subtitle">Artisanal Specialty Cafe Operations &bull; Downtown Roastery #01</div>
                <div style="font-size: 9px; color: #78716C; margin-top: 4px;">
                    Jl. Veteran No. 42, Downtown Core &bull; Tel: (021) 555-CAFE &bull; NPWP: 01.345.678.9-012.000
                </div>
            </td>
            <td style="width: 45%; vertical-align: top;">
                <div class="report-title">Laporan Audit Penjualan Resmi</div>
                <div class="report-meta">
                    <strong>Periode Audit:</strong> {{ $audit['dateRangeText'] }}<br>
                    <strong>Waktu Cetak:</strong> {{ $audit['generatedAt'] }}<br>
                    <strong>Auditor / Manager:</strong> {{ auth()->user()->full_name ?? 'Elena Vance' }} ({{ auth()->user()->role ?? 'MANAGER' }})<br>
                    <strong>Status Audit:</strong> <span style="color: #16A34A; font-weight: bold;">VERIFIED &amp; ACID COMPLIANT</span>
                </div>
            </td>
        </tr>
    </table>

    <!-- KPI Summary Grid -->
    <table class="kpi-table">
        <tr>
            <td class="kpi-card" style="width: 25%;">
                <div class="kpi-label">Total Omzet Bruto</div>
                <div class="kpi-value">${{ number_format($audit['totalRevenue'], 2) }}</div>
                <div class="kpi-sub">{{ $audit['totalOrders'] }} Transaksi Lunas</div>
            </td>
            <td class="kpi-card" style="width: 25%;">
                <div class="kpi-label">Rata-Rata Nilai Tiket (AOV)</div>
                <div class="kpi-value">${{ number_format($audit['avgTicket'], 2) }}</div>
                <div class="kpi-sub">Per Tiket Pesanan Kasir</div>
            </td>
            <td class="kpi-card" style="width: 25%;">
                <div class="kpi-label">Pajak Restoran PB1 (10%)</div>
                <div class="kpi-value">${{ number_format($audit['totalTax'], 2) }}</div>
                <div class="kpi-sub">Subtotal: ${{ number_format($audit['totalSubtotal'], 2) }}</div>
            </td>
            <td class="kpi-card" style="width: 25%;">
                <div class="kpi-label">Komposisi Pembayaran</div>
                <div class="kpi-value" style="font-size: 12px; margin-top: 2px;">
                    CASH: ${{ number_format($audit['cashTotal'], 2) }} ({{ $audit['cashPct'] }}%)<br>
                    QRIS: ${{ number_format($audit['qrisTotal'], 2) }} ({{ $audit['qrisPct'] }}%)
                </div>
                <div class="kpi-sub">Dual Channel Settlement</div>
            </td>
        </tr>
    </table>

    <!-- Detailed Transaction Ledger Table -->
    <table class="ledger-table">
        <thead>
            <tr>
                <th style="width: 7%;">No. Order</th>
                <th style="width: 13%;">Waktu Transaksi</th>
                <th style="width: 14%;">Kasir Bertugas</th>
                <th style="width: 28%;">Rincian Item Pesanan &amp; Kuantitas</th>
                <th style="width: 8%;" class="text-center">Metode</th>
                <th style="width: 7%;" class="text-center">Status</th>
                <th style="width: 7%;" class="text-right">Subtotal</th>
                <th style="width: 7%;" class="text-right">PB1 (10%)</th>
                <th style="width: 9%;" class="text-right">Grand Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse($audit['orders'] as $order)
                @php
                    $itemList = [];
                    foreach ($order->orderDetails as $detail) {
                        $name = $detail->menu ? $detail->menu->item_name : "Menu #{$detail->menu_id}";
                        $itemList[] = "{$name} (x{$detail->quantity})";
                    }
                    $itemsSummary = !empty($itemList) ? implode(', ', $itemList) : 'Pesanan POS Standar';
                @endphp
                <tr>
                    <td style="font-weight: bold; color: #3B2314;">
                        #{{ str_pad($order->order_id, 4, '0', STR_PAD_LEFT) }}
                    </td>
                    <td>{{ $order->created_at ? $order->created_at->format('d/m/Y H:i:s') : '-' }}</td>
                    <td>{{ $order->user ? $order->user->full_name : 'Staff Kasir' }}</td>
                    <td style="font-size: 9px; color: #554336;">{{ $itemsSummary }}</td>
                    <td class="text-center">
                        <span class="{{ $order->payment_method === 'CASH' ? 'badge-cash' : 'badge-qris' }}">
                            {{ $order->payment_method }}
                        </span>
                    </td>
                    <td class="text-center">
                        <span class="badge-paid">{{ $order->payment_status }}</span>
                    </td>
                    <td class="text-right">${{ number_format($order->calculated_subtotal, 2) }}</td>
                    <td class="text-right">${{ number_format($order->calculated_tax, 2) }}</td>
                    <td class="text-right" style="font-weight: bold; color: #3B2314;">
                        ${{ number_format((float)$order->total_amount, 2) }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" style="text-align: center; padding: 20px; color: #78716C;">
                        Tidak ada riwayat transaksi penjualan pada periode ini.
                    </td>
                </tr>
            @endforelse

            <!-- Total Row -->
            <tr class="total-row">
                <td colspan="4" style="text-align: left; padding-left: 10px;">
                    TOTAL AUDIT PENJUALAN ({{ $audit['totalOrders'] }} Transaksi Terverifikasi)
                </td>
                <td colspan="2" class="text-center">
                    CASH ({{ $audit['cashCount'] }}) | QRIS ({{ $audit['qrisCount'] }})
                </td>
                <td class="text-right">${{ number_format($audit['totalSubtotal'], 2) }}</td>
                <td class="text-right">${{ number_format($audit['totalTax'], 2) }}</td>
                <td class="text-right" style="font-size: 11px; color: #D97706;">
                    ${{ number_format($audit['totalRevenue'], 2) }}
                </td>
            </tr>
        </tbody>
    </table>

    <!-- Sign-off & Audit Authorization Block -->
    <table class="signoff-table">
        <tr>
            <td style="width: 50%; padding-right: 15px;">
                <div class="signoff-box">
                    <div class="signoff-title">Diverifikasi &amp; Disiapkan Oleh:</div>
                    <div style="height: 35px;"></div>
                    <div class="signoff-name">Alex Rivera / Sarah Jenkins</div>
                    <div class="signoff-role">Head Barista &amp; Floor Cashier Supervisor</div>
                </div>
            </td>
            <td style="width: 50%; padding-left: 15px;">
                <div class="signoff-box">
                    <div class="signoff-title">Disetujui &amp; Disahkan Oleh:</div>
                    <div style="height: 35px;"></div>
                    <div class="signoff-name">Elena Vance, S.M., MBA</div>
                    <div class="signoff-role">General Cafe Operations Manager</div>
                </div>
            </td>
        </tr>
    </table>

    <!-- Document Footer -->
    <div class="footer-note">
        Dokumen ini dibuat secara elektronik melalui Sistem Intelijen Operasional <strong>CafeSync (v0.7.0)</strong> pada {{ $audit['generatedAt'] }}.<br>
        Segala bentuk manipulasi data pada berkas audit ini bertentangan dengan kebijakan audit internal perusahaan. &bull; Halaman 1 dari 1
    </div>
</body>
</html>
