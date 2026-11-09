<!DOCTYPE html>
<html lang="en" class="h-full scroll-smooth bg-[#FDFBF7]">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CafeSync — Artisanal Specialty Coffee POS & Multi-Role Operations Platform</title>

    <!-- Google Fonts: Plus Jakarta Sans & Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
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

    <!-- Top Navigation Header -->
    <header class="bg-[#3B2314] text-white border-b border-[#2A180E] sticky top-0 z-50 shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16 sm:h-20">
                <!-- Brand Logo -->
                <a href="{{ url('/') }}" class="flex items-center gap-3 group">
                    <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-[#D97706] to-[#B45309] flex items-center justify-center shadow-md group-hover:scale-105 transition-transform">
                        <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 8h1a4 4 0 0 1 0 8h-1M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"/>
                            <line x1="6" y1="1" x2="6" y2="4" stroke-linecap="round" stroke-width="2"/>
                            <line x1="10" y1="1" x2="10" y2="4" stroke-linecap="round" stroke-width="2"/>
                            <line x1="14" y1="1" x2="14" y2="4" stroke-linecap="round" stroke-width="2"/>
                        </svg>
                    </div>
                    <div>
                        <span class="font-heading font-extrabold text-xl tracking-tight text-white block leading-tight">CafeSync</span>
                        <span class="text-[11px] uppercase font-semibold tracking-widest text-[#D97706]">Specialty Operations</span>
                    </div>
                </a>

                <!-- Nav Links & Quick Demo CTAs -->
                <div class="flex items-center gap-2 sm:gap-4">
                    <a href="#features" class="hidden md:inline-flex text-xs font-semibold text-[#FAF2ED]/80 hover:text-white px-3 py-2 rounded-lg hover:bg-white/5 transition-colors">
                        Core Features
                    </a>
                    <a href="#architecture" class="hidden md:inline-flex text-xs font-semibold text-[#FAF2ED]/80 hover:text-white px-3 py-2 rounded-lg hover:bg-white/5 transition-colors">
                        Architecture
                    </a>
                    <a href="#roles" class="hidden sm:inline-flex text-xs font-semibold text-[#FAF2ED]/80 hover:text-white px-3 py-2 rounded-lg hover:bg-white/5 transition-colors">
                        Multi-Role Demo
                    </a>

                    @auth
                        <a href="{{ match(auth()->user()->role) { 'STAFF' => route('pos.index'), 'HRD' => route('hr.roster'), 'MANAGER' => route('manager.dashboard'), default => route('pos.index') } }}" 
                           class="bg-[#D97706] hover:bg-[#B45309] text-white text-xs sm:text-sm font-bold px-4 py-2 sm:py-2.5 rounded-xl shadow-md transition-all flex items-center gap-1.5">
                            <span>Open My Portal ({{ auth()->user()->role }})</span>
                            <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
                        </a>
                    @else
                        <a href="{{ route('login') }}" 
                           class="bg-[#FAF2ED]/10 hover:bg-[#FAF2ED]/20 text-white border border-[#FAF2ED]/20 text-xs sm:text-sm font-semibold px-3.5 py-2 sm:py-2.5 rounded-xl transition-all">
                            Sign In
                        </a>
                        <a href="#roles" 
                           class="bg-[#D97706] hover:bg-[#B45309] text-white text-xs sm:text-sm font-bold px-4 py-2 sm:py-2.5 rounded-xl shadow-md shadow-[#D97706]/30 transition-all flex items-center gap-1.5">
                            <span>Try Live Demo</span>
                            <span class="material-symbols-outlined text-[16px]">rocket_launch</span>
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-grow">
        <!-- HERO SECTION -->
        <section class="relative bg-gradient-to-b from-[#3B2314] via-[#2A180E] to-[#1E120A] text-white pt-16 pb-24 sm:pt-24 sm:pb-32 overflow-hidden">
            <!-- Background Glows -->
            <div class="absolute -top-40 -left-40 w-96 h-96 bg-[#D97706]/20 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute top-1/2 -right-40 w-96 h-96 bg-[#D97706]/15 rounded-full blur-3xl pointer-events-none"></div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center">
                <!-- Badges Pill -->
                <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-white/10 backdrop-blur-md border border-white/15 text-xs font-semibold text-[#FAF2ED] mb-6 shadow-inner">
                    <span class="w-2 h-2 rounded-full bg-[#16A34A] animate-pulse"></span>
                    <span>Enterprise Software Portfolio</span>
                    <span class="text-[#FAF2ED]/40">&bull;</span>
                    <span class="text-[#D97706]">53/53 Tests Passed</span>
                </div>

                <!-- Main Hero Headline -->
                <h1 class="font-heading font-extrabold text-3xl sm:text-5xl lg:text-6xl tracking-tight max-w-4xl mx-auto leading-tight sm:leading-none text-white">
                    Seamless Operations for Modern Specialty Coffee.
                </h1>

                <p class="mt-6 text-base sm:text-xl text-[#FAF2ED]/85 max-w-3xl mx-auto leading-relaxed">
                    A unified monolith web platform connecting high-speed <strong class="text-white">Web POS</strong> with atomic recipe ingredient deductions, <strong class="text-white">HR Shift Rosters &amp; Payroll</strong>, raw inventory ledger tracking, and <strong class="text-white">Executive Intelligence</strong>.
                </p>

                <!-- Call to Actions -->
                <div class="mt-10 flex flex-wrap items-center justify-center gap-4">
                    <a href="#roles" class="bg-[#D97706] hover:bg-[#B45309] text-white font-bold px-6 py-3.5 rounded-xl shadow-xl shadow-[#D97706]/30 text-sm sm:text-base flex items-center gap-2 transition-transform active:scale-[0.98]">
                        <span class="material-symbols-outlined text-[20px]">play_circle</span>
                        <span>1-Click Instant Live Demo</span>
                    </a>
                    <a href="{{ route('login') }}" class="bg-white/10 hover:bg-white/20 text-white border border-white/20 font-semibold px-5 py-3.5 rounded-xl text-sm sm:text-base transition-colors flex items-center gap-2">
                        <span class="material-symbols-outlined text-[20px]">lock</span>
                        <span>Standard Sign-In Portal</span>
                    </a>
                </div>

                <!-- Key Metrics Strip -->
                <div class="mt-16 grid grid-cols-2 md:grid-cols-4 gap-4 max-w-4xl mx-auto pt-8 border-t border-white/10 text-left">
                    <div class="bg-white/5 backdrop-blur-sm p-4 rounded-xl border border-white/10">
                        <p class="text-xs uppercase tracking-wider text-[#FAF2ED]/60 font-medium">Architecture</p>
                        <p class="text-lg sm:text-xl font-bold font-heading text-white mt-1">Multi-Role RBAC</p>
                        <p class="text-[11px] text-[#FAF2ED]/70 mt-0.5">Staff, HRD, &amp; Cafe Manager</p>
                    </div>
                    <div class="bg-white/5 backdrop-blur-sm p-4 rounded-xl border border-white/10">
                        <p class="text-xs uppercase tracking-wider text-[#FAF2ED]/60 font-medium">POS Reliability</p>
                        <p class="text-lg sm:text-xl font-bold font-heading text-[#D97706] mt-1">Atomic Recipes</p>
                        <p class="text-[11px] text-[#FAF2ED]/70 mt-0.5">Gram &amp; ml stock deductions</p>
                    </div>
                    <div class="bg-white/5 backdrop-blur-sm p-4 rounded-xl border border-white/10">
                        <p class="text-xs uppercase tracking-wider text-[#FAF2ED]/60 font-medium">Checkout Flow</p>
                        <p class="text-lg sm:text-xl font-bold font-heading text-white mt-1">Cash &amp; QRIS</p>
                        <p class="text-[11px] text-[#FAF2ED]/70 mt-0.5">Dynamic gateway simulation</p>
                    </div>
                    <div class="bg-white/5 backdrop-blur-sm p-4 rounded-xl border border-white/10">
                        <p class="text-xs uppercase tracking-wider text-[#FAF2ED]/60 font-medium">Auditing &amp; Exports</p>
                        <p class="text-lg sm:text-xl font-bold font-heading text-emerald-400 mt-1">CSV &amp; PDF</p>
                        <p class="text-[11px] text-[#FAF2ED]/70 mt-0.5">Comprehensive audit reports</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- 1-CLICK INSTANT DEMO ROLES SECTION -->
        <section id="roles" class="py-16 sm:py-20 bg-white border-b border-[#E7E5E4]">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-3xl mx-auto mb-12">
                    <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-[#FAF2ED] text-[#D97706] border border-[#D97706]/30 inline-block mb-3">
                        Interactive Live Preview
                    </span>
                    <h2 class="font-heading font-extrabold text-2xl sm:text-4xl text-[#1E1B18] tracking-tight">
                        Select a Role &amp; Enter Instantly (1-Click Demo)
                    </h2>
                    <p class="text-sm sm:text-base text-[#78716C] mt-2">
                        No manual password entry required. Select a workstation persona to test full functionality:
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Staff / Cashier Card -->
                    <div class="bg-[#FDFBF7] border-2 border-[#E7E5E4] hover:border-[#D97706] rounded-2xl p-6 transition-all duration-300 hover:shadow-xl flex flex-col justify-between group">
                        <div>
                            <div class="flex items-center justify-between mb-4">
                                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-[#3B2314] to-[#2A180E] text-white flex items-center justify-center text-xl shadow-md">
                                    ☕
                                </div>
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-[#FAF2ED] text-[#D97706] border border-[#D97706]/30">
                                    STAFF / CASHIER
                                </span>
                            </div>
                            <h3 class="font-heading font-bold text-lg text-[#1E1B18] group-hover:text-[#D97706] transition-colors">
                                Alex Rivera (Lead Cashier)
                            </h3>
                            <p class="text-xs text-[#78716C] mt-1 font-mono">cashier@cafesync.local</p>
                            
                            <ul class="mt-4 space-y-2 text-xs text-[#1E1B18]/80">
                                <li class="flex items-start gap-2">
                                    <span class="text-[#16A34A] font-bold">✓</span>
                                    <span>Web POS Order Terminal &amp; Catalog</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-[#16A34A] font-bold">✓</span>
                                    <span>Recipe Ingredient Verification &amp; QRIS</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-[#16A34A] font-bold">✓</span>
                                    <span>80mm Thermal Receipt Printing &amp; Shift Clock-In</span>
                                </li>
                            </ul>
                        </div>

                        <div class="mt-6 pt-4 border-t border-[#E7E5E4]">
                            <a href="{{ route('demo.login', 'staff') }}" 
                               class="w-full bg-[#3B2314] hover:bg-[#D97706] text-white text-xs sm:text-sm font-bold py-2.5 px-4 rounded-xl shadow transition-all flex items-center justify-center gap-2">
                                <span>Launch Cashier Terminal</span>
                                <span class="material-symbols-outlined text-[16px]">login</span>
                            </a>
                        </div>
                    </div>

                    <!-- HRD Manager Card -->
                    <div class="bg-[#FDFBF7] border-2 border-[#E7E5E4] hover:border-emerald-600 rounded-2xl p-6 transition-all duration-300 hover:shadow-xl flex flex-col justify-between group">
                        <div>
                            <div class="flex items-center justify-between mb-4">
                                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-emerald-800 to-emerald-950 text-white flex items-center justify-center text-xl shadow-md">
                                    📋
                                </div>
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    HRD MANAGER
                                </span>
                            </div>
                            <h3 class="font-heading font-bold text-lg text-[#1E1B18] group-hover:text-emerald-700 transition-colors">
                                David Miller (HR Specialist)
                            </h3>
                            <p class="text-xs text-[#78716C] mt-1 font-mono">hrd@cafesync.local</p>
                            
                            <ul class="mt-4 space-y-2 text-xs text-[#1E1B18]/80">
                                <li class="flex items-start gap-2">
                                    <span class="text-[#16A34A] font-bold">✓</span>
                                    <span>Staff Shift Roster &amp; Monthly Calendar</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-[#16A34A] font-bold">✓</span>
                                    <span>Automated Tardiness Alerts (>15m Late)</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-[#16A34A] font-bold">✓</span>
                                    <span>Automated Monthly Payroll &amp; Overtime Calc</span>
                                </li>
                            </ul>
                        </div>

                        <div class="mt-6 pt-4 border-t border-[#E7E5E4]">
                            <a href="{{ route('demo.login', 'hrd') }}" 
                               class="w-full bg-emerald-800 hover:bg-emerald-700 text-white text-xs sm:text-sm font-bold py-2.5 px-4 rounded-xl shadow transition-all flex items-center justify-center gap-2">
                                <span>Launch HR &amp; Roster Portal</span>
                                <span class="material-symbols-outlined text-[16px]">login</span>
                            </a>
                        </div>
                    </div>

                    <!-- Cafe Manager Card -->
                    <div class="bg-[#FDFBF7] border-2 border-[#E7E5E4] hover:border-[#D97706] rounded-2xl p-6 transition-all duration-300 hover:shadow-xl flex flex-col justify-between group">
                        <div>
                            <div class="flex items-center justify-between mb-4">
                                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-[#D97706] to-[#B45309] text-white flex items-center justify-center text-xl shadow-md">
                                    📊
                                </div>
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-800 border border-amber-300">
                                    CAFE MANAGER
                                </span>
                            </div>
                            <h3 class="font-heading font-bold text-lg text-[#1E1B18] group-hover:text-[#D97706] transition-colors">
                                Elena Vance (General Manager)
                            </h3>
                            <p class="text-xs text-[#78716C] mt-1 font-mono">manager@cafesync.local</p>
                            
                            <ul class="mt-4 space-y-2 text-xs text-[#1E1B18]/80">
                                <li class="flex items-start gap-2">
                                    <span class="text-[#16A34A] font-bold">✓</span>
                                    <span>Executive Analytics &amp; Hourly Rush Velocity</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-[#16A34A] font-bold">✓</span>
                                    <span>Restock Requisitions (1-Click Bulk PO Approval)</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-[#16A34A] font-bold">✓</span>
                                    <span>Sales Audit Ledger Exports (CSV &amp; PDF)</span>
                                </li>
                            </ul>
                        </div>

                        <div class="mt-6 pt-4 border-t border-[#E7E5E4]">
                            <a href="{{ route('demo.login', 'manager') }}" 
                               class="w-full bg-[#D97706] hover:bg-[#B45309] text-white text-xs sm:text-sm font-bold py-2.5 px-4 rounded-xl shadow transition-all flex items-center justify-center gap-2">
                                <span>Launch Executive Dashboard</span>
                                <span class="material-symbols-outlined text-[16px]">login</span>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="mt-8 text-center">
                    <p class="text-xs text-[#78716C]">
                        Want to test the Brute-Force lockout defense mechanism? 
                        <a href="{{ route('login') }}" class="text-[#D97706] font-semibold hover:underline">Go to Standard Sign-In Screen &rarr;</a>
                    </p>
                </div>
            </div>
        </section>

        <!-- KEY FEATURES DEEP DIVE SECTION -->
        <section id="features" class="py-16 sm:py-24 bg-[#FAF2ED] border-b border-[#E7E5E4]">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-3xl mx-auto mb-16">
                    <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-[#F4ECE7] text-[#3B2314] border border-[#E7E5E4] inline-block mb-3">
                        Enterprise Workflows
                    </span>
                    <h2 class="font-heading font-extrabold text-2xl sm:text-4xl text-[#1E1B18] tracking-tight">
                        Designed for Real Specialty Coffee Operations
                    </h2>
                    <p class="text-sm sm:text-base text-[#78716C] mt-2">
                        Engineered with strict transaction safety, robust data integrity, and high-frequency usability:
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <!-- Feature 1 -->
                    <div class="bg-white rounded-2xl p-6 border border-[#E7E5E4] shadow-sm hover:shadow-md transition-shadow">
                        <div class="w-10 h-10 rounded-xl bg-[#FAF2ED] text-[#D97706] flex items-center justify-center font-bold mb-4">
                            <span class="material-symbols-outlined">point_of_sale</span>
                        </div>
                        <h4 class="font-heading font-bold text-base text-[#1E1B18]">POS &amp; Atomic Recipes</h4>
                        <p class="text-xs text-[#78716C] mt-2 leading-relaxed">
                            Every catalog item maps to precise recipe ingredients in grams or milliliters. Stock levels deduct atomically during checkout with complete rollback safety.
                        </p>
                    </div>

                    <!-- Feature 2 -->
                    <div class="bg-white rounded-2xl p-6 border border-[#E7E5E4] shadow-sm hover:shadow-md transition-shadow">
                        <div class="w-10 h-10 rounded-xl bg-[#FAF2ED] text-[#D97706] flex items-center justify-center font-bold mb-4">
                            <span class="material-symbols-outlined">inventory_2</span>
                        </div>
                        <h4 class="font-heading font-bold text-base text-[#1E1B18]">Inventory &amp; PO Engine</h4>
                        <p class="text-xs text-[#78716C] mt-2 leading-relaxed">
                            Configurable thresholds trigger automated low-stock warnings. Baristas initiate restock requests, while managers review, reject, or bulk-approve purchase orders.
                        </p>
                    </div>

                    <!-- Feature 3 -->
                    <div class="bg-white rounded-2xl p-6 border border-[#E7E5E4] shadow-sm hover:shadow-md transition-shadow">
                        <div class="w-10 h-10 rounded-xl bg-[#FAF2ED] text-[#D97706] flex items-center justify-center font-bold mb-4">
                            <span class="material-symbols-outlined">badge</span>
                        </div>
                        <h4 class="font-heading font-bold text-base text-[#1E1B18]">HR Roster &amp; Payroll</h4>
                        <p class="text-xs text-[#78716C] mt-2 leading-relaxed">
                            Clock-in logs automatically detect tardiness (>15m) and alert HR. Unscheduled shifts require manager PIN override. Overtime wage totals aggregate in real-time.
                        </p>
                    </div>

                    <!-- Feature 4 -->
                    <div class="bg-white rounded-2xl p-6 border border-[#E7E5E4] shadow-sm hover:shadow-md transition-shadow">
                        <div class="w-10 h-10 rounded-xl bg-[#FAF2ED] text-[#D97706] flex items-center justify-center font-bold mb-4">
                            <span class="material-symbols-outlined">monitoring</span>
                        </div>
                        <h4 class="font-heading font-bold text-base text-[#1E1B18]">Intelligence &amp; Auditing</h4>
                        <p class="text-xs text-[#78716C] mt-2 leading-relaxed">
                            Hourly sales velocity highlights morning and afternoon rushes. Audits labor cost ratios against revenue, providing 1-click exports to CSV and styled landscape PDF reports.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- TECH STACK & SYSTEM ARCHITECTURE -->
        <section id="architecture" class="py-16 sm:py-20 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="bg-gradient-to-br from-[#3B2314] to-[#1E120A] rounded-3xl p-8 sm:p-12 text-white relative overflow-hidden shadow-2xl">
                    <div class="max-w-3xl">
                        <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-white/10 text-[#D97706] border border-white/15 inline-block mb-3">
                            Engineering Standards
                        </span>
                        <h2 class="font-heading font-extrabold text-2xl sm:text-4xl tracking-tight text-white">
                            Modern Architecture &amp; Code Quality
                        </h2>
                        <p class="text-sm sm:text-base text-[#FAF2ED]/80 mt-3 leading-relaxed">
                            Built following strict Laravel industry conventions, Service Layer separation of concerns, and automated integration testing.
                        </p>
                    </div>

                    <div class="mt-8 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
                        <div class="bg-white/10 backdrop-blur-sm p-3.5 rounded-xl border border-white/10 text-center">
                            <p class="text-xs text-[#FAF2ED]/70 font-semibold">Backend</p>
                            <p class="font-heading font-bold text-sm sm:text-base text-white mt-1">Laravel 12 / 11</p>
                        </div>
                        <div class="bg-white/10 backdrop-blur-sm p-3.5 rounded-xl border border-white/10 text-center">
                            <p class="text-xs text-[#FAF2ED]/70 font-semibold">Runtime</p>
                            <p class="font-heading font-bold text-sm sm:text-base text-[#D97706] mt-1">PHP 8.3 / 8.2</p>
                        </div>
                        <div class="bg-white/10 backdrop-blur-sm p-3.5 rounded-xl border border-white/10 text-center">
                            <p class="text-xs text-[#FAF2ED]/70 font-semibold">Database</p>
                            <p class="font-heading font-bold text-sm sm:text-base text-white mt-1">MySQL / SQLite</p>
                        </div>
                        <div class="bg-white/10 backdrop-blur-sm p-3.5 rounded-xl border border-white/10 text-center">
                            <p class="text-xs text-[#FAF2ED]/70 font-semibold">Styling</p>
                            <p class="font-heading font-bold text-sm sm:text-base text-sky-300 mt-1">Tailwind CSS 4</p>
                        </div>
                        <div class="bg-white/10 backdrop-blur-sm p-3.5 rounded-xl border border-white/10 text-center">
                            <p class="text-xs text-[#FAF2ED]/70 font-semibold">Reactivity</p>
                            <p class="font-heading font-bold text-sm sm:text-base text-white mt-1">Alpine.js 3</p>
                        </div>
                        <div class="bg-white/10 backdrop-blur-sm p-3.5 rounded-xl border border-white/10 text-center">
                            <p class="text-xs text-[#FAF2ED]/70 font-semibold">Test Suite</p>
                            <p class="font-heading font-bold text-sm sm:text-base text-emerald-400 mt-1">53 Feature Tests</p>
                        </div>
                    </div>

                    <div class="mt-8 flex flex-wrap items-center gap-4 pt-6 border-t border-white/15">
                        <a href="{{ route('demo.login', 'manager') }}" class="bg-[#D97706] hover:bg-[#B45309] text-white font-bold px-5 py-2.5 rounded-xl text-xs sm:text-sm shadow transition-all flex items-center gap-1.5">
                            <span>Open Live Demo Now</span>
                            <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
                        </a>
                        <a href="{{ route('login') }}" class="bg-white/10 hover:bg-white/20 text-white font-semibold px-4 py-2.5 rounded-xl text-xs sm:text-sm border border-white/20 transition-colors">
                            Standard Sign-In Screen
                        </a>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="bg-[#2A180E] text-[#FAF2ED]/70 py-8 border-t border-[#3B2314]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-[#D97706]"></span>
                <span class="font-heading font-bold text-white text-sm">CafeSync Platform</span>
                <span>&bull; Artisanal Specialty Cafe Operations &amp; POS</span>
            </div>
            <p>&copy; {{ date('Y') }} CafeSync. Enterprise Information System Portfolio.</p>
        </div>
    </footer>
</body>
</html>
