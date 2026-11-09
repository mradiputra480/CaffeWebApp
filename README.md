# ☕ CafeSync — Specialty Coffee Operations & Multi-Role POS Platform

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel 12">
  <img src="https://img.shields.io/badge/PHP-8.3-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP 8.3">
  <img src="https://img.shields.io/badge/Tailwind_CSS-4.x-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white" alt="Tailwind CSS">
  <img src="https://img.shields.io/badge/Alpine.js-3.x-8BC0D0?style=for-the-badge&logo=alpinedotjs&logoColor=white" alt="Alpine.js">
  <img src="https://img.shields.io/badge/Database-MySQL_%7C_SQLite-4479A1?style=for-the-badge&logo=mysql&logoColor=white" alt="MySQL & SQLite">
  <img src="https://img.shields.io/badge/Tests-53%20Passed%20(100%25)-16A34A?style=for-the-badge&logo=checkmarx&logoColor=white" alt="Tests 53 Passed">
  <img src="https://img.shields.io/badge/License-MIT-blue?style=for-the-badge" alt="License MIT">
</p>

---

## 📌 Ringkasan Eksekutif (Executive Summary)

**CafeSync** adalah platform monolitik web modern untuk digitalisasi operasional kafe artisanal dan *specialty coffee roastery*. Sistem ini menghubungkan 4 pilar penting dalam satu database transaksional:
1. **Web POS Terminal Kasir** dengan pemotongan gramasi bahan baku otomatis berbasis resep (*Atomic Recipe Stock Deduction*).
2. **Manajemen Rantai Pasok & Bahan Mentah** lengkap dengan ambang batas minimum (*low-stock alerts*), *restock requisition*, dan *manager approval deck*.
3. **Presensi Shift Staf & Payroll HR** dengan deteksi keterlambatan otomatis (>15 menit), PIN *override* manajer, dan perhitungan upah lembur bulanan.
4. **Dasbor Analitik Eksekutif** yang mendeteksi tren lonjakan jam sibuk (*sales rush velocity*), rasio biaya tenaga kerja (*labor cost ratio*), dan ekspor laporan audit finansial resmi ke format **CSV** dan **PDF**.

---

## 🚀 Live Demo & Kredensial Uji Coba

> **Interactive Demo:** Kunjungi halaman utama untuk menikmati fitur **1-Click Demo Login** tanpa perlu mengetik kata sandi secara manual.

| Peran (Role) | Pengguna Demo | Alamat Email | Kata Sandi | Portal Akses Langsung |
| :--- | :--- | :--- | :--- | :--- |
| **Cashier (Staff)** | Alex Rivera | `cashier@cafesync.local` | `password123` | `/pos` &bull; Terminal POS Kasir |
| **Barista (Staff)** | Sarah Jenkins | `barista@cafesync.local` | `password123` | `/pos` &bull; Terminal POS Kasir |
| **HRD Manager** | David Miller | `hrd@cafesync.local` | `password123` | `/hr/roster` &bull; Portal Shift & Payroll |
| **Cafe Manager** | Elena Vance | `manager@cafesync.local` | `password123` | `/manager/dashboard` &bull; Executive Analytics |

> 🛡️ **Uji Pertahanan Keamanan (Brute-Force Lockout):** Coba masukkan kata sandi yang salah sebanyak 3 kali berturut-turut pada akun terdaftar di `/login`. Sistem secara otomatis mengunci akun selama 15 menit dan mencatat insiden keamanan ke *log*.

---

## 🌟 Modul & Fitur Utama (Core Modules)

```text
+---------------------------------------------------------------------------------+
|                                 CAFESYNC PLATFORM                                |
+---------------------------------------------------------------------------------+
|   ☕ POS KASIR        📦 INVENTORY          📋 HR & SHIFT       📊 ANALITIK      |
|  - Real-time Cart    - Recipe Mapping     - NFC/PIN Clock-In  - Rush Hour Trend |
|  - Recipe Check      - Threshold Alerts   - Late Detection    - Labor Cost %    |
|  - Cash / QRIS       - Staff Reorder PO   - Manager Override  - CSV Export      |
|  - Thermal Receipt   - Manager Approval   - Auto Payroll Calc - PDF Audit Doc   |
+---------------------------------------------------------------------------------+
```

### 1. ☕ Web POS Terminal & Atomic Recipe Deduction
- **Katalog Menu Real-time:** Menghitung kapasitas saji maksimum (*max servings*) setiap menu secara dinamis berdasarkan sisa stok bahan baku gramatur terendah di gudang.
- **Dua Metode Pembayaran:**
  - *Cash:* Validasi nominal tunai tendered vs total pesanan, kalkulasi otomatis uang kembalian (*change due*).
  - *Dynamic QRIS:* Integrasi simulasi *Payment Gateway QRIS* dengan *polling payload* dan konfirmasi instan.
- **Transaksi ACID:** Apabila stok salah satu bahan baku tidak mencukupi saat proses pembayaran, seluruh pesanan di-rollback otomatis demi konsistensi data.
- **Struk Thermal 80mm:** Tampilan struk kasir profesional siap cetak dengan metadata kasir, breakdown subtotal, pajak PPN 10%, dan metode pembayaran.

### 2. 📦 Gudang Bahan Baku Mentah & Siklus Persetujuan PO
- **Pengawasan Gramasi & Volume:** Melacak bahan baku dalam satuan presisi (`grams`, `ml`, `pcs`) seperti Biji Arabika, Susu Segar, Sirup Vanilla, dan Oat Milk.
- **Ambang Batas Kritis (*Threshold Alerts*):** Indikator visual *Optimal, Low Stock, & Critical/Out of Stock*.
- **Workflow Restock Requisition:** Kasir/staf dapat mengajukan permintaan order ulang saat bahan baku menipis; Manajer memiliki kendali penuh untuk menyetujui tunggal, menyetujui massal (*bulk approve*), atau menolak pesanan.
- **Buku Besar Penerimaan (*Stock Intake Ledger*):** Mencatat setiap pasokan yang masuk, nomor batch, supplier, dan otomatis menuntaskan status PO yang disetujui.

### 3. 📋 HR Staff Shift Roster & Payroll Engine
- **Presensi Shift Kerja:** Pencatatan Clock-In dan Clock-Out karyawan dengan validasi jadwal harian.
- **Deteksi Keterlambatan Otomatis:** Presensi lebih dari 15 menit dari jam mulai jadwal langsung diklasifikasikan sebagai `LATE` dan memicu notifikasi peringatan (*HR Alert*).
- **Manager Override PIN:** Jika karyawan bekerja di luar jadwal shift resmi, sistem meminta PIN verifikasi dari manajer kafe (`1234` atau `9999`).
- **Kalkulator Payroll Bulanan:** Mengakumulasikan total jam kerja reguler dan jam lembur (*overtime*) dikalikan dengan tarif upah per jam (*hourly rate*) masing-masing staf.

### 4. 📊 Dasbor Eksekutif & Intelijen Operasional
- **Metrik Utama (KPIs):** Pendapatan harian/mingguan/bulanan, laba kotor, jumlah transaksi, dan rata-rata nilai keranjang (*Average Ticket Value*).
- **Deteksi Jam Sibuk (*Hourly Sales Velocity*):** Menganalisis lonjakan pesanan pada *Morning Surge* (08:00 - 11:00) dan *Afternoon Surge* (14:00 - 17:00).
- **Audit Rasio Biaya Tenaga Kerja (*Labor Cost Ratio*):** Membandingkan total upah karyawan dengan total pendapatan penjualan (*target sehat: < 30%*).
- **Ekspor Dokumen Resmi:** Cetak laporan audit penjualan berfilter tanggal dalam format **CSV** dan dokumen resmi **PDF** berstandar cetak lanskap A4.

---

## 🛠️ Tech Stack & Standar Arsitektur

- **Backend Framework:** [Laravel 12](https://laravel.com) / PHP 8.3
- **Database Engine:** MySQL 8.4 (InnoDB) & SQLite (Zero-config out of the box)
- **Frontend & Styling:** [Tailwind CSS v4](https://tailwindcss.com) & [Alpine.js v3](https://alpinejs.dev)
- **Bundler:** Vite 6
- **Laporan PDF:** Barryvdh DomPDF
- **Testing Framework:** PHPUnit 12 & Pest (53 Feature Tests, 270 Assertions)
- **Code Linter:** Laravel Pint (Strict PSR-12 standard)
- **Deployment:** Docker Multi-stage, Render Blueprint (`render.yaml`), Fly.io, GitHub Actions CI/CD

---

## ⚙️ Panduan Menjalankan Proyek Secara Lokal

### Prasyarat
- **PHP** `>= 8.2` (Direkomendasikan PHP 8.3)
- **Composer** `>= 2.x`
- **Node.js** `>= 20.x` & `npm`

### Opsi A: Menggunakan Composer & SQLite (Instan Tanpa Konfigurasi DB)

1. **Clone repository:**
   ```bash
   git clone https://github.com/USERNAME/SI-CafeSync.git
   cd SI-CafeSync
   ```

2. **Salin file environment & konfigurasi SQLite:**
   ```bash
   cp .env.example .env
   ```

3. **Install dependensi PHP & Node.js:**
   ```bash
   composer install
   npm install
   ```

4. **Generate App Key & Build Assets:**
   ```bash
   php artisan key:generate
   npm run build
   ```

5. **Jalankan Migrasi & Seeder Demo Data:**
   ```bash
   php artisan migrate --seed
   ```

6. **Jalankan Development Server:**
   ```bash
   php artisan serve
   ```
   Buka browser di `http://localhost:8000`.

---

### Opsi B: Menggunakan Docker Compose (1 Perintah)

```bash
docker compose up -d
```
Aplikasi langsung berjalan di `http://localhost:8080` lengkap dengan migrasi database SQLite dan seeder data otomatis.

---

## 🧪 Menjalankan Pengujian Otomatis (Automated Testing)

Proyek ini dilengkapi dengan cakupan pengujian integrasi (Feature Tests) menyeluruh terhadap otentikasi, otorisasi RBAC, transaksi stok atomik, presensi shift, dan audit eksekutif:

```bash
php artisan test
```

Hasil eksekusi:
```text
PASS  Tests\Feature\AuthenticationAndRbacTest (8 tests)
PASS  Tests\Feature\ExampleTest (3 tests)
PASS  Tests\Feature\ExecutiveAnalyticsAndReportingTest (11 tests)
PASS  Tests\Feature\POSTerminalAndStockDeductionTest (9 tests)
PASS  Tests\Feature\RawInventoryAndRestockApprovalTest (11 tests)
PASS  Tests\Feature\ShiftAttendanceAndPayrollTest (11 tests)

Tests:    53 passed (270 assertions)
Duration: ~1.9s
```

---

## 🌐 Panduan Deployment ke Cloud (Live App Gratis)

### Deployment ke Render.com (Gratis & Otomatis)
Repository ini telah menyertakan berkas `render.yaml` dan `Dockerfile` produksi:
1. Fork atau Push repositori ini ke akun GitHub Anda.
2. Buka [Render.com](https://render.com) dan hubungkan akun GitHub Anda.
3. Klik **New +** &rarr; Pilih **Blueprint**.
4. Pilih repository `SI-CafeSync`.
5. Render akan secara otomatis membaca `render.yaml`, membangun *Docker image*, membuat database SQLite, menjalankan migrasi & seeder, dan mempublikasikan aplikasi dengan URL HTTPS gratis (misal: `https://cafesync-demo.onrender.com`).

---

## 📂 Struktur Direktori Proyek

```text
SI CafeSync/
├── app/
│   ├── Http/Controllers/
│   │   ├── Auth/             # Login, Logout, Lockout 15m, 1-Click Demo
│   │   ├── HR/               # Roster, Clock In/Out, Shift Scheduling
│   │   ├── Inventory/        # Ingredient Ledger, Restock Requisition & Approvals
│   │   ├── Manager/          # Executive Analytics & CSV/PDF Audit Export
│   │   └── POS/              # POS Checkout, Recipe Checks, QRIS Simulation
│   ├── Models/               # Eloquent Models (User, Order, Ingredient, Recipe, etc.)
│   ├── Services/             # Domain Logic (Analytics, Payroll, Inventory, QRIS)
│   └── Middleware/           # Role-based Access Control (RoleMiddleware)
├── database/
│   ├── migrations/           # 14 Skema Migrasi Database
│   └── seeders/              # DatabaseSeeder dengan data kafe realistis
├── docs/                     # Spesifikasi Sistem, Flowchart, dan Skema ERD
├── resources/
│   ├── views/                # Antarmuka Blade (Modern Warm Espresso Theme)
│   ├── css/                  # Tailwind CSS konfigurasi
│   └── js/                   # Alpine.js interaktivitas
├── tests/
│   └── Feature/              # 53 Skrip Pengujian Otomatis Fitur
├── .github/workflows/        # CI/CD GitHub Actions (Lint + Test Automations)
├── Dockerfile                # Multi-stage production container
└── render.yaml               # 1-Click Cloud Deployment blueprint
```

---

## 📄 Lisensi & Kontributor

- **Pengembang:** Portofolio Rekayasa Perangkat Lunak / Sistem Informasi Kafe
- **Lisensi:** [MIT License](LICENSE) &bull; Terbuka untuk eksplorasi dan pengembangan lebih lanjut.
