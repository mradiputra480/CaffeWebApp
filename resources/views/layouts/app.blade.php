<!DOCTYPE html>
<html lang="id" class="h-full bg-[#FDFBF7]">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'CafeSync') - Artisanal Specialty Cafe Operations</title>

    <!-- Google Fonts: Plus Jakarta Sans & Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet">

    <!-- Styles & Scripts via Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'Inter', sans-serif;
            color: #1E1B18;
        }
        h1, h2, h3, h4, h5, h6, .font-heading {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        .font-tabular {
            font-feature-settings: "tnum" 1;
            font-variant-numeric: tabular-nums;
        }
    </style>
</head>
<body class="min-h-full flex flex-col bg-[#FDFBF7] text-[#1E1B18] antialiased selection:bg-[#D97706] selection:text-white">
    <!-- Top Navigation Bar -->
    <header class="bg-[#3B2314] text-white shadow-md border-b border-[#2A180E] sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Brand & Logo -->
                <div class="flex items-center gap-3">
                    <a href="{{ url('/') }}" class="flex items-center gap-2.5 group">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-[#D97706] to-[#B45309] flex items-center justify-center shadow-inner group-hover:scale-105 transition-transform">
                            <!-- Coffee Cup Icon -->
                            <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 8h1a4 4 0 0 1 0 8h-1M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"/>
                                <line x1="6" y1="1" x2="6" y2="4" stroke-linecap="round" stroke-width="2"/>
                                <line x1="10" y1="1" x2="10" y2="4" stroke-linecap="round" stroke-width="2"/>
                                <line x1="14" y1="1" x2="14" y2="4" stroke-linecap="round" stroke-width="2"/>
                            </svg>
                        </div>
                        <div>
                            <span class="font-heading font-bold text-lg tracking-tight text-white block leading-tight">CafeSync</span>
                            <span class="text-[11px] tracking-wider text-[#FAF2ED]/70 font-medium uppercase">Artisanal Platform</span>
                        </div>
                    </a>

                    <!-- Navigation Links Based on Role -->
                    @auth
                    <nav class="hidden md:flex items-center gap-1 ml-6 border-l border-[#FAF2ED]/20 pl-6 text-sm font-medium">
                        @if(auth()->user()->isStaff() || auth()->user()->isManager())
                        <a href="{{ route('pos.index') }}" class="px-3 py-1.5 rounded-lg transition-colors {{ request()->routeIs('pos.*') ? 'bg-[#D97706] text-white shadow-sm' : 'text-[#FAF2ED]/80 hover:bg-[#FAF2ED]/10 hover:text-white' }}">
                            ☕ POS Kasir
                        </a>
                        <a href="{{ route('inventory.index') }}" class="px-3 py-1.5 rounded-lg transition-colors {{ request()->routeIs('inventory.*') ? 'bg-[#D97706] text-white shadow-sm' : 'text-[#FAF2ED]/80 hover:bg-[#FAF2ED]/10 hover:text-white' }}">
                            📦 Stok Bahan Mentah
                        </a>
                        @endif

                        @if(auth()->user()->isHrd() || auth()->user()->isManager())
                        <a href="{{ route('hr.roster') }}" class="px-3 py-1.5 rounded-lg transition-colors {{ request()->routeIs('hr.*') ? 'bg-[#D97706] text-white shadow-sm' : 'text-[#FAF2ED]/80 hover:bg-[#FAF2ED]/10 hover:text-white' }}">
                            📋 Roster & Presensi HR
                        </a>
                        @endif

                        @if(auth()->user()->isManager())
                        <a href="{{ route('manager.dashboard') }}" class="px-3 py-1.5 rounded-lg transition-colors {{ request()->routeIs('manager.*') ? 'bg-[#D97706] text-white shadow-sm' : 'text-[#FAF2ED]/80 hover:bg-[#FAF2ED]/10 hover:text-white' }}">
                            📊 Executive Analytics
                        </a>
                        @endif
                    </nav>
                    @endauth
                </div>

                <!-- User Profile & Actions -->
                @auth
                <div class="flex items-center gap-3">
                    <!-- Role Pill Badge -->
                    @php
                        $roleColors = [
                            'STAFF' => 'bg-[#D97706]/20 text-[#D97706] border-[#D97706]/40',
                            'HRD' => 'bg-emerald-500/20 text-emerald-300 border-emerald-500/40',
                            'MANAGER' => 'bg-amber-100 text-[#3B2314] border-amber-200 font-bold',
                        ];
                    @endphp
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold border {{ $roleColors[auth()->user()->role] ?? 'bg-white/10 text-white' }}">
                        <span class="w-1.5 h-1.5 rounded-full bg-current animate-pulse"></span>
                        {{ auth()->user()->role }}
                    </span>

                    <!-- User Name & Avatar -->
                    <div class="hidden sm:flex items-center gap-2 text-right">
                        <div>
                            <p class="text-sm font-semibold text-white leading-tight">{{ auth()->user()->full_name }}</p>
                            <p class="text-xs text-[#FAF2ED]/60">{{ auth()->user()->email }}</p>
                        </div>
                        <div class="w-9 h-9 rounded-full bg-[#D97706] flex items-center justify-center font-bold text-white text-sm shadow">
                            {{ strtoupper(substr(auth()->user()->full_name, 0, 1)) }}
                        </div>
                    </div>

                    <!-- Logout Button -->
                    <form action="{{ route('logout') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" title="Keluar dari sistem" class="p-2 rounded-lg text-[#FAF2ED]/70 hover:text-white hover:bg-red-500/20 hover:border-red-500/40 border border-transparent transition-all flex items-center gap-1.5 text-xs font-medium">
                            <svg class="w-4 h-4 text-red-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                            <span class="hidden sm:inline text-red-200">Logout</span>
                        </button>
                    </form>
                </div>
                @endauth
            </div>
        </div>
    </header>

    <!-- Global Notifications & Alerts -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full mt-4">
        @if(session('success'))
            <div class="mb-4 bg-[#DCFCE7] border border-[#16A34A]/30 text-[#16A34A] px-4 py-3 rounded-xl flex items-center gap-2 shadow-sm text-sm font-medium">
                <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if(session('status'))
            <div class="mb-4 bg-[#DCFCE7] border border-[#16A34A]/30 text-[#16A34A] px-4 py-3 rounded-xl flex items-center gap-2 shadow-sm text-sm font-medium">
                <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 bg-[#FEE2E2] border border-[#DC2626]/30 text-[#DC2626] px-4 py-3 rounded-xl flex items-center gap-2 shadow-sm text-sm font-medium">
                <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif
    </div>

    <!-- Main Content Area -->
    <main class="flex-grow max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 w-full">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="mt-auto border-t border-[#E7E5E4] bg-white py-4 text-center text-xs text-[#78716C]">
        <div class="max-w-7xl mx-auto px-4 flex flex-col sm:flex-row items-center justify-between gap-2">
            <p>&copy; {{ date('Y') }} <strong>CafeSync</strong> - Enterprise POS & Multi-Role Specialty Cafe Management.</p>
            <p class="font-tabular text-[11px] bg-[#FAF2ED] px-2.5 py-1 rounded-full border border-[#E7E5E4] text-[#3B2314] font-medium">
                Sprint 6 (v0.7.0) &bull; Executive Analytics &amp; Sales Audit Active
            </p>
        </div>
    </footer>

    <!-- Page Specific Scripts -->
    @stack('scripts')
</body>
</html>
