@extends('layouts.app')

@section('title', 'Roster & Presensi HR - CafeSync')

@section('content')
<div class="space-y-6" x-data="{
    activeTab: '{{ $activeTab ?? 'roster' }}',
    showManualAttendanceModal: false,
    showAddScheduleModal: false,
    selectedStaffId: '',
    attendanceAction: 'clock_in',
    selectedStaffName: '',
    selectedStaffRate: 0,
    modalOverridePin: '',
    modalOverrideNotes: '',
    openPresensiModal(staffId, staffName, rate, isClockedIn) {
        this.selectedStaffId = staffId;
        this.selectedStaffName = staffName;
        this.selectedStaffRate = rate;
        this.attendanceAction = isClockedIn ? 'clock_out' : 'clock_in';
        this.modalOverridePin = '';
        this.modalOverrideNotes = '';
        this.showManualAttendanceModal = true;
    }
}">
    <!-- Header Bar with Filter & Quick Actions -->
    <div class="bg-white rounded-2xl p-6 border border-[#E7E5E4] shadow-sm flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-[#16A34A] border border-emerald-200">
                    HR &amp; Employee Portal
                </span>
                <span class="text-xs text-[#78716C]">Shift Attendance, Rostering &amp; Payroll</span>
            </div>
            <h1 class="text-2xl font-bold font-heading text-[#1E1B18] mt-1">Staff Roster &amp; Attendance Management</h1>
            <p class="text-xs sm:text-sm text-[#78716C]">Work shift monitoring, automated tardiness alerts, and monthly payroll calculation.</p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <!-- Month Selector Filter -->
            <form action="{{ route('hr.roster') }}" method="GET" class="flex items-center gap-2 bg-[#FAF2ED] p-1.5 rounded-xl border border-[#E7E5E4]">
                <input type="hidden" name="tab" :value="activeTab">
                <select name="month" onchange="this.form.submit()" class="bg-white border border-[#E7E5E4] text-xs font-semibold rounded-lg px-2.5 py-1.5 text-[#3B2314] focus:ring-1 focus:ring-[#D97706]">
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create(null, $m, 1)->translatedFormat('F') }}
                        </option>
                    @endfor
                </select>
                <select name="year" onchange="this.form.submit()" class="bg-white border border-[#E7E5E4] text-xs font-semibold rounded-lg px-2.5 py-1.5 text-[#3B2314] focus:ring-1 focus:ring-[#D97706]">
                    @for($y = 2025; $y <= 2027; $y++)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </form>

            <!-- Action Buttons -->
            <button type="button" @click="showAddScheduleModal = true" class="px-3.5 py-2 rounded-xl bg-white border border-[#E7E5E4] text-[#3B2314] hover:bg-[#FAF2ED] text-xs font-semibold transition-colors flex items-center gap-1.5 shadow-sm">
                <svg class="w-4 h-4 text-[#D97706]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Atur Shift</span>
            </button>

            <button type="button" @click="openPresensiModal('', 'Pilih Karyawan', 0, false)" class="px-4 py-2 rounded-xl bg-[#D97706] hover:bg-[#B45309] text-white text-xs font-semibold transition-colors flex items-center gap-1.5 shadow-sm active:scale-95">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                <span>Catat Presensi</span>
            </button>
        </div>
    </div>

    <!-- Alert HR Keterlambatan Banner (High Priority) -->
    @if(isset($unacknowledged_alerts) && $unacknowledged_alerts->count() > 0)
    <div class="bg-gradient-to-r from-amber-500/10 via-orange-500/10 to-red-500/10 border-l-4 border-[#EA580C] bg-white p-5 rounded-2xl border border-[#E7E5E4] shadow-sm">
        <div class="flex items-start justify-between gap-4">
            <div class="flex items-start gap-3">
                <div class="w-9 h-9 rounded-xl bg-[#EA580C] text-white flex items-center justify-center font-bold text-base flex-shrink-0 shadow">
                    ⚠️
                </div>
                <div>
                    <h3 class="text-sm font-bold font-heading text-[#1E1B18] flex items-center gap-2">
                        Peringatan Presensi HR: Terdapat {{ $unacknowledged_alerts->count() }} Keterlambatan Shift
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#FFEDD5] text-[#EA580C]">Menunggu Tindak Lanjut</span>
                    </h3>
                    <div class="mt-2 space-y-2">
                        @foreach($unacknowledged_alerts as $alert)
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 p-2.5 rounded-xl bg-[#FAF2ED] border border-[#E7E5E4] text-xs">
                            <div>
                                <span class="font-bold text-[#3B2314]">{{ $alert->user->full_name }}</span> &bull; 
                                <span class="text-[#78716C]">{{ $alert->message }}</span>
                                <span class="text-[10px] text-slate-400 block sm:inline sm:ml-2 font-tabular">({{ $alert->created_at->diffForHumans() }})</span>
                            </div>
                            <form action="{{ route('hr.alerts.acknowledge', $alert->alert_id) }}" method="POST" class="inline flex-shrink-0">
                                @csrf
                                <button type="submit" class="px-3 py-1 rounded-lg bg-white border border-[#E7E5E4] hover:bg-emerald-50 hover:text-emerald-700 hover:border-emerald-300 font-semibold text-[11px] text-[#3B2314] transition-colors shadow-xs">
                                    ✓ Tandai Selesai
                                </button>
                            </form>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- 4 KPI Metrics Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Staff -->
        <div class="bg-white p-5 rounded-2xl border border-[#E7E5E4] shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-[#78716C] uppercase">Total Karyawan</span>
                <span class="w-8 h-8 rounded-lg bg-slate-100 text-[#3B2314] flex items-center justify-center font-bold text-sm">👥</span>
            </div>
            <p class="text-3xl font-extrabold font-heading text-[#3B2314] font-tabular">{{ $total_staff ?? count($staffMembers) }}</p>
            <p class="text-xs text-[#78716C] mt-1">Staff, HRD & Manager Terdaftar</p>
        </div>

        <!-- Active on Floor -->
        <div class="bg-white p-5 rounded-2xl border border-[#E7E5E4] shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-[#78716C] uppercase">Sedang Bertugas</span>
                <span class="w-8 h-8 rounded-lg bg-emerald-50 text-[#16A34A] flex items-center justify-center font-bold text-sm">☕</span>
            </div>
            <p class="text-3xl font-extrabold font-heading text-[#16A34A] font-tabular">{{ $active_staff_count ?? 0 }}</p>
            <p class="text-xs text-[#78716C] mt-1">Staf aktif di lantai kafe saat ini</p>
        </div>

        <!-- Total Monthly Hours -->
        <div class="bg-white p-5 rounded-2xl border border-[#E7E5E4] shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-[#78716C] uppercase">Jam Kerja Bulan Ini</span>
                <span class="w-8 h-8 rounded-lg bg-amber-50 text-[#D97706] flex items-center justify-center font-bold text-sm">⏱️</span>
            </div>
            <p class="text-3xl font-extrabold font-heading text-[#3B2314] font-tabular">{{ number_format($total_monthly_hours ?? 0, 1) }} <span class="text-sm font-semibold text-[#78716C]">jam</span></p>
            <p class="text-xs text-[#78716C] mt-1">Periode {{ $month_name ?? 'Bulan Berjalan' }}</p>
        </div>

        <!-- Estimated Labor Cost -->
        <div class="bg-white p-5 rounded-2xl border border-[#E7E5E4] shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-[#78716C] uppercase">Estimasi Biaya Upah</span>
                <span class="w-8 h-8 rounded-lg bg-emerald-50 text-[#16A34A] flex items-center justify-center font-bold text-sm">💵</span>
            </div>
            <p class="text-3xl font-extrabold font-heading text-[#16A34A] font-tabular">${{ number_format($total_monthly_payroll ?? 0, 2) }}</p>
            <p class="text-xs text-[#78716C] mt-1">Formula: total_hours &times; hourly_rate</p>
        </div>
    </div>

    <!-- Tab Navigation Bar (Pill Tabs) -->
    <div class="bg-[#F4ECE7] p-1.5 rounded-2xl flex gap-1.5 overflow-x-auto w-full sm:w-max border border-[#E7E5E4]">
        <button type="button" @click="activeTab = 'roster'" :class="activeTab === 'roster' ? 'bg-[#3B2314] text-white shadow-sm' : 'text-[#78716C] hover:text-[#1E1B18]'" class="px-5 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2">
            <span>📋 Staff Roster & Payroll Estimator</span>
            <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-white/20">{{ count($staff_roster ?? $staffMembers) }}</span>
        </button>

        <button type="button" @click="activeTab = 'schedules'" :class="activeTab === 'schedules' ? 'bg-[#3B2314] text-white shadow-sm' : 'text-[#78716C] hover:text-[#1E1B18]'" class="px-5 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2">
            <span>🗓️ Penjadwalan Shift</span>
            <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-white/20">{{ count($shift_schedules ?? []) }}</span>
        </button>

        <button type="button" @click="activeTab = 'logs'" :class="activeTab === 'logs' ? 'bg-[#3B2314] text-white shadow-sm' : 'text-[#78716C] hover:text-[#1E1B18]'" class="px-5 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2">
            <span>📊 Log Riwayat Presensi</span>
            <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-white/20">{{ count($recent_attendances ?? $recentAttendances) }}</span>
        </button>
    </div>

    <!-- ======================================================== -->
    <!-- TAB 1: STAFF ROSTER & ESTIMASI TOTAL UPAH BULANAN -->
    <!-- ======================================================== -->
    <div x-show="activeTab === 'roster'" x-cloak class="bg-white rounded-2xl border border-[#E7E5E4] shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-[#E7E5E4] flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <div>
                <h2 class="text-base font-bold font-heading text-[#1E1B18]">Daftar Karyawan & Estimasi Penggajian Bulanan</h2>
                <p class="text-xs text-[#78716C]">Perhitungan otomatis upah: <code>total_hours &times; hourly_rate</code> (Periode: {{ $month_name }})</p>
            </div>
            <span class="text-xs text-[#D97706] font-semibold bg-[#FAF2ED] px-3 py-1 rounded-full border border-[#D97706]/20">
                Sprint 3 (v0.4.0) Verified
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-[#FAF2ED] text-[#78716C] text-xs font-semibold uppercase tracking-wider border-b border-[#E7E5E4]">
                    <tr>
                        <th class="px-6 py-3">Karyawan</th>
                        <th class="px-6 py-3">Peran (Role)</th>
                        <th class="px-6 py-3">Shift Hari Ini</th>
                        <th class="px-6 py-3">Status Saat Ini</th>
                        <th class="px-6 py-3">Tarif / Jam</th>
                        <th class="px-6 py-3">Jam Kerja Bulan Ini</th>
                        <th class="px-6 py-3">Lembur</th>
                        <th class="px-6 py-3 text-right">Estimasi Upah Bulanan</th>
                        <th class="px-6 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E7E5E4] text-[#1E1B18]">
                    @foreach($staff_roster as $item)
                    @php
                        $u = $item['user'];
                        $pay = $item['payroll'];
                        $active = $item['active_shift'];
                        $sched = $item['today_schedule'];
                    @endphp
                    <tr class="hover:bg-[#FAF2ED]/50 transition-colors">
                        <!-- Employee Info -->
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full bg-[#3B2314] text-white flex items-center justify-center font-bold text-xs shadow-xs">
                                    {{ strtoupper(substr($u->full_name, 0, 1)) }}
                                </div>
                                <div>
                                    <p class="font-bold text-[#1E1B18] leading-tight">{{ $u->full_name }}</p>
                                    <p class="text-xs text-[#78716C] font-mono">{{ $u->email }}</p>
                                </div>
                            </div>
                        </td>

                        <!-- Role -->
                        <td class="px-6 py-4">
                            @if($u->role === 'STAFF')
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-50 text-[#D97706] border border-amber-200">STAFF</span>
                            @elseif($u->role === 'HRD')
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-[#16A34A] border border-emerald-200">HRD</span>
                            @else
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-[#3B2314] text-white">MANAGER</span>
                            @endif
                        </td>

                        <!-- Today Schedule -->
                        <td class="px-6 py-4 text-xs">
                            @if($sched)
                                <span class="font-semibold text-[#1E1B18] block">{{ $sched->shift_name }}</span>
                                <span class="text-[#78716C] font-tabular">{{ \Carbon\Carbon::parse($sched->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($sched->end_time)->format('H:i') }} WIB</span>
                            @else
                                <span class="text-amber-700 bg-amber-50 px-2 py-0.5 rounded text-[11px] font-medium border border-amber-200">
                                    Tak Terjadwal / Off
                                </span>
                            @endif
                        </td>

                        <!-- Current Attendance Status -->
                        <td class="px-6 py-4 text-xs">
                            @if($active)
                                <span class="px-2.5 py-1 rounded-full font-bold bg-[#DCFCE7] text-[#16A34A] border border-[#16A34A]/30 flex items-center gap-1.5 w-max">
                                    <span class="w-2 h-2 rounded-full bg-[#16A34A] animate-pulse"></span>
                                    Bertugas ({{ $active->clock_in->format('H:i') }})
                                </span>
                            @else
                                <span class="px-2.5 py-0.5 rounded-full font-medium bg-slate-100 text-slate-600">
                                    Tidak Aktif
                                </span>
                            @endif
                        </td>

                        <!-- Hourly Rate -->
                        <td class="px-6 py-4 font-bold font-tabular text-[#3B2314]">
                            ${{ number_format($u->hourly_rate, 2) }}
                        </td>

                        <!-- Monthly Total Hours -->
                        <td class="px-6 py-4 font-bold font-tabular text-[#1E1B18]">
                            {{ number_format($pay['total_hours'], 2) }} <span class="text-xs font-normal text-[#78716C]">jam</span>
                        </td>

                        <!-- Overtime Hours -->
                        <td class="px-6 py-4 font-tabular">
                            @if($pay['overtime_hours'] > 0)
                                <span class="text-purple-700 font-bold bg-purple-50 px-2 py-0.5 rounded border border-purple-200">
                                    +{{ number_format($pay['overtime_hours'], 2) }} jam
                                </span>
                            @else
                                <span class="text-[#78716C]">0.00 jam</span>
                            @endif
                        </td>

                        <!-- Estimated Monthly Wage -->
                        <td class="px-6 py-4 text-right">
                            <p class="text-base font-extrabold font-tabular text-[#D97706]">
                                ${{ number_format($pay['estimated_wage'], 2) }}
                            </p>
                            <span class="text-[11px] text-[#78716C] font-tabular block">
                                {{ $pay['total_hours'] }}j &times; ${{ number_format($pay['hourly_rate'], 2) }}
                            </span>
                        </td>

                        <!-- Quick Actions -->
                        <td class="px-6 py-4 text-center">
                            <button type="button" 
                                    @click="openPresensiModal({{ $u->user_id }}, '{{ addslashes($u->full_name) }}', {{ (float)$u->hourly_rate }}, {{ $active ? 'true' : 'false' }})"
                                    class="px-3 py-1.5 rounded-lg border border-[#E7E5E4] hover:bg-[#FAF2ED] text-xs font-semibold text-[#3B2314] transition-colors shadow-xs">
                                {{ $active ? 'Clock Out' : 'Presensi' }}
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- TAB 2: JADWAL SHIFT KARYAWAN (SHIFT SCHEDULES) -->
    <!-- ======================================================== -->
    <div x-show="activeTab === 'schedules'" x-cloak class="bg-white rounded-2xl border border-[#E7E5E4] shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-[#E7E5E4] flex items-center justify-between">
            <div>
                <h2 class="text-base font-bold font-heading text-[#1E1B18]">Penugasan Jadwal Shift Kafe</h2>
                <p class="text-xs text-[#78716C]">Alokasi jam kerja staf untuk validasi ketepatan (toleransi 15 menit) dan deteksi lembur.</p>
            </div>
            <button type="button" @click="showAddScheduleModal = true" class="px-3.5 py-1.5 rounded-xl bg-[#D97706] text-white text-xs font-semibold hover:bg-[#B45309] transition-colors flex items-center gap-1">
                + Tambah Jadwal
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-[#FAF2ED] text-[#78716C] text-xs font-semibold uppercase tracking-wider border-b border-[#E7E5E4]">
                    <tr>
                        <th class="px-6 py-3">Nama Karyawan</th>
                        <th class="px-6 py-3">Nama Shift</th>
                        <th class="px-6 py-3">Jam Kerja (WIB)</th>
                        <th class="px-6 py-3">Durasi</th>
                        <th class="px-6 py-3">Hari / Tanggal</th>
                        <th class="px-6 py-3">Keterangan</th>
                        <th class="px-6 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E7E5E4] text-[#1E1B18]">
                    @forelse($shift_schedules as $sched)
                    <tr class="hover:bg-[#FAF2ED]/50 transition-colors">
                        <td class="px-6 py-4 font-semibold text-[#1E1B18] flex items-center gap-2.5">
                            <div class="w-7 h-7 rounded-full bg-[#3B2314] text-white flex items-center justify-center font-bold text-xs">
                                {{ strtoupper(substr($sched->user->full_name, 0, 1)) }}
                            </div>
                            <span>{{ $sched->user->full_name }}</span>
                        </td>
                        <td class="px-6 py-4 font-medium">{{ $sched->shift_name }}</td>
                        <td class="px-6 py-4 font-bold font-tabular text-[#3B2314]">
                            {{ \Carbon\Carbon::parse($sched->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($sched->end_time)->format('H:i') }} WIB
                        </td>
                        <td class="px-6 py-4 font-tabular text-xs">
                            {{ $sched->getDurationHours() }} jam
                        </td>
                        <td class="px-6 py-4 text-xs">
                            @if($sched->scheduled_date)
                                <span class="bg-blue-50 text-blue-700 px-2 py-0.5 rounded border border-blue-200">
                                    {{ $sched->scheduled_date->format('d M Y') }}
                                </span>
                            @else
                                <span class="bg-slate-100 text-slate-700 px-2 py-0.5 rounded font-medium">
                                    Setiap Hari (ALL)
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-xs text-[#78716C]">{{ $sched->notes ?? '-' }}</td>
                        <td class="px-6 py-4 text-center">
                            <form action="{{ route('hr.shifts.destroy', $sched->schedule_id) }}" method="POST" onsubmit="return confirm('Hapus jadwal shift ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-500 hover:text-red-700 text-xs font-semibold p-1">
                                    Hapus
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-[#78716C] text-sm">Belum ada jadwal shift terdaftar.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- TAB 3: RIWAYAT LOG PRESENSI LENGKAP (ATTENDANCE LOGS) -->
    <!-- ======================================================== -->
    <div x-show="activeTab === 'logs'" x-cloak class="bg-white rounded-2xl border border-[#E7E5E4] shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-[#E7E5E4] flex items-center justify-between">
            <div>
                <h2 class="text-base font-bold font-heading text-[#1E1B18]">Log Riwayat Presensi Lengkap</h2>
                <p class="text-xs text-[#78716C]">Audit trail waktu clock-in, clock-out, status keterlambatan, lembur, dan override manager.</p>
            </div>
            <span class="text-xs text-[#78716C]">Menampilkan {{ count($recent_attendances) }} catatan terakhir</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-[#FAF2ED] text-[#78716C] text-xs font-semibold uppercase tracking-wider border-b border-[#E7E5E4]">
                    <tr>
                        <th class="px-6 py-3">Staf</th>
                        <th class="px-6 py-3">Jam Masuk (Clock-In)</th>
                        <th class="px-6 py-3">Jam Keluar (Clock-Out)</th>
                        <th class="px-6 py-3">Total Jam</th>
                        <th class="px-6 py-3">Lembur</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3">Catatan Override</th>
                        <th class="px-6 py-3 text-right">Upah Shift</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E7E5E4] text-[#1E1B18]">
                    @forelse($recent_attendances as $att)
                    <tr class="hover:bg-[#FAF2ED]/50 transition-colors">
                        <td class="px-6 py-4 font-semibold text-[#1E1B18] flex items-center gap-2.5">
                            <div class="w-7 h-7 rounded-full bg-[#3B2314] text-white flex items-center justify-center font-bold text-xs">
                                {{ strtoupper(substr($att->user->full_name, 0, 1)) }}
                            </div>
                            <div>
                                <p class="leading-tight">{{ $att->user->full_name }}</p>
                                <span class="text-[10px] text-[#78716C] font-mono">{{ $att->user->role }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 font-tabular text-xs">
                            <span class="font-bold text-[#1E1B18] block">{{ $att->clock_in->format('H:i') }} WIB</span>
                            <span class="text-[#78716C]">{{ $att->clock_in->format('d M Y') }}</span>
                        </td>
                        <td class="px-6 py-4 font-tabular text-xs">
                            @if($att->clock_out)
                                <span class="font-bold text-[#1E1B18] block">{{ $att->clock_out->format('H:i') }} WIB</span>
                                <span class="text-[#78716C]">{{ $att->clock_out->format('d M Y') }}</span>
                            @else
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-[#DCFCE7] text-[#16A34A] animate-pulse">
                                    Sedang Aktif
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 font-bold font-tabular text-[#3B2314]">
                            {{ number_format($att->total_hours, 2) }} jam
                        </td>
                        <td class="px-6 py-4 font-tabular">
                            @if($att->overtime_hours > 0)
                                <span class="px-2 py-0.5 rounded text-xs font-bold bg-purple-50 text-purple-700 border border-purple-200">
                                    +{{ number_format($att->overtime_hours, 2) }} jam
                                </span>
                            @else
                                <span class="text-[#78716C] text-xs">0.00</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($att->status === 'ON_TIME')
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-[#DCFCE7] text-[#16A34A] border border-[#16A34A]/30">
                                    ✓ On Time
                                </span>
                            @elseif($att->status === 'LATE')
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-[#FFEDD5] text-[#EA580C] border border-[#EA580C]/30">
                                    ⚠️ Late
                                </span>
                            @else
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-purple-100 text-purple-800 border border-purple-300">
                                    Overtime
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-xs text-[#78716C]">
                            {{ $att->manager_override_notes ?: '-' }}
                        </td>
                        <td class="px-6 py-4 text-right font-extrabold font-tabular text-[#D97706]">
                            ${{ number_format($att->estimatedEarnings(), 2) }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-8 text-center text-[#78716C] text-sm">Belum ada riwayat presensi tercatat.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- MODAL: CATAT PRESENSI MANUAL (CLOCK-IN / CLOCK-OUT) -->
    <!-- ======================================================== -->
    <div x-show="showManualAttendanceModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-[#3B2314]/60 backdrop-blur-sm" @click="showManualAttendanceModal = false"></div>

        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md border border-[#E7E5E4]">
                <div class="bg-[#3B2314] px-6 py-4 text-white flex items-center justify-between">
                    <h3 class="text-base font-bold font-heading">Catat Presensi Staf Manual</h3>
                    <button type="button" @click="showManualAttendanceModal = false" class="text-white/70 hover:text-white">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div class="p-6">
                    <!-- Action Choice: Clock In vs Clock Out -->
                    <div class="flex bg-[#F4ECE7] p-1 rounded-xl mb-4">
                        <button type="button" @click="attendanceAction = 'clock_in'" :class="attendanceAction === 'clock_in' ? 'bg-[#D97706] text-white shadow-sm' : 'text-[#78716C]'" class="w-1/2 py-2 text-xs font-bold rounded-lg transition-colors">
                            Clock In (Masuk Shift)
                        </button>
                        <button type="button" @click="attendanceAction = 'clock_out'" :class="attendanceAction === 'clock_out' ? 'bg-[#DC2626] text-white shadow-sm' : 'text-[#78716C]'" class="w-1/2 py-2 text-xs font-bold rounded-lg transition-colors">
                            Clock Out (Selesai Shift)
                        </button>
                    </div>

                    <!-- Clock In Form -->
                    <form x-show="attendanceAction === 'clock_in'" action="{{ route('attendance.clock-in') }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-bold uppercase text-[#78716C] mb-1">Pilih Karyawan</label>
                            <select name="user_id" x-model="selectedStaffId" required class="w-full px-3 py-2 rounded-lg border border-[#E7E5E4] text-sm focus:ring-2 focus:ring-[#D97706]">
                                <option value="">-- Pilih Staf --</option>
                                @foreach($staffMembers as $s)
                                    <option value="{{ $s->user_id }}">{{ $s->full_name }} ({{ $s->role }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase text-[#78716C] mb-1">PIN Override Manager (Jika Unscheduled)</label>
                            <input type="password" name="override_pin" placeholder="Default: 1234" class="w-full px-3 py-2 rounded-lg border border-[#E7E5E4] text-sm font-mono tracking-wider">
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase text-[#78716C] mb-1">Catatan Persetujuan</label>
                            <input type="text" name="override_notes" placeholder="Alasan presensi manual oleh HR/Manager" class="w-full px-3 py-2 rounded-lg border border-[#E7E5E4] text-xs">
                        </div>

                        <div class="flex items-center justify-end gap-2 pt-2">
                            <button type="button" @click="showManualAttendanceModal = false" class="px-4 py-2 rounded-lg border border-[#E7E5E4] text-xs font-semibold text-[#78716C]">Batal</button>
                            <button type="submit" class="px-5 py-2 rounded-lg bg-[#D97706] text-white font-semibold text-xs hover:bg-[#B45309]">Proses Clock-In</button>
                        </div>
                    </form>

                    <!-- Clock Out Form -->
                    <form x-show="attendanceAction === 'clock_out'" action="{{ route('attendance.clock-out') }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-bold uppercase text-[#78716C] mb-1">Pilih Karyawan yang Sedang Bertugas</label>
                            <select name="user_id" x-model="selectedStaffId" required class="w-full px-3 py-2 rounded-lg border border-[#E7E5E4] text-sm focus:ring-2 focus:ring-[#DC2626]">
                                <option value="">-- Pilih Staf Aktif --</option>
                                @foreach($staffMembers as $s)
                                    @if($s->activeAttendance())
                                        <option value="{{ $s->user_id }}">{{ $s->full_name }} (Shift Sejak: {{ $s->activeAttendance()->clock_in->format('H:i') }} WIB)</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>

                        <div class="p-3 bg-red-50 rounded-xl border border-red-200 text-xs text-red-800">
                            Clock-out akan menghentikan pencatatan shift, menghitung durasi aktif, dan menambahkan jam lembur jika melebihi shift standard.
                        </div>

                        <div class="flex items-center justify-end gap-2 pt-2">
                            <button type="button" @click="showManualAttendanceModal = false" class="px-4 py-2 rounded-lg border border-[#E7E5E4] text-xs font-semibold text-[#78716C]">Batal</button>
                            <button type="submit" class="px-5 py-2 rounded-lg bg-[#DC2626] text-white font-semibold text-xs hover:bg-red-700">Proses Clock-Out</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- MODAL: TAMBAH JADWAL SHIFT KARYAWAN -->
    <!-- ======================================================== -->
    <div x-show="showAddScheduleModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-[#3B2314]/60 backdrop-blur-sm" @click="showAddScheduleModal = false"></div>

        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md border border-[#E7E5E4]">
                <div class="bg-[#3B2314] px-6 py-4 text-white flex items-center justify-between">
                    <h3 class="text-base font-bold font-heading">Tambah Jadwal Shift Karyawan</h3>
                    <button type="button" @click="showAddScheduleModal = false" class="text-white/70 hover:text-white">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <form action="{{ route('hr.shifts.store') }}" method="POST" class="p-6 space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold uppercase text-[#78716C] mb-1">Pilih Karyawan</label>
                        <select name="user_id" required class="w-full px-3 py-2 rounded-lg border border-[#E7E5E4] text-sm focus:ring-2 focus:ring-[#D97706]">
                            <option value="">-- Pilih Staf --</option>
                            @foreach($staffMembers as $s)
                                <option value="{{ $s->user_id }}">{{ $s->full_name }} ({{ $s->role }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-[#78716C] mb-1">Nama Shift</label>
                        <input type="text" name="shift_name" placeholder="contoh: Morning Cashier Shift" required class="w-full px-3 py-2 rounded-lg border border-[#E7E5E4] text-sm">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold uppercase text-[#78716C] mb-1">Jam Mulai (HH:MM)</label>
                            <input type="time" name="start_time" value="08:00" required class="w-full px-3 py-2 rounded-lg border border-[#E7E5E4] text-sm font-mono">
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase text-[#78716C] mb-1">Jam Selesai (HH:MM)</label>
                            <input type="time" name="end_time" value="16:00" required class="w-full px-3 py-2 rounded-lg border border-[#E7E5E4] text-sm font-mono">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-[#78716C] mb-1">Hari Penugasan</label>
                        <select name="day_of_week" class="w-full px-3 py-2 rounded-lg border border-[#E7E5E4] text-sm">
                            <option value="ALL">Setiap Hari Kerja (Senin - Minggu)</option>
                            <option value="Monday">Senin</option>
                            <option value="Tuesday">Selasa</option>
                            <option value="Wednesday">Rabu</option>
                            <option value="Thursday">Kamis</option>
                            <option value="Friday">Jumat</option>
                            <option value="Saturday">Sabtu</option>
                            <option value="Sunday">Minggu</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-[#78716C] mb-1">Catatan</label>
                        <input type="text" name="notes" placeholder="Catatan shift atau penempatan workstation" class="w-full px-3 py-2 rounded-lg border border-[#E7E5E4] text-xs">
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2">
                        <button type="button" @click="showAddScheduleModal = false" class="px-4 py-2 rounded-lg border border-[#E7E5E4] text-xs font-semibold text-[#78716C]">Batal</button>
                        <button type="submit" class="px-5 py-2 rounded-lg bg-[#D97706] text-white font-semibold text-xs hover:bg-[#B45309]">Simpan Jadwal Shift</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
