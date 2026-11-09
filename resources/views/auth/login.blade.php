<!DOCTYPE html>
<html lang="en" class="h-full bg-[#FDFBF7]">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sign In to CafeSync Portal - Specialty Cafe Operations</title>

    <!-- Google Fonts: Plus Jakarta Sans & Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">

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
<body class="min-h-full flex items-center justify-center p-4 sm:p-6 lg:p-8 bg-[#FDFBF7]">

    <!-- Main Container Card (Split 2-Column Layout) -->
    <div 
        x-data="{
            activeRole: '{{ old('role', 'STAFF') }}',
            email: '{{ old('email', 'cashier@cafesync.local') }}',
            password: '',
            showPassword: false,
            selectRole(role, defaultEmail) {
                this.activeRole = role;
                this.email = defaultEmail;
            }
        }"
        class="w-full max-w-5xl bg-white rounded-2xl shadow-xl border border-[#E7E5E4] overflow-hidden grid grid-cols-1 lg:grid-cols-12 min-h-[620px]"
    >
        <!-- LEFT PANEL: Brand Showcase (Dark Roast Theme #33302C / #3B2314) -->
        <div class="lg:col-span-5 bg-gradient-to-br from-[#3B2314] via-[#2A180E] to-[#1E120A] text-white p-8 sm:p-10 flex flex-col justify-between relative overflow-hidden">
            <!-- Decorative Espresso Glow Elements -->
            <div class="absolute -top-24 -left-24 w-64 h-64 bg-[#D97706]/15 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute -bottom-24 -right-24 w-72 h-72 bg-[#D97706]/10 rounded-full blur-3xl pointer-events-none"></div>

            <!-- Header Branding -->
            <div class="relative z-10">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-[#D97706] to-[#B45309] flex items-center justify-center shadow-lg ring-2 ring-white/10">
                        <!-- Coffee Cup Icon -->
                        <svg class="w-7 h-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 8h1a4 4 0 0 1 0 8h-1M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"/>
                            <line x1="6" y1="1" x2="6" y2="4" stroke-linecap="round" stroke-width="2"/>
                            <line x1="10" y1="1" x2="10" y2="4" stroke-linecap="round" stroke-width="2"/>
                            <line x1="14" y1="1" x2="14" y2="4" stroke-linecap="round" stroke-width="2"/>
                        </svg>
                    </div>
                    <div>
                        <h1 class="font-heading font-extrabold text-2xl tracking-tight text-white leading-none">CafeSync</h1>
                        <span class="text-xs uppercase font-semibold tracking-widest text-[#D97706]">Specialty Operations</span>
                    </div>
                </div>

                <div class="mt-8 space-y-3">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 backdrop-blur-sm border border-white/10 text-xs font-medium text-[#FAF2ED]">
                        <span class="w-2 h-2 rounded-full bg-[#16A34A] animate-pulse"></span>
                        <span>Engine v1.0 &bull; Multi-Role Guard</span>
                    </div>
                    <h2 class="font-heading text-2xl sm:text-3xl font-bold leading-tight text-white">
                        Seamless Operations for Modern Specialty Coffee.
                    </h2>
                    <p class="text-sm text-[#FAF2ED]/80 leading-relaxed">
                        A unified platform for high-speed POS orders, staff attendance & overtime, raw inventory tracking, and executive analytics.
                    </p>
                </div>
            </div>

            <!-- Middle Highlights / Feature Metrics -->
            <div class="relative z-10 my-8 py-5 border-y border-white/10 space-y-3">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-[#D97706]/20 flex items-center justify-center text-[#D97706]">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-white">Automated Inventory Deduction</p>
                        <p class="text-[11px] text-[#FAF2ED]/60">Atomic recipe-level gram &amp; ml stock deductions</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-emerald-500/20 flex items-center justify-center text-emerald-400">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-white">Brute-Force Lockout Defense</p>
                        <p class="text-[11px] text-[#FAF2ED]/60">Automatic 15m lockout after 3 consecutive failed logins</p>
                    </div>
                </div>
            </div>

            <!-- Bottom Quote & Aesthetic Stempel -->
            <div class="relative z-10 pt-2">
                <blockquote class="italic text-xs text-[#FAF2ED]/70 border-l-2 border-[#D97706] pl-3">
                    "Crafting perfection in every cup, syncing excellence in every shift."
                </blockquote>
                <p class="mt-3 text-[11px] text-[#FAF2ED]/50 font-medium">
                    &copy; {{ date('Y') }} CafeSync Platform &bull; Enterprise Monolith
                </p>
            </div>
        </div>

        <!-- RIGHT PANEL: Form Card (White Surface Card #FFFFFF) -->
        <div class="lg:col-span-7 p-8 sm:p-12 flex flex-col justify-center bg-white">
            <div class="w-full max-w-md mx-auto">
                <!-- Title & Subtitle -->
                <div class="mb-6">
                    <h3 class="font-heading text-2xl sm:text-3xl font-bold text-[#1E1B18] tracking-tight">
                        Staff Portal Sign-In
                    </h3>
                    <p class="text-sm text-[#78716C] mt-1.5">
                        Select your workstation role and enter your registered credentials.
                    </p>
                </div>

                <!-- Alert Messages: Status / Success -->
                @if (session('status'))
                    <div class="mb-5 bg-[#DCFCE7] border border-[#16A34A]/30 text-[#16A34A] px-4 py-3 rounded-xl flex items-start gap-2.5 text-xs font-medium shadow-sm">
                        <svg class="w-4 h-4 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span>{{ session('status') }}</span>
                    </div>
                @endif

                <!-- Alert Messages: Error / Lockout -->
                @if ($errors->any())
                    <div class="mb-5 bg-[#FEE2E2] border border-[#DC2626]/30 text-[#DC2626] px-4 py-3.5 rounded-xl shadow-sm text-xs space-y-1">
                        <div class="flex items-center gap-2 font-semibold">
                            <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                            <span>Authentication Warning:</span>
                        </div>
                        <ul class="list-disc list-inside pl-1 text-[13px] leading-relaxed">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Role Pill Switcher (Stitch Component & Alpine.js) -->
                <div class="mb-6">
                    <label class="block text-xs font-semibold uppercase tracking-wider text-[#78716C] mb-2">
                        Select Workstation Role
                    </label>
                    <div class="bg-[#F4ECE7] p-1.5 rounded-xl flex gap-1 border border-[#E7E5E4]">
                        <!-- Staff Pill -->
                        <button 
                            type="button" 
                            @click="selectRole('STAFF', 'cashier@cafesync.local')" 
                            :class="activeRole === 'STAFF' ? 'bg-[#3B2314] text-white shadow-sm font-semibold' : 'text-[#78716C] hover:text-[#1E1B18] font-medium'"
                            class="flex-1 py-2 px-2.5 rounded-lg text-xs sm:text-sm text-center transition-all flex items-center justify-center gap-1.5 cursor-pointer"
                        >
                            <span>☕</span>
                            <span>Staff / Cashier</span>
                        </button>

                        <!-- HRD Pill -->
                        <button 
                            type="button" 
                            @click="selectRole('HRD', 'hrd@cafesync.local')" 
                            :class="activeRole === 'HRD' ? 'bg-[#3B2314] text-white shadow-sm font-semibold' : 'text-[#78716C] hover:text-[#1E1B18] font-medium'"
                            class="flex-1 py-2 px-2.5 rounded-lg text-xs sm:text-sm text-center transition-all flex items-center justify-center gap-1.5 cursor-pointer"
                        >
                            <span>📋</span>
                            <span>HR &amp; Shift</span>
                        </button>

                        <!-- Manager Pill -->
                        <button 
                            type="button" 
                            @click="selectRole('MANAGER', 'manager@cafesync.local')" 
                            :class="activeRole === 'MANAGER' ? 'bg-[#3B2314] text-white shadow-sm font-semibold' : 'text-[#78716C] hover:text-[#1E1B18] font-medium'"
                            class="flex-1 py-2 px-2.5 rounded-lg text-xs sm:text-sm text-center transition-all flex items-center justify-center gap-1.5 cursor-pointer"
                        >
                            <span>📊</span>
                            <span>Manager</span>
                        </button>
                    </div>
                </div>

                <!-- Main Login Form -->
                <form action="{{ route('login.post') }}" method="POST" class="space-y-4">
                    @csrf
                    
                    <!-- Hidden Selected Role Field -->
                    <input type="hidden" name="role" :value="activeRole">

                    <!-- Email Input Field -->
                    <div>
                        <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-[#78716C] mb-1.5">
                            Work Email Address
                        </label>
                        <div class="relative rounded-lg shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#78716C]">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.206"/>
                                </svg>
                            </div>
                            <input 
                                type="email" 
                                id="email" 
                                name="email" 
                                x-model="email"
                                required 
                                autocomplete="email"
                                placeholder="name@cafesync.local"
                                class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-[#E7E5E4] focus:outline-none focus:ring-2 focus:ring-[#D97706] focus:border-transparent text-sm text-[#1E1B18] transition-all bg-white placeholder-[#78716C]/50"
                            >
                        </div>
                    </div>

                    <!-- Password Input Field -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-[#78716C]">
                                Password
                            </label>
                            <span class="text-[11px] text-[#78716C]">Bcrypt Secured</span>
                        </div>
                        <div class="relative rounded-lg shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#78716C]">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                            </div>
                            <input 
                                :type="showPassword ? 'text' : 'password'" 
                                id="password" 
                                name="password" 
                                x-model="password"
                                required 
                                autocomplete="current-password"
                                placeholder="••••••••"
                                class="w-full pl-10 pr-10 py-2.5 rounded-lg border border-[#E7E5E4] focus:outline-none focus:ring-2 focus:ring-[#D97706] focus:border-transparent text-sm text-[#1E1B18] transition-all bg-white placeholder-[#78716C]/50"
                            >
                            <button 
                                type="button" 
                                @click="showPassword = !showPassword"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-[#78716C] hover:text-[#1E1B18] cursor-pointer"
                                title="Toggle password visibility"
                            >
                                <svg x-show="!showPassword" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                <svg x-show="showPassword" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="display: none;">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Options: Remember Me & Reset info -->
                    <div class="flex items-center justify-between text-xs pt-1">
                        <label class="flex items-center gap-2 cursor-pointer text-[#78716C] hover:text-[#1E1B18]">
                            <input 
                                type="checkbox" 
                                name="remember" 
                                class="rounded border-[#E7E5E4] text-[#D97706] focus:ring-[#D97706] w-4 h-4"
                            >
                            <span>Remember session (8 Hours)</span>
                        </label>
                        <span class="text-[#D97706] hover:underline cursor-pointer" title="Contact General Manager for credential resets">
                            Need Help?
                        </span>
                    </div>

                    <!-- Primary Action Button (Warm Amber #D97706) -->
                    <div class="pt-2">
                        <button 
                            type="submit" 
                            class="w-full bg-[#D97706] hover:bg-[#B45309] text-white font-semibold py-3 px-4 rounded-xl shadow-md hover:shadow-lg active:scale-[0.98] transition-all flex items-center justify-center gap-2 text-sm tracking-wide cursor-pointer"
                        >
                            <span>Sign In to CafeSync Terminal</span>
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                            </svg>
                        </button>
                    </div>
                </form>

                <!-- Quick Testing Demo Account Pill Selection -->
                <div class="mt-6 pt-5 border-t border-[#E7E5E4]">
                    <p class="text-[11px] font-semibold text-[#78716C] uppercase tracking-wider mb-2.5 flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#D97706]"></span>
                        <span>Quick Demo Accounts (Password: <code>password123</code>):</span>
                    </p>
                    <div class="grid grid-cols-3 gap-2">
                        <button 
                            type="button"
                            @click="selectRole('STAFF', 'cashier@cafesync.local'); password = 'password123'"
                            class="px-2.5 py-1.5 rounded-lg border border-[#E7E5E4] bg-[#FAF2ED] hover:bg-[#F4ECE7] text-left transition-colors cursor-pointer"
                        >
                            <p class="text-[11px] font-bold text-[#3B2314]">Alex (Staff)</p>
                            <p class="text-[10px] text-[#78716C] truncate">/pos</p>
                        </button>

                        <button 
                            type="button"
                            @click="selectRole('HRD', 'hrd@cafesync.local'); password = 'password123'"
                            class="px-2.5 py-1.5 rounded-lg border border-[#E7E5E4] bg-[#FAF2ED] hover:bg-[#F4ECE7] text-left transition-colors cursor-pointer"
                        >
                            <p class="text-[11px] font-bold text-[#3B2314]">David (HRD)</p>
                            <p class="text-[10px] text-[#78716C] truncate">/hr/roster</p>
                        </button>

                        <button 
                            type="button"
                            @click="selectRole('MANAGER', 'manager@cafesync.local'); password = 'password123'"
                            class="px-2.5 py-1.5 rounded-lg border border-[#E7E5E4] bg-[#FAF2ED] hover:bg-[#F4ECE7] text-left transition-colors cursor-pointer"
                        >
                            <p class="text-[11px] font-bold text-[#3B2314]">Elena (Manager)</p>
                            <p class="text-[10px] text-[#78716C] truncate">/dashboard</p>
                        </button>
                    </div>
                </div>

                <!-- Footer Security Note -->
                <div class="mt-5 text-center">
                    <p class="text-[11px] text-[#78716C] leading-normal flex items-center justify-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span>Security Defense: Automatic 15-minute lockout after 3 consecutive failed logins.</span>
                    </p>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
