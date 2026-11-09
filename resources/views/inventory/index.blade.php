@extends('layouts.app')

@section('title', 'Monitor Stok Bahan Baku Mentah & Restock Approval')

@section('content')
<div x-data="inventoryApp()" x-init="init()" class="space-y-6 pb-12">

    <!-- Top Operational Banner & Control Deck -->
    <div class="flex flex-col gap-4">
        <!-- Header Row -->
        <div class="flex flex-col xl:flex-row xl:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="px-2.5 py-0.5 rounded-full bg-[#FFEDD5] text-[#9A3412] text-xs font-semibold uppercase tracking-wider border border-[#FDBA74]/50">
                        Cold Storage & Dry Cellar
                    </span>
                    <span class="w-1.5 h-1.5 rounded-full bg-[#78716C]"></span>
                    <span class="text-xs text-[#78716C] font-medium flex items-center gap-1">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        Live Ledger Synced
                    </span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-heading font-bold text-[#1E1B18] tracking-tight">
                    Pengawasan Stok Bahan Baku Mentah
                </h1>
                <p class="text-sm text-[#78716C] mt-0.5">
                    Monitor bobot/volume biji kopi, susu, kemasan, dan sirup secara terpusat (Raw Materials & Restock Lifecycle).
                </p>
            </div>

            <!-- Action CTAs -->
            <div class="flex items-center gap-2.5 flex-wrap">
                <!-- Request Reorder Button (Staff & Manager) -->
                <button 
                    type="button"
                    @click="openRestockModal()"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-white border border-[#E7E5E4] hover:bg-[#FAF2ED] text-[#1E1B18] text-xs font-semibold shadow-sm transition-all active:scale-[0.98]">
                    <span class="material-symbols-outlined text-[18px] text-[#D97706]">receipt_long</span>
                    <span>Ajukan Restock (Reorder)</span>
                </button>

                <!-- Record Stock Intake Button (Staff & Manager) -->
                <button 
                    type="button"
                    @click="openRecordIntakeModal()"
                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-[#D97706] hover:bg-[#B45309] text-white text-xs font-semibold shadow-md shadow-[#D97706]/20 transition-all active:scale-[0.98]">
                    <span class="material-symbols-outlined text-[18px]">add_box</span>
                    <span>+ Record Stock Intake</span>
                </button>

                @if(auth()->user()->isManager() && $pendingRestocks->count() > 0)
                <!-- Bulk Approve Button (Manager Only) -->
                <form action="{{ route('inventory.restock.approve-all') }}" method="POST" class="inline" onsubmit="return confirm('Setujui semua {{ $pendingRestocks->count() }} pengajuan restock PENDING sekaligus?')">
                    @csrf
                    <button 
                        type="submit"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-semibold shadow-sm transition-all active:scale-[0.98]">
                        <span class="material-symbols-outlined text-[18px]">done_all</span>
                        <span>Setujui Semua PO ({{ $pendingRestocks->count() }})</span>
                    </button>
                </form>
                @endif
            </div>
        </div>

        <!-- Metric Summary Cards Grid (4 KPI Cards) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
            <!-- KPI 1: Total Tracked Raw Items -->
            <div class="bg-white p-5 rounded-2xl border border-[#E7E5E4] shadow-sm flex flex-col justify-between relative overflow-hidden group hover:shadow-md transition-shadow">
                <div class="absolute top-0 right-0 w-24 h-24 bg-[#FAF2ED] rounded-bl-full pointer-events-none -mr-4 -mt-4 transition-transform group-hover:scale-110"></div>
                <div class="flex items-center justify-between mb-3 relative z-10">
                    <span class="text-xs uppercase tracking-wider text-[#78716C] font-semibold">Total Tracked Raw Items</span>
                    <div class="w-8 h-8 rounded-lg bg-[#FAF2ED] flex items-center justify-center text-[#D97706]">
                        <span class="material-symbols-outlined text-[20px]">inventory_2</span>
                    </div>
                </div>
                <div class="relative z-10">
                    <div class="flex items-baseline gap-2">
                        <span class="text-3xl font-heading font-bold text-[#1E1B18] tracking-tight font-tabular">{{ $kpis['total_items'] }}</span>
                        <span class="text-xs text-[#78716C] font-medium">Bahan Baku</span>
                    </div>
                    <p class="text-xs text-[#78716C] mt-1 flex items-center gap-1.5">
                        <span class="font-semibold text-[#D97706] font-tabular">Valuasi: ${{ number_format($kpis['total_valuation'], 2) }}</span>
                        <span>&bull; 3 Ruang Simpan</span>
                    </p>
                </div>
                <div class="w-full bg-[#F5F5F4] h-1.5 rounded-full mt-4 overflow-hidden relative z-10">
                    <div class="bg-[#D97706] h-full rounded-full" style="width: 100%"></div>
                </div>
            </div>

            <!-- KPI 2: Healthy Stock Levels -->
            <div class="bg-white p-5 rounded-2xl border border-[#E7E5E4] shadow-sm flex flex-col justify-between relative overflow-hidden group hover:shadow-md transition-shadow">
                <div class="absolute top-0 right-0 w-24 h-24 bg-[#DCFCE7]/40 rounded-bl-full pointer-events-none -mr-4 -mt-4 transition-transform group-hover:scale-110"></div>
                <div class="flex items-center justify-between mb-3 relative z-10">
                    <span class="text-xs uppercase tracking-wider text-[#78716C] font-semibold">Healthy Stock</span>
                    <div class="w-8 h-8 rounded-lg bg-[#DCFCE7] flex items-center justify-center text-[#16A34A]">
                        <span class="material-symbols-outlined text-[20px]">check_circle</span>
                    </div>
                </div>
                <div class="relative z-10">
                    <div class="flex items-baseline gap-2">
                        <span class="text-3xl font-heading font-bold text-[#16A34A] tracking-tight font-tabular">{{ $kpis['healthy_count'] }}</span>
                        <span class="text-xs text-[#78716C] font-medium">Items Aman</span>
                        <span class="ml-auto inline-flex items-center px-2 py-0.5 rounded-full bg-[#DCFCE7] text-[#16A34A] text-xs font-bold font-tabular">
                            {{ $kpis['healthy_percent'] }}%
                        </span>
                    </div>
                    <p class="text-xs text-[#78716C] mt-1">Stok di atas batas ambang aman (&gt; min_threshold)</p>
                </div>
                <div class="w-full bg-[#F5F5F4] h-1.5 rounded-full mt-4 overflow-hidden relative z-10">
                    <div class="bg-[#16A34A] h-full rounded-full transition-all duration-500" style="width: {{ $kpis['healthy_percent'] }}%"></div>
                </div>
            </div>

            <!-- KPI 3: Low Stock Warnings -->
            <div class="bg-white p-5 rounded-2xl border border-[#E7E5E4] shadow-sm flex flex-col justify-between relative overflow-hidden group hover:shadow-md transition-shadow">
                <div class="absolute top-0 right-0 w-24 h-24 bg-[#FFEDD5]/50 rounded-bl-full pointer-events-none -mr-4 -mt-4 transition-transform group-hover:scale-110"></div>
                <div class="flex items-center justify-between mb-3 relative z-10">
                    <span class="text-xs uppercase tracking-wider text-[#78716C] font-semibold">Low Stock</span>
                    <div class="w-8 h-8 rounded-lg bg-[#FFEDD5] flex items-center justify-center text-[#EA580C]">
                        <span class="material-symbols-outlined text-[20px]">warning</span>
                    </div>
                </div>
                <div class="relative z-10">
                    <div class="flex items-baseline gap-2">
                        <span class="text-3xl font-heading font-bold text-[#EA580C] tracking-tight font-tabular">{{ $kpis['low_stock_count'] }}</span>
                        <span class="text-xs text-[#78716C] font-medium">Items Menipis</span>
                        <span class="ml-auto inline-flex items-center px-2 py-0.5 rounded-full bg-[#FFEDD5] text-[#EA580C] text-xs font-bold">
                            Action Needed
                        </span>
                    </div>
                    <p class="text-xs text-[#78716C] mt-1">&le; Ambang batas minimum, draft restock dipicu</p>
                </div>
                <div class="w-full bg-[#F5F5F4] h-1.5 rounded-full mt-4 overflow-hidden relative z-10">
                    <div class="bg-[#EA580C] h-full rounded-full transition-all duration-500" style="width: {{ $kpis['low_stock_percent'] }}%"></div>
                </div>
            </div>

            <!-- KPI 4: Depleted / Out of Stock -->
            <div class="bg-white p-5 rounded-2xl border border-[#E7E5E4] shadow-sm flex flex-col justify-between relative overflow-hidden group hover:shadow-md transition-shadow">
                <div class="absolute top-0 right-0 w-24 h-24 bg-[#FEE2E2]/50 rounded-bl-full pointer-events-none -mr-4 -mt-4 transition-transform group-hover:scale-110"></div>
                <div class="flex items-center justify-between mb-3 relative z-10">
                    <span class="text-xs uppercase tracking-wider text-[#78716C] font-semibold">Out of Stock</span>
                    <div class="w-8 h-8 rounded-lg bg-[#FEE2E2] flex items-center justify-center text-[#DC2626]">
                        <span class="material-symbols-outlined text-[20px]">error</span>
                    </div>
                </div>
                <div class="relative z-10">
                    <div class="flex items-baseline gap-2">
                        <span class="text-3xl font-heading font-bold text-[#DC2626] tracking-tight font-tabular">{{ $kpis['out_of_stock_count'] }}</span>
                        <span class="text-xs text-[#78716C] font-medium">Bahan Habis</span>
                        <span class="ml-auto inline-flex items-center px-2 py-0.5 rounded-full bg-[#FEE2E2] text-[#DC2626] text-xs font-bold {{ $kpis['out_of_stock_count'] > 0 ? 'animate-pulse' : '' }}">
                            Critical Gap
                        </span>
                    </div>
                    <p class="text-xs text-[#78716C] mt-1">Menu terdampak terkunci otomatis di POS kasir</p>
                </div>
                <div class="w-full bg-[#F5F5F4] h-1.5 rounded-full mt-4 overflow-hidden relative z-10">
                    <div class="bg-[#DC2626] h-full rounded-full transition-all duration-500" style="width: {{ $kpis['out_of_stock_percent'] }}%"></div>
                </div>
            </div>
        </div>

        <!-- Filter & Rapid Search Bar Strip -->
        <div class="bg-white p-3.5 rounded-2xl border border-[#E7E5E4] shadow-sm flex flex-col md:flex-row items-center gap-3">
            <!-- Search Input -->
            <div class="relative w-full md:w-5/12">
                <span class="material-symbols-outlined absolute left-3.5 top-2.5 text-[#78716C] text-[18px]">search</span>
                <input 
                    type="text" 
                    x-model="searchQuery" 
                    @input="filterRows()" 
                    placeholder="Cari bahan mentah, SKU, atau kategori..." 
                    class="w-full pl-10 pr-4 py-2 bg-[#FAF2ED]/60 border border-[#E7E5E4] rounded-xl text-xs text-[#1E1B18] placeholder-[#78716C] focus:outline-none focus:ring-2 focus:ring-[#D97706]/40 focus:border-[#D97706] transition-all">
            </div>

            <!-- Category Filter -->
            <div class="w-full md:w-3/12">
                <select 
                    x-model="selectedCategory" 
                    @change="filterRows()" 
                    class="w-full px-3 py-2 bg-[#FAF2ED]/60 border border-[#E7E5E4] rounded-xl text-xs text-[#1E1B18] font-medium focus:outline-none focus:ring-2 focus:ring-[#D97706]/40 cursor-pointer">
                    <option value="All">Semua Kategori</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}">{{ $cat }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Status Filter -->
            <div class="w-full md:w-2/12">
                <select 
                    x-model="selectedStatus" 
                    @change="filterRows()" 
                    class="w-full px-3 py-2 bg-[#FAF2ED]/60 border border-[#E7E5E4] rounded-xl text-xs text-[#1E1B18] font-medium focus:outline-none focus:ring-2 focus:ring-[#D97706]/40 cursor-pointer">
                    <option value="All">Semua Status</option>
                    <option value="Optimal">Optimal (Healthy)</option>
                    <option value="Low Stock">Low Stock</option>
                    <option value="Out of Stock">Out of Stock</option>
                </select>
            </div>

            <!-- Reset Filter Button -->
            <div class="w-full md:w-2/12 flex items-center justify-end">
                <button 
                    type="button" 
                    @click="resetFilters()" 
                    class="w-full py-2 px-3 rounded-xl border border-[#E7E5E4] hover:bg-[#FAF2ED] text-xs font-medium text-[#78716C] hover:text-[#1E1B18] transition-colors flex items-center justify-center gap-1">
                    <span class="material-symbols-outlined text-[16px]">restart_alt</span>
                    <span>Reset Filter</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Primary Workspace: Split Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

        <!-- Main Left Column: Raw Stock Ledger Table & Intake Logs (approx 68-70%) -->
        <div class="lg:col-span-8 flex flex-col gap-6">

            <!-- Card: Live Raw Stock Ledger -->
            <div class="bg-white rounded-2xl border border-[#E7E5E4] shadow-sm overflow-hidden">
                <!-- Table Header Bar -->
                <div class="px-5 py-4 bg-[#FAF2ED] border-b border-[#E7E5E4] flex items-center justify-between flex-wrap gap-2">
                    <div class="flex items-center gap-2.5">
                        <span class="material-symbols-outlined text-[#D97706] text-[22px]">layers</span>
                        <div>
                            <h2 class="font-heading font-bold text-base text-[#1E1B18]">Live Raw Stock Ledger</h2>
                            <p class="text-[11px] text-[#78716C]">Stok aktual bahan baku mentah, satuan volume/berat, dan kapasitas tangki</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="px-2.5 py-1 rounded-full bg-white border border-[#E7E5E4] text-xs text-[#78716C] font-medium">
                            Menampilkan: <strong class="text-[#1E1B18] font-tabular" x-text="visibleCount">{{ $ingredients->count() }}</strong> bahan
                        </span>
                    </div>
                </div>

                <!-- Raw Materials Table -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse" id="inventoryTable">
                        <thead>
                            <tr class="bg-[#F5F5F4]/70 border-b border-[#E7E5E4] text-[11px] uppercase tracking-wider text-[#78716C] font-semibold">
                                <th class="py-3 px-4">Bahan Baku & SKU</th>
                                <th class="py-3 px-3">Kategori</th>
                                <th class="py-3 px-4 min-w-[200px]">Stok Terkini vs Kapasitas</th>
                                <th class="py-3 px-3">Satuan</th>
                                <th class="py-3 px-3">Ambang Batas</th>
                                <th class="py-3 px-3 text-center">Status</th>
                                <th class="py-3 px-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#E7E5E4] text-xs">
                            @forelse($ingredients as $item)
                                @php
                                    $statusClass = 'Optimal';
                                    $badgeBg = 'bg-[#DCFCE7] text-[#16A34A] border-emerald-300';
                                    $barBg = 'bg-[#16A34A]';
                                    $rowBg = '';

                                    if ($item->isOutOfStock()) {
                                        $statusClass = 'Out of Stock';
                                        $badgeBg = 'bg-[#FEE2E2] text-[#DC2626] border-red-300';
                                        $barBg = 'bg-[#DC2626]';
                                        $rowBg = 'bg-[#FEF2F2]/40';
                                    } elseif ($item->isLowStock()) {
                                        $statusClass = 'Low Stock';
                                        $badgeBg = 'bg-[#FFEDD5] text-[#EA580C] border-orange-300';
                                        $barBg = 'bg-[#EA580C]';
                                        $rowBg = 'bg-[#FFF7ED]/40';
                                    }

                                    $catIcons = [
                                        'Specialty Beans' => 'coffee',
                                        'Dairy & Alternatives' => 'water_bottle',
                                        'Botanicals & Powders' => 'spa',
                                        'Bakery & Pastry' => 'bakery_dining',
                                        'Syrups & Botanicals' => 'liquor',
                                        'Packaging & Ware' => 'inventory',
                                    ];
                                    $iconName = $catIcons[$item->category] ?? 'category';
                                @endphp
                                <tr 
                                    class="hover:bg-[#FAF2ED]/60 transition-colors group {{ $rowBg }}"
                                    data-id="{{ $item->ingredient_id }}"
                                    data-name="{{ strtolower($item->item_name) }}"
                                    data-sku="{{ strtolower($item->display_sku) }}"
                                    data-category="{{ $item->category ?? 'General' }}"
                                    data-status="{{ $statusClass }}"
                                    data-supplier="{{ $item->preferred_supplier ?? '' }}">
                                    
                                    <!-- Column 1: Ingredient & SKU -->
                                    <td class="py-3.5 px-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-9 h-9 rounded-xl bg-[#FAF2ED] border border-[#E7E5E4] flex items-center justify-center flex-shrink-0 text-[#3B2314] group-hover:bg-[#D97706] group-hover:text-white transition-colors">
                                                <span class="material-symbols-outlined text-[18px]">{{ $iconName }}</span>
                                            </div>
                                            <div class="min-w-0">
                                                <div class="font-heading font-bold text-xs text-[#1E1B18] truncate group-hover:text-[#D97706] transition-colors">
                                                    {{ $item->item_name }}
                                                </div>
                                                <div class="text-[11px] text-[#78716C] font-mono">
                                                    {{ $item->display_sku }} &bull; <span class="text-[#78716C] font-sans">{{ $item->storage_location ?? 'Storage' }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Column 2: Category -->
                                    <td class="py-3.5 px-3">
                                        <span class="inline-flex px-2 py-0.5 rounded-lg bg-[#FAF2ED] border border-[#E7E5E4] text-[11px] font-medium text-[#1E1B18] whitespace-nowrap">
                                            {{ $item->category ?? 'General' }}
                                        </span>
                                    </td>

                                    <!-- Column 3: In-Stock vs Capacity -->
                                    <td class="py-3.5 px-4">
                                        <div class="flex flex-col gap-1.5">
                                            <div class="flex justify-between items-center text-[11px]">
                                                <span class="font-semibold text-[#1E1B18] font-tabular">
                                                    {{ number_format($item->current_stock, 0) }} / {{ number_format($item->max_capacity ?: ($item->minimum_threshold * 4), 0) }} {{ $item->unit }}
                                                </span>
                                                <span class="font-bold font-tabular {{ $item->isOutOfStock() ? 'text-[#DC2626]' : ($item->isLowStock() ? 'text-[#EA580C]' : 'text-[#16A34A]') }}">
                                                    {{ $item->capacity_percentage }}%
                                                </span>
                                            </div>
                                            <div class="w-full bg-[#E7E5E4] h-2 rounded-full overflow-hidden">
                                                <div class="{{ $barBg }} h-full rounded-full transition-all duration-500" style="width: {{ $item->capacity_percentage }}%"></div>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Column 4: Unit -->
                                    <td class="py-3.5 px-3">
                                        <span class="font-mono text-[11px] font-semibold text-[#1E1B18] bg-[#F5F5F4] px-2 py-0.5 rounded-md border border-[#E7E5E4]">
                                            {{ $item->unit }}
                                        </span>
                                    </td>

                                    <!-- Column 5: Reorder Point (Minimum Threshold) -->
                                    <td class="py-3.5 px-3">
                                        <div class="font-semibold text-xs font-tabular text-[#1E1B18]">
                                            {{ number_format($item->minimum_threshold, 0) }} {{ $item->unit }}
                                        </div>
                                        @if($item->isLowStock() || $item->isOutOfStock())
                                            <span class="text-[10px] text-[#DC2626] font-semibold block leading-tight">Breached par!</span>
                                        @else
                                            <span class="text-[10px] text-[#78716C] block leading-tight">Safe buffer</span>
                                        @endif
                                    </td>

                                    <!-- Column 6: Status Badge -->
                                    <td class="py-3.5 px-3 text-center">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold border {{ $badgeBg }}">
                                            <span class="w-1.5 h-1.5 rounded-full bg-current {{ $item->isOutOfStock() ? 'animate-pulse' : '' }}"></span>
                                            {{ $statusClass }}
                                        </span>
                                    </td>

                                    <!-- Column 7: Actions -->
                                    <td class="py-3.5 px-4 text-right">
                                        <div class="inline-flex items-center gap-1">
                                            <!-- Quick Intake Button -->
                                            <button 
                                                type="button"
                                                @click="prefillIntake({{ $item->toJson() }})"
                                                title="Catat barang masuk (Intake)"
                                                class="px-2 py-1 rounded-lg bg-[#FAF2ED] hover:bg-[#D97706] hover:text-white text-[#1E1B18] border border-[#E7E5E4] text-[11px] font-semibold transition-colors flex items-center gap-1">
                                                <span class="material-symbols-outlined text-[14px]">add</span>
                                                <span>Intake</span>
                                            </button>

                                            <!-- Reorder Button -->
                                            <button 
                                                type="button"
                                                @click="prefillRestock({{ $item->toJson() }})"
                                                title="Buat pengajuan restock"
                                                class="px-2 py-1 rounded-lg bg-[#D97706]/10 hover:bg-[#D97706] hover:text-white text-[#D97706] text-[11px] font-semibold transition-colors flex items-center gap-1">
                                                <span class="material-symbols-outlined text-[14px]">local_shipping</span>
                                                <span>Reorder</span>
                                            </button>

                                            @if(auth()->user()->isManager())
                                            <!-- Manager Quick Adjust/Audit -->
                                            <button 
                                                type="button"
                                                @click="prefillAdjust({{ $item->toJson() }})"
                                                title="Audit & sesuaikan parameter stok"
                                                class="p-1 rounded-lg text-[#78716C] hover:text-[#1E1B18] hover:bg-[#FAF2ED] transition-colors">
                                                <span class="material-symbols-outlined text-[16px]">tune</span>
                                            </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-8 text-center text-xs text-[#78716C]">
                                        Belum ada data bahan baku mentah.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Table Footer -->
                <div class="px-5 py-3 bg-[#FAF2ED] border-t border-[#E7E5E4] flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-[#78716C]">
                    <span>Total terdaftar: <strong class="text-[#1E1B18] font-tabular">{{ $ingredients->count() }}</strong> bahan mentah aktif</span>
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center gap-1 text-[11px] text-[#16A34A] font-semibold">
                            <span class="w-2 h-2 rounded-full bg-[#16A34A]"></span> Optimal &gt; Threshold
                        </span>
                        <span class="inline-flex items-center gap-1 text-[11px] text-[#EA580C] font-semibold">
                            <span class="w-2 h-2 rounded-full bg-[#EA580C]"></span> Low &le; Threshold
                        </span>
                        <span class="inline-flex items-center gap-1 text-[11px] text-[#DC2626] font-semibold">
                            <span class="w-2 h-2 rounded-full bg-[#DC2626]"></span> Depleted = 0
                        </span>
                    </div>
                </div>
            </div>

            <!-- Card: Recent Inbound Stock Intake History (Audit Trail) -->
            <div class="bg-white rounded-2xl border border-[#E7E5E4] shadow-sm overflow-hidden">
                <div class="px-5 py-4 bg-[#FAF2ED] border-b border-[#E7E5E4] flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-800 flex items-center justify-center">
                            <span class="material-symbols-outlined text-[18px]">local_shipping</span>
                        </div>
                        <div>
                            <h3 class="font-heading font-bold text-sm text-[#1E1B18]">Riwayat Penerimaan Barang (Recent Stock Intakes)</h3>
                            <p class="text-[11px] text-[#78716C]">Log audit penerimaan fisik dari supplier yang menambah saldo stok</p>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs divide-y divide-[#E7E5E4]">
                        <thead class="bg-[#F5F5F4]/50 text-[11px] text-[#78716C] uppercase font-semibold">
                            <tr>
                                <th class="py-2.5 px-4">Waktu Terima</th>
                                <th class="py-2.5 px-4">Bahan Mentah</th>
                                <th class="py-2.5 px-3">Jumlah Masuk</th>
                                <th class="py-2.5 px-3">Supplier & Invoice</th>
                                <th class="py-2.5 px-3">Lokasi Simpan</th>
                                <th class="py-2.5 px-3">Pencatat</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#E7E5E4]">
                            @forelse($recentIntakes as $intake)
                                <tr class="hover:bg-[#FAF2ED]/50 transition-colors">
                                    <td class="py-3 px-4 font-mono text-[11px] text-[#78716C]">
                                        {{ $intake->created_at->format('d M Y, H:i') }}
                                    </td>
                                    <td class="py-3 px-4 font-semibold text-[#1E1B18]">
                                        {{ $intake->ingredient->item_name ?? 'Item' }}
                                    </td>
                                    <td class="py-3 px-3 font-tabular font-bold text-emerald-700">
                                        +{{ number_format($intake->quantity_received, 2) }} {{ $intake->unit }}
                                    </td>
                                    <td class="py-3 px-3">
                                        <div class="font-medium text-[#1E1B18]">{{ $intake->supplier_name ?? '-' }}</div>
                                        <div class="text-[10px] text-[#78716C] font-mono">{{ $intake->invoice_number ?? 'No INV' }}</div>
                                    </td>
                                    <td class="py-3 px-3 text-[#78716C]">
                                        {{ $intake->storage_location ?? '-' }}
                                    </td>
                                    <td class="py-3 px-3 text-[#78716C]">
                                        {{ $intake->receivedByUser->full_name ?? 'User' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-6 text-center text-xs text-[#78716C]">
                                        Belum ada catatan transaksi penerimaan stok barang masuk.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <!-- Main Right Column: Restock Approval Deck & Storage Bay Saturation (approx 30-32%) -->
        <div class="lg:col-span-4 flex flex-col gap-6 sticky top-20">

            <!-- Card: Restock Approval Deck (For Manager & Staff Visibility) -->
            <div class="bg-white rounded-2xl border border-[#E7E5E4] shadow-sm overflow-hidden flex flex-col">
                <!-- Requisitions Header -->
                <div class="p-4 bg-[#FAF2ED] border-b border-[#E7E5E4] flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <span class="material-symbols-outlined text-[#D97706] text-[22px]">order_approve</span>
                        <div>
                            <h3 class="font-heading font-bold text-sm text-[#1E1B18]">Restock Requisitions Deck</h3>
                            <p class="text-[11px] text-[#78716C]">Persetujuan pengadaan bahan baku kafe</p>
                        </div>
                    </div>
                    @if($pendingRestocks->count() > 0)
                        <span class="px-2.5 py-0.5 rounded-full bg-red-100 text-red-700 text-[11px] font-bold animate-pulse border border-red-200">
                            {{ $pendingRestocks->count() }} PENDING
                        </span>
                    @else
                        <span class="px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-700 text-[11px] font-bold border border-emerald-200">
                            ALL CLEAR
                        </span>
                    @endif
                </div>

                <!-- Requisitions List -->
                <div class="p-4 flex flex-col gap-3.5 max-h-[460px] overflow-y-auto">
                    @forelse($pendingRestocks as $req)
                        <div class="p-3.5 rounded-xl bg-[#FAF2ED]/60 border border-[#E7E5E4] hover:border-[#D97706]/40 transition-all flex flex-col gap-2.5 group">
                            <!-- Card Header: Title & Quantity -->
                            <div class="flex items-start justify-between gap-2">
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-red-500 animate-ping"></span>
                                    <span class="font-heading font-bold text-xs text-[#1E1B18] group-hover:text-[#D97706] transition-colors">
                                        {{ $req->ingredient->item_name }}
                                    </span>
                                </div>
                                <span class="text-xs font-bold font-tabular px-2 py-0.5 rounded-md bg-[#D97706] text-white">
                                    +{{ number_format($req->quantity_requested, 0) }} {{ $req->ingredient->unit }}
                                </span>
                            </div>

                            <!-- Supplier & Financial Info -->
                            <div class="text-[11px] text-[#78716C] flex flex-col gap-1">
                                <div class="flex items-center justify-between">
                                    <span class="flex items-center gap-1">
                                        <span class="material-symbols-outlined text-[14px] text-[#D97706]">storefront</span>
                                        {{ $req->ingredient->preferred_supplier ?? 'Distributor Utama' }}
                                    </span>
                                    <span class="font-bold text-[#1E1B18] font-tabular">
                                        Est. ${{ number_format($req->total_estimated_cost, 2) }}
                                    </span>
                                </div>
                                <div class="flex items-center justify-between text-[10px] text-[#78716C]">
                                    <span>Diajukan: {{ $req->requestedBy->full_name ?? 'Sistem Kasir' }}</span>
                                    <span class="font-mono">{{ $req->created_at->diffForHumans() }}</span>
                                </div>
                                @if($req->notes)
                                    <p class="text-[11px] text-[#78716C] bg-white p-2 rounded-lg border border-[#E7E5E4] mt-0.5 italic">
                                        "{{ $req->notes }}"
                                    </p>
                                @endif
                            </div>

                            <!-- Manager Approval Actions -->
                            @if(auth()->user()->isManager())
                                <div class="pt-2 border-t border-[#E7E5E4] flex items-center justify-between gap-2">
                                    <!-- Reject Form -->
                                    <form action="{{ route('inventory.restock.reject', $req->request_id) }}" method="POST" class="w-1/2" onsubmit="return confirm('Tolak pengajuan restock ini?')">
                                        @csrf
                                        <button 
                                            type="submit" 
                                            class="w-full py-1.5 px-3 rounded-lg border border-red-300 hover:bg-red-50 text-red-600 font-semibold text-[11px] transition-colors flex items-center justify-center gap-1">
                                            <span class="material-symbols-outlined text-[14px]">close</span>
                                            <span>Reject</span>
                                        </button>
                                    </form>

                                    <!-- Approve Form -->
                                    <form action="{{ route('inventory.restock.approve', $req->request_id) }}" method="POST" class="w-1/2">
                                        @csrf
                                        <button 
                                            type="submit" 
                                            class="w-full py-1.5 px-3 rounded-lg bg-emerald-700 hover:bg-emerald-800 text-white font-semibold text-[11px] shadow-sm transition-all flex items-center justify-center gap-1">
                                            <span class="material-symbols-outlined text-[14px]">check</span>
                                            <span>Approve</span>
                                        </button>
                                    </form>
                                </div>
                            @else
                                <div class="pt-2 border-t border-[#E7E5E4] text-[11px] text-[#78716C] flex items-center justify-between">
                                    <span class="inline-flex items-center gap-1 text-amber-700 font-medium">
                                        <span class="material-symbols-outlined text-[13px]">hourglass_empty</span>
                                        Menunggu Persetujuan Manager
                                    </span>
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="py-8 text-center text-xs text-[#78716C] flex flex-col items-center justify-center gap-2">
                            <div class="w-10 h-10 rounded-full bg-emerald-50 text-[#16A34A] flex items-center justify-center">
                                <span class="material-symbols-outlined text-[20px]">task_alt</span>
                            </div>
                            <span class="font-medium text-[#1E1B18]">Semua Requisition Sudah Disetujui</span>
                            <span class="text-[11px] text-[#78716C]">Tidak ada pengajuan bahan baku berstatus PENDING saat ini.</span>
                        </div>
                    @endforelse
                </div>

                <!-- Requisitions Deck Footer -->
                <div class="p-4 bg-[#FAF2ED] border-t border-[#E7E5E4] text-xs">
                    <div class="flex items-center justify-between text-[#78716C]">
                        <span>Otoritas Persetujuan:</span>
                        <span class="font-bold text-[#D97706]">Cafe Manager Only</span>
                    </div>
                </div>
            </div>

            <!-- Card: Storage Bay Saturation -->
            <div class="bg-white p-5 rounded-2xl border border-[#E7E5E4] shadow-sm flex flex-col gap-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[#3B2314] text-[20px]">warehouse</span>
                        <h3 class="font-heading font-bold text-sm text-[#1E1B18]">Storage Bay Saturation</h3>
                    </div>
                    <span class="text-[11px] text-[#78716C]">Live Telemetry</span>
                </div>

                <div class="space-y-3.5 text-xs">
                    <!-- Bay 1: Walk-In Cold Vault -->
                    <div>
                        <div class="flex justify-between items-center text-[11px] mb-1">
                            <span class="font-medium text-[#1E1B18]">Walk-In Cold Vault (Dairy & Syrups)</span>
                            <span class="font-bold text-[#16A34A] font-tabular">68% full</span>
                        </div>
                        <div class="w-full bg-[#E7E5E4] h-1.5 rounded-full overflow-hidden">
                            <div class="bg-[#16A34A] h-full rounded-full" style="width: 68%"></div>
                        </div>
                    </div>

                    <!-- Bay 2: Bean Degas Silo Room -->
                    <div>
                        <div class="flex justify-between items-center text-[11px] mb-1">
                            <span class="font-medium text-[#1E1B18]">Bean Degas & Silo Room (Roast Lots)</span>
                            <span class="font-bold text-[#D97706] font-tabular">50% full</span>
                        </div>
                        <div class="w-full bg-[#E7E5E4] h-1.5 rounded-full overflow-hidden">
                            <div class="bg-[#D97706] h-full rounded-full" style="width: 50%"></div>
                        </div>
                    </div>

                    <!-- Bay 3: Dry Goods Mezzanine -->
                    <div>
                        <div class="flex justify-between items-center text-[11px] mb-1">
                            <span class="font-medium text-[#1E1B18]">Dry Goods Mezzanine (Cups, Lids)</span>
                            <span class="font-bold text-[#EA580C] font-tabular">82% full</span>
                        </div>
                        <div class="w-full bg-[#E7E5E4] h-1.5 rounded-full overflow-hidden">
                            <div class="bg-[#EA580C] h-full rounded-full" style="width: 82%"></div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>

    <!-- MODAL 1: Record Stock Intake (Barang Masuk Fisik) -->
    <div 
        x-show="isRecordIntakeOpen" 
        x-cloak 
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-[#1E1B18]/50 backdrop-blur-sm"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0">

        <div 
            @click.away="isRecordIntakeOpen = false" 
            class="bg-white rounded-2xl shadow-2xl max-w-lg w-full border border-[#E7E5E4] overflow-hidden"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100">

            <!-- Modal Header -->
            <div class="p-5 bg-[#FAF2ED] border-b border-[#E7E5E4] flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-[#D97706] text-white flex items-center justify-center shadow-sm">
                        <span class="material-symbols-outlined text-[22px]">add_box</span>
                    </div>
                    <div>
                        <h3 class="font-heading font-bold text-base text-[#1E1B18]">Record Stock Intake</h3>
                        <p class="text-[11px] text-[#78716C]">Pencatatan penerimaan fisik barang masuk dari supplier</p>
                    </div>
                </div>
                <button @click="isRecordIntakeOpen = false" class="p-1 rounded-lg text-[#78716C] hover:text-[#1E1B18] hover:bg-white transition-colors">
                    <span class="material-symbols-outlined text-[20px]">close</span>
                </button>
            </div>

            <!-- Modal Form -->
            <form action="{{ route('inventory.intake') }}" method="POST" class="p-5 flex flex-col gap-4 text-xs">
                @csrf

                <!-- Select Ingredient -->
                <div>
                    <label class="block font-semibold text-[#1E1B18] mb-1 uppercase tracking-wider text-[11px]">Bahan Baku Mentah *</label>
                    <select 
                        name="ingredient_id" 
                        x-model="intakeForm.ingredient_id" 
                        @change="onIntakeIngredientChange()" 
                        required 
                        class="w-full p-2.5 bg-[#FAF2ED]/60 border border-[#E7E5E4] rounded-xl text-xs text-[#1E1B18] focus:outline-none focus:ring-2 focus:ring-[#D97706]">
                        <option value="">-- Pilih Bahan Baku --</option>
                        @foreach($allIngredients as $ing)
                            <option value="{{ $ing->ingredient_id }}" data-unit="{{ $ing->unit }}" data-supplier="{{ $ing->preferred_supplier }}" data-location="{{ $ing->storage_location }}">
                                {{ $ing->item_name }} (Stok saat ini: {{ number_format($ing->current_stock, 0) }} {{ $ing->unit }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Quantity & Unit -->
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-[#1E1B18] mb-1 uppercase tracking-wider text-[11px]">Jumlah Masuk *</label>
                        <input 
                            type="number" 
                            step="any" 
                            min="0.01" 
                            name="quantity_received" 
                            x-model="intakeForm.quantity_received" 
                            required 
                            placeholder="e.g. 5000" 
                            class="w-full p-2.5 bg-[#FAF2ED]/60 border border-[#E7E5E4] rounded-xl text-xs text-[#1E1B18] focus:outline-none focus:ring-2 focus:ring-[#D97706]">
                    </div>
                    <div>
                        <label class="block font-semibold text-[#1E1B18] mb-1 uppercase tracking-wider text-[11px]">Satuan</label>
                        <input 
                            type="text" 
                            name="unit" 
                            x-model="intakeForm.unit" 
                            readonly 
                            placeholder="grams / ml / pcs" 
                            class="w-full p-2.5 bg-[#F5F5F4] border border-[#E7E5E4] rounded-xl text-xs text-[#78716C] font-mono font-semibold">
                    </div>
                </div>

                <!-- Supplier Name & Invoice -->
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-[#1E1B18] mb-1 uppercase tracking-wider text-[11px]">Nama Supplier / Hub</label>
                        <input 
                            type="text" 
                            name="supplier_name" 
                            x-model="intakeForm.supplier_name" 
                            placeholder="e.g. OriginDirect Coffee Labs" 
                            class="w-full p-2.5 bg-[#FAF2ED]/60 border border-[#E7E5E4] rounded-xl text-xs text-[#1E1B18] focus:outline-none focus:ring-2 focus:ring-[#D97706]">
                    </div>
                    <div>
                        <label class="block font-semibold text-[#1E1B18] mb-1 uppercase tracking-wider text-[11px]">No. Invoice / Surat Jalan</label>
                        <input 
                            type="text" 
                            name="invoice_number" 
                            x-model="intakeForm.invoice_number" 
                            placeholder="PO-2026-889" 
                            class="w-full p-2.5 bg-[#FAF2ED]/60 border border-[#E7E5E4] rounded-xl text-xs text-[#1E1B18] focus:outline-none focus:ring-2 focus:ring-[#D97706]">
                    </div>
                </div>

                <!-- Storage Destination & Batch Number -->
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-[#1E1B18] mb-1 uppercase tracking-wider text-[11px]">Lokasi Ruang Simpan</label>
                        <select 
                            name="storage_location" 
                            x-model="intakeForm.storage_location" 
                            class="w-full p-2.5 bg-[#FAF2ED]/60 border border-[#E7E5E4] rounded-xl text-xs text-[#1E1B18] focus:outline-none focus:ring-2 focus:ring-[#D97706]">
                            <option value="Walk-In Cold Vault">Walk-In Cold Vault</option>
                            <option value="Bean Degas Silo Room">Bean Degas Silo Room</option>
                            <option value="Dry Goods Mezzanine">Dry Goods Mezzanine</option>
                            <option value="Main Bar Secondary Caddy">Main Bar Secondary Caddy</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold text-[#1E1B18] mb-1 uppercase tracking-wider text-[11px]">Lot / Batch Ref</label>
                        <input 
                            type="text" 
                            name="batch_number" 
                            x-model="intakeForm.batch_number" 
                            placeholder="LOT-ROAST-9902" 
                            class="w-full p-2.5 bg-[#FAF2ED]/60 border border-[#E7E5E4] rounded-xl text-xs text-[#1E1B18] focus:outline-none focus:ring-2 focus:ring-[#D97706]">
                    </div>
                </div>

                <!-- Optional: Tautkan dengan Pengajuan Restock -->
                <div>
                    <label class="block font-semibold text-[#1E1B18] mb-1 uppercase tracking-wider text-[11px]">Selesaikan Pengajuan Restock (Opsional)</label>
                    <select 
                        name="restock_request_id" 
                        x-model="intakeForm.restock_request_id" 
                        class="w-full p-2.5 bg-[#FAF2ED]/60 border border-[#E7E5E4] rounded-xl text-xs text-[#1E1B18] focus:outline-none focus:ring-2 focus:ring-[#D97706]">
                        <option value="">-- Tidak ditautkan dengan pengajuan spesifik --</option>
                        @foreach($allRestocks->whereIn('status', ['PENDING', 'APPROVED']) as $r)
                            <option value="{{ $r->request_id }}">
                                PO #{{ $r->request_id }} - {{ $r->ingredient->item_name }} (+{{ number_format($r->quantity_requested, 0) }} {{ $r->ingredient->unit }}) - Status: {{ $r->status }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Notes -->
                <div>
                    <label class="block font-semibold text-[#1E1B18] mb-1 uppercase tracking-wider text-[11px]">Catatan / Kondisi Fisik</label>
                    <textarea 
                        name="notes" 
                        rows="2" 
                        placeholder="Kondisi kemasan utuh, suhu penyimpanan -2°C..." 
                        class="w-full p-2.5 bg-[#FAF2ED]/60 border border-[#E7E5E4] rounded-xl text-xs text-[#1E1B18] focus:outline-none focus:ring-2 focus:ring-[#D97706]"></textarea>
                </div>

                <!-- Form Footer -->
                <div class="mt-2 flex items-center justify-end gap-2.5 pt-3 border-t border-[#E7E5E4]">
                    <button 
                        type="button" 
                        @click="isRecordIntakeOpen = false" 
                        class="px-4 py-2 rounded-xl border border-[#E7E5E4] hover:bg-[#FAF2ED] text-[#78716C] font-semibold text-xs transition-colors">
                        Batal
                    </button>
                    <button 
                        type="submit" 
                        class="px-5 py-2 rounded-xl bg-[#D97706] hover:bg-[#B45309] text-white font-semibold text-xs shadow-md transition-all active:scale-[0.98] flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[16px]">check_circle</span>
                        <span>Simpan & Tambah Saldo Stok</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 2: Request Restock Requisition (Staff / Manager) -->
    <div 
        x-show="isRequestRestockOpen" 
        x-cloak 
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-[#1E1B18]/50 backdrop-blur-sm"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0">

        <div 
            @click.away="isRequestRestockOpen = false" 
            class="bg-white rounded-2xl shadow-2xl max-w-md w-full border border-[#E7E5E4] overflow-hidden"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100">

            <!-- Modal Header -->
            <div class="p-5 bg-[#FAF2ED] border-b border-[#E7E5E4] flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-[#3B2314] text-white flex items-center justify-center shadow-sm">
                        <span class="material-symbols-outlined text-[22px]">local_shipping</span>
                    </div>
                    <div>
                        <h3 class="font-heading font-bold text-base text-[#1E1B18]">Ajukan Restock Reorder</h3>
                        <p class="text-[11px] text-[#78716C]">Buat pengajuan restock berstatus PENDING ke Manager</p>
                    </div>
                </div>
                <button @click="isRequestRestockOpen = false" class="p-1 rounded-lg text-[#78716C] hover:text-[#1E1B18] hover:bg-white transition-colors">
                    <span class="material-symbols-outlined text-[20px]">close</span>
                </button>
            </div>

            <!-- Modal Form -->
            <form action="{{ route('inventory.restock.store') }}" method="POST" class="p-5 flex flex-col gap-4 text-xs">
                @csrf

                <!-- Select Ingredient -->
                <div>
                    <label class="block font-semibold text-[#1E1B18] mb-1 uppercase tracking-wider text-[11px]">Bahan Baku *</label>
                    <select 
                        name="ingredient_id" 
                        x-model="restockForm.ingredient_id" 
                        @change="onRestockIngredientChange()" 
                        required 
                        class="w-full p-2.5 bg-[#FAF2ED]/60 border border-[#E7E5E4] rounded-xl text-xs text-[#1E1B18] focus:outline-none focus:ring-2 focus:ring-[#D97706]">
                        <option value="">-- Pilih Bahan Baku --</option>
                        @foreach($allIngredients as $ing)
                            <option value="{{ $ing->ingredient_id }}" data-unit="{{ $ing->unit }}" data-threshold="{{ $ing->minimum_threshold }}" data-current="{{ $ing->current_stock }}">
                                {{ $ing->item_name }} (Sisa: {{ number_format($ing->current_stock, 0) }} {{ $ing->unit }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Quantity Requested -->
                <div>
                    <label class="block font-semibold text-[#1E1B18] mb-1 uppercase tracking-wider text-[11px]">Jumlah yang Diajukan *</label>
                    <div class="relative">
                        <input 
                            type="number" 
                            step="any" 
                            min="0.01" 
                            name="quantity_requested" 
                            x-model="restockForm.quantity_requested" 
                            required 
                            placeholder="e.g. 5000" 
                            class="w-full p-2.5 bg-[#FAF2ED]/60 border border-[#E7E5E4] rounded-xl text-xs text-[#1E1B18] focus:outline-none focus:ring-2 focus:ring-[#D97706]">
                        <span class="absolute right-3.5 top-2.5 text-xs text-[#78716C] font-mono font-semibold" x-text="restockForm.unit"></span>
                    </div>
                </div>

                <!-- Notes / Reason -->
                <div>
                    <label class="block font-semibold text-[#1E1B18] mb-1 uppercase tracking-wider text-[11px]">Alasan Pengajuan / Keterangan</label>
                    <textarea 
                        name="notes" 
                        rows="3" 
                        x-model="restockForm.notes" 
                        placeholder="Stok menipis, persiapan lonjakan pesanan akhir pekan..." 
                        class="w-full p-2.5 bg-[#FAF2ED]/60 border border-[#E7E5E4] rounded-xl text-xs text-[#1E1B18] focus:outline-none focus:ring-2 focus:ring-[#D97706]"></textarea>
                </div>

                <!-- Footer -->
                <div class="mt-2 flex items-center justify-end gap-2.5 pt-3 border-t border-[#E7E5E4]">
                    <button 
                        type="button" 
                        @click="isRequestRestockOpen = false" 
                        class="px-4 py-2 rounded-xl border border-[#E7E5E4] hover:bg-[#FAF2ED] text-[#78716C] font-semibold text-xs transition-colors">
                        Batal
                    </button>
                    <button 
                        type="submit" 
                        class="px-5 py-2 rounded-xl bg-[#3B2314] hover:bg-[#2A180E] text-white font-semibold text-xs shadow-md transition-all active:scale-[0.98] flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[16px]">send</span>
                        <span>Kirim ke Cafe Manager</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    @if(auth()->user()->isManager())
    <!-- MODAL 3: Manager Quick Adjust / Audit Threshold -->
    <div 
        x-show="isAdjustOpen" 
        x-cloak 
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-[#1E1B18]/50 backdrop-blur-sm"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0">

        <div 
            @click.away="isAdjustOpen = false" 
            class="bg-white rounded-2xl shadow-2xl max-w-md w-full border border-[#E7E5E4] overflow-hidden"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100">

            <div class="p-5 bg-[#FAF2ED] border-b border-[#E7E5E4] flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-[#1E1B18] text-white flex items-center justify-center shadow-sm">
                        <span class="material-symbols-outlined text-[22px]">tune</span>
                    </div>
                    <div>
                        <h3 class="font-heading font-bold text-base text-[#1E1B18]">Audit & Parameter Stok</h3>
                        <p class="text-[11px] text-[#78716C]" x-text="adjustForm.itemName"></p>
                    </div>
                </div>
                <button @click="isAdjustOpen = false" class="p-1 rounded-lg text-[#78716C] hover:text-[#1E1B18] hover:bg-white transition-colors">
                    <span class="material-symbols-outlined text-[20px]">close</span>
                </button>
            </div>

            <form :action="'/inventory/ingredients/' + adjustForm.id" method="POST" class="p-5 flex flex-col gap-4 text-xs">
                @csrf
                @method('PUT')

                <div>
                    <label class="block font-semibold text-[#1E1B18] mb-1 uppercase tracking-wider text-[11px]">Ambang Batas Minimum (Threshold) *</label>
                    <input 
                        type="number" 
                        step="any" 
                        min="0" 
                        name="minimum_threshold" 
                        x-model="adjustForm.threshold" 
                        required 
                        class="w-full p-2.5 bg-[#FAF2ED]/60 border border-[#E7E5E4] rounded-xl text-xs text-[#1E1B18] focus:outline-none focus:ring-2 focus:ring-[#D97706]">
                    <span class="text-[10px] text-[#78716C] mt-1 block">Jika stok &le; angka ini, status berubah jadi Low Stock & draft PO terpicu.</span>
                </div>

                <div>
                    <label class="block font-semibold text-[#1E1B18] mb-1 uppercase tracking-wider text-[11px]">Kapasitas Maksimal Tangki/Gudang</label>
                    <input 
                        type="number" 
                        step="any" 
                        min="0" 
                        name="max_capacity" 
                        x-model="adjustForm.capacity" 
                        class="w-full p-2.5 bg-[#FAF2ED]/60 border border-[#E7E5E4] rounded-xl text-xs text-[#1E1B18] focus:outline-none focus:ring-2 focus:ring-[#D97706]">
                </div>

                <div>
                    <label class="block font-semibold text-[#1E1B18] mb-1 uppercase tracking-wider text-[11px]">Harga Pokok Satuan (Unit Cost USD/IDR)</label>
                    <input 
                        type="number" 
                        step="any" 
                        min="0" 
                        name="unit_cost" 
                        x-model="adjustForm.unitCost" 
                        class="w-full p-2.5 bg-[#FAF2ED]/60 border border-[#E7E5E4] rounded-xl text-xs text-[#1E1B18] focus:outline-none focus:ring-2 focus:ring-[#D97706]">
                </div>

                <div class="mt-2 flex items-center justify-end gap-2.5 pt-3 border-t border-[#E7E5E4]">
                    <button 
                        type="button" 
                        @click="isAdjustOpen = false" 
                        class="px-4 py-2 rounded-xl border border-[#E7E5E4] hover:bg-[#FAF2ED] text-[#78716C] font-semibold text-xs transition-colors">
                        Batal
                    </button>
                    <button 
                        type="submit" 
                        class="px-5 py-2 rounded-xl bg-[#D97706] hover:bg-[#B45309] text-white font-semibold text-xs shadow-md transition-all active:scale-[0.98]">
                        Perbarui Parameter
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

</div>
@endsection

@push('scripts')
<script>
function inventoryApp() {
    return {
        searchQuery: '',
        selectedCategory: 'All',
        selectedStatus: 'All',
        selectedSupplier: 'All',
        visibleCount: {{ $ingredients->count() }},

        // Modals states
        isRecordIntakeOpen: false,
        isRequestRestockOpen: false,
        isAdjustOpen: false,

        // Form models
        intakeForm: {
            ingredient_id: '',
            quantity_received: '',
            unit: '',
            supplier_name: '',
            invoice_number: '',
            storage_location: 'Walk-In Cold Vault',
            batch_number: '',
            restock_request_id: '',
        },

        restockForm: {
            ingredient_id: '',
            quantity_requested: '',
            unit: 'grams',
            notes: '',
        },

        adjustForm: {
            id: '',
            itemName: '',
            threshold: '',
            capacity: '',
            unitCost: '',
        },

        init() {
            this.filterRows();
        },

        filterRows() {
            const query = this.searchQuery.toLowerCase().trim();
            const cat = this.selectedCategory;
            const stat = this.selectedStatus;
            const supp = this.selectedSupplier;

            const rows = document.querySelectorAll('#inventoryTable tbody tr[data-id]');
            let count = 0;

            rows.forEach(row => {
                const name = row.getAttribute('data-name') || '';
                const sku = row.getAttribute('data-sku') || '';
                const rowCat = row.getAttribute('data-category') || '';
                const rowStat = row.getAttribute('data-status') || '';
                const rowSupp = row.getAttribute('data-supplier') || '';

                const matchesQuery = !query || name.includes(query) || sku.includes(query) || rowCat.toLowerCase().includes(query);
                const matchesCat = (cat === 'All') || (rowCat === cat);
                const matchesStat = (stat === 'All') || (rowStat === stat);
                const matchesSupp = (supp === 'All') || (rowSupp === supp);

                if (matchesQuery && matchesCat && matchesStat && matchesSupp) {
                    row.style.display = '';
                    count++;
                } else {
                    row.style.display = 'none';
                }
            });

            this.visibleCount = count;
        },

        resetFilters() {
            this.searchQuery = '';
            this.selectedCategory = 'All';
            this.selectedStatus = 'All';
            this.selectedSupplier = 'All';
            this.filterRows();
        },

        openRecordIntakeModal() {
            this.isRecordIntakeOpen = true;
        },

        openRestockModal() {
            this.isRequestRestockOpen = true;
        },

        prefillIntake(item) {
            this.intakeForm.ingredient_id = item.ingredient_id;
            this.intakeForm.unit = item.unit;
            this.intakeForm.supplier_name = item.preferred_supplier || '';
            this.intakeForm.storage_location = item.storage_location || 'Walk-In Cold Vault';
            this.intakeForm.quantity_received = '';
            this.intakeForm.invoice_number = '';
            this.intakeForm.batch_number = '';
            this.intakeForm.restock_request_id = '';
            this.isRecordIntakeOpen = true;
        },

        onIntakeIngredientChange() {
            const select = document.querySelector('select[name="ingredient_id"]');
            if (select && select.selectedOptions.length > 0) {
                const opt = select.selectedOptions[0];
                this.intakeForm.unit = opt.getAttribute('data-unit') || '';
                this.intakeForm.supplier_name = opt.getAttribute('data-supplier') || '';
                this.intakeForm.storage_location = opt.getAttribute('data-location') || 'Walk-In Cold Vault';
            }
        },

        prefillRestock(item) {
            this.restockForm.ingredient_id = item.ingredient_id;
            this.restockForm.unit = item.unit;
            this.restockForm.quantity_requested = item.minimum_threshold ? (item.minimum_threshold * 2) : 100;
            this.restockForm.notes = 'Persiapan restock bahan baku mentah ' + item.item_name;
            this.isRequestRestockOpen = true;
        },

        onRestockIngredientChange() {
            const select = document.querySelector('select[name="ingredient_id"]');
            if (select && select.selectedOptions.length > 0) {
                const opt = select.selectedOptions[0];
                this.restockForm.unit = opt.getAttribute('data-unit') || 'units';
            }
        },

        prefillAdjust(item) {
            this.adjustForm.id = item.ingredient_id;
            this.adjustForm.itemName = item.item_name + ' (' + item.unit + ')';
            this.adjustForm.threshold = item.minimum_threshold;
            this.adjustForm.capacity = item.max_capacity || '';
            this.adjustForm.unitCost = item.unit_cost || '';
            this.isAdjustOpen = true;
        }
    };
}
</script>
@endpush
