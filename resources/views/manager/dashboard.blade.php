@extends('layouts.app')

@section('title', 'Executive Analytics & Sales Audit')

@section('content')
<div class="space-y-6 pb-12" x-data="{
    dateFilterModal: false,
    exportModal: false,
    activePeriod: '{{ $period }}'
}">
    <!-- Top Workspace Action Bar -->
    <div class="flex flex-col xl:flex-row xl:items-center justify-between gap-4 bg-white rounded-2xl p-6 border border-[#E7E5E4] shadow-sm">
        <div class="flex flex-col gap-1">
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-widest bg-[#3B2314] text-white">
                    Executive Intelligence
                </span>
                <span class="w-1.5 h-1.5 rounded-full bg-[#E7E5E4]"></span>
                <span class="text-xs font-medium text-[#16A34A] flex items-center gap-1">
                    <span class="w-2 h-2 rounded-full bg-[#16A34A] animate-pulse"></span> Real-Time Audit Active
                </span>
            </div>
            <h1 class="text-2xl lg:text-3xl font-extrabold font-heading text-[#1E1B18] tracking-tight mt-0.5">
                Dasbor Eksekutif dan Intelijen Operasional
            </h1>
            <p class="text-xs sm:text-sm text-[#78716C]">
                Dasbor Eksekutif &amp; Intelijen Penjualan: Pengawasan finansial tingkat tinggi, tren jam sibuk, dan audit rasio biaya tenaga kerja.
            </p>
        </div>

        <!-- Branch, Interval Filters & Export Controls -->
        <div class="flex flex-wrap items-center gap-2.5">
            <!-- Branch Selector Badge -->
            <div class="hidden sm:flex items-center gap-2 bg-[#FAF2ED] border border-[#E7E5E4] px-3.5 py-2 rounded-xl shadow-xs">
                <span class="text-base">☕</span>
                <span class="text-xs font-bold text-[#3B2314]">Downtown Roastery #01</span>
            </div>

            <!-- Time Interval Filter Pills -->
            <div class="flex items-center bg-[#F4ECE7] p-1 rounded-xl border border-[#E7E5E4]">
                <a href="{{ route('manager.dashboard', ['period' => 'today']) }}"
                   class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all {{ $period === 'today' ? 'bg-white text-[#D97706] shadow-sm' : 'text-[#78716C] hover:text-[#1E1B18]' }}">
                    Hari Ini
                </a>
                <a href="{{ route('manager.dashboard', ['period' => 'yesterday']) }}"
                   class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all {{ $period === 'yesterday' ? 'bg-white text-[#D97706] shadow-sm' : 'text-[#78716C] hover:text-[#1E1B18]' }}">
                    Kemarin
                </a>
                <a href="{{ route('manager.dashboard', ['period' => '7days']) }}"
                   class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all {{ in_array($period, ['7days', 'week']) ? 'bg-white text-[#D97706] shadow-sm' : 'text-[#78716C] hover:text-[#1E1B18]' }}">
                    7 Hari
                </a>
                <a href="{{ route('manager.dashboard', ['period' => 'month']) }}"
                   class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all {{ $period === 'month' ? 'bg-white text-[#D97706] shadow-sm' : 'text-[#78716C] hover:text-[#1E1B18]' }}">
                    Bulan Ini
                </a>
                <button type="button" @click="dateFilterModal = true"
                        class="px-3 py-1.5 rounded-lg text-xs font-semibold flex items-center gap-1 transition-all {{ $period === 'custom' ? 'bg-white text-[#D97706] shadow-sm' : 'text-[#78716C] hover:text-[#1E1B18]' }}">
                    <span>Custom</span>
                    <span class="material-symbols-outlined text-[14px]">calendar_month</span>
                </button>
            </div>

            <!-- Export Buttons Dropdown / Actions -->
            <div class="flex items-center gap-1.5">
                <a href="{{ route('manager.export-sales', ['format' => 'csv', 'start_date' => $startDate, 'end_date' => $endDate]) }}"
                   title="Ekspor data penjualan ke CSV"
                   class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-white border border-[#E7E5E4] text-xs font-bold text-[#1E1B18] hover:bg-[#FAF2ED] hover:text-[#D97706] shadow-xs transition-colors">
                    <span class="material-symbols-outlined text-[16px] text-[#16A34A]">table_view</span>
                    <span>CSV</span>
                </a>
                <a href="{{ route('manager.export-sales', ['format' => 'pdf', 'start_date' => $startDate, 'end_date' => $endDate]) }}"
                   title="Unduh laporan audit resmi format PDF"
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-[#3B2314] text-white text-xs font-bold hover:bg-[#2A180E] shadow-xs transition-all">
                    <span class="material-symbols-outlined text-[16px] text-red-400">picture_as_pdf</span>
                    <span>Audit PDF</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Active Filter Notice (if custom) -->
    @if($period === 'custom')
    <div class="bg-amber-50 border border-amber-200 text-amber-900 px-4 py-2.5 rounded-xl text-xs flex items-center justify-between">
        <div class="flex items-center gap-2">
            <span class="material-symbols-outlined text-[18px] text-[#D97706]">filter_alt</span>
            <span>Menampilkan data kustom: <strong>{{ $dateLabel }}</strong></span>
        </div>
        <a href="{{ route('manager.dashboard') }}" class="font-bold underline hover:text-[#B45309]">Reset ke Hari Ini</a>
    </div>
    @endif

    <!-- 4 Primary Executive KPI Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <!-- KPI 1: Revenue Hari Ini (Gross Revenue) -->
        <div class="bg-white rounded-2xl p-5 border border-[#E7E5E4] shadow-sm hover:shadow-md transition-shadow relative overflow-hidden flex flex-col justify-between">
            <div class="flex items-start justify-between">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-[#78716C]">
                        {{ $period === 'today' ? 'Revenue Hari Ini' : 'Total Omzet Kotor' }}
                    </span>
                    <div class="flex items-baseline gap-1 mt-2">
                        <span class="text-3xl font-extrabold font-heading text-[#3B2314] font-tabular">
                            ${{ number_format($totalRevenue, 2) }}
                        </span>
                    </div>
                </div>
                <div class="w-11 h-11 rounded-xl bg-amber-50 text-[#D97706] flex items-center justify-center border border-amber-200 shadow-inner">
                    <span class="material-symbols-outlined text-[24px]">payments</span>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-[#F4ECE7] flex items-center justify-between text-xs">
                <div class="flex items-center gap-1 font-semibold {{ $revenueGrowthPct >= 0 ? 'text-[#16A34A]' : 'text-[#DC2626]' }}">
                    <span class="material-symbols-outlined text-[16px]">
                        {{ $revenueGrowthPct >= 0 ? 'trending_up' : 'trending_down' }}
                    </span>
                    <span class="font-tabular">{{ $revenueGrowthPct >= 0 ? '+' : '' }}{{ $revenueGrowthPct }}%</span>
                </div>
                <span class="text-[#78716C] font-tabular">
                    vs. periode lalu (${{ number_format($prevRevenue, 2) }})
                </span>
            </div>
        </div>

        <!-- KPI 2: Total Transaksi (Total Paid Orders & AOV) -->
        <div class="bg-white rounded-2xl p-5 border border-[#E7E5E4] shadow-sm hover:shadow-md transition-shadow relative overflow-hidden flex flex-col justify-between">
            <div class="flex items-start justify-between">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-[#78716C]">
                        Total Transaksi Terbayar
                    </span>
                    <div class="flex items-baseline gap-2 mt-2">
                        <span class="text-3xl font-extrabold font-heading text-[#1E1B18] font-tabular">
                            {{ $totalOrdersCount }}
                        </span>
                        <span class="text-xs font-medium text-[#78716C]">Tiket Kasir</span>
                    </div>
                </div>
                <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center border border-blue-200 shadow-inner">
                    <span class="material-symbols-outlined text-[24px]">receipt_long</span>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-[#F4ECE7] flex items-center justify-between text-xs">
                <span class="text-[#78716C]">
                    Avg Ticket: <strong class="text-[#1E1B18] font-tabular">${{ number_format($avgTicket, 2) }}</strong>
                </span>
                <span class="font-semibold {{ $avgTicketGrowthDiff >= 0 ? 'text-[#16A34A]' : 'text-[#DC2626]' }} font-tabular">
                    {{ $avgTicketGrowthDiff >= 0 ? '+$' : '-$' }}{{ number_format(abs($avgTicketGrowthDiff), 2) }}
                </span>
            </div>
        </div>

        <!-- KPI 3: Rasio Biaya Tenaga Kerja (Labor Cost Ratio) -->
        <div class="bg-white rounded-2xl p-5 border border-[#E7E5E4] shadow-sm hover:shadow-md transition-shadow relative overflow-hidden flex flex-col justify-between">
            <div class="flex items-start justify-between">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-[#78716C]">
                        Labor Cost Ratio (LCR)
                    </span>
                    <div class="flex items-baseline gap-2 mt-2">
                        <span class="text-3xl font-extrabold font-heading text-[#3B2314] font-tabular">
                            {{ number_format($laborCostRatio, 1) }}%
                        </span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $laborStatusBadge }}">
                            {{ $laborStatus }}
                        </span>
                    </div>
                </div>
                <div class="w-11 h-11 rounded-xl bg-emerald-50 text-[#16A34A] flex items-center justify-center border border-emerald-200 shadow-inner">
                    <span class="material-symbols-outlined text-[24px]">pie_chart</span>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-[#F4ECE7] flex items-center justify-between text-xs">
                <span class="text-[#78716C]">Biaya Upah: <strong class="text-[#3B2314] font-tabular">${{ number_format($laborCost, 2) }}</strong></span>
                <span class="text-[#D97706] font-semibold text-[11px]">Target: &lt; 20%</span>
            </div>
        </div>

        <!-- KPI 4: Staf Lantai Aktif & Margin -->
        <div class="bg-white rounded-2xl p-5 border border-[#E7E5E4] shadow-sm hover:shadow-md transition-shadow relative overflow-hidden flex flex-col justify-between">
            <div class="flex items-start justify-between">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-[#78716C]">
                        Staf On-Duty Lantai
                    </span>
                    <div class="flex items-baseline gap-2 mt-2">
                        <span class="text-3xl font-extrabold font-heading text-[#16A34A] font-tabular">
                            {{ $activeStaffCount }}
                        </span>
                        <span class="text-xs font-medium text-[#78716C]">Barista Aktif</span>
                    </div>
                </div>
                <div class="w-11 h-11 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center border border-purple-200 shadow-inner">
                    <span class="material-symbols-outlined text-[24px]">groups</span>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-[#F4ECE7] flex items-center justify-between text-xs">
                <div class="flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-[#16A34A]"></span>
                    <span class="font-semibold text-[#1E1B18]">Optimal Floor</span>
                </div>
                <span class="text-[#78716C]">Target: 4–6 Pax</span>
            </div>
        </div>
    </div>

    <!-- Main Analytical 2-Column Split (7 Cols Left vs 5 Cols Right) -->
    <div class="grid grid-cols-1 xl:grid-cols-12 gap-8 items-start">
        <!-- LEFT COLUMN (7 of 12) -->
        <div class="xl:col-span-7 space-y-6">
            <!-- Chart Card: Hourly Sales Velocity & Peak Store Volume -->
            <div class="bg-white rounded-2xl p-6 border border-[#E7E5E4] shadow-sm">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-[#F4ECE7]">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-[#D97706] text-[22px]">schedule</span>
                            <h2 class="text-lg font-bold font-heading text-[#1E1B18]">
                                Tren Penjualan per Jam (Hourly Sales Velocity)
                            </h2>
                        </div>
                        <p class="text-xs text-[#78716C] mt-0.5">
                            Visualisasi volume omzet &amp; pesanan per jam untuk mendeteksi lonjakan jam sibuk kafe.
                        </p>
                    </div>

                    <!-- Chart Legend Badges -->
                    <div class="flex items-center gap-3 text-xs">
                        <div class="flex items-center gap-1.5">
                            <span class="w-3 h-3 rounded-full bg-[#D97706]"></span>
                            <span class="font-medium text-[#78716C]">Sales ($)</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="w-3 h-3 rounded-full bg-amber-200"></span>
                            <span class="font-medium text-[#78716C]">Jumlah Pesanan</span>
                        </div>
                    </div>
                </div>

                <!-- Peak Period Highlight Chips -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 my-5">
                    <!-- Morning Surge -->
                    <div class="flex items-center gap-3 p-3.5 rounded-xl bg-[#FAF2ED] border border-amber-200/60">
                        <div class="w-10 h-10 rounded-lg bg-amber-100 flex items-center justify-center text-[#D97706] shrink-0 font-bold">
                            <span class="material-symbols-outlined text-[20px]">wb_twilight</span>
                        </div>
                        <div class="flex flex-col min-w-0">
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-xs text-[#3B2314]">08:00 AM – 10:30 AM</span>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-[#D97706] text-white uppercase tracking-wider">
                                    Morning Surge
                                </span>
                            </div>
                            <span class="text-xs text-[#78716C] mt-0.5 truncate">
                                Omzet Lonjakan Pagi: <strong class="text-[#3B2314] font-tabular">${{ number_format($hourlyVelocity['morningSurge']['sales'], 2) }}</strong> ({{ $hourlyVelocity['morningSurge']['orders'] }} tiket)
                            </span>
                        </div>
                    </div>

                    <!-- Afternoon Lull Surge -->
                    <div class="flex items-center gap-3 p-3.5 rounded-xl bg-[#FAF2ED] border border-amber-200/60">
                        <div class="w-10 h-10 rounded-lg bg-amber-200/80 flex items-center justify-center text-[#B45309] shrink-0 font-bold">
                            <span class="material-symbols-outlined text-[20px]">wb_sunny</span>
                        </div>
                        <div class="flex flex-col min-w-0">
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-xs text-[#3B2314]">02:00 PM – 03:30 PM</span>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-[#3B2314] text-white uppercase tracking-wider">
                                    Afternoon Rush
                                </span>
                            </div>
                            <span class="text-xs text-[#78716C] mt-0.5 truncate">
                                Omzet Lonjakan Siang: <strong class="text-[#3B2314] font-tabular">${{ number_format($hourlyVelocity['afternoonSurge']['sales'], 2) }}</strong> ({{ $hourlyVelocity['afternoonSurge']['orders'] }} tiket)
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Graphic Canvas Chart Container -->
                <div class="relative w-full h-72">
                    <canvas id="hourlySalesChart"></canvas>
                </div>

                <!-- Footer Chart Insight -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mt-4 pt-3 border-t border-[#F4ECE7] text-xs">
                    <div class="flex items-center gap-2 text-[#1E1B18]">
                        <span class="material-symbols-outlined text-[#D97706] text-[18px]">insights</span>
                        <span>Jam puncak transaksi: <strong class="font-tabular">{{ $hourlyVelocity['peakHourLabel'] }}</strong> (Volume: ${{ number_format($hourlyVelocity['maxHourSales'], 2) }})</span>
                    </div>
                    <span class="text-[#78716C] font-tabular">
                        Rata-rata: ${{ number_format($hourlyVelocity['avgPerHour'], 2) }}/jam operasional
                    </span>
                </div>
            </div>

            <!-- Top Margin Ingredients Breakdown -->
            <div class="bg-white rounded-2xl p-6 border border-[#E7E5E4] shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[#D97706] text-[20px]">inventory_2</span>
                        <h3 class="text-base font-bold font-heading text-[#1E1B18]">
                            Bahan Baku Margin Tertinggi &amp; Laju Konsumsi
                        </h3>
                    </div>
                    <span class="text-xs text-[#78716C]">Formula Resep Terintegrasi</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @foreach($topMarginIngredients as $item)
                    <div class="p-4 rounded-xl bg-[#FAF2ED] border border-[#E7E5E4] flex flex-col justify-between">
                        <div>
                            <div class="flex items-start justify-between">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-[#78716C]">{{ $item['category'] }}</span>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#DCFCE7] text-[#16A34A]">{{ $item['margin'] }}</span>
                            </div>
                            <h4 class="font-bold text-sm text-[#1E1B18] mt-1">{{ $item['name'] }}</h4>
                            <p class="text-xs text-[#78716C] mt-0.5">{{ $item['brew_type'] }}</p>
                        </div>
                        <div class="mt-4 pt-3 border-t border-[#E7E5E4]/60 flex items-center justify-between text-xs">
                            <span class="text-[#78716C]">Laju Depresi:</span>
                            <span class="font-bold text-[#1E1B18] font-tabular">{{ $item['depletion_rate'] }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Recent Sales Ledger Audit Table -->
            <div class="bg-white rounded-2xl p-6 border border-[#E7E5E4] shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-base font-bold font-heading text-[#1E1B18]">
                            Audit Trail Transaksi Kasir Terkini
                        </h3>
                        <p class="text-xs text-[#78716C]">10 transaksi penjualan terakhir yang tercatat di database.</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('manager.export-sales', ['format' => 'csv']) }}" class="text-xs font-semibold text-[#D97706] hover:underline flex items-center gap-1">
                            <span>Unduh Semua</span>
                            <span class="material-symbols-outlined text-[14px]">download</span>
                        </a>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-[#FAF2ED] text-[#78716C] uppercase font-semibold border-b border-[#E7E5E4]">
                            <tr>
                                <th class="px-4 py-3">Order #</th>
                                <th class="px-4 py-3">Waktu</th>
                                <th class="px-4 py-3">Kasir</th>
                                <th class="px-4 py-3">Metode</th>
                                <th class="px-4 py-3 text-right">Grand Total</th>
                                <th class="px-4 py-3 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#E7E5E4] text-[#1E1B18]">
                            @forelse($recentOrders as $order)
                            <tr class="hover:bg-[#FAF2ED]/50 transition-colors">
                                <td class="px-4 py-3 font-bold font-tabular text-[#3B2314]">
                                    #{{ str_pad($order->order_id, 4, '0', STR_PAD_LEFT) }}
                                </td>
                                <td class="px-4 py-3 text-[#78716C]">
                                    {{ $order->created_at ? $order->created_at->format('H:i:s d/m') : '-' }}
                                </td>
                                <td class="px-4 py-3 font-medium">
                                    {{ $order->user ? $order->user->full_name : 'System Kasir' }}
                                </td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $order->payment_method === 'CASH' ? 'bg-[#DCFCE7] text-[#16A34A]' : 'bg-[#FFEDD5] text-[#D97706]' }}">
                                        {{ $order->payment_method }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right font-bold font-tabular">
                                    ${{ number_format((float)$order->total_amount, 2) }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#DCFCE7] text-[#16A34A]">
                                        {{ $order->payment_status }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="px-4 py-6 text-center text-[#78716C]">
                                    Belum ada data transaksi yang tercatat.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- RIGHT COLUMN (5 of 12) -->
        <div class="xl:col-span-5 space-y-6">
            <!-- Staff Attendance & Labor Audit Deck -->
            <div class="bg-white rounded-2xl p-6 border border-[#E7E5E4] shadow-sm">
                <div class="flex items-center justify-between pb-4 border-b border-[#F4ECE7]">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-[#D97706] text-[20px]">groups</span>
                            <h3 class="text-base font-bold font-heading text-[#1E1B18]">
                                Presensi Staf &amp; Audit Upah
                            </h3>
                        </div>
                        <p class="text-xs text-[#78716C] mt-0.5">Monitoring staf bertugas dan efisiensi jam kerja.</p>
                    </div>
                    <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-[#DCFCE7] text-[#16A34A]">
                        Live Shift
                    </span>
                </div>

                <!-- Labor Gauge & Summary -->
                <div class="p-4 rounded-xl bg-[#FAF2ED] my-4 border border-[#E7E5E4]">
                    <div class="flex items-center justify-between text-xs mb-2">
                        <span class="font-bold text-[#1E1B18]">Efisiensi Jam Kerja Shift</span>
                        <span class="font-bold text-[#16A34A] font-tabular">Status: Terkendali</span>
                    </div>
                    <div class="w-full bg-[#E7E5E4] h-2.5 rounded-full overflow-hidden flex">
                        <div class="bg-[#16A34A] h-full rounded-l-full" style="width: 75%;" title="Jam Terjadwal"></div>
                        <div class="bg-[#D97706] h-full" style="width: 15%;" title="Sisa Shift"></div>
                        <div class="bg-[#DC2626] h-full rounded-r-full" style="width: 10%;" title="Resiko Lembur"></div>
                    </div>
                    <div class="flex items-center justify-between text-[11px] text-[#78716C] mt-2 font-tabular">
                        <span>Aktif: <strong>{{ $activeStaffCount }} Staf</strong></span>
                        <span>Biaya Upah: <strong class="text-[#3B2314]">${{ number_format($laborCost, 2) }}</strong></span>
                    </div>
                </div>

                <!-- Active On-Duty Staf List -->
                <div class="space-y-3">
                    @forelse($activeStaff as $att)
                    <div class="p-3 rounded-xl bg-[#FAF2ED]/60 border border-[#E7E5E4] flex items-center justify-between hover:bg-[#FAF2ED] transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="relative">
                                <div class="w-9 h-9 rounded-full bg-[#3B2314] text-white flex items-center justify-center font-bold text-xs">
                                    {{ strtoupper(substr($att->user->full_name ?? 'U', 0, 1)) }}
                                </div>
                                <span class="absolute -bottom-0.5 -right-0.5 w-2.5 h-2.5 rounded-full bg-[#16A34A] border-2 border-white"></span>
                            </div>
                            <div>
                                <h5 class="font-bold text-xs text-[#1E1B18]">{{ $att->user->full_name }}</h5>
                                <p class="text-[11px] text-[#78716C]">
                                    {{ $att->user->role }} &bull; Masuk: {{ $att->clock_in ? $att->clock_in->format('H:i') : '-' }} WIB
                                </p>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-[#DCFCE7] text-[#16A34A]">
                                {{ $att->status }}
                            </span>
                            <p class="text-[11px] text-[#78716C] font-tabular mt-0.5">
                                ${{ number_format((float)$att->user->hourly_rate, 2) }}/jam
                            </p>
                        </div>
                    </div>
                    @empty
                    <div class="p-4 rounded-xl bg-[#FAF2ED] text-center text-xs text-[#78716C]">
                        Tidak ada staf yang sedang clock-in aktif saat ini.
                    </div>
                    @endforelse
                </div>

                <!-- Quick Roster Link -->
                <div class="mt-4 pt-3 border-t border-[#F4ECE7] text-center">
                    <a href="{{ route('hr.roster') }}" class="text-xs font-semibold text-[#D97706] hover:underline flex items-center justify-center gap-1">
                        <span>Kelola Jadwal &amp; Roster HRD</span>
                        <span class="material-symbols-outlined text-[14px]">arrow_forward</span>
                    </a>
                </div>
            </div>

            <!-- Inventory Low Stock Alerts Card -->
            <div class="bg-white rounded-2xl p-6 border border-[#E7E5E4] shadow-sm">
                <div class="flex items-center justify-between pb-3 border-b border-[#F4ECE7]">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[#DC2626] text-[20px]">warning</span>
                        <h3 class="text-base font-bold font-heading text-[#1E1B18]">
                            Peringatan Stok Menipis
                        </h3>
                    </div>
                    <span class="px-2 py-0.5 rounded-full text-xs font-bold {{ $lowStockIngredients->count() > 0 ? 'bg-[#FEE2E2] text-[#DC2626]' : 'bg-[#DCFCE7] text-[#16A34A]' }} font-tabular">
                        {{ $lowStockIngredients->count() }} Item
                    </span>
                </div>

                <div class="mt-4 space-y-2.5">
                    @forelse($lowStockIngredients as $ing)
                    <div class="p-3 rounded-xl bg-red-50/60 border border-red-200/70 flex items-center justify-between">
                        <div>
                            <h5 class="font-bold text-xs text-[#DC2626]">{{ $ing->item_name }}</h5>
                            <p class="text-[11px] text-[#78716C]">
                                Sisa: <strong class="text-[#DC2626] font-tabular">{{ number_format($ing->current_stock, 1) }} {{ $ing->unit }}</strong> &bull; Min: {{ number_format($ing->minimum_threshold, 1) }} {{ $ing->unit }}
                            </p>
                        </div>
                        <a href="{{ route('inventory.index') }}" class="px-2.5 py-1 rounded-lg text-xs font-bold bg-[#DC2626] text-white hover:bg-red-700 transition-colors shadow-xs">
                            Reorder
                        </a>
                    </div>
                    @empty
                    <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs flex items-center gap-2">
                        <span class="material-symbols-outlined text-[20px] text-emerald-600">check_circle</span>
                        <span>Seluruh bahan baku berada dalam kondisi aman di atas batas minimum.</span>
                    </div>
                    @endforelse
                </div>

                <div class="mt-4 pt-3 border-t border-[#F4ECE7] text-center">
                    <a href="{{ route('inventory.index') }}" class="text-xs font-semibold text-[#D97706] hover:underline flex items-center justify-center gap-1">
                        <span>Buka Modul Persediaan &amp; Restock Approval</span>
                        <span class="material-symbols-outlined text-[14px]">arrow_forward</span>
                    </a>
                </div>
            </div>

            <!-- Quick Export Center Tile -->
            <div class="bg-gradient-to-br from-[#3B2314] to-[#2A180E] text-white rounded-2xl p-6 shadow-md">
                <div class="flex items-center gap-2 text-amber-300">
                    <span class="material-symbols-outlined text-[20px]">download</span>
                    <h3 class="font-bold font-heading text-sm uppercase tracking-wider">
                        Pusat Unduhan Laporan Audit
                    </h3>
                </div>
                <p class="text-xs text-white/80 mt-1">
                    Unduh rekapitulasi audit transaksi kasir terverifikasi untuk rekonsiliasi kas dan pelaporan pajak.
                </p>

                <div class="grid grid-cols-2 gap-3 mt-4">
                    <a href="{{ route('manager.export-sales', ['format' => 'csv', 'start_date' => $startDate, 'end_date' => $endDate]) }}"
                       class="flex items-center justify-center gap-1.5 py-2.5 px-3 rounded-xl bg-white/10 hover:bg-white/20 border border-white/20 text-white text-xs font-bold transition-all">
                        <span class="material-symbols-outlined text-[16px] text-emerald-400">table_view</span>
                        <span>Ekspor CSV</span>
                    </a>
                    <a href="{{ route('manager.export-sales', ['format' => 'pdf', 'start_date' => $startDate, 'end_date' => $endDate]) }}"
                       class="flex items-center justify-center gap-1.5 py-2.5 px-3 rounded-xl bg-[#D97706] hover:bg-[#B45309] text-white text-xs font-bold shadow-sm transition-all">
                        <span class="material-symbols-outlined text-[16px] text-white">picture_as_pdf</span>
                        <span>Ekspor PDF</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Custom Date Filter -->
    <div x-show="dateFilterModal" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100">
        <div class="bg-white rounded-2xl p-6 max-w-md w-full border border-[#E7E5E4] shadow-xl"
             @click.away="dateFilterModal = false">
            <div class="flex items-center justify-between pb-3 border-b border-[#E7E5E4]">
                <h3 class="text-base font-bold font-heading text-[#1E1B18] flex items-center gap-2">
                    <span class="material-symbols-outlined text-[#D97706]">calendar_month</span>
                    <span>Pilih Rentang Waktu Audit</span>
                </h3>
                <button type="button" @click="dateFilterModal = false" class="text-[#78716C] hover:text-[#1E1B18]">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <form action="{{ route('manager.dashboard') }}" method="GET" class="mt-4 space-y-4">
                <input type="hidden" name="period" value="custom">

                <div>
                    <label class="block text-xs font-bold text-[#1E1B18] mb-1">Tanggal Mulai</label>
                    <input type="date" name="custom_start" value="{{ $startDate }}"
                           class="w-full px-3 py-2 border border-[#E7E5E4] rounded-xl text-xs focus:ring-2 focus:ring-[#D97706] focus:outline-none" required>
                </div>

                <div>
                    <label class="block text-xs font-bold text-[#1E1B18] mb-1">Tanggal Selesai</label>
                    <input type="date" name="custom_end" value="{{ $endDate }}"
                           class="w-full px-3 py-2 border border-[#E7E5E4] rounded-xl text-xs focus:ring-2 focus:ring-[#D97706] focus:outline-none" required>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" @click="dateFilterModal = false"
                            class="px-4 py-2 rounded-xl border border-[#E7E5E4] text-xs font-semibold text-[#78716C] hover:bg-[#FAF2ED]">
                        Batal
                    </button>
                    <button type="submit"
                            class="px-5 py-2 rounded-xl bg-[#D97706] hover:bg-[#B45309] text-white text-xs font-bold shadow-sm transition-all">
                        Terapkan Filter
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- Load Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('hourlySalesChart');
    if (!ctx) return;

    const hourlyLabels = @json($hourlyVelocity['labels']);
    const salesData = @json($hourlyVelocity['sales']);
    const ordersData = @json($hourlyVelocity['orders']);

    // Create custom gradient for area fill
    const chartContext = ctx.getContext('2d');
    const amberGradient = chartContext.createLinearGradient(0, 0, 0, 260);
    amberGradient.addColorStop(0, 'rgba(217, 119, 6, 0.35)');
    amberGradient.addColorStop(1, 'rgba(217, 119, 6, 0.00)');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: hourlyLabels,
            datasets: [
                {
                    label: 'Omzet Penjualan ($)',
                    data: salesData,
                    borderColor: '#D97706',
                    borderWidth: 3,
                    backgroundColor: amberGradient,
                    fill: true,
                    tension: 0.35,
                    pointBackgroundColor: '#FFFFFF',
                    pointBorderColor: '#3B2314',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    yAxisID: 'ySales'
                },
                {
                    label: 'Jumlah Transaksi',
                    data: ordersData,
                    type: 'bar',
                    backgroundColor: 'rgba(255, 212, 189, 0.65)',
                    hoverBackgroundColor: '#FFB77D',
                    borderRadius: 4,
                    yAxisID: 'yOrders',
                    barThickness: 16
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: '#1E1B18',
                    titleColor: '#FFFFFF',
                    bodyColor: '#FAF2ED',
                    titleFont: { family: 'Plus Jakarta Sans', size: 12, weight: 'bold' },
                    bodyFont: { family: 'Inter', size: 11 },
                    padding: 10,
                    cornerRadius: 8,
                    callbacks: {
                        label: function (context) {
                            if (context.dataset.yAxisID === 'ySales') {
                                return ' Omzet: $' + Number(context.raw).toFixed(2);
                            }
                            return ' Pesanan: ' + context.raw + ' tiket';
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        color: '#78716C',
                        font: { family: 'Inter', size: 10 }
                    }
                },
                ySales: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    grid: {
                        color: '#F4ECE7',
                        drawBorder: false
                    },
                    ticks: {
                        color: '#78716C',
                        font: { family: 'Inter', size: 10 },
                        callback: function (value) {
                            return '$' + value;
                        }
                    }
                },
                yOrders: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    grid: {
                        drawOnChartArea: false
                    },
                    ticks: {
                        color: '#78716C',
                        font: { family: 'Inter', size: 10 },
                        stepSize: 1
                    }
                }
            }
        }
    });
});
</script>
@endpush
