<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk #{{ str_pad($order->order_id, 4, '0', STR_PAD_LEFT) }} - CafeSync</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700&display=swap');

        * { margin: 0; padding: 0; box-sizing: border-box; }

        @page {
            size: 80mm auto;
            margin: 0;
        }

        body {
            font-family: 'Inter', 'Courier New', monospace;
            font-size: 12px;
            line-height: 1.5;
            color: #1E1B18;
            width: 80mm;
            margin: 0 auto;
            padding: 8mm 5mm;
            background: #fff;
        }

        .font-tabular {
            font-feature-settings: "tnum" 1;
            font-variant-numeric: tabular-nums;
        }

        .receipt-header {
            text-align: center;
            border-bottom: 2px dashed #3B2314;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }

        .receipt-header .brand {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-weight: 700;
            font-size: 20px;
            color: #3B2314;
            letter-spacing: -0.5px;
        }

        .receipt-header .tagline {
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: #78716C;
            margin-top: 2px;
        }

        .receipt-header .address {
            font-size: 10px;
            color: #78716C;
            margin-top: 4px;
        }

        .receipt-meta {
            border-bottom: 1px dashed #E7E5E4;
            padding-bottom: 8px;
            margin-bottom: 8px;
            font-size: 11px;
        }

        .receipt-meta .row {
            display: flex;
            justify-content: space-between;
            line-height: 1.7;
        }

        .receipt-meta .label {
            color: #78716C;
        }

        .receipt-meta .value {
            font-weight: 600;
            color: #1E1B18;
        }

        .items-header {
            display: flex;
            justify-content: space-between;
            font-weight: 700;
            font-size: 10px;
            text-transform: uppercase;
            color: #78716C;
            letter-spacing: 0.5px;
            border-bottom: 1px solid #E7E5E4;
            padding-bottom: 4px;
            margin-bottom: 6px;
        }

        .item-line {
            margin-bottom: 6px;
        }

        .item-line .name {
            font-weight: 600;
            font-size: 12px;
        }

        .item-line .detail {
            display: flex;
            justify-content: space-between;
            font-size: 11px;
            color: #78716C;
        }

        .item-line .detail .price {
            color: #1E1B18;
            font-weight: 500;
        }

        .receipt-totals {
            border-top: 2px dashed #3B2314;
            margin-top: 10px;
            padding-top: 8px;
        }

        .receipt-totals .row {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
            line-height: 1.8;
        }

        .receipt-totals .row.grand-total {
            font-size: 16px;
            font-weight: 700;
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: #3B2314;
            border-top: 1px solid #E7E5E4;
            margin-top: 4px;
            padding-top: 4px;
        }

        .payment-info {
            border-top: 1px dashed #E7E5E4;
            margin-top: 8px;
            padding-top: 8px;
        }

        .payment-info .row {
            display: flex;
            justify-content: space-between;
            font-size: 11px;
            line-height: 1.8;
        }

        .payment-badge {
            display: inline-block;
            background: #DCFCE7;
            color: #16A34A;
            font-weight: 700;
            font-size: 10px;
            padding: 2px 8px;
            border-radius: 100px;
            text-transform: uppercase;
        }

        .receipt-footer {
            text-align: center;
            border-top: 2px dashed #3B2314;
            margin-top: 12px;
            padding-top: 10px;
            font-size: 10px;
            color: #78716C;
        }

        .receipt-footer .thank-you {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-weight: 700;
            font-size: 14px;
            color: #3B2314;
            margin-bottom: 4px;
        }

        @media print {
            body { width: 80mm; padding: 2mm 3mm; }
            .no-print { display: none !important; }
        }

        @media screen {
            body {
                max-width: 320px;
                margin: 30px auto;
                padding: 20px 16px;
                border: 1px solid #E7E5E4;
                border-radius: 12px;
                box-shadow: 0 4px 20px rgba(0,0,0,0.06);
            }
        }

        .print-controls {
            text-align: center;
            margin-bottom: 16px;
        }

        .print-controls button {
            background: #D97706;
            color: #fff;
            border: none;
            padding: 10px 24px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            margin: 0 4px;
            transition: all 0.15s;
        }

        .print-controls button:hover {
            background: #B45309;
        }

        .print-controls .btn-back {
            background: #fff;
            color: #1E1B18;
            border: 1px solid #E7E5E4;
        }

        .print-controls .btn-back:hover {
            background: #FAF2ED;
        }
    </style>
</head>
<body>
    {{-- Print Controls (hidden when printing) --}}
    <div class="print-controls no-print">
        <button onclick="window.print()">🖨️ Cetak Struk</button>
        <button class="btn-back" onclick="window.location.href='{{ route('pos.index') }}'">← Kembali ke POS</button>
    </div>

    {{-- Receipt Header --}}
    <div class="receipt-header">
        <div class="brand">☕ CafeSync</div>
        <div class="tagline">Artisanal Specialty Cafe</div>
        <div class="address">
            Jl. Kopi Nusantara No.42, Jakarta<br>
            Telp: (021) 555-CAFE
        </div>
    </div>

    {{-- Order Metadata --}}
    <div class="receipt-meta">
        <div class="row">
            <span class="label">No. Order:</span>
            <span class="value font-tabular">#{{ str_pad($order->order_id, 4, '0', STR_PAD_LEFT) }}</span>
        </div>
        <div class="row">
            <span class="label">Tanggal:</span>
            <span class="value font-tabular">{{ $order->created_at ? $order->created_at->format('d/m/Y') : now()->format('d/m/Y') }}</span>
        </div>
        <div class="row">
            <span class="label">Waktu:</span>
            <span class="value font-tabular">{{ $order->created_at ? $order->created_at->format('H:i:s') : now()->format('H:i:s') }} WIB</span>
        </div>
        <div class="row">
            <span class="label">Kasir:</span>
            <span class="value">{{ $order->user->full_name ?? 'Staff' }}</span>
        </div>
        <div class="row">
            <span class="label">Pembayaran:</span>
            <span class="value">
                <span class="payment-badge">{{ $order->payment_method }} - {{ $order->payment_status }}</span>
            </span>
        </div>
        @if($order->qris_reference)
        <div class="row">
            <span class="label">Ref QRIS:</span>
            <span class="value font-tabular" style="font-size:9px;">{{ $order->qris_reference }}</span>
        </div>
        @endif
    </div>

    {{-- Item Lines --}}
    <div class="items-header">
        <span>Item</span>
        <span>Subtotal</span>
    </div>

    @foreach($order->orderDetails as $detail)
    <div class="item-line">
        <div class="name">{{ $detail->menu->item_name ?? 'Menu Item' }}</div>
        <div class="detail">
            <span>{{ $detail->quantity }}x @ ${{ number_format($detail->menu->price ?? 0, 2) }}</span>
            <span class="price font-tabular">${{ number_format($detail->subtotal, 2) }}</span>
        </div>
    </div>
    @endforeach

    {{-- Totals Section --}}
    <div class="receipt-totals">
        <div class="row">
            <span>Subtotal</span>
            <span class="font-tabular">${{ number_format($subtotal, 2) }}</span>
        </div>
        <div class="row">
            <span>Pajak (10%)</span>
            <span class="font-tabular">${{ number_format($taxAmount, 2) }}</span>
        </div>
        <div class="row grand-total">
            <span>GRAND TOTAL</span>
            <span class="font-tabular">${{ number_format($order->total_amount, 2) }}</span>
        </div>
    </div>

    {{-- Payment Details --}}
    <div class="payment-info">
        @if($order->payment_method === 'CASH')
        <div class="row">
            <span>Tunai Diterima</span>
            <span class="font-tabular" style="font-weight:600;">${{ number_format($order->cash_tendered, 2) }}</span>
        </div>
        <div class="row">
            <span>Kembalian</span>
            <span class="font-tabular" style="font-weight:600; color:#16A34A;">${{ number_format($order->change_due, 2) }}</span>
        </div>
        @elseif($order->payment_method === 'QRIS')
        <div class="row">
            <span>Metode</span>
            <span style="font-weight:600;">Dynamic QRIS</span>
        </div>
        @endif
    </div>

    {{-- Footer --}}
    <div class="receipt-footer">
        <div class="thank-you">Terima Kasih!</div>
        <p>Nikmati kopi Anda ☕</p>
        <p style="margin-top:6px;">
            Powered by <strong>CafeSync v0.5.0</strong><br>
            Sprint 4 • POS Terminal & Atomic Stock
        </p>
    </div>
</body>
</html>
