# ☕ CafeSync — Specialty Coffee Multi-Role POS & ERP Platform

<p align="center">
  <a href="https://mradiputra480.github.io/CaffeWebApp/">
    <img src="https://img.shields.io/badge/Live_Demo-GitHub_Pages-D97706?style=for-the-badge&logo=github&logoColor=white" alt="Live Demo GitHub Pages">
  </a>
  <img src="https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel 12">
  <img src="https://img.shields.io/badge/PHP-8.3-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP 8.3">
  <img src="https://img.shields.io/badge/Tailwind_CSS-4.x-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white" alt="Tailwind CSS 4">
  <img src="https://img.shields.io/badge/Alpine.js-3.x-8BC0D0?style=for-the-badge&logo=alpinedotjs&logoColor=white" alt="Alpine.js 3">
  <img src="https://img.shields.io/badge/Database-MySQL_%7C_SQLite-4479A1?style=for-the-badge&logo=mysql&logoColor=white" alt="MySQL & SQLite">
  <img src="https://img.shields.io/badge/Tests-53%20Passed%20(100%25)-16A34A?style=for-the-badge&logo=checkmarx&logoColor=white" alt="Tests 53 Passed">
  <img src="https://img.shields.io/badge/License-MIT-blue?style=for-the-badge" alt="License MIT">
</p>

---

## 📌 Executive Summary

**CafeSync** is an enterprise-grade monolith web application built for artisanal specialty cafes and coffee roasteries. It unifies four critical operational pillars within a single ACID-compliant transactional database:
1. **High-Speed Web POS Terminal** with real-time recipe-level inventory deductions (*Atomic Gram/Milliliter Deductions*).
2. **Raw Inventory & Supply Chain Engine** equipped with automated threshold alerts (*Low-Stock Alarms*), staff restock requisitions, and a manager approval deck.
3. **HR Shift Scheduling & Payroll System** featuring automated tardiness detection (>15 minutes late), manager override PINs for unscheduled shifts, and monthly hourly wage/overtime calculations.
4. **Executive Intelligence & Audit Reporting** capturing peak hourly rush velocity (*Morning & Afternoon Surges*), labor cost ratios (*LCR*), and official audit exports to **CSV** and styled **PDF**.

---

## 🌐 Live Demonstrations & Quick Access

### 1. Interactive Web Showcase (Hosted on GitHub Pages)
Explore the interactive UI components, POS catalog, and executive dashboard live directly in your browser:
👉 **[https://mradiputra480.github.io/CaffeWebApp/](https://mradiputra480.github.io/CaffeWebApp/)**

---

### 2. Full-Stack Live App Demo Credentials

The application includes a **1-Click Instant Demo Login** portal on the home screen (`/`), bypassing manual password entry for technical reviewers:

| Role | Demo Persona | Email | Password | Direct Portal Route |
| :--- | :--- | :--- | :--- | :--- |
| **Cashier (Staff)** | Alex Rivera | `cashier@cafesync.local` | `password123` | `/pos` &bull; POS Order Terminal |
| **Barista (Staff)** | Sarah Jenkins | `barista@cafesync.local` | `password123` | `/pos` &bull; Barista Station |
| **HRD Manager** | David Miller | `hrd@cafesync.local` | `password123` | `/hr/roster` &bull; Staff Roster & Payroll |
| **Cafe Manager** | Elena Vance | `manager@cafesync.local` | `password123` | `/manager/dashboard` &bull; Executive Intelligence |

> 🛡️ **Brute-Force Lockout Defense Test:** Entering an invalid password three consecutive times locks the target account for 15 minutes, logging a critical security event to prevent brute-force credential stuffing.

---

## 🏗️ Architecture & System Design

```text
+-----------------------------------------------------------------------------------------+
|                                    CAFESYNC ARCHITECTURE                                |
+-----------------------------------------------------------------------------------------+
|                                                                                         |
|   +-------------------+    +--------------------+    +-----------------------------+    |
|   |   STAFF / CASHIER |    |    HRD SPECIALIST  |    |        CAFE MANAGER         |    |
|   +-------------------+    +--------------------+    +-----------------------------+    |
|             |                        |                              |                   |
|             v                        v                              v                   |
|     [ Web POS Terminal ]     [ Shift Roster & ]           [ Executive Analytics & ]     |
|     - Fast Catalog Search    [ Payroll Engine ]           [ Reorder PO Approval   ]     |
|     - Recipe Stock Check     - NFC / PIN Clock-in         - Rush Velocity Surge         |
|     - Cash & QRIS Gateway    - Late Detection (>15m)      - Labor Cost Ratio (LCR)      |
|     - 80mm Thermal Receipt   - Overtime Wage Recalculator - CSV & Styled PDF Audits     |
|             |                        |                              |                   |
|             +------------------------+------------------------------+                   |
|                                      |                                                  |
|                                      v                                                  |
|                     +----------------------------------+                                |
|                     |   SERVICE LAYER & BUSINESS LOGIC |                                |
|                     | - InventoryService (Atomic ACID) |                                |
|                     | - AttendancePayrollService       |                                |
|                     | - AnalyticsService (Rush Engine) |                                |
|                     | - QRISPaymentService (Mock API)  |                                |
|                     +----------------------------------+                                |
|                                      |                                                  |
|                                      v                                                  |
|                     +----------------------------------+                                |
|                     |    RELATIONAL DATABASE LAYER     |                                |
|                     |   MySQL 8.4 (InnoDB) / SQLite    |                                |
|                     +----------------------------------+                                |
+-----------------------------------------------------------------------------------------+
```

---

## 🌟 Core Modules Breakdown

### 1. ☕ High-Speed Web POS Terminal & Atomic Stock Deductions
- **Dynamic Serving Capacity Calculation:** Calculates the maximum servings available for each beverage or pastry in real-time, bottlenecked by the lowest available ingredient stock.
- **Dual Payment Workflows:**
  - *Cash:* Validates tendered amounts against total order value and computes exact change.
  - *Simulated Dynamic QRIS:* Generates dynamic QR payloads with background polling and instant transaction settlement callbacks.
- **ACID Transaction Rollback:** When stock for any constituent ingredient is depleted during concurrent order placement, the transaction rolls back cleanly, maintaining 100% ledger consistency.
- **80mm Thermal Receipt Engine:** Automatically renders print-ready thermal customer receipts containing cashier metadata, order line items, subtotal, 10% tax breakdown, and payment details.

### 2. 📦 Raw Ingredient Inventory & Restock Approval Workflow
- **Precision Metric Tracking:** Tracks raw materials in precise fractional units (`grams`, `ml`, `pcs`), including Arabica beans, whole milk, barista oat milk, syrups, and eco-packaging.
- **Multi-State Threshold Alarms:** Visual badges for *Optimal*, *Low Stock*, and *Critical/Out of Stock* states.
- **Requisition Lifecycle:** Baristas and cashiers can initiate restock requests; General Managers review them in an approval deck with options to approve, reject, or perform bulk 1-click approvals.
- **Stock Intake Ledger:** Records supplier shipments, batch numbers, unit costs, and storage locations, automatically closing approved purchase requisitions.

### 3. 📋 HR Shift Scheduling & Automated Payroll Engine
- **Shift Schedule Enforcement:** Validates worker clock-ins against scheduled rosters.
- **Automated Tardiness Alerts:** Clock-ins occurring >15 minutes past the start time are flagged as `LATE`, triggering high-priority alerts on the HR roster dashboard.
- **Manager Override PINs:** Clocking in for unscheduled shifts requires a verified 4-digit manager PIN (`1234` or `9999`).
- **Real-Time Wage & Overtime Computation:** Aggregates billable regular hours and overtime multipliers against individual hourly wage rates.

### 4. 📊 Executive Intelligence & Financial Auditing
- **Operational KPIs:** Tracks daily/weekly/monthly gross revenue, net margins, customer ticket counts, and average ticket values.
- **Sales Velocity Analysis:** Identifies operational peak rushes during *Morning Surge* (08:00 - 11:00) and *Afternoon Surge* (14:00 - 17:00).
- **Labor Cost Ratio (LCR):** Audits labor spend as a percentage of gross revenue to protect operational margins (*Healthy benchmark: < 30%*).
- **Audit Exports:** One-click exports of audit ledgers to machine-readable **CSV** and print-ready landscape **PDF** documents.

---

## 🛠️ Tech Stack & Engineering Standards

| Layer | Technologies | Description |
| :--- | :--- | :--- |
| **Backend Framework** | **Laravel 12.x / PHP 8.3** | Strict typing, Constructor Promotion, Domain Service Layer, Eloquent ORM |
| **Database** | **MySQL 8.4 (InnoDB) & SQLite** | Foreign key constraints, row-level locking, cross-database compatibility |
| **Frontend Styling** | **Tailwind CSS v4** | Modern dark roast & warm espresso color palette tokens, responsive utility layout |
| **Client Reactivity** | **Alpine.js v3** | Declarative state management for real-time cart, quantity toggles, and modals |
| **Asset Pipeline** | **Vite 6** | Ultra-fast HMR and optimized production asset minification |
| **PDF Generation** | **Barryvdh DomPDF** | Structured landscape A4 financial reports and thermal receipts |
| **Code Linter** | **Laravel Pint** | Strict PSR-12 code style compliance |
| **Testing** | **PHPUnit 12 & Pest** | 53 Automated feature & integration tests (270 assertions) |
| **Containerization** | **Docker & Docker Compose** | Multi-stage production container build (Alpine Linux based) |
| **Continuous Integration** | **GitHub Actions** | Automated CI pipeline executing linting, asset builds, and test suites |

---

## 💻 Local Setup & Installation

### Prerequisites
- **PHP** `>= 8.2` (PHP 8.3 recommended)
- **Composer** `>= 2.x`
- **Node.js** `>= 20.x` & `npm`

---

### Option A: Zero-Config Local Setup with SQLite (Fastest)

1. **Clone the repository:**
   ```bash
   git clone https://github.com/mradiputra480/CaffeWebApp.git
   cd CaffeWebApp
   ```

2. **Copy environment configuration:**
   ```bash
   cp .env.example .env
   ```

3. **Install dependencies:**
   ```bash
   composer install
   npm install
   ```

4. **Generate Application Key & Build Assets:**
   ```bash
   php artisan key:generate
   npm run build
   ```

5. **Run Migrations & Seed Demo Data:**
   ```bash
   php artisan migrate --seed
   ```

6. **Start the local development server:**
   ```bash
   php artisan serve
   ```
   Open your browser at `http://localhost:8000`.

---

### Option B: Docker Compose (1 Command)

```bash
docker compose up -d
```
The application will spin up at `http://localhost:8080` with pre-migrated seed data and optimized production assets.

---

## 🧪 Automated Testing Suite

The codebase features comprehensive feature test coverage validating authentication, role-based security, atomic stock transactions, payroll math, and audit exports:

```bash
php artisan test
```

**Test Execution Output:**
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

## ☁️ Cloud Deployment (Render.com)

This repository includes a ready-to-deploy `render.yaml` blueprint and multi-stage `Dockerfile`:

1. Sign in to [Render.com](https://render.com) using your GitHub account.
2. Navigate to **New +** &rarr; select **Blueprint**.
3. Connect the repository: **`mradiputra480/CaffeWebApp`**.
4. Click **Apply**. Render will automatically build the container, run SQLite migrations and seeders, and provision a free HTTPS URL (e.g., `https://caffewebapp.onrender.com`).

---

## 📂 Repository Structure

```text
CaffeWebApp/
├── app/
│   ├── Http/Controllers/
│   │   ├── Auth/             # Authentication, 15m Lockout Defense, 1-Click Demo
│   │   ├── HR/               # Roster, Shift Scheduling, Attendance Tracking
│   │   ├── Inventory/        # Ingredient Inventory, Restock Requests & Approvals
│   │   ├── Manager/          # Executive Analytics Dashboard, CSV/PDF Audit Exports
│   │   └── POS/              # POS Checkout, Recipe Stock Check, QRIS Simulation
│   ├── Models/               # Eloquent Models (User, Order, Ingredient, Recipe, etc.)
│   ├── Services/             # Domain Services (Analytics, Payroll, Inventory, QRIS)
│   └── Middleware/           # Role-Based Access Control (RoleMiddleware)
├── database/
│   ├── migrations/           # 14 Relational Database Migrations
│   └── seeders/              # Comprehensive Cafe Operational Seed Data
├── docs/
│   ├── index.html            # Interactive Live Showcase Hub for GitHub Pages
│   └── design_mockups/       # Interactive HTML/Tailwind Screen Prototypes
├── resources/
│   ├── views/                # Blade Templates (Warm Espresso Specialty Aesthetic)
│   ├── css/                  # Tailwind CSS Design Tokens
│   └── js/                   # Alpine.js Client Reactivity Handlers
├── tests/
│   └── Feature/              # 53 Automated Integration Test Cases
├── Dockerfile                # Production Multi-Stage Container Definition
├── docker-compose.yml        # Local Containerized Development Setup
└── render.yaml               # 1-Click Cloud Deployment Blueprint
```

---

## 📄 License & Credits

- **Author:** Software Engineer Portfolio Project
- **License:** Open-sourced under the [MIT License](LICENSE).
