@extends('layouts.app')

@section('title', 'POS Terminal & Presensi Shift')

@section('content')
<script>
window.posConfig = {
    menuItems: {!! json_encode($menuData) !!},
    categories: {!! json_encode($categories) !!},
    nextOrderNumber: {{ (int)$nextOrderNumber }},
    isClockedIn: {{ $activeShift ? 'true' : 'false' }},
    clockInTime: '{{ $activeShift ? $activeShift->clock_in->toIso8601String() : '' }}',
    hourlyRate: {{ (float)$user->hourly_rate }},
    hasSchedule: {{ $todaySchedule ? 'true' : 'false' }},
    scheduleName: '{{ $todaySchedule ? addslashes($todaySchedule->shift_name) : 'Shift Tak Terjadwal' }}',
    scheduleStart: '{{ $todaySchedule ? $todaySchedule->start_time : '' }}',
    scheduleEnd: '{{ $todaySchedule ? $todaySchedule->end_time : '' }}',
    requiresOverrideSession: {{ session('requires_override') ? 'true' : 'false' }},
    csrfToken: '{{ csrf_token() }}',
    routes: {
        processOrder: '{{ route('api.pos.orders') }}',
        checkStock: '{{ route('api.pos.check-stock') }}',
        getMenu: '{{ route('api.pos.menu') }}',
        qrisGenerate: '{{ route('api.qris.generate') }}',
        qrisStatus: '{{ url('/api/qris/status') }}',
        qrisSimulate: '{{ route('api.qris.simulate') }}',
        receiptBase: '{{ url('/pos/receipt') }}'
    }
};
</script>

<div class="space-y-6" x-data="posAppHandler(window.posConfig)">
    <!-- ======================================================== -->
    <!-- 1. ACTIVE CASHIER & SHIFT ATTENDANCE BAR (SPRINT 3)       -->
    <!-- ======================================================== -->
    <div class="bg-white rounded-2xl p-4 sm:p-5 border border-[#E7E5E4] shadow-sm flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <!-- Avatar -->
            <div class="relative flex-shrink-0">
                <div class="w-13 h-13 sm:w-14 sm:h-14 rounded-2xl bg-gradient-to-br from-[#3B2314] to-[#2A180E] text-white flex items-center justify-center font-bold text-xl shadow-md border border-[#FAF2ED]/30">
                    {{ strtoupper(substr($user->full_name, 0, 1)) }}
                </div>
                <span class="absolute -bottom-1 -right-1 w-4 h-4 rounded-full border-2 border-white {{ $activeShift ? 'bg-[#16A34A] animate-pulse' : 'bg-amber-400' }}" title="{{ $activeShift ? 'Sedang Bertugas' : 'Belum Presensi' }}"></span>
            </div>

            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-[#FAF2ED] text-[#D97706] border border-[#D97706]/30">
                        Terminal POS Kasir #01
                    </span>
                    <span class="text-xs text-[#78716C] font-mono font-tabular">Rate: ${{ number_format($user->hourly_rate, 2) }}/jam</span>
                    @if($todaySchedule)
                        <span class="text-xs bg-slate-100 text-slate-700 px-2 py-0.5 rounded-md font-medium font-tabular">
                            Jadwal: {{ \Carbon\Carbon::parse($todaySchedule->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($todaySchedule->end_time)->format('H:i') }} WIB
                        </span>
                    @else
                        <span class="text-xs bg-amber-50 text-amber-700 px-2 py-0.5 rounded-md font-medium border border-amber-200">
                            Hari Ini: Shift Tak Terjadwal
                        </span>
                    @endif
                </div>
                <h1 class="text-lg sm:text-xl font-bold font-heading text-[#1E1B18] mt-0.5">{{ $user->full_name }}</h1>
                <p class="text-xs text-[#78716C]">
                    Role: <strong class="text-[#3B2314]">{{ $user->role }}</strong> &bull; 
                    {{ $todaySchedule ? $todaySchedule->shift_name : 'Tidak ada jadwal terdaftar untuk hari ini' }}
                </p>
            </div>
        </div>

        <!-- Shift Controls -->
        <div class="flex flex-wrap items-center gap-3">
            @if($activeShift)
                <!-- Clocked-In Active Indicator -->
                <div class="bg-[#FAF2ED] px-3.5 py-2 rounded-xl border border-[#E7E5E4] flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-[#16A34A] text-white flex items-center justify-center font-bold text-sm shadow-inner">
                        ⏱️
                    </div>
                    <div>
                        <div class="flex items-center gap-1.5">
                            <span class="text-[10px] uppercase font-bold text-[#78716C] tracking-wider">Durasi Shift Aktif</span>
                            @if($activeShift->status === 'LATE')
                                <span class="px-1.5 py-0.2 rounded text-[10px] font-bold bg-[#FFEDD5] text-[#EA580C]">LATE</span>
                            @elseif($activeShift->status === 'OVERTIME')
                                <span class="px-1.5 py-0.2 rounded text-[10px] font-bold bg-purple-100 text-purple-700">LEMBUR</span>
                            @else
                                <span class="px-1.5 py-0.2 rounded text-[10px] font-bold bg-[#DCFCE7] text-[#16A34A]">ON TIME</span>
                            @endif
                        </div>
                        <p class="text-sm font-extrabold text-[#3B2314] font-tabular flex items-center gap-1.5" x-text="liveDurationText">
                            Menghitung...
                        </p>
                    </div>
                </div>

                <!-- Clock Out Trigger Button -->
                <button type="button" @click="openClockOutModal()" class="px-3.5 py-2 rounded-xl bg-[#FEE2E2] text-[#DC2626] hover:bg-[#DC2626] hover:text-white font-semibold text-xs transition-all flex items-center gap-1.5 shadow-sm border border-[#DC2626]/20 active:scale-95">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    <span>Clock Out</span>
                </button>
            @else
                <!-- Not Clocked In Indicator -->
                <div class="bg-amber-50 px-3.5 py-2 rounded-xl border border-amber-200 flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-lg bg-[#D97706] text-white flex items-center justify-center font-bold text-sm shadow-inner">
                        ⚠️
                    </div>
                    <div>
                        <p class="text-[10px] uppercase font-bold text-amber-800 tracking-wider">Status Presensi</p>
                        <p class="text-xs font-bold text-amber-900">Belum Clock-In</p>
                    </div>
                </div>

                <!-- Clock In Trigger Button -->
                <button type="button" @click="openClockInModal()" class="px-4 py-2 rounded-xl bg-[#D97706] text-white hover:bg-[#B45309] font-semibold text-xs transition-all flex items-center gap-1.5 shadow-md hover:shadow-lg active:scale-95">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                    </svg>
                    <span>Clock In Shift</span>
                </button>
            @endif
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- 2. POS TERMINAL 2-COLUMN SPLIT LAYOUT (SPRINT 4)          -->
    <!-- ======================================================== -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        <!-- SISI KIRI: KATALOG MENU & SEARCH (8 Kolom Desktop) -->
        <div class="lg:col-span-7 xl:col-span-8 space-y-4">
            
            <!-- Search Bar & Statistics Header -->
            <div class="bg-white p-4 rounded-2xl border border-[#E7E5E4] shadow-sm flex flex-col sm:flex-row gap-3 items-center justify-between">
                <!-- Search Box -->
                <div class="relative w-full sm:w-80">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#78716C]">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <input 
                        type="text" 
                        x-model="searchQuery" 
                        placeholder="Cari menu (e.g. Espresso, Croissant)..." 
                        class="w-full pl-10 pr-9 py-2.5 rounded-xl border border-[#E7E5E4] focus:ring-2 focus:ring-[#D97706] focus:border-[#D97706] text-sm bg-[#FDFBF7]/60 placeholder-[#78716C] transition-all"
                    >
                    <button 
                        x-show="searchQuery.length > 0" 
                        @click="searchQuery = ''" 
                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-[#78716C] hover:text-[#1E1B18]"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <!-- Catalog Counter & Status -->
                <div class="flex items-center gap-2 text-xs text-[#78716C] self-end sm:self-center">
                    <span>Menampilkan:</span>
                    <span class="px-2.5 py-1 rounded-full bg-[#FAF2ED] text-[#3B2314] font-bold font-tabular border border-[#E7E5E4]" x-text="filteredMenuItems.length + ' item'">
                        0 item
                    </span>
                    <button 
                        @click="refreshMenuData()" 
                        title="Perbarui data stok menu" 
                        class="p-1.5 rounded-lg text-[#78716C] hover:text-[#D97706] hover:bg-[#FAF2ED] transition-colors"
                        :class="isLoadingMenu ? 'animate-spin text-[#D97706]' : ''"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Filter Kategori Pill Chips (Stitch Design) -->
            <div class="bg-white p-2 rounded-2xl border border-[#E7E5E4] shadow-sm">
                <div class="bg-[#F4ECE7] p-1.5 rounded-xl flex gap-1.5 overflow-x-auto no-scrollbar">
                    <button 
                        type="button"
                        @click="selectedCategory = 'ALL'"
                        class="px-4 py-2 rounded-lg text-xs font-semibold transition-all whitespace-nowrap flex items-center gap-1.5"
                        :class="selectedCategory === 'ALL' ? 'bg-[#3B2314] text-white shadow-sm' : 'text-[#78716C] hover:text-[#1E1B18] hover:bg-white/50'"
                    >
                        <span>Semua Kategori</span>
                        <span class="px-1.5 py-0.2 rounded-full text-[10px] font-tabular" :class="selectedCategory === 'ALL' ? 'bg-[#D97706] text-white' : 'bg-white text-[#78716C]'" x-text="menuItems.length">
                            0
                        </span>
                    </button>

                    <template x-for="category in categories" :key="category">
                        <button 
                            type="button"
                            @click="selectedCategory = category"
                            class="px-3.5 py-2 rounded-lg text-xs font-semibold transition-all whitespace-nowrap flex items-center gap-1.5"
                            :class="selectedCategory === category ? 'bg-[#3B2314] text-white shadow-sm' : 'text-[#78716C] hover:text-[#1E1B18] hover:bg-white/50'"
                        >
                            <span x-text="category"></span>
                            <span class="px-1.5 py-0.2 rounded-full text-[10px] font-tabular" :class="selectedCategory === category ? 'bg-[#D97706] text-white' : 'bg-white text-[#78716C]'" x-text="countCategory(category)">
                                0
                            </span>
                        </button>
                    </template>
                </div>
            </div>

            <!-- Grid Kartu Menu POS dengan Live Stock Badge -->
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
                <template x-for="item in filteredMenuItems" :key="item.menu_id">
                    <div 
                        class="bg-white rounded-2xl border transition-all duration-200 p-4 flex flex-col justify-between group"
                        :class="{
                            'border-[#E7E5E4] hover:border-[#D97706] hover:shadow-md': item.max_servings > 0,
                            'border-red-200 bg-slate-50/70 opacity-65': item.max_servings === 0
                        }"
                    >
                        <div>
                            <!-- Thumbnail & Badges Container -->
                            <div class="relative w-full h-36 rounded-xl bg-[#FAF2ED] overflow-hidden border border-[#E7E5E4] mb-3">
                                <img 
                                    :src="item.image_url" 
                                    :alt="item.item_name" 
                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                    loading="lazy"
                                >

                                <!-- Category Chip (Top Left) -->
                                <span class="absolute top-2 left-2 px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-[#3B2314]/80 text-white backdrop-blur-sm shadow-sm" x-text="item.category">
                                </span>

                                <!-- Live Stock Badge (Top Right) -->
                                <div class="absolute top-2 right-2">
                                    <template x-if="item.max_servings > 10">
                                        <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-[#DCFCE7] text-[#16A34A] border border-[#16A34A]/30 backdrop-blur-sm shadow-sm flex items-center gap-1 font-tabular">
                                            <span class="w-1.5 h-1.5 rounded-full bg-[#16A34A]"></span>
                                            <span x-text="item.max_servings + ' porsi'"></span>
                                        </span>
                                    </template>
                                    <template x-if="item.max_servings > 0 && item.max_servings <= 10">
                                        <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-[#FFEDD5] text-[#EA580C] border border-[#EA580C]/30 backdrop-blur-sm shadow-sm flex items-center gap-1 font-tabular animate-pulse">
                                            <span class="w-1.5 h-1.5 rounded-full bg-[#EA580C]"></span>
                                            <span x-text="'Sisa ' + item.max_servings + ' porsi'"></span>
                                        </span>
                                    </template>
                                    <template x-if="item.max_servings === 0">
                                        <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-[#FEE2E2] text-[#DC2626] border border-[#DC2626]/30 backdrop-blur-sm shadow-sm flex items-center gap-1">
                                            <span>✕ Habis</span>
                                        </span>
                                    </template>
                                </div>
                            </div>

                            <!-- Menu Information -->
                            <h3 class="text-sm font-bold font-heading text-[#1E1B18] line-clamp-1" x-text="item.item_name"></h3>
                            <p class="text-[11px] text-[#78716C] mt-0.5" x-text="item.recipes_count + ' Takaran Bahan Terkait'"></p>
                        </div>

                        <!-- Price & Add Button -->
                        <div class="mt-4 pt-3 border-t border-[#E7E5E4] flex items-center justify-between">
                            <div>
                                <span class="text-[10px] text-[#78716C] uppercase font-bold block">Harga</span>
                                <span class="text-lg font-extrabold text-[#D97706] font-tabular" x-text="'$' + item.price.toFixed(2)"></span>
                            </div>

                            <!-- Early Stock Check CTA Button -->
                            <template x-if="item.max_servings > 0">
                                <button 
                                    type="button" 
                                    @click="addToCart(item)"
                                    class="px-3.5 py-2 rounded-xl bg-[#D97706] text-white hover:bg-[#B45309] font-semibold text-xs transition-all shadow-sm flex items-center gap-1.5 active:scale-95 group-hover:shadow"
                                >
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    <span>Tambah</span>
                                </button>
                            </template>

                            <template x-if="item.max_servings === 0">
                                <button 
                                    type="button" 
                                    disabled
                                    class="px-3.5 py-2 rounded-xl bg-slate-200 text-slate-500 font-medium text-xs cursor-not-allowed"
                                >
                                    Stok Habis
                                </button>
                            </template>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Empty State jika pencarian tidak menemukan hasil -->
            <div x-show="filteredMenuItems.length === 0" class="bg-white rounded-2xl p-10 border border-[#E7E5E4] text-center space-y-3">
                <div class="w-12 h-12 rounded-2xl bg-amber-50 text-[#D97706] flex items-center justify-center mx-auto text-xl font-bold">
                    ☕
                </div>
                <h4 class="text-base font-bold text-[#1E1B18] font-heading">Tidak Ada Menu yang Sesuai</h4>
                <p class="text-xs text-[#78716C] max-w-sm mx-auto">
                    Katalog untuk kata kunci atau kategori ini tidak ditemukan. Coba bersihkan pencarian atau pilih kategori lain.
                </p>
                <button 
                    type="button" 
                    @click="searchQuery = ''; selectedCategory = 'ALL'"
                    class="px-4 py-2 rounded-xl bg-[#FAF2ED] text-[#D97706] font-semibold text-xs border border-[#D97706]/30 hover:bg-[#D97706] hover:text-white transition-colors"
                >
                    Reset Filter
                </button>
            </div>
        </div>

        <!-- SISI KANAN: LIVE CART & CHECKOUT PANEL (4-5 Kolom Desktop) -->
        <div class="lg:col-span-5 xl:col-span-4 sticky top-20 space-y-4">
            
            <div class="bg-white rounded-2xl border border-[#E7E5E4] shadow-sm p-5 space-y-4">
                
                <!-- Cart Header -->
                <div class="flex items-center justify-between pb-3 border-b border-[#E7E5E4]">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-[#FAF2ED] text-[#D97706] flex items-center justify-center font-bold text-sm">
                            🛒
                        </div>
                        <div>
                            <h2 class="text-base font-bold font-heading text-[#1E1B18]">Keranjang Pesanan</h2>
                            <span class="text-[11px] text-[#78716C] font-tabular" x-text="'Order #' + String(nextOrderNumber).padStart(4, '0')"></span>
                        </div>
                    </div>

                    <template x-if="cart.length > 0">
                        <button 
                            type="button" 
                            @click="clearCart()" 
                            class="text-xs text-[#DC2626] hover:underline font-medium"
                        >
                            Kosongkan
                        </button>
                    </template>
                </div>

                <!-- Clock-In Warning if Not Clocked In -->
                <template x-if="!isClockedIn">
                    <div class="p-3 rounded-xl bg-amber-50 border border-amber-300 text-xs text-amber-900 flex items-start gap-2">
                        <span class="text-base">⚠️</span>
                        <div>
                            <strong class="font-bold">Presensi Diperlukan:</strong>
                            <p class="text-[11px] text-amber-800 mt-0.5">Anda belum Clock-In shift. Silakan lakukan Clock-In sebelum menyelesaikan transaksi kasir.</p>
                            <button type="button" @click="openClockInModal()" class="mt-1 text-[11px] font-bold text-[#D97706] underline hover:text-[#B45309]">
                                Clock In Sekarang →
                            </button>
                        </div>
                    </div>
                </template>

                <!-- Cart Items List -->
                <div class="space-y-3 max-h-[340px] overflow-y-auto pr-1">
                    <!-- Empty Cart State -->
                    <template x-if="cart.length === 0">
                        <div class="py-12 text-center space-y-2">
                            <div class="w-12 h-12 rounded-full bg-[#FAF2ED] text-[#D97706] flex items-center justify-center mx-auto text-xl">
                                ☕
                            </div>
                            <p class="text-sm font-semibold text-[#1E1B18]">Keranjang Masih Kosong</p>
                            <p class="text-xs text-[#78716C] max-w-[220px] mx-auto">
                                Klik tombol <strong>+ Tambah</strong> pada kartu produk di sebelah kiri untuk memulai pesanan.
                            </p>
                        </div>
                    </template>

                    <!-- Cart Item Rows -->
                    <template x-for="(item, index) in cart" :key="item.menu_id">
                        <div class="p-3 rounded-xl bg-[#FDFBF7] border border-[#E7E5E4] flex items-center justify-between gap-3">
                            <div class="min-w-0 flex-grow">
                                <h4 class="text-xs font-bold text-[#1E1B18] truncate" x-text="item.item_name"></h4>
                                <span class="text-[11px] text-[#78716C] font-tabular" x-text="'$' + item.price.toFixed(2) + ' / pcs'"></span>
                            </div>

                            <!-- Stepper Controls (+ / -) -->
                            <div class="flex items-center gap-1.5 flex-shrink-0">
                                <button 
                                    type="button" 
                                    @click="decrementItem(index)"
                                    class="w-7 h-7 rounded-lg bg-white border border-[#E7E5E4] text-[#1E1B18] hover:bg-[#FAF2ED] font-bold text-xs flex items-center justify-center transition-colors shadow-xs active:scale-95"
                                >
                                    -
                                </button>
                                <span class="w-7 text-center font-extrabold text-xs font-tabular text-[#3B2314]" x-text="item.quantity"></span>
                                <button 
                                    type="button" 
                                    @click="incrementItem(index)"
                                    class="w-7 h-7 rounded-lg bg-white border border-[#E7E5E4] text-[#1E1B18] hover:bg-[#FAF2ED] font-bold text-xs flex items-center justify-center transition-colors shadow-xs active:scale-95"
                                    :disabled="item.quantity >= item.max_servings"
                                    :class="item.quantity >= item.max_servings ? 'opacity-40 cursor-not-allowed' : ''"
                                >
                                    +
                                </button>
                            </div>

                            <!-- Subtotal & Remove -->
                            <div class="text-right flex-shrink-0 flex items-center gap-2">
                                <span class="text-xs font-extrabold text-[#3B2314] font-tabular" x-text="'$' + (item.price * item.quantity).toFixed(2)"></span>
                                <button 
                                    type="button" 
                                    @click="removeItem(index)" 
                                    class="text-slate-400 hover:text-[#DC2626] transition-colors p-1"
                                    title="Hapus item"
                                >
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Financial Calculation Breakdown -->
                <div class="pt-3 border-t border-[#E7E5E4] space-y-2 text-xs">
                    <div class="flex items-center justify-between text-[#78716C]">
                        <span>Subtotal Item</span>
                        <span class="font-semibold font-tabular text-[#1E1B18]" x-text="'$' + cartSubtotal.toFixed(2)">$0.00</span>
                    </div>
                    <div class="flex items-center justify-between text-[#78716C]">
                        <span>Pajak Restoran (PB1 10%)</span>
                        <span class="font-semibold font-tabular text-[#1E1B18]" x-text="'$' + cartTax.toFixed(2)">$0.00</span>
                    </div>
                    <div class="pt-2 border-t border-[#E7E5E4] flex items-center justify-between">
                        <span class="text-sm font-bold font-heading text-[#3B2314]">Grand Total</span>
                        <span class="text-2xl font-extrabold font-heading text-[#3B2314] font-tabular" x-text="'$' + cartGrandTotal.toFixed(2)">$0.00</span>
                    </div>
                </div>

                <!-- Payment Method Toggle Tabs -->
                <div class="space-y-3 pt-2">
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-[#78716C]">Metode Pembayaran</label>
                    <div class="grid grid-cols-2 gap-2">
                        <button 
                            type="button" 
                            @click="paymentMethod = 'CASH'"
                            class="p-2.5 rounded-xl border text-xs font-semibold flex items-center justify-center gap-2 transition-all"
                            :class="paymentMethod === 'CASH' ? 'bg-[#3B2314] text-white border-[#3B2314] shadow-sm' : 'bg-white border-[#E7E5E4] text-[#1E1B18] hover:bg-[#FAF2ED]'"
                        >
                            <span>💵 Tunai (Cash)</span>
                        </button>
                        <button 
                            type="button" 
                            @click="paymentMethod = 'QRIS'"
                            class="p-2.5 rounded-xl border text-xs font-semibold flex items-center justify-center gap-2 transition-all"
                            :class="paymentMethod === 'QRIS' ? 'bg-[#3B2314] text-white border-[#3B2314] shadow-sm' : 'bg-white border-[#E7E5E4] text-[#1E1B18] hover:bg-[#FAF2ED]'"
                        >
                            <span>📱 Dynamic QRIS</span>
                        </button>
                    </div>

                    <!-- CASH DRAWER: Calculator & Change Due -->
                    <div x-show="paymentMethod === 'CASH'" class="p-3.5 rounded-xl bg-[#FAF2ED] border border-[#E7E5E4] space-y-3">
                        <div>
                            <label class="block text-[10px] uppercase font-bold text-[#78716C] mb-1">Uang Tunai Diserahkan (Cash Tendered)</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-[#78716C] font-bold text-sm">$</span>
                                <input 
                                    type="number" 
                                    step="0.01" 
                                    min="0"
                                    x-model.number="cashTendered"
                                    placeholder="0.00" 
                                    class="w-full pl-7 pr-3 py-2 rounded-lg border border-[#E7E5E4] text-sm font-tabular font-bold bg-white text-[#1E1B18] focus:ring-2 focus:ring-[#D97706]"
                                >
                            </div>
                        </div>

                        <!-- Quick Cash Tender Preset Pills -->
                        <div class="flex flex-wrap gap-1.5">
                            <button 
                                type="button" 
                                @click="cashTendered = cartGrandTotal"
                                class="px-2.5 py-1 rounded-md text-[11px] font-semibold bg-white border border-[#E7E5E4] hover:bg-amber-100 hover:border-amber-300 text-[#3B2314] transition-colors"
                            >
                                Uang Pas
                            </button>
                            <button 
                                type="button" 
                                @click="cashTendered = 10"
                                class="px-2.5 py-1 rounded-md text-[11px] font-semibold bg-white border border-[#E7E5E4] hover:bg-amber-100 text-[#3B2314] font-tabular"
                            >
                                $10
                            </button>
                            <button 
                                type="button" 
                                @click="cashTendered = 20"
                                class="px-2.5 py-1 rounded-md text-[11px] font-semibold bg-white border border-[#E7E5E4] hover:bg-amber-100 text-[#3B2314] font-tabular"
                            >
                                $20
                            </button>
                            <button 
                                type="button" 
                                @click="cashTendered = 50"
                                class="px-2.5 py-1 rounded-md text-[11px] font-semibold bg-white border border-[#E7E5E4] hover:bg-amber-100 text-[#3B2314] font-tabular"
                            >
                                $50
                            </button>
                            <button 
                                type="button" 
                                @click="cashTendered = 100"
                                class="px-2.5 py-1 rounded-md text-[11px] font-semibold bg-white border border-[#E7E5E4] hover:bg-amber-100 text-[#3B2314] font-tabular"
                            >
                                $100
                            </button>
                        </div>

                        <!-- Change Due Display Box -->
                        <div class="p-2.5 rounded-lg border flex items-center justify-between" :class="cashTendered >= cartGrandTotal ? 'bg-[#DCFCE7] border-[#16A34A]/30 text-emerald-900' : 'bg-[#FFEDD5] border-[#EA580C]/30 text-orange-900'">
                            <div>
                                <span class="text-[10px] uppercase font-bold block" x-text="cashTendered >= cartGrandTotal ? 'Uang Kembalian (Change Due)' : 'Kurang Bayar'"></span>
                                <span class="text-base font-extrabold font-tabular" x-text="'$' + Math.abs(cashTendered - cartGrandTotal).toFixed(2)">$0.00</span>
                            </div>
                            <span class="text-xs font-bold" x-text="cashTendered >= cartGrandTotal ? '✓ Lunas' : 'Kurang'"></span>
                        </div>

                        <!-- Action Submit Cash Button -->
                        <button 
                            type="button" 
                            @click="submitCashOrder()"
                            :disabled="cart.length === 0 || cashTendered < cartGrandTotal || isProcessingOrder || !isClockedIn"
                            class="w-full py-3 rounded-xl bg-[#D97706] text-white hover:bg-[#B45309] font-bold text-sm shadow-md transition-all flex items-center justify-center gap-2 active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <span x-show="!isProcessingOrder">💵 Bayar Tunai & Cetak Struk</span>
                            <span x-show="isProcessingOrder">Memproses Transaksi...</span>
                        </button>
                    </div>

                    <!-- QRIS DRAWER -->
                    <div x-show="paymentMethod === 'QRIS'" class="p-3.5 rounded-xl bg-[#FAF2ED] border border-[#E7E5E4] space-y-3">
                        <div class="text-xs text-[#78716C] space-y-1">
                            <p class="font-bold text-[#1E1B18]">📱 Dynamic QRIS Payment Gateway</p>
                            <p class="text-[11px]">Sistem akan menghasilkan QR Code dinamis dengan masa berlaku 3 menit (180 detik) dan polling otomatis.</p>
                        </div>

                        <button 
                            type="button" 
                            @click="initiateQRISPayment()"
                            :disabled="cart.length === 0 || isProcessingOrder || !isClockedIn"
                            class="w-full py-3 rounded-xl bg-[#3B2314] text-white hover:bg-[#2A180E] font-bold text-sm shadow-md transition-all flex items-center justify-center gap-2 active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <span x-show="!isProcessingOrder">⚡ Tampilkan QR Code QRIS</span>
                            <span x-show="isProcessingOrder">Menyiapkan QR Code...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- 3. MODAL QRIS DINAMIS & POLLING STATUS (3 MENIT)          -->
    <!-- ======================================================== -->
    <div x-show="showQRISModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-qris-title" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-[#3B2314]/70 backdrop-blur-sm transition-opacity" @click="closeQRISModal()"></div>

        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md border border-[#E7E5E4]">
                <!-- Header Modal -->
                <div class="bg-[#3B2314] px-6 py-4 text-white flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-[#D97706] flex items-center justify-center font-bold text-sm shadow">
                            QR
                        </div>
                        <div>
                            <h3 class="text-base font-bold font-heading text-white" id="modal-qris-title">Pembayaran QRIS Dinamis</h3>
                            <p class="text-xs text-[#FAF2ED]/70 font-tabular" x-text="'Ref: ' + (qrisData.transaction_id || 'Membuat...')"></p>
                        </div>
                    </div>
                    <button type="button" @click="closeQRISModal()" class="text-[#FAF2ED]/70 hover:text-white p-1 rounded-lg">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div class="p-6 text-center space-y-4">
                    <!-- Grand Total Tagihan -->
                    <div>
                        <span class="text-xs uppercase font-bold text-[#78716C] tracking-wider block">Total Tagihan</span>
                        <p class="text-3xl font-extrabold text-[#3B2314] font-tabular mt-0.5" x-text="'$' + cartGrandTotal.toFixed(2)"></p>
                    </div>

                    <!-- Countdown Timer 180s -->
                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-[#78716C] font-medium">Batas Waktu Pembayaran:</span>
                            <span class="font-extrabold font-tabular text-sm" :class="qrisRemainingSeconds < 30 ? 'text-red-600 animate-pulse' : 'text-[#3B2314]'" x-text="qrisTimerDisplay">
                                03:00
                            </span>
                        </div>
                        <!-- Progress bar -->
                        <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden border border-slate-200">
                            <div 
                                class="h-full rounded-full transition-all duration-1000"
                                :class="qrisRemainingSeconds < 30 ? 'bg-red-500' : 'bg-[#D97706]'"
                                :style="'width: ' + ((qrisRemainingSeconds / 180) * 100) + '%'"
                            ></div>
                        </div>
                    </div>

                    <!-- QR Code Render Canvas / SVG -->
                    <div class="w-60 h-60 mx-auto bg-white p-3 rounded-2xl border-2 border-[#3B2314]/20 shadow-inner flex items-center justify-center relative">
                        <template x-if="qrisData.qr_image_url && !isQRISTimeout">
                            <img :src="qrisData.qr_image_url" alt="QRIS Code" class="w-full h-full object-contain">
                        </template>

                        <template x-if="!qrisData.qr_image_url && !isQRISTimeout">
                            <div class="animate-pulse flex flex-col items-center gap-2 text-slate-400">
                                <svg class="w-8 h-8 animate-spin text-[#D97706]" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                </svg>
                                <span class="text-xs">Membuat QR Code...</span>
                            </div>
                        </template>

                        <!-- Timeout Overlay -->
                        <template x-if="isQRISTimeout">
                            <div class="absolute inset-0 bg-white/90 backdrop-blur-xs rounded-2xl flex flex-col items-center justify-center p-4 text-center space-y-2">
                                <span class="text-2xl">⌛</span>
                                <h4 class="text-sm font-bold text-red-600">Waktu Pembayaran Habis</h4>
                                <p class="text-[11px] text-[#78716C]">Sesi 3 menit telah berakhir. Silakan generate ulang QR atau bayar tunai.</p>
                                <button 
                                    type="button" 
                                    @click="initiateQRISPayment()"
                                    class="px-3.5 py-1.5 rounded-lg bg-[#D97706] text-white text-xs font-bold shadow hover:bg-[#B45309]"
                                >
                                    Generate Ulang QR
                                </button>
                            </div>
                        </template>
                    </div>

                    <p class="text-xs text-[#78716C]">
                        Pindai QR menggunakan aplikasi mobile banking atau e-wallet (BCA, Mandiri, GoPay, OVO, Dana).
                    </p>

                    <!-- Polling Status Indicator -->
                    <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-center gap-2 text-xs text-slate-600">
                        <span class="w-2 h-2 rounded-full bg-[#16A34A] animate-ping"></span>
                        <span x-text="qrisPollingStatusText">Menunggu konfirmasi pembayaran pelanggan...</span>
                    </div>

                    <!-- Simulator Button for Demo/Testing -->
                    <div class="pt-2 border-t border-[#E7E5E4]">
                        <button 
                            type="button" 
                            @click="simulateQRISSuccess()"
                            class="w-full py-2 px-3 rounded-lg bg-emerald-50 text-emerald-800 hover:bg-emerald-100 border border-emerald-200 text-xs font-semibold flex items-center justify-center gap-1.5 transition-colors"
                        >
                            <span>⚡ Simulasikan Pembayaran Berhasil (Testing Kasir)</span>
                        </button>
                    </div>
                </div>

                <div class="bg-slate-50 px-6 py-3 border-t border-[#E7E5E4] flex items-center justify-between">
                    <button 
                        type="button" 
                        @click="closeQRISModal(); paymentMethod = 'CASH'"
                        class="text-xs font-semibold text-[#78716C] hover:text-[#1E1B18]"
                    >
                        Beralih ke Tunai (Cash)
                    </button>
                    <button 
                        type="button" 
                        @click="closeQRISModal()"
                        class="px-4 py-1.5 rounded-lg border border-[#E7E5E4] text-xs font-semibold text-[#78716C] hover:bg-white"
                    >
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- 4. MODAL TRANSAKSI BERHASIL & CETAK STRUK THERMAL          -->
    <!-- ======================================================== -->
    <div x-show="showSuccessModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-success-title" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-[#3B2314]/70 backdrop-blur-sm transition-opacity" @click="closeSuccessModal()"></div>

        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md border border-[#E7E5E4]">
                <!-- Success Banner -->
                <div class="bg-gradient-to-br from-[#16A34A] to-emerald-700 p-6 text-white text-center space-y-2">
                    <div class="w-16 h-16 rounded-full bg-white/20 text-white flex items-center justify-center mx-auto text-3xl font-bold shadow-inner">
                        ✓
                    </div>
                    <h3 class="text-xl font-bold font-heading" id="modal-success-title">Transaksi Berhasil!</h3>
                    <p class="text-xs text-emerald-100">Stok bahan baku telah dipotong secara atomik.</p>
                </div>

                <div class="p-6 space-y-4" x-show="lastOrderData">
                    <!-- Order Detail Card -->
                    <div class="p-4 rounded-xl bg-[#FAF2ED] border border-[#E7E5E4] space-y-2 text-xs">
                        <div class="flex justify-between pb-2 border-b border-[#E7E5E4]">
                            <span class="text-[#78716C]">Nomor Pesanan:</span>
                            <span class="font-extrabold font-heading text-[#3B2314] font-tabular" x-text="'#' + String(lastOrderData?.order_id || '').padStart(4, '0')"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-[#78716C]">Total Tagihan:</span>
                            <span class="font-bold font-tabular text-[#1E1B18]" x-text="'$' + (lastOrderData?.total_amount || 0).toFixed(2)"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-[#78716C]">Metode:</span>
                            <span class="font-bold text-[#D97706]" x-text="lastOrderData?.payment_method"></span>
                        </div>
                        <template x-if="lastOrderData?.payment_method === 'CASH'">
                            <div>
                                <div class="flex justify-between">
                                    <span class="text-[#78716C]">Tunai Diterima:</span>
                                    <span class="font-tabular font-semibold" x-text="'$' + (lastOrderData?.cash_tendered || 0).toFixed(2)"></span>
                                </div>
                                <div class="flex justify-between pt-1 border-t border-[#E7E5E4] text-emerald-700 font-bold">
                                    <span>Kembalian:</span>
                                    <span class="font-tabular text-sm" x-text="'$' + (lastOrderData?.change_due || 0).toFixed(2)"></span>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- Action Buttons -->
                    <div class="space-y-2">
                        <a 
                            :href="lastOrderData?.receipt_url" 
                            target="_blank"
                            class="w-full py-3 rounded-xl bg-[#D97706] text-white hover:bg-[#B45309] font-bold text-sm shadow-md transition-all flex items-center justify-center gap-2"
                        >
                            <span>🖨️ Cetak Struk Belanja Thermal</span>
                        </a>

                        <button 
                            type="button" 
                            @click="closeSuccessModal()"
                            class="w-full py-2.5 rounded-xl border border-[#E7E5E4] text-xs font-semibold text-[#78716C] hover:bg-slate-50 transition-colors"
                        >
                            Tutup & Pesanan Baru
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- 5. MODAL PERINGATAN STOK KURANG (SHORTAGE ALERT)         -->
    <!-- ======================================================== -->
    <div x-show="showShortageModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-shortage-title" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-[#3B2314]/70 backdrop-blur-sm transition-opacity" @click="showShortageModal = false"></div>

        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md border border-[#E7E5E4]">
                <div class="bg-[#DC2626] p-4 text-white flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="text-xl">⚠️</span>
                        <h3 class="text-base font-bold font-heading text-white" id="modal-shortage-title">Stok Bahan Baku Tidak Cukup</h3>
                    </div>
                    <button type="button" @click="showShortageModal = false" class="text-white hover:opacity-80">
                        ✕
                    </button>
                </div>

                <div class="p-6 space-y-4">
                    <p class="text-xs text-[#78716C]" x-text="shortageErrorMessage"></p>

                    <!-- Table of Deficit Ingredients -->
                    <div class="rounded-xl border border-[#E7E5E4] overflow-hidden">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-[#FAF2ED] text-[#78716C] font-semibold border-b border-[#E7E5E4]">
                                <tr>
                                    <th class="p-2.5">Bahan Baku</th>
                                    <th class="p-2.5 text-right">Dibutuhkan</th>
                                    <th class="p-2.5 text-right">Tersedia</th>
                                    <th class="p-2.5 text-right text-red-600">Defisit</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#E7E5E4]">
                                <template x-for="item in shortageItems" :key="item.ingredient_id || item.item_name">
                                    <tr>
                                        <td class="p-2.5 font-bold text-[#1E1B18]" x-text="item.item_name"></td>
                                        <td class="p-2.5 text-right font-tabular" x-text="item.required + ' ' + item.unit"></td>
                                        <td class="p-2.5 text-right font-tabular text-[#78716C]" x-text="item.available + ' ' + item.unit"></td>
                                        <td class="p-2.5 text-right font-tabular font-extrabold text-red-600" x-text="'-' + item.deficit + ' ' + item.unit"></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    <div class="flex justify-end pt-2">
                        <button 
                            type="button" 
                            @click="showShortageModal = false"
                            class="px-4 py-2 rounded-xl bg-[#3B2314] text-white text-xs font-semibold hover:bg-[#2A180E]"
                        >
                            Mengerti & Sesuaikan
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- 6. MODAL CLOCK-IN SHIFT (PUNCTUALITY / LATE / OVERRIDE)  -->
    <!-- ======================================================== -->
    <div x-show="showClockInModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-clockin-title" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-[#3B2314]/60 backdrop-blur-sm transition-opacity" @click="showClockInModal = false"></div>

        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-[#E7E5E4]">
                <div class="bg-[#3B2314] px-6 py-4 text-white flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-xl bg-[#D97706] flex items-center justify-center font-bold text-base shadow">
                            ⏱️
                        </div>
                        <div>
                            <h3 class="text-base font-bold font-heading text-white" id="modal-clockin-title">Presensi Masuk Shift (Clock In)</h3>
                            <p class="text-xs text-[#FAF2ED]/70">Validasi ketepatan jadwal & otorisasi manager</p>
                        </div>
                    </div>
                    <button type="button" @click="showClockInModal = false" class="text-[#FAF2ED]/70 hover:text-white p-1 rounded-lg">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <form action="{{ route('attendance.clock-in') }}" method="POST" class="p-6 space-y-5">
                    @csrf
                    <input type="hidden" name="user_id" value="{{ $user->user_id }}">

                    <div class="p-4 rounded-xl bg-[#FAF2ED] border border-[#E7E5E4] flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-[#3B2314] text-white flex items-center justify-center font-bold text-sm">
                                {{ strtoupper(substr($user->full_name, 0, 1)) }}
                            </div>
                            <div>
                                <p class="text-sm font-bold text-[#1E1B18]">{{ $user->full_name }}</p>
                                <p class="text-xs text-[#78716C]">{{ $user->email }} &bull; Role: {{ $user->role }}</p>
                            </div>
                        </div>
                        <span class="px-2.5 py-1 rounded-full text-xs font-bold font-tabular bg-white border border-[#E7E5E4] text-[#3B2314]">
                            ${{ number_format($user->hourly_rate, 2) }}/hr
                        </span>
                    </div>

                    <div class="grid grid-cols-2 gap-3 text-center">
                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
                            <span class="text-[10px] uppercase font-bold text-slate-500">Waktu Sekarang (WIB)</span>
                            <p class="text-lg font-extrabold text-[#3B2314] font-tabular mt-0.5" x-text="currentTimeDisplay">--:--:--</p>
                            <span class="text-[11px] text-slate-500" x-text="currentDateDisplay">--</span>
                        </div>

                        <div class="p-3 rounded-xl border" :class="statusEvaluationClass">
                            <span class="text-[10px] uppercase font-bold" :class="statusEvaluationTextClass">Status Presensi</span>
                            <p class="text-sm font-extrabold font-heading mt-1" :class="statusEvaluationTextClass" x-text="statusEvaluationTitle">
                                Memeriksa Jadwal...
                            </p>
                            <span class="text-[10px] leading-tight block mt-0.5" x-text="statusEvaluationSubtext">--</span>
                        </div>
                    </div>

                    @if($todaySchedule)
                        <div class="p-3.5 rounded-xl border border-emerald-200 bg-emerald-50/50 text-xs">
                            <div class="flex items-center justify-between font-semibold text-emerald-900 mb-1">
                                <span>Jadwal Terdaftar Hari Ini</span>
                                <span class="bg-emerald-100 text-emerald-800 px-2 py-0.5 rounded-full font-tabular">
                                    {{ \Carbon\Carbon::parse($todaySchedule->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($todaySchedule->end_time)->format('H:i') }} WIB
                                </span>
                            </div>
                            <p class="text-emerald-700">
                                <strong>{{ $todaySchedule->shift_name }}</strong> (Durasi: {{ $todaySchedule->getDurationHours() }} jam).
                            </p>
                        </div>
                    @else
                        <div class="p-3.5 rounded-xl border border-amber-300 bg-amber-50 text-xs">
                            <div class="flex items-center gap-1.5 font-bold text-amber-900 mb-1">
                                <span>⚠️ Shift Tidak Terjadwal (Unscheduled)</span>
                            </div>
                            <p class="text-amber-800">
                                Wajib disertai PIN Otorisasi Manager.
                            </p>
                        </div>
                    @endif

                    <div x-show="!hasSchedule || showOverrideFields" class="p-4 rounded-xl bg-[#FAF2ED] border border-amber-300 space-y-3">
                        <div class="flex items-center justify-between">
                            <label class="block text-xs font-bold uppercase tracking-wider text-[#3B2314]">
                                🔑 Otorisasi Manager Override
                            </label>
                            <span class="text-[10px] text-amber-800 font-medium">Default: 1234</span>
                        </div>
                        <input type="password" name="override_pin" placeholder="Masukkan PIN Manager" class="w-full px-3.5 py-2.5 rounded-lg border border-[#E7E5E4] text-sm tracking-widest font-mono bg-white">
                        <input type="text" name="override_notes" placeholder="Catatan otorisasi" class="w-full px-3 py-1.5 rounded-lg border border-[#E7E5E4] text-xs bg-white text-[#1E1B18]">
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-2">
                        <button type="button" @click="showClockInModal = false" class="px-4 py-2 rounded-lg border border-[#E7E5E4] text-sm font-semibold text-[#78716C] hover:bg-slate-50">
                            Batal
                        </button>
                        <button type="submit" class="px-5 py-2.5 rounded-lg bg-[#D97706] hover:bg-[#B45309] text-white font-semibold text-sm shadow-sm">
                            Konfirmasi Clock-In
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- 7. MODAL CLOCK-OUT SHIFT (ACTIVE HOURS & OVERTIME)       -->
    <!-- ======================================================== -->
    <div x-show="showClockOutModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-clockout-title" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-[#3B2314]/60 backdrop-blur-sm transition-opacity" @click="showClockOutModal = false"></div>

        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-[#E7E5E4]">
                <div class="bg-[#DC2626] px-6 py-4 text-white flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-xl bg-white/20 flex items-center justify-center font-bold text-base shadow">
                            🏁
                        </div>
                        <div>
                            <h3 class="text-base font-bold font-heading text-white" id="modal-clockout-title">Selesai Shift (Clock Out)</h3>
                            <p class="text-xs text-red-100">Kalkulasi jam kerja aktif, lembur & akumulasi upah</p>
                        </div>
                    </div>
                    <button type="button" @click="showClockOutModal = false" class="text-white/80 hover:text-white p-1 rounded-lg">
                        ✕
                    </button>
                </div>

                <form action="{{ route('attendance.clock-out') }}" method="POST" class="p-6 space-y-5">
                    @csrf
                    @if($activeShift)
                        <input type="hidden" name="attendance_id" value="{{ $activeShift->attendance_id }}">
                    @endif

                    <div class="bg-[#FAF2ED] p-4 rounded-xl border border-[#E7E5E4] space-y-3">
                        <div class="flex items-center justify-between text-xs text-[#78716C] pb-2 border-b border-[#E7E5E4]">
                            <span>Shift ID: #{{ $activeShift->attendance_id ?? '-' }}</span>
                            <span class="font-semibold text-[#1E1B18]">{{ $user->full_name }} ({{ $user->role }})</span>
                        </div>
                        <div class="grid grid-cols-2 gap-3 text-center">
                            <div>
                                <span class="text-[10px] uppercase font-bold text-[#78716C]">Jam Masuk</span>
                                <p class="text-base font-bold text-[#1E1B18] font-tabular mt-0.5">
                                    {{ $activeShift ? $activeShift->clock_in->format('H:i') . ' WIB' : '-' }}
                                </p>
                            </div>
                            <div>
                                <span class="text-[10px] uppercase font-bold text-[#78716C]">Jam Keluar</span>
                                <p class="text-base font-bold text-[#DC2626] font-tabular mt-0.5" x-text="currentTimeDisplay">--:-- WIB</p>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-3">
                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 text-center">
                            <span class="text-[10px] uppercase font-bold text-slate-500">Total Jam</span>
                            <p class="text-lg font-extrabold text-[#3B2314] font-tabular mt-0.5" x-text="calculatedTotalHours + ' jam'">0.00 jam</p>
                        </div>
                        <div class="p-3 rounded-xl text-center border" :class="calculatedOvertimeHours > 0 ? 'bg-purple-50 border-purple-200 text-purple-900' : 'bg-slate-50 border-slate-200 text-slate-500'">
                            <span class="text-[10px] uppercase font-bold">Lembur</span>
                            <p class="text-lg font-extrabold font-tabular mt-0.5" x-text="calculatedOvertimeHours + ' jam'">0.00 jam</p>
                        </div>
                        <div class="p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-center text-emerald-900">
                            <span class="text-[10px] uppercase font-bold text-emerald-700">Estimasi Upah</span>
                            <p class="text-lg font-extrabold font-tabular mt-0.5" x-text="'$' + calculatedEarnings">$0.00</p>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-2">
                        <button type="button" @click="showClockOutModal = false" class="px-4 py-2 rounded-lg border border-[#E7E5E4] text-sm font-semibold text-[#78716C] hover:bg-slate-50">
                            Batal
                        </button>
                        <button type="submit" class="px-5 py-2.5 rounded-lg bg-[#DC2626] hover:bg-red-700 text-white font-semibold text-sm shadow-sm">
                            Konfirmasi Clock-Out
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function posAppHandler(config) {
    return {
        // Data State
        menuItems: config.menuItems || [],
        categories: config.categories || [],
        nextOrderNumber: config.nextOrderNumber || 1,
        searchQuery: '',
        selectedCategory: 'ALL',
        isLoadingMenu: false,

        // Cart State
        cart: [],
        paymentMethod: 'CASH', // 'CASH' or 'QRIS'
        cashTendered: 0,
        isProcessingOrder: false,

        // Modals State
        showQRISModal: false,
        showSuccessModal: false,
        showShortageModal: false,
        showClockInModal: config.requiresOverrideSession,
        showClockOutModal: false,
        showOverrideFields: config.requiresOverrideSession,

        // QRIS State
        qrisData: {},
        qrisRemainingSeconds: 180,
        qrisTimerInterval: null,
        qrisPollingInterval: null,
        isQRISTimeout: false,
        qrisPollingStatusText: 'Menunggu konfirmasi pembayaran pelanggan...',

        // Success / Shortage Data
        lastOrderData: null,
        shortageItems: [],
        shortageErrorMessage: '',

        // Attendance & Shift State
        isClockedIn: config.isClockedIn,
        clockInTime: config.clockInTime ? new Date(config.clockInTime) : null,
        hourlyRate: config.hourlyRate,
        hasSchedule: config.hasSchedule,
        scheduleName: config.scheduleName,
        scheduleStart: config.scheduleStart,
        scheduleEnd: config.scheduleEnd,
        now: new Date(),
        liveDurationText: '00h 00m 00s',
        currentTimeDisplay: '--:--',
        currentDateDisplay: '',
        calculatedTotalHours: '0.00',
        calculatedOvertimeHours: '0.00',
        calculatedEarnings: '0.00',

        init() {
            this.updateClock();
            setInterval(() => this.updateClock(), 1000);
        },

        // ==========================================
        // Computed Properties (Getters)
        // ==========================================
        get filteredMenuItems() {
            let list = this.menuItems;

            if (this.selectedCategory !== 'ALL') {
                list = list.filter(item => item.category === this.selectedCategory);
            }

            if (this.searchQuery.trim().length > 0) {
                const q = this.searchQuery.toLowerCase();
                list = list.filter(item => item.item_name.toLowerCase().includes(q));
            }

            return list;
        },

        get cartSubtotal() {
            return this.cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        },

        get cartTax() {
            return Math.round(this.cartSubtotal * 0.10 * 100) / 100;
        },

        get cartGrandTotal() {
            return Math.round((this.cartSubtotal + this.cartTax) * 100) / 100;
        },

        get qrisTimerDisplay() {
            const minutes = Math.floor(this.qrisRemainingSeconds / 60);
            const seconds = this.qrisRemainingSeconds % 60;
            return String(minutes).padStart(2, '0') + ':' + String(seconds).padStart(2, '0');
        },

        countCategory(category) {
            return this.menuItems.filter(item => item.category === category).length;
        },

        // ==========================================
        // Cart Operations & Early Stock Check
        // ==========================================
        addToCart(menu) {
            // Check if cashier is clocked in
            if (!this.isClockedIn) {
                this.openClockInModal();
                return;
            }

            // Early Stock Check: verify if max servings reached
            const existingIndex = this.cart.findIndex(i => i.menu_id === menu.menu_id);
            const currentQtyInCart = existingIndex > -1 ? this.cart[existingIndex].quantity : 0;

            if (currentQtyInCart + 1 > menu.max_servings) {
                this.shortageErrorMessage = `Bahan baku untuk ${menu.item_name} tidak mencukupi untuk menambah porsi (Maksimal tersedia: ${menu.max_servings} porsi).`;
                this.shortageItems = [{
                    item_name: menu.item_name,
                    required: currentQtyInCart + 1,
                    available: menu.max_servings,
                    unit: 'porsi',
                    deficit: (currentQtyInCart + 1) - menu.max_servings
                }];
                this.showShortageModal = true;
                return;
            }

            if (existingIndex > -1) {
                this.cart[existingIndex].quantity += 1;
            } else {
                this.cart.push({
                    menu_id: menu.menu_id,
                    item_name: menu.item_name,
                    price: menu.price,
                    quantity: 1,
                    max_servings: menu.max_servings,
                });
            }

            // Auto update default cash tendered if cash method is chosen
            if (this.cashTendered < this.cartGrandTotal) {
                this.cashTendered = this.cartGrandTotal;
            }
        },

        incrementItem(index) {
            const item = this.cart[index];
            if (item.quantity + 1 > item.max_servings) {
                this.shortageErrorMessage = `Stok bahan untuk ${item.item_name} telah mencapai batas maksimum (${item.max_servings} porsi).`;
                this.shortageItems = [{
                    item_name: item.item_name,
                    required: item.quantity + 1,
                    available: item.max_servings,
                    unit: 'porsi',
                    deficit: 1
                }];
                this.showShortageModal = true;
                return;
            }
            item.quantity += 1;
            if (this.cashTendered < this.cartGrandTotal) {
                this.cashTendered = this.cartGrandTotal;
            }
        },

        decrementItem(index) {
            if (this.cart[index].quantity > 1) {
                this.cart[index].quantity -= 1;
            } else {
                this.removeItem(index);
            }
            if (this.cashTendered < this.cartGrandTotal) {
                this.cashTendered = this.cartGrandTotal;
            }
        },

        removeItem(index) {
            this.cart.splice(index, 1);
            if (this.cart.length === 0) {
                this.cashTendered = 0;
            } else if (this.cashTendered < this.cartGrandTotal) {
                this.cashTendered = this.cartGrandTotal;
            }
        },

        clearCart() {
            this.cart = [];
            this.cashTendered = 0;
        },

        // ==========================================
        // Order Submission: CASH Payment
        // ==========================================
        async submitCashOrder() {
            if (this.cart.length === 0 || this.cashTendered < this.cartGrandTotal) return;

            this.isProcessingOrder = true;

            const payload = {
                items: this.cart.map(i => ({ menu_id: i.menu_id, quantity: i.quantity })),
                payment_method: 'CASH',
                cash_tendered: this.cashTendered,
            };

            try {
                const response = await fetch(config.routes.processOrder, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': config.csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });

                const data = await response.json();

                if (!response.ok || !data.success) {
                    if (data.shortages) {
                        this.shortageErrorMessage = data.message || 'Stok bahan baku tidak mencukupi.';
                        this.shortageItems = data.shortages;
                        this.showShortageModal = true;
                    } else {
                        alert(data.message || 'Terjadi kesalahan saat memproses pesanan.');
                    }
                    this.isProcessingOrder = false;
                    return;
                }

                // Order Success
                this.handleOrderSuccess(data.data);

            } catch (err) {
                alert('Gagal menghubungi server: ' + err.message);
            } finally {
                this.isProcessingOrder = false;
            }
        },

        // ==========================================
        // Dynamic QRIS Pipeline (Timer & Polling)
        // ==========================================
        async initiateQRISPayment() {
            if (this.cart.length === 0) return;

            this.isProcessingOrder = true;
            this.isQRISTimeout = false;
            this.qrisData = {};
            this.qrisRemainingSeconds = 180;
            this.qrisPollingStatusText = 'Menyiapkan QR Code dinamis...';

            const orderRef = 'ORD-' + String(this.nextOrderNumber).padStart(4, '0') + '-' + Date.now().toString().slice(-4);

            try {
                const response = await fetch(config.routes.qrisGenerate, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': config.csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        amount: this.cartGrandTotal,
                        order_reference: orderRef
                    })
                });

                const res = await response.json();
                if (!response.ok || !res.success) {
                    alert('Gagal membuat payload QRIS: ' + (res.message || 'Error'));
                    this.isProcessingOrder = false;
                    return;
                }

                this.qrisData = res.data;
                this.showQRISModal = true;
                this.startQRISTimerAndPolling();

            } catch (err) {
                alert('Gagal membuat QRIS: ' + err.message);
            } finally {
                this.isProcessingOrder = false;
            }
        },

        startQRISTimerAndPolling() {
            this.clearQRISTimers();
            this.qrisRemainingSeconds = 180;
            this.isQRISTimeout = false;
            this.qrisPollingStatusText = 'Menunggu pembayaran pelanggan via scan QR...';

            // 1. Countdown timer loop (every second)
            this.qrisTimerInterval = setInterval(() => {
                this.qrisRemainingSeconds--;
                if (this.qrisRemainingSeconds <= 0) {
                    this.handleQRISTimeout();
                }
            }, 1000);

            // 2. Polling status loop (every 3 seconds)
            this.qrisPollingInterval = setInterval(() => {
                this.checkQRISStatus();
            }, 3000);
        },

        async checkQRISStatus() {
            if (!this.qrisData.transaction_id || this.isQRISTimeout) return;

            try {
                const res = await fetch(`${config.routes.qrisStatus}/${this.qrisData.transaction_id}`, {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await res.json();

                if (data && data.data && data.data.status === 'SUCCESS') {
                    this.clearQRISTimers();
                    this.qrisPollingStatusText = '✓ Pembayaran berhasil diterima! Menyimpan transaksi...';
                    
                    // Finalize order in DB via processOrder endpoint
                    await this.finalizeQRISOrder();
                } else if (data && data.data && data.data.status === 'EXPIRED') {
                    this.handleQRISTimeout();
                }
            } catch (e) {
                console.error('Polling error', e);
            }
        },

        async simulateQRISSuccess() {
            if (!this.qrisData.transaction_id) return;

            try {
                const res = await fetch(config.routes.qrisSimulate, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': config.csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ transaction_id: this.qrisData.transaction_id })
                });

                const data = await res.json();
                if (data.success) {
                    this.qrisPollingStatusText = '✓ Simulasi Sukses Diterima! Menyelesaikan pesanan...';
                    this.checkQRISStatus();
                }
            } catch (err) {
                alert('Gagal mensimulasikan QRIS: ' + err.message);
            }
        },

        async finalizeQRISOrder() {
            const payload = {
                items: this.cart.map(i => ({ menu_id: i.menu_id, quantity: i.quantity })),
                payment_method: 'QRIS',
                qris_reference: this.qrisData.transaction_id,
            };

            try {
                const response = await fetch(config.routes.processOrder, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': config.csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });

                const data = await response.json();
                if (response.ok && data.success) {
                    this.closeQRISModal();
                    this.handleOrderSuccess(data.data);
                } else {
                    alert(data.message || 'Gagal menyimpan pesanan QRIS.');
                }
            } catch (err) {
                alert('Error finalisasi pesanan QRIS: ' + err.message);
            }
        },

        handleQRISTimeout() {
            this.clearQRISTimers();
            this.isQRISTimeout = true;
            this.qrisPollingStatusText = 'Waktu pembayaran telah habis (timeout 3 menit).';
        },

        closeQRISModal() {
            this.clearQRISTimers();
            this.showQRISModal = false;
        },

        clearQRISTimers() {
            if (this.qrisTimerInterval) clearInterval(this.qrisTimerInterval);
            if (this.qrisPollingInterval) clearInterval(this.qrisPollingInterval);
        },

        // ==========================================
        // Post-Order Handlers & Reset
        // ==========================================
        handleOrderSuccess(orderData) {
            this.lastOrderData = orderData;
            this.showSuccessModal = true;
            this.nextOrderNumber++;

            // Update live menu stock if returned from backend
            if (orderData.updated_menu) {
                this.menuItems = orderData.updated_menu;
            } else {
                this.refreshMenuData();
            }

            // Clear current cart
            this.clearCart();
        },

        closeSuccessModal() {
            this.showSuccessModal = false;
            this.lastOrderData = null;
        },

        async refreshMenuData() {
            this.isLoadingMenu = true;
            try {
                const res = await fetch(config.routes.getMenu, {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await res.json();
                if (data.success && data.data) {
                    this.menuItems = data.data;
                }
            } catch (err) {
                console.error('Failed to reload menu', err);
            } finally {
                this.isLoadingMenu = false;
            }
        },

        // ==========================================
        // Shift Attendance Helpers (Sprint 3)
        // ==========================================
        updateClock() {
            this.now = new Date();
            this.currentTimeDisplay = this.now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' }) + ' WIB';
            this.currentDateDisplay = this.now.toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'short', year: 'numeric' });

            if (this.isClockedIn && this.clockInTime) {
                const diffMs = Math.max(0, this.now - this.clockInTime);
                const totalSeconds = Math.floor(diffMs / 1000);
                const hours = Math.floor(totalSeconds / 3600);
                const minutes = Math.floor((totalSeconds % 3600) / 60);
                const seconds = totalSeconds % 60;

                this.liveDurationText = 
                    String(hours).padStart(2, '0') + 'h ' + 
                    String(minutes).padStart(2, '0') + 'm ' + 
                    String(seconds).padStart(2, '0') + 's active';

                const totalHoursDecimal = (totalSeconds / 3600);
                this.calculatedTotalHours = totalHoursDecimal.toFixed(2);
                
                const overtime = Math.max(0, totalHoursDecimal - 8.00);
                this.calculatedOvertimeHours = overtime.toFixed(2);
                this.calculatedEarnings = (totalHoursDecimal * this.hourlyRate).toFixed(2);
            }
        },

        openClockInModal() {
            this.showClockInModal = true;
        },

        openClockOutModal() {
            this.updateClock();
            this.showClockOutModal = true;
        },

        get statusEvaluationTitle() {
            if (!this.hasSchedule) return 'Shift Tak Terjadwal';
            if (this.minutesDifferenceFromSchedule() > 15) return 'Terlambat (> 15 Menit)';
            return 'Tepat Waktu (On Time)';
        },

        get statusEvaluationSubtext() {
            if (!this.hasSchedule) return 'Wajib PIN Otorisasi Manager';
            const diff = this.minutesDifferenceFromSchedule();
            if (diff > 15) return `Lewat ${diff} menit dari jadwal. Alert akan dikirim ke HR.`;
            return '<= 15 menit dari jam shift. Status: ON_TIME.';
        },

        get statusEvaluationClass() {
            if (!this.hasSchedule) return 'bg-amber-50 border-amber-300';
            if (this.minutesDifferenceFromSchedule() > 15) return 'bg-orange-50 border-orange-300';
            return 'bg-emerald-50 border-emerald-300';
        },

        get statusEvaluationTextClass() {
            if (!this.hasSchedule) return 'text-amber-800';
            if (this.minutesDifferenceFromSchedule() > 15) return 'text-orange-700';
            return 'text-emerald-800';
        },

        minutesDifferenceFromSchedule() {
            if (!this.hasSchedule || !this.scheduleStart) return 0;
            const [sHour, sMin] = this.scheduleStart.split(':').map(Number);
            const scheduled = new Date(this.now);
            scheduled.setHours(sHour, sMin, 0, 0);

            const diffMs = this.now - scheduled;
            if (diffMs <= 0) return 0;
            return Math.floor(diffMs / 60000);
        }
    };
}
</script>
@endpush
@endsection
