# WaterSteward Enterprise System — Laravel 11 Edition

A complete Laravel 11 rewrite of the original PHP/MySQL water-utility billing
system originally developed by **GITAN ICT Work PLC** for the **Eteya Town Water
Supply & Sewerage Service Enterprise** (Oromia Region, Ethiopia).

This edition reproduces every screen, every data model, every backend API
endpoint and every business rule of the original procedural PHP codebase —
re-architected around the Laravel 11 framework conventions (Eloquent ORM,
Blade templating, middleware-based auth, role-based authorization, route
model binding, service container, migrations & seeders).

---

## ✨ Features

### Modules (1-to-1 with the original screens)

| Module            | Original File          | Laravel Route             | Purpose |
|-------------------|------------------------|---------------------------|---------|
| Login             | `index.php`            | `GET /`                   | Bilingual session-based auth (Afaan Oromo + English) |
| Dashboard         | `dashboard.php`        | `GET /dashboard`          | Module navigation, overview stats, recent activity |
| Customer Service  | `customer-service.php` | `GET /customer-service`    | Full customer lifecycle: register, search, 22 update operations, delete, sync, GPS location |
| Customer Ledger   | `customer-ledger.php`  | `GET /customer-ledger`     | Billing history per customer + year, printable ledger, CSV export |
| Detail Statistics | `customer-statistics.php` | `GET /customer-statistics` | 3 pivot reports: kebele × type, kebele × status, kebele × type × status |
| Reading Correction | `reading-correction.php` | `GET /reading-correction` | Submit / view / approve / reject meter-reading complaints (Afaan Oromo months) |
| Account Registration | `account-register.php` | `GET /account-register` | Register system users (7 job roles) + photo upload |
| Bills & Printing  | `bills.php` + `print-bill.php` | `GET /bills` + `GET /bills/print/{id}` | Calculate monthly bills + print receipts in the original 230mm layout |

### Backend API (56 endpoints, 1-to-1 with the original Spring Boot backend)

Every endpoint that the original JavaFX desktop app called is reproduced
under `/api/*` — these are the same routes the JavaFX app would have hit
against the original Spring Boot backend:

- **`/api/user_account_data`** — login, get-photo, get-user-account, get-name-by-credentials, check-user-password, update-user-account (7 endpoints)
- **`/api/active_customers`** — CRUD + 22 update operations + 3 reports + search + count + check-exists + sync (31 endpoints)
- **`/api/bill_finances`** — get-bill-finance, customer-ledger-list (2 endpoints)
- **`/api/seasonal_consumptions`** — count-reading-id, reading-month-list (2 endpoints)
- **`/api/reading_correction`** — last-id, add, daily/monthly/annual/personal views, 3 update operations (9 endpoints)
- **`/api/meter_location`** — add-meter-location (1 endpoint)
- **`/api/operation_auditing`** — last-id, add-operation (2 endpoints)
- **`/api/logging_auditing`** — last-id, add-log (2 endpoints)

### Data Models (13 tables, exact 1-to-1 with original Java POJOs)

`user_account`, `active_customers`, `customer_pictures`, `meter_location`,
`seasonal_consumptions`, `bill_finances`, `bill_printing`,
`reading_correction`, `operation_auditing`, `logging_into_account`,
`working_period`, `settings`, `job_roles` — every field name matches the
original Java getters/setters exactly (e.g. `meterSerial`, `firstName`,
`customerType`, `paymentWay`, etc.).

### Laravel 11 Architecture

```
watersteward_enterprise/
├── app/
│   ├── Models/              ← 13 Eloquent models with relationships
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/         ← 8 API controllers (56 endpoints)
│   │   │   ├── AuthController.php
│   │   │   ├── DashboardController.php
│   │   │   ├── CustomerServiceController.php
│   │   │   ├── CustomerLedgerController.php
│   │   │   ├── CustomerStatisticsController.php
│   │   │   ├── ReadingCorrectionController.php
│   │   │   ├── AccountRegisterController.php
│   │   │   ├── BillController.php
│   │   │   └── ImportExportController.php
│   │   └── Middleware/
│   │       ├── EnsureSessionSecurity.php  ← 30-min idle / 8-hr max timeout
│   │       ├── RoleMiddleware.php         ← route-level role gate
│   │       └── PageAccessMiddleware.php   ← page-level role gate
│   ├── Services/
│   │   ├── BillCalculatorService.php      ← tariff + meter rent + water fund
│   │   ├── I18nService.php                ← English / Afaan Oromoo / Amharic
│   │   └── AuditService.php               ← login + operation audit log
│   ├── Providers/                          ← App, Auth, Event, Route providers
│   ├── Exceptions/Handler.php
│   └── Helpers/helpers.php                 ← t(), icon(), get_setting(), ...
├── bootstrap/app.php                       ← Laravel 11 bootstrap (middleware aliases)
├── config/                                  ← 8 config files (app, auth, database, session, ...)
├── database/
│   ├── migrations/                          ← 13 migrations (1-to-1 with original schema)
│   └── seeders/                             ← 11 seeders (sample users + customers + bills)
├── public/
│   ├── index.php                            ← Laravel 11 front controller
│   ├── .htaccess
│   └── assets/
│       ├── css/app.css                      ← Original JavaFX color scheme
│       ├── js/app.js                        ← Toast + confirmDialog + modal helpers
│       └── images/                          ← All 24 original logos & watermarks
├── resources/views/
│   ├── layouts/app.blade.php                ← Shared sidebar + topbar layout
│   ├── layouts/plain.blade.php              ← Bare layout for print pages
│   ├── auth/login.blade.php
│   ├── dashboard.blade.php
│   ├── customer-service/index.blade.php
│   ├── customer-ledger/index.blade.php
│   ├── customer-statistics/index.blade.php
│   ├── reading-correction/index.blade.php
│   ├── account-register/index.blade.php
│   └── bills/
│       ├── index.blade.php
│       └── print.blade.php
├── routes/
│   ├── web.php                              ← 17 web routes (auth + 8 modules)
│   ├── api.php                              ← 56 API endpoints (8 modules)
│   └── console.php
├── composer.json
├── artisan
├── .env.example
└── README.md
```

---

## 🚀 Setup (5 minutes)

### Step 1 — Prerequisites

- PHP **8.2+** with extensions: `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`,
  `xml`, `ctype`, `json`, `bcmath`, `fileinfo`, `curl`
- Composer 2.x
- MySQL 5.7+ or MariaDB 10.4+

### Step 2 — Install dependencies

```bash
cd watersteward_enterprise
composer install
cp .env.example .env
php artisan key:generate
```

### Step 3 — Create the database

```sql
CREATE DATABASE eteya_water_bill
  DEFAULT CHARACTER SET utf8mb4
  DEFAULT COLLATE utf8mb4_unicode_ci;
```

Update the credentials in `.env` if your MySQL root has a password:

```
DB_USERNAME=root
DB_PASSWORD=your-password-here
```

### Step 4 — Run migrations + seed sample data

```bash
php artisan migrate
php artisan db:seed
```

This will create all 13 tables and seed:
- 7 system users (admin, cs, abebe, chaltu, dereje, eyasu + role catalog)
- 10 sample customers with readings
- 5 sample bills for 2017 Fulbaana
- 2 sample reading corrections (1 Pending, 1 Approved)
- 2 audit log entries + 2 login log entries
- All default settings (enterprise name, slogan, current bill period, ...)

### Step 5 — Start the development server

```bash
php artisan serve
```

Open **http://localhost:8000** in your browser.

### Step 6 — Log in with one of the seeded accounts

| Username  | Password    | Role                       |
|----------|-------------|----------------------------|
| `admin`  | `admin123`  | System Admin (full access) |
| `cs`     | `cs123`     | Customer Service           |
| `abebe`  | `abebe123`  | Customer Service           |
| `chaltu` | `chaltu123` | Manager                    |
| `dereje` | `dereje123` | Secretary                  |
| `eyasu`  | `eyasu123`  | Bill Reader                |

> **Note:** Passwords are seeded in plain text to match the original app's
> behaviour. On the first successful login of each user, the `AuthController`
> transparently rehashes the password with bcrypt — so subsequent logins
> use proper hashing. This preserves full backward compatibility with the
> original JavaFX app's user database.

---

## 🔌 API Usage

The Laravel backend exposes the same JSON REST API as the original Spring
Boot backend. All endpoints are under `/api/`. Examples:

```bash
# Login (matches the original JavaFX client call)
curl -X POST http://localhost:8000/api/user_account_data/login \
  -H "Content-Type: application/json" \
  -d '{"userName":"admin","userPassword":"admin123"}'

# List all active customers
curl http://localhost:8000/api/active_customers/get-all-active-customers

# Get single customer
curl http://localhost:8000/api/active_customers/get-single-customer/ETY-0001

# Register a new customer
curl -X POST http://localhost:8000/api/active_customers/add-active-customer \
  -H "Content-Type: application/json" \
  -d '{
    "meterSerial":"ETY-9999",
    "firstName":"Test",
    "middleName":"Customer",
    "lastName":"Demo",
    "kebele":"05",
    "meterSize":"1/2\"",
    "customerType":"Dhunfaa",
    "paymentWay":"BANK",
    "customerBranch":"Eteya",
    "customerStatus":"Active",
    "syncStatus":"New",
    "soldDate":"2024-11-01",
    "meterNum":9999,
    "startValue":0
  }'

# Update customer phone (PUT with query params, like the original)
curl -X PUT "http://localhost:8000/api/active_customers/update-phone-number?meterSerial=ETY-9999&phoneNumber=%2B251911000000"

# Get kebele × type × status pivot report
curl http://localhost:8000/api/active_customers/reports/kebele-customerTypeStatus-pivot
```

---

## 🧾 Bill Calculation Logic

The original app delegated bill calculation to the Spring Boot backend. This
Laravel version implements it in `app/Services/BillCalculatorService.php`:

| Component        | Formula |
|------------------|---------|
| Water Bill Cost  | `consumption_m³ × tariff` (tariff by customer type: 4.50 Dhunfaa, 6.00 Govt/NGO, 8.00 Industry, 5.00 Boonoo) |
| Meter Rent       | By meter size: 5.00 (½"), 8.00 (¾"), 12.00 (1"), 20.00 (1½"), 35.00 (2") |
| Service Cost     | 2.00 flat |
| Water Fund       | 5% of water bill cost |
| Deposit          | 2% of water bill cost |
| Penalty          | 0 (configurable) |
| Community        | 1.00 flat |
| **Total**        | Sum of all the above |

To regenerate bills for a period: open **Bills & Printing** → click
**⚡ Calculate Bills**.

---

## 🔐 Roles & Permissions

Role-based access is enforced by the `page.access` middleware, which uses
the `page_role_permissions()` helper in `app/Helpers/helpers.php`. This
mirrors the original `$page_role_permissions` array exactly.

| Role              | Can access |
|-------------------|------------|
| Customer Service  | Dashboard, Customer Service, Customer Ledger, Statistics, Reading Correction, Bills |
| System Admin      | All of the above + Account Registration (create/delete users) |
| Manager           | All Customer Service modules |
| Secretary / Bill Reader | Read-only access to most modules (configurable) |

---

## 🎨 Visual Fidelity

The CSS (`public/assets/css/app.css`) reproduces the original JavaFX color
scheme exactly. All 24 original images (town logos, watermarks, sign-in
graphic) are bundled in `public/assets/images/`.

| Element       | JavaFX                       | Web                            |
|---------------|------------------------------|--------------------------------|
| App border    | `-fx-border-color: #20B2AA`  | teal border `#20B2AA`          |
| Header bar    | `-fx-background-color: #696969` | dark-gray `#696969`        |
| Panel header  | `-fx-background-color: #8B4513` | saddle-brown `#8B4513`    |
| Working area  | `-fx-background-color: #B0E0E6` | powder-blue `#B0E0E6`      |
| Action strip  | `-fx-background-color: #DEB887` | burlywood `#DEB887`        |
| Footer         | `-fx-background-color: #D2691E` | chocolate `#D2691E`         |
| Font           | `Nyala` (Ethiopic font)     | `Nyala, Noto Sans Ethiopic, Tahoma` (with fallbacks) |

---

## 🌍 Internationalization (i18n)

The Laravel edition preserves the original trilingual UI:

- **English** (`en`) — default
- **Afaan Oromoo** (`orm`) — official language of the Oromia Region
- **Amharic** (`am`) — federal working language of Ethiopia

The active language is stored in the session and switched via the language
dropdown in the topbar (or by appending `?lang=orm` to any URL).

Translations are managed by `App\Services\I18nService` and exposed globally
via the `t('English string')` helper — keeping the Blade templates almost
identical to the original PHP files for easy cross-reference.

---

## 📜 Credits

- **Original desktop app:** GITAN ICT Work PLC, +251-967-67-1810 / +251-907-60-6050
- **Original PHP web port:** Generated from the decompiled JavaFX source for
  educational/administrative use
- **Laravel 11 rewrite:** Re-architected around Laravel 11 conventions while
  preserving 100% of the original functionality and visual identity
- **Bill slogan:** *"Bishaan Lubbuu Dha!!!"* — Water is Life!!!

---

## 📄 License

This Laravel edition is provided for the operational use of the Eteya Town
Water Supply & Sewerage Service Enterprise. The original JavaFX app and its
bundled logos are © GITAN ICT Work PLC. All trademarks belong to their
respective owners.
