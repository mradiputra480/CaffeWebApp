# CafeSync - UI/UX Design System & Implementation Guidelines (`UI_UX_Guidelines.md`)

**Document Version:** 1.0.0  
**Design Theme:** Warm Espresso Modern SaaS  
**Reference Bundle:** `stitch_cafesync_management_platform`  
**Primary Typography:** Plus Jakarta Sans & Inter  
**Author:** Lead System Architect & Senior Full-Stack Developer  

---

## 1. Filosofi & Karakteristik Desain (Design Philosophy)

Sistem antarmuka **CafeSync** memadukan efisiensi operasional sistem **Enterprise POS SaaS** dengan kehangatan estetika kafe artisanal (*Artisanal Specialty Coffee Craft*). Antarmuka didesain untuk menghindari kesan enterprise yang kaku dan steril (*cold grey/blue*), beralih ke fondasi bernuansa hangat (*Warm Neutral Canvas*) dengan aksen warna kopi (*Deep Espresso*) dan ambar keemasan (*Warm Amber*).

### Tiga Pilar Pengalaman Pengguna:
1. **Utilitarian Clarity under Rush Hours**: Elemen tombol kasir berukuran besar (minimal target sentuh 44px), kontras teks tinggi, dan pemisahan informasi yang tegas untuk kondisi pencahayaan kafe yang beragam dan kecepatan transaksi tinggi.
2. **Tactile & Responsive Affordances**: Penggunaan komponen *pill tabs*, *floating counter badges*, dan elevasi mikro saat *hover/active* yang memberikan umpan balik visual instan.
3. **Tabular Metric Stability**: Seluruh data angka, harga, sisa stok, dan timer kasir wajib menerapkan `font-feature-settings: "tnum"` agar karakter angka memiliki lebar seragam (*monospaced tabular*) dan tidak bergeser (*jitter*) saat data diperbarui secara *real-time*.

---

## 2. Palet Warna Resmi (Color Palette Tokens)

```text
[ #FDFBF7 ] Base Canvas (Off-White)
    │
    ├── [ #FFFFFF ] Surface Card Container
    │       ├── Border: 1px solid #E7E5E4
    │       └── Text Primary: #1E1B18 (Deep Charcoal)
    │
    ├── [ #3B2314 ] Brand Secondary (Deep Espresso)
    │       └── Digunakan pada: Sidebar Navigasi, Dark Badges, Header Utama
    │
    ├── [ #D97706 ] Interactive Primary (Warm Amber)
    │       └── Digunakan pada: Tombol Utama, Tab Aktif, Hover Focus, Ring Accent
    │
    ├── [ #16A34A ] Functional Success (Emerald)
    │       └── Digunakan pada: Stok Aman, Pembayaran QRIS Sukses, Presensi On Time
    │
    ├── [ #EA580C ] Warning Accent (Warm Orange)
    │       └── Digunakan pada: Stok Menipis (Low Stock <= Threshold), Shift Terlambat
    │
    └── [ #DC2626 ] Functional Danger (Crimson Red)
            └── Digunakan pada: Stok Habis, Pembayaran Batal, Akun Terkunci
```

### 2.1. Tabel Token Warna Tailwind CSS

| Token Nama | Nilai HEX | Peran dalam Antarmuka |
| :--- | :--- | :--- |
| `bg-surface` / Canvas | `#FDFBF7` | Latar belakang dasar halaman seluruh aplikasi |
| `surface-container-lowest` | `#FFFFFF` | Latar kartu (*cards*), tabel transaksi, dan form input |
| `surface-container-low` | `#FAF2ED` | Latar bilah shift (*active cashier bar*) & sub-header |
| `surface-container` | `#F4ECE7` | Track latar belakang *pill selector* dan chips |
| `surface-container-high` | `#EEE7E1` | Latar tombol sekunder & batas pemisah |
| `brand-espresso` | `#3B2314` | Sidebar navigasi, tipografi judul brand, header modal |
| `primary` (Amber) | `#D97706` | Tombol CTA utama, status checkout, aksen fokus |
| `primary-hover` | `#B45309` | Status *hover* tombol CTA utama |
| `tertiary` (Success) | `#16A34A` | Tag status *Ready*, *Paid*, *Optimal Stock* |
| `tertiary-bg` | `#DCFCE7` | Latar belakang pastel untuk badge status sukses |
| `warning` | `#EA580C` | Tag status *Low Stock*, peringatan keterlambatan shift |
| `warning-bg` | `#FFEDD5` | Latar belakang pastel untuk badge peringatan |
| `error` (Danger) | `#DC2626` | Tag status *Out of Stock*, gagal bayar, tombol Clock-Out |
| `error-bg` | `#FEE2E2` | Latar belakang pastel untuk badge bahaya / error |
| `on-surface` (Text) | `#1E1B18` | Warna teks utama (*body*, label harga, nama menu) |
| `text-muted` | `#78716C` | Teks sekunder, metadata satuan bahan, stempel waktu |
| `border-hairline` | `#E7E5E4` | Garis batas tipis 1px pembungkus seluruh kartu |

---

## 3. Sistem Tipografi (Typography Hierarchy)

Sistem menggunakan kombinasi dua font Google Fonts:
- **Headings, Brand, & Metric Numbers**: `Plus Jakarta Sans` (Weight: 600 Semi-Bold, 700 Bold)
- **Body, Table Data, Forms, & POS Labels**: `Inter` (Weight: 400 Regular, 500 Medium, 600 Semi-Bold)

### 3.1. Skala Tipografi

| Token Kelas | Font Family | Ukuran | Line Height | Weight | Penggunaan Utama |
| :--- | :--- | :---: | :---: | :---: | :--- |
| `headline-xl` | Plus Jakarta Sans | 40px | 48px | 700 | Headline promosi login & metrik utama |
| `headline-lg` | Plus Jakarta Sans | 32px | 40px | 600 | Judul halaman dasbor & ringkasan omzet |
| `headline-md` | Plus Jakarta Sans | 24px | 32px | 600 | Judul modal transaksi & kategori menu |
| `headline-sm` | Plus Jakarta Sans | 18px | 26px | 600 | Nama item menu di katalog & card title |
| `body-lg` | Inter | 16px | 24px | 400 | Teks deskripsi pengantar & subjudul |
| `body-md` | Inter | 14px | 20px | 400 | Teks isi standar, input form, baris tabel |
| `body-sm` | Inter | 12px | 18px | 400 | Metadata satuan, waktu transaksi |
| `label-lg` | Inter | 14px | 20px | 600 | Teks tombol CTA, header kolom tabel |
| `label-md` | Inter | 12px | 16px | 500 | Teks chips kategori, status badge |
| `label-sm` | Inter | 11px | 14px | 600 | Sub-label ALL-CAPS, teks indikator mini |

> [!IMPORTANT]
> **Aturan Tabular Figures (`tnum`)**:
> Untuk semua elemen yang menampilkan angka (harga di POS, total belanja, sisa gramasi stok bahan, hitungan mundur timer QRIS, dan jam aktif shift), wajib disertakan kelas CSS:
> ```css
> .font-tabular {
>   font-feature-settings: "tnum" 1;
>   font-variant-numeric: tabular-nums;
> }
> ```

---

## 4. Elevasi, Border Radius, & Kedalaman Visual (Elevation & Shapes)

### 4.1. Tingkat Elevasi (Shadow & Dividers)
- **Level 0 (Flat Canvas)**: Latar belakang halaman `#FDFBF7` murni tanpa bayangan.
- **Level 1 (Card & Containers)**: Permukaan kartu putih `#FFFFFF` dibatasi *hairline border* 1px (`#E7E5E4`) dan bayangan lembut:  
  `box-shadow: 0px 4px 20px rgba(0, 0, 0, 0.04);`
- **Level 2 (Hover & Active Panels)**: Kartu menu atau dropdown yang sedang dipilih:  
  `box-shadow: 0px 8px 24px rgba(30, 27, 24, 0.06), 0px 2px 6px rgba(30, 27, 24, 0.02);`
- **Level 3 (Modal Dialogs & Slide Drawers)**: Modal pembayaran QRIS dan pengajuan restock:  
  `box-shadow: 0px 20px 40px rgba(30, 27, 24, 0.12);` dengan *backdrop overlay* `rgba(30, 27, 24, 0.45)`.

### 4.2. Aturan Lengkungan Sudut (*Border Radius*)
- **Input Fields & Form Selectors**: `rounded-lg` (8px / `0.5rem`).
- **Cards, Panel Kontainer, & Modal Dialog**: `rounded-xl` (12px / `0.75rem`) hingga `rounded-2xl` (16px / `1rem`).
- **Pills, Chips Kategori, Badges, & Avatar Status**: `rounded-full` (`9999px`) untuk menghadirkan kenyamanan sentuhan layar tablet/touchscreen.

---

## 5. Spesifikasi Komponen Inti (UI Components Specification)

### 5.1. Tombol (Buttons)
1. **Primary Action Button (Warm Amber)**:
   - Kelas: `bg-[#D97706] text-white hover:bg-[#B45309] active:scale-[0.98] font-semibold rounded-lg px-4 py-2.5 transition-all shadow-sm`
   - Penggunaan: *"Process Payment"*, *"Add to Cart"*, *"Record Intake"*.
2. **Secondary Administrative Button (Deep Espresso)**:
   - Kelas: `bg-[#3B2314] text-white hover:bg-[#2A180E] active:scale-[0.98] font-semibold rounded-lg px-4 py-2.5 transition-all shadow-sm`
   - Penggunaan: *"Approve Restock PO"*, *"Export Audit CSV"*.
3. **Ghost / Outline Button**:
   - Kelas: `bg-white border border-[#E7E5E4] text-[#1E1B18] hover:bg-[#FAF2ED] rounded-lg px-3.5 py-2 transition-colors`
   - Penggunaan: *"Clear Search"*, *"Filter Range"*, *"Print Kitchen Ticket"*.
4. **Destructive / Danger Button**:
   - Kelas: `bg-[#FEE2E2] text-[#DC2626] hover:bg-[#DC2626] hover:text-white rounded-lg px-3 py-1.5 transition-colors font-medium`
   - Penggunaan: *"Clock Out"*, *"Void Order"*, *"Reject Request"*.

### 5.2. Filter Chips & Pill Tabs
- **Track Kontainer**: `bg-[#F4ECE7] p-1 rounded-full flex gap-1`
- **Item Tidak Aktif**: `px-3.5 py-1.5 rounded-full text-[#78716C] hover:text-[#1E1B18] font-medium text-sm transition-colors`
- **Item Aktif**: `px-3.5 py-1.5 rounded-full bg-[#3B2314] text-white shadow-sm font-semibold text-sm`

### 5.3. Status Badges
- **Optimal / Paid**: `bg-[#DCFCE7] text-[#16A34A] px-2.5 py-1 rounded-full text-xs font-semibold inline-flex items-center gap-1`
- **Low Stock / Late**: `bg-[#FFEDD5] text-[#EA580C] px-2.5 py-1 rounded-full text-xs font-semibold inline-flex items-center gap-1`
- **Out of Stock / Critical**: `bg-[#FEE2E2] text-[#DC2626] px-2.5 py-1 rounded-full text-xs font-semibold inline-flex items-center gap-1`

### 5.4. Kartu Menu POS (Menu Catalog Card)
- Latar belakang putih dengan padding 16px, border hairline, thumbnail produk rasio 4:3.
- Menampilkan nama menu (`headline-sm`), badge ketersediaan porsi berdasarkan kalkulasi stok resep bahan baku (*"Stock: 45 cups"*).
- Jika stok resep habis: Kartu menjadi semi-transparan (`opacity-60`), tombol *"Add"* dinonaktifkan (*disabled*), dan muncul tag merah *"Out of Stock"*.

### 5.5. Panel Keranjang Kasir (Order Cart Sidebar)
- Terletak di sisi kanan layar (lebar 360px - 420px).
- Menampilkan nomor order aktif (`Order #1042`), daftar baris item dengan kontrol increment/decrement `[ - ] [ Qty ] [ + ]`.
- Baris rincian: Subtotal, Pajak 10%, dan Grand Total dengan ukuran font tebal `headline-md`.
- Tombol pemilih metode pembayaran berupa kartu radio horizontal: **[ Dynamic QRIS ]** vs **[ Cash ]**.

---

## 6. Pemetaan Implementasi Layar Berdasarkan Desain Stitch

### 6.1. Layar 1: Multi-Role Portal Login (`cafesync_portal_sign_in`)
- **Struktur**: 2 Kolom (*Split Layout*) pada desktop:
  - Sisi Kiri: Panel *Brand Showcase* gelap dengan latar belakang `#33302C` (*Dark Roast*), logo cangkir kopi CafeSync, kutipan artisanal kafe, dan visual hero bar kopi modern.
  - Sisi Kanan: Kartu formulir putih bersih dengan *Role Pill Switcher* (Staff/Barista, Inventory, Manager & HR), input email/username, input password, serta tombol masuk besar berwarna Warm Amber `#D97706`.

### 6.2. Layar 2: Web POS Terminal Kasir (`staff_web_pos_terminal`)
- **Struktur Header**: Menampilkan foto profil kasir yang bertugas, badge peran (*Barista*), durasi shift aktif (*"03h 42m active"*), tombol cepat *Break*, dan tombol *Clock Out*.
- **Area Katalog**: Bilah pencarian instan + scanner barcode, deretan chip kategori (*All, Espresso, Cold Brew, Bakery, Syrups*), dan grid 3-kolom kartu produk kopi & pastry.
- **Area Keranjang**: Panel kanan yang selalu terlihat (*sticky cart*), mendukung pembayaran tunai langsung dengan kalkulator uang kembalian cepat atau pembukaan modal QRIS 3 menit.

### 6.3. Layar 3: Monitor Stok Bahan Baku Mentah (`raw_supply_inventory_portal`)
- **Struktur**: Navigasi sidebar kiri bernuansa Warm Cream `#FAF2ED`.
- **Header Stat Grid**: 4 Kartu KPI (*Tracked Raw Items, Optimal Stock Levels, Low Stock Items, Inventory Value*).
- **Tabel Bahan Baku**: Kolom Nama Bahan, Kategori (*Specialty Beans, Dairy, Packaging*), Stok Fisik + Satuan (*grams, ml, pcs*), Ambang Batas (*Minimum Threshold*), Nilai Modal, dan Tombol Cepat *"Request Reorder"*.

### 6.4. Layar 4: Ringkasan Eksekutif & HR Auditing (`executive_analytics_hr_dashboard`)
- **Metrik Utama**: Omzet kotor hari ini (*Today's Gross Revenue*), Total tiket transaksi terbayar, Rasio biaya tenaga kerja (*Labor Cost Ratio Target < 20%*), dan Jumlah staf aktif di lantai kafe.
- **Visualisasi Grafik**: Diagram kombinasi area & bar chart bernuansa amber yang menggambarkan *Hourly Sales Velocity*, secara otomatis menandai lonjakan pagi (*Morning Surge 08:00 - 10:30*) dan lonjakan siang (*Afternoon Lull Surge 14:00 - 15:30*).
- **Tab Roster & Presensi HR**: Tabel data kehadiran lengkap dengan status *On Time*, *Late*, selisih jam lembur, dan tombol *Manager Override*.
