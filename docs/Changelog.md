# CafeSync - System Changelog & Development History (`Changelog.md`)

Semua perubahan, penambahan fitur, dan dokumentasi pengembangan sistem **CafeSync** dicatat dalam dokumen ini dengan berpedoman pada standar [Keep a Changelog](https://keepachangelog.com/en/1.0.0/) dan [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [[v0.1.0]] - 2026-10-15 (Planning & Blueprint Architecture Phase)

### Added
- **System Analysis & Requirement Engineering**:
  - Penelaahan menyeluruh dokumen spesifikasi sistem (*System Requirements Specification - SRS*) dari file `CafeSync - Information System Specification (Points 2-7).docx`.
  - Ekstraksi dan analisis desain wireframe dari bundle `stitch_cafesync_management_platform.zip` mencakup portal login, web POS terminal, monitor bahan baku mentah, dan executive analytics.
- **Dokumentasi Pendukung Pengembangan (System Documentation Suite)**:
  - Pembuatan `docs/Appflow.md`: Pemetaan end-to-end user journey, RBAC matrix, flowchart otentikasi dengan proteksi lockout 15 menit, flowchart POS sale dengan deduplikasi stok otomatis, flowchart shift presensi & lembur, serta restock lifecycle.
  - Pembuatan `docs/Architecture.md`: Spesifikasi arsitektur teknis dengan rekomendasi Laravel 11 + Blade/Alpine.js + Tailwind CSS, struktur direktori Laragon, pola transaksi ACID `lockForUpdate()`, standar RESTful endpoint, enkripsi Bcrypt, dan pipeline QRIS dinamis.
  - Pembuatan `docs/Database_Schema.md`: Diagram visual ERD berbasis Mermaid dan DDL SQL MySQL 8.4 untuk 8 tabel relasional lengkap dengan indexing, foreign key constraints, dan seeder data awal.
  - Pembuatan `docs/UI_UX_Guidelines.md`: Panduan desain visual berbasis tema *"Warm Espresso Modern"* (Deep Espresso `#3B2314`, Warm Amber `#D97706`, Off-white `#FDFBF7`, Emerald `#16A34A`, Red `#DC2626`), tipografi Plus Jakarta Sans & Inter (tabular figures `tnum`), dan spesifikasi komponen Stitch.
  - Pembuatan `docs/Changelog.md`: Format pelacakan riwayat versi dan roadmap sprint.

---

## [[v0.2.0]] - 2026-10-22 (Database Engine & Foundation Setup - Sprint 1 Completed)

### Added
- [x] Inisialisasi framework Laravel di direktori root Laragon `C:\laragon\www\SI CafeSync`.
- [x] Konfigurasi database MySQL 8.4 `cafesync_db` dan file `.env` (MySQL connection on 127.0.0.1:3306).
- [x] Pembuatan file-file Laravel Migrations untuk 8 tabel: `users`, `attendance`, `ingredients`, `menu`, `recipes`, `orders`, `order_details`, dan `restock_requests`.
- [x] Pembuatan Model Eloquent dengan relasi lengkap (`User`, `Attendance`, `Ingredient`, `Menu`, `Recipe`, `Order`, `OrderDetail`, `RestockRequest`).
- [x] Pembuatan Seeder data awal (`DatabaseSeeder.php`): 4 akun pengguna (Alex, Sarah, David, Elena), 8 bahan baku mentah, 5 katalog menu, mapping resep komposisi gramasi, data kehadiran aktif, serta sampel transaksi POS #1042.
- [x] Eksekusi sukses `php artisan migrate:fresh --seed` terverifikasi pada basis data MySQL.

---

## [[v0.3.0]] - 2026-10-22 (Multi-Role Authentication & Security Guard - Sprint 2 Completed)

### Added
- [x] Implementasi UI Portal Login responsif (`resources/views/auth/login.blade.php`) berbasis layout Stitch `cafesync_portal_sign_in` dan `UI_UX_Guidelines.md`:
  - Split 2-kolom: Brand Showcase gelap (*Dark Roast* `#3B2314` / `#2A180E`) dan Surface Card putih `#FFFFFF`.
  - Komponen Role Pill Switcher berbasis Alpine.js (`STAFF`, `HRD`, `MANAGER`) dengan pre-fill instan kredensial uji coba cepat.
  - Tombol CTA utama Warm Amber (`#D97706`), toggle lihat/sembunyikan kata sandi, dan input email/password modern.
- [x] Implementasi `app/Http/Controllers/Auth/AuthController.php` menangani:
  - Verifikasi kredensial login terenkripsi Bcrypt.
  - Proteksi *brute-force* otomatis: kunci akun selama 15 menit (`locked_until = now()->addMinutes(15)`) jika 3 kali berturut-turut gagal login.
  - Tampilan umpan balik informatif (sisa percobaan login sebelum dikunci, hitungan mundur menit saat akun terkunci).
  - Mekanisme Logout aman dengan invalidasi sesi dan regenerasi CSRF token.
- [x] Pembuatan Middleware RBAC `app/Http/Middleware/RoleMiddleware.php`:
  - Validasi otorisasi peran (`STAFF`, `HRD`, `MANAGER`) pada tingkat request.
  - Penolakan otomatis (403 Forbidden / redirect) jika pengguna mencoba mengakses modul di luar hak perannya.
  - Proteksi terintegrasi bagi akun berstatus `isLocked()`.
  - Pendaftaran alias `'role'` pada konfigurasi middleware Laravel 11 (`bootstrap/app.php`).
- [x] Konfigurasi routing otomatis terotentikasi di `routes/web.php`:
  - Staff $\rightarrow$ `/pos` (`POSController@index`)
  - HRD $\rightarrow$ `/hr/roster` (`AttendanceController@roster`)
  - Manager $\rightarrow$ `/manager/dashboard` (`AnalyticsController@index`)
- [x] Penyusunan Master Layout Warm Espresso (`resources/views/layouts/app.blade.php`) dilengkapi header navigasi, role badge, profil pengguna aktif, dan tombol logout terotentikasi.
- [x] Pembuatan rangkaian pengujian fitur otomatis (`tests/Feature/AuthenticationAndRbacTest.php` - 8 skenario pengujian dengan 55 asersi lolos 100%).

---

## [[v0.4.0]] - 2026-10-22 (Shift Attendance & HR Time Tracking - Sprint 3 Completed)

### Added
- [x] **Database Schema Extension**:
  - Pembuatan tabel `shift_schedules` (`database/migrations/2026_10_22_000001_create_shift_schedules_table.php`) untuk menyimpan penugasan shift kerja per karyawan, jam mulai, jam selesai, hari/tanggal, dan durasi shift.
  - Pembuatan tabel `hr_alerts` (`database/migrations/2026_10_22_000002_create_hr_alerts_table.php`) untuk pelaporan otomatis keterlambatan staf dan otorisasi manager override.
  - Pembuatan Eloquent Model `ShiftSchedule` dan `HrAlert` terhubung dengan relasi `User` dan `Attendance`.
- [x] **Service Layer & Business Logic (`app/Services/AttendancePayrollService.php`)**:
  - Validasi ketepatan shift: On Time ($\le 15$ menit) vs Late ($> 15$ menit).
  - Pemicu otomatis notifikasi/peringatan keterlambatan (`HrAlert`) ke dasbor HR dengan pencatatan selisih menit riil.
  - Logika verifikasi Manager Override PIN (default `1234` atau kata sandi akun Manager) untuk shift yang tidak terjadwal (*unscheduled*).
  - Perhitungan jam kerja aktif (`clock_out - clock_in`) dan akumulasi lembur (`overtime_hours`) jika durasi kerja melebihi durasi shift terjadwal (atau melebihi 8 jam standar).
  - Kalkulasi estimasi total upah bulanan: $\text{total\_hours} \times \text{hourly\_rate}$ per karyawan.
- [x] **UI/UX Modal Clock-In & Clock-Out di Terminal POS (`resources/views/pos/index.blade.php`)**:
  - Active Cashier & Shift Bar dengan live elapsed timer (`00h 00m 00s active`), status badge (*On Time / Late / Overtime*), dan jadwal hari ini.
  - Modal Clock-In interaktif dengan deteksi real-time ketepatan waktu, peringatan keterlambatan, dan input PIN Manager Override untuk shift tak terjadwal.
  - Modal Clock-Out dengan kalkulasi live total jam kerja, akumulasi jam lembur, dan estimasi upah sesi kasir.
- [x] **UI/UX Dasbor HR Roster (`resources/views/hr/roster.blade.php`)**:
  - 4 Kartu KPI Ringkasan: Total Karyawan, Staf Sedang Bertugas, Jam Kerja Terakumulasi Bulan Ini, dan Estimasi Biaya Upah (*Labor Cost*).
  - Banner peringatan keterlambatan (*Late HR Alert Box*) dilengkapi tombol konfirmasi audit 1-klik (*Acknowledge Alert*).
  - 3 Tab Navigasi (*Pill Tabs*):
    1. *Staff Roster & Payroll Estimator*: Rekapitulasi staf, role, status aktif, tarif per jam, jam normal, lembur, dan total estimasi upah bulanan (`total_hours * hourly_rate`).
    2. *Penjadwalan Shift*: Monitoring alokasi jadwal shift karyawan dan form modal tambah shift baru.
    3. *Log Riwayat Presensi*: Audit trail lengkap waktu masuk, keluar, status, catatan override manager, dan estimasi upah sesi.
  - Modal pencatatan presensi manual (*Manual Attendance Entry*) untuk tim HRD.
- [x] **Pengujian Otomatis Fitur (`tests/Feature/ShiftAttendanceAndPayrollTest.php`)**:
  - 9 skenario pengujian komprehensif (On Time clock in, Late clock in triggering HR alert, Unscheduled shift override rejection & approval, Duplicate shift prevention, Overtime accumulator calculation, Monthly payroll rendering, Alert acknowledgement, Shift scheduling CRUD).
  - Total pengujian sistem: 19 tes dengan 95 asersi lolos 100%.

---

## [[v0.5.0]] - 2026-10-29 (POS Terminal & Atomic Stock Deduction - Sprint 4 Completed)

### Added
- [x] **Antarmuka POS Web Terminal Responsif (`resources/views/pos/index.blade.php`)**:
  - Split 2-kolom desktop (Sisi Kiri: Katalog & Ordering Station; Sisi Kanan: Live Sticky Cart).
  - Search bar interaktif dengan reset 1-klik dan pencarian instan nama menu.
  - Filter kategori berbentuk *Pill Chips* (`All`, `Espresso`, `Cold Brew`, `Bakery`, dll.) dengan penghitung jumlah item real-time.
  - Grid kartu menu modern dengan badge status stok dinamis: *Optimal* (hijau, $>10$ porsi), *Low Stock* (oranye berdenyut, $\le 10$ porsi), dan *Habis / Out of Stock* (merah, $0$ porsi + semi-transparan + tombol disabled).
  - Keranjang belanja aktif (*Live Cart*) dengan kontrol stepper kuantitas `[ - ] [ Qty ] [ + ]`, tombol hapus baris, dan penanganan status kosong (*Empty State*).
  - Kotak kalkulasi finansial otomatis: Subtotal, Pajak Restoran (PB1 10%), dan Grand Total dengan format angka tabular monospaced (`font-tabular` / `tnum`).
- [x] **Verifikasi Stok Resep di Awal (*Early Stock Check*)**:
  - Implementasi `app/Services/InventoryService.php`:
    - `checkStockAvailability(Menu $menu, int $qty)`: Menghitung ketersediaan bahan baku per item menu dan menghitung selisih defisit jika stok fisik kurang.
    - `checkCartStockAvailability(array $cartItems)`: Agregasi total kebutuhan bahan baku lintas item keranjang sebelum checkout.
    - `calculateMaxServings(Menu $menu)`: Kalkulasi kapasitas porsi maksimum yang dapat diproduksi berdasarkan stok bahan baku terendah (*bottleneck ingredient*).
  - Modal Alert Stok Kurang (*Insufficient Stock Modal*) dengan tabel detail bahan baku yang defisit.
- [x] **Alur Pembayaran Tunai (*Cash Payment Calculator*)**:
  - Pilihan radio card pembayaran tunai (*Cash Drawer*).
  - Input jumlah uang diserahkan (`cash_tendered`) dilengkapi tombol preset cepat (*Uang Pas, $10, $20, $50, $100*).
  - Kalkulasi instan uang kembalian (`change_due` = `cash_tendered - grand_total`) dan peringatan visual jika uang yang diserahkan kurang dari total tagihan.
- [x] **Simulasi Pembayaran QRIS Dinamis (*Dynamic QRIS Pipeline*)**:
  - Implementasi `app/Services/QRISPaymentService.php`:
    - Pembuatan payload QRIS dinamis EMVCo berseri unik (`QRIS-SYNC-XXXXXXXX`) dan render QR code image via endpoint `/api/qris/generate`.
    - Modal QRIS interaktif kasir dengan countdown timer 3 menit (180 detik) dan visual progress bar susut waktu.
    - Polling interval otomatis setiap 3 detik ke `/api/qris/status/{transId}`.
    - Penanganan status *Timeout / Expired* dengan opsi *Regenerate QR* atau beralih ke pembayaran Tunai.
    - Tombol simulasi sukses instan (`/api/qris/simulate-success`) untuk pengujian alur kasir tanpa perangkat eksternal.
- [x] **Integritas Database Transaction (ACID)**:
  - Pembungkusan transaksi kasir dalam `DB::transaction()` dengan *Pessimistic Row-Level Locking* (`Ingredient::lockForUpdate()`).
  - Pemotongan stok bahan baku mentah (`ingredients.current_stock`) secara atomik berdasarkan takaran komposisi gramasi di tabel `recipes`.
  - Pemicu otomatis draft permintaan pembelian kembali (*Restock Request*) dengan status `'PENDING'` jika `current_stock <= minimum_threshold`.
  - Rollback otomatis tanpa data korup jika terjadi kekurangan bahan baku atau anomali saat transaksi.
- [x] **Format Cetak Struk Belanja Thermal 80mm (`resources/views/pos/receipt.blade.php`)**:
  - Endpoint cetak struk kasir `/pos/receipt/{id}` (`POSController@receipt`).
  - Layout thermal receipt 80mm standar industri dilengkapi header CafeSync, nomor pesanan, stempel waktu, rincian per baris item, subtotal, rincian pajak 10%, grand total, detail pembayaran (Cash tendered/change atau QRIS reference), dan ucapan terima kasih.
- [x] **Pengujian Fitur Otomatis Komprehensif (`tests/Feature/POSTerminalAndStockDeductionTest.php`)**:
  - 9 skenario pengujian (RBAC terminal access, Early stock check availability, Cash payment calculation, Insufficient cash rejection, Dynamic QRIS lifecycle, Atomic DB stock deduction, Auto restock threshold alert, ACID rollback on shortage, Thermal receipt rendering).
  - Total pengujian sistem saat ini: **28 tes dengan 153 asersi lolos 100%**.

---

---

## [[v0.6.0]] - 2026-10-29 (Raw Supply Inventory & Restock Approval - Sprint 5 Completed)

### Added
- [x] **Database Schema & Data Model Extensions**:
  - Migrasi `database/migrations/2026_10_29_000001_add_category_and_max_capacity_to_ingredients_table.php`: Menambahkan kolom `category`, `sku`, `max_capacity`, `storage_location`, dan `preferred_supplier` pada tabel `ingredients`.
  - Migrasi `database/migrations/2026_10_29_000002_create_stock_intakes_table.php`: Pembuatan tabel `stock_intakes` untuk audit penerimaan fisik barang masuk dari supplier (mencakup `ingredient_id`, `user_id`, `restock_request_id`, `quantity_received`, `unit`, `supplier_name`, `invoice_number`, `storage_location`, `batch_number`, `notes`).
  - Pembuatan Eloquent Model `StockIntake` dengan relasi lengkap ke `Ingredient`, `User`, dan `RestockRequest`.
  - Pembaruan Model `Ingredient` dan `RestockRequest` dengan helper status (`isHealthy()`, `isLowStock()`, `isOutOfStock()`, `capacity_percentage`, `display_sku`, `total_estimated_cost`).
- [x] **Service Layer & Business Logic (`app/Services/InventoryService.php`)**:
  - `getInventoryKPIs()`: Perhitungan real-time 4 metrik KPI (Total Tracked Raw Items, Healthy Stock, Low Stock Warnings, Depleted/Out of Stock) dan kalkulasi total nilai aset bahan baku (*inventory valuation*).
  - `recordStockIntake()`: Operasi penerimaan barang masuk atomik dalam `DB::transaction()` dengan pessimistic locking `Ingredient::lockForUpdate()`, penambahan saldo stok fisik, pencatatan log audit `stock_intakes`, serta pembaruan otomatis status pengajuan restock `APPROVED` terkait menjadi `COMPLETED`.
  - `createRestockRequest()`: Pembuatan draft pengajuan restock berstatus `PENDING` oleh Staff maupun Manager.
  - `approveRestockRequest()` & `rejectRestockRequest()`: Validasi wewenang Cafe Manager untuk menyetujui (*Approve*) atau menolak (*Reject*) requisition order.
- [x] **Antarmuka Raw Material & Supply Inventory (`resources/views/inventory/index.blade.php`)**:
  - Diimplementasikan berdasarkan desain Stitch `raw_supply_inventory_portal/code.html` dan palet warna Warm Espresso Modern (`#3B2314`, `#D97706`, `#FAF2ED`, `#16A34A`, `#EA580C`, `#DC2626`).
  - **4 KPI Metric Cards**: Total Tracked Raw Items (dengan total valuasi modal), Healthy Stock Levels (persentase & safe operating buffer), Low Stock Warnings (peringatan batas threshold), dan Depleted/Out of Stock (indikator defisit kritis dengan badge berdenyut).
  - **Filter & Rapid Search Bar Interaktif**: Pencarian instan nama/SKU/kategori, dropdown kategori (*Specialty Beans, Dairy & Alternatives, Packaging & Ware, Syrups & Botanicals, dll.*), filter status (*Optimal, Low Stock, Out of Stock*), dan tombol reset 1-klik via Alpine.js.
  - **Tabel Live Raw Stock Ledger**:
    - Kolom: Bahan Baku & SKU, Kategori (*pill badge*), Stok Terkini vs Kapasitas Maksimal (*visual progress bar* & persentase warna dinamis), Satuan (*grams, ml, pcs*), Ambang Batas (*Minimum Threshold*), Status Badge (*Optimal/Green, Low Stock/Orange, Out of Stock/Red*), serta tombol aksi cepat *Intake*, *Reorder*, dan *Audit*.
  - **Audit Trail Penerimaan Barang**: Tabel log riwayat barang masuk terkini (*Recent Stock Intakes*) dengan stempel waktu, nama supplier, nomor invoice/surat jalan, dan penanggung jawab penerima.
  - **Storage Bay Saturation Deck**: Indikator persentase keterisian ruang penyimpanan (*Walk-In Cold Vault, Bean Degas Silo Room, Dry Goods Mezzanine*).
- [x] **Modal Form "Record Stock Intake"**:
  - Form modal interaktif pencatatan barang masuk dari supplier: dropdown bahan baku dengan info stok langsung, kuantitas masuk, satuan, nama distributor, nomor surat jalan / invoice PO, ruang simpan, lot batch, penautan pengajuan restock, dan catatan fisik.
- [x] **Antarmuka Restock Approval untuk Cafe Manager**:
  - Deck persetujuan pengajuan restock di sisi kanan layar (*Requisitions Deck*):
    - Tampilan kartu pengajuan bahan berstatus `PENDING` lengkap dengan kuantitas yang diminta, distributor, estimasi biaya (*unit cost*), nama pemohon (Staff/Kasir/Sistem POS), dan waktu pengajuan.
    - Tombol aksi **Approve** (hijau emerald) dan **Reject** (merah outline) dengan konfirmasi aman khusus hak akses Manager.
    - Tombol eksekutif **"Setujui Semua PO"** (*Bulk Approve*) untuk pengesahan massal requisition order.
- [x] **Routing & Navigasi**:
  - Pendaftaran route grup `/inventory` terproteksi RBAC untuk peran `STAFF` dan `MANAGER` di `routes/web.php`.
  - Pembatasan endpoint persetujuan `restock/{id}/approve` dan `restock/{id}/reject` secara ketat hanya untuk peran `MANAGER` (403 Forbidden bagi peran lain).
  - Penambahan menu navigasi "📦 Stok Bahan Mentah" di header navbar utama (`resources/views/layouts/app.blade.php`).
- [x] **Rangkaian Pengujian Otomatis Komprehensif (`tests/Feature/RawInventoryAndRestockApprovalTest.php`)**:
  - 10 skenario pengujian (RBAC access control, UI KPI cards & table rendering, Record stock intake atomicity, Restock completion trigger, Staff requisition creation, Manager approval lifecycle, Manager rejection lifecycle, Non-manager RBAC restriction, Bulk approval, Manager parameter audit).
  - Total pengujian sistem saat ini: **40 tes dengan 214 asersi lolos 100%**.

---

## [[v0.7.0]] - 2026-11-05 (Executive Analytics & Sales Audit Reporting - Sprint 6 Completed)

### Added
- [x] **Service Layer Intelijen Eksekutif (`app/Services/AnalyticsService.php`)**:
  - `getExecutiveDashboardMetrics()`: Agregasi real-time metrik omzet kotor (*Gross Revenue*), total tiket pesanan terbayar (*Total Paid Orders*), rata-rata nilai transaksi (*Average Ticket / AOV*), staf aktif di lantai kafe (*Floor Staff On-Duty*), dan estimasi margin kotor kafe.
  - Perhitungan Rasio Biaya Tenaga Kerja (*Labor Cost Ratio - LCR*):
    $$\text{Labor Cost Ratio} = \left( \frac{\text{Total Biaya Upah Jam Kerja Riil}}{\text{Total Omzet Transaksi Kasir}} \right) \times 100\%$$
    Lengkap dengan indikator ambang batas status (*Healthy < 20%*, *Moderate 20-25%*, *High Risk > 25%*).
  - `getHourlySalesVelocity()`: Visualisasi agnostik basis data (kompatibel MySQL 8.4 `HOUR()` & SQLite `strftime('%H')`) untuk mengelompokkan volume penjualan per jam (06:00 – 21:00) serta mendeteksi secara otomatis lonjakan jam sibuk pagi (*Morning Surge 08:00 – 10:30*) dan jam sibuk sore (*Afternoon Rush 14:00 – 15:30*).
  - `resolveDateRange()`: Dukungan filter fleksibel rentang waktu (*Hari Ini, Kemarin, 7 Hari Terakhir, Bulan Ini, dan Kustom Tanggal*).
  - `getTopMarginIngredientsBreakdown()`: Analisis performa bahan baku bermargin tinggi (*Ethiopia Yirgacheffe G1 +82%*, *Oat Milk Barista +74%*, *Madagascar Vanilla +91%*) dan laju penipisan stok per hari.
- [x] **Antarmuka Dasbor Eksekutif Manager (`resources/views/manager/dashboard.blade.php`)**:
  - Implementasi desain Warm Espresso Modern berbasis wireframe Stitch `executive_analytics_hr_dashboard`:
    - **Header Workspace**: Indikator real-time audit, badge cabang (*Downtown Roastery #01*), pill selector filter waktu (*Hari Ini / Kemarin / 7 Hari / Bulan Ini / Custom*), dan modal interaktif tanggal kustom.
    - **4 Kartu Metrik KPI Utama**:
      1. *Gross Revenue* (dengan persentase perbandingan periode lalu).
      2. *Total Paid Orders* (dengan kalkulasi Average Order Value).
      3. *Labor Cost Ratio (LCR)* (dengan tag status dinamis Healthy/Risk dan target benchmark < 20%).
      4. *Active Staff On-Duty* (dengan status kapasitas lantai dan target coverage).
    - **Interactive Chart (Chart.js)**: Area line chart kombinasi bar bergradasi Warm Amber (`#D97706`) dan cream bar (`#FFD4BD`) menggambarkan *Hourly Sales Velocity* dengan dual y-axis (omzet mata uang & kuantitas pesanan) serta kartu highlight Morning & Afternoon Surge.
    - **Deck Monitoring Jam Kerja & Upah Staf**: Status live shift, visual labor gauge progress bar (*jam terjadwal, sisa shift, resiko lembur*), daftar kasir & barista bertugas, serta tautan cepat ke Roster HR.
    - **Peringatan Bahan Baku Menipis**: Kartu reorder terintegrasi dengan ambang batas minimum (*Inventory Health*).
    - **Audit Trail Transaksi Kasir Terkini**: Tabel 10 transaksi terakhir lengkap dengan nomor order, stempel waktu, nama staf kasir, status pembayaran, dan nominal belanja.
- [x] **Fitur Ekspor Laporan Audit Penjualan Resmi (`/manager/export-sales`)**:
  - Implementasi `AnalyticsController@exportAudit`:
    - **Ekspor CSV (`format=csv`)**: Menghasilkan berkas CSV standar dengan header UTF-8 BOM (kompatibel penuh Microsoft Excel), rincian nomor order, stempel waktu ISO, kasir bertugas, metode pembayaran, referensi QRIS/Cash, rincian item & kuantitas menu, subtotal, pajak restoran PB1 (10%), grand total, serta baris rekapitulasi audit.
    - **Ekspor PDF Resmi (`format=pdf`)**: Integrasi dengan `barryvdh/laravel-dompdf` menggunakan template lanskap Warm Espresso (`resources/views/manager/sales_audit_pdf.blade.php`) lengkap dengan kop surat resmi CafeSync, NPWP, tabel ringkasan KPI eksekutif, tabel ledger transaksi detil, status verifikasi ACID, serta blok otorisasi & tanda tangan digital Manajer Operasional.
- [x] **Proteksi Otorisasi & Hak Akses (RBAC)**:
  - Pembatasan ketat route `/manager/dashboard` dan `/manager/export-sales` hanya untuk peran `MANAGER`. Staf kasir dan HRD secara otomatis ditolak dengan kode respon `403 Forbidden`.
- [x] **Rangkaian Pengujian Otomatis Komprehensif (`tests/Feature/ExecutiveAnalyticsAndReportingTest.php`)**:
  - 11 skenario pengujian (RBAC dashboard access, Staff restriction, HRD restriction, Guest redirect, Revenue & orders accuracy, Labor cost ratio calculation, Hourly velocity surge detection, Custom date filter, CSV export validation, PDF export validation, Non-manager export restriction).
  - Total pengujian sistem saat ini: **51 tes dengan 264 asersi lolos 100%**.

---

## [Roadmap Milestone Mendatang]

### [[v1.0.0]] - Final Testing, Hardening & Production Release (Sprint 7)
- [ ] Pengujian menyeluruh (*End-to-End Testing*) skenario transaksi multi-role simultan.
- [ ] Optimasi query database dan caching asset via Vite.
- [ ] Penyerahan sistem dan panduan operasional pengguna.

