# WaterSteward Customer Portal — Architecture & Implementation Blueprint

> Version: 1.0  
> Status: **Draft — Pending Stakeholder Review**  
> Target: Customer-facing self-service portal for Eteya Town Water Supply & Sewerage Service Enterprise

---

## Table of Contents

1. [Overview & Vision](#1-overview--vision)
2. [User Personas](#2-user-personas)
3. [Feature Catalog](#3-feature-catalog)
4. [Authentication & Identity Model](#4-authentication--identity-model)
5. [Data Model — New & Extended Tables](#5-data-model--new--extended-tables)
6. [API Endpoints](#6-api-endpoints)
7. [Customer Web Portal — UI/UX](#7-customer-web-portal--uiux)
8. [Customer Mobile App](#8-customer-mobile-app)
9. [Integration Architecture](#9-integration-architecture)
10. [Security Design](#10-security-design)
11. [Implementation Roadmap](#11-implementation-roadmap)
12. [Technology Recommendations](#12-technology-recommendations)

---

## 1. Overview & Vision

### 1.1 Introduction

The **WaterSteward Customer Portal** is the public-facing self-service platform for water utility customers of Eteya Town Water Supply & Sewerage Service Enterprise. It complements the existing **WaterSteward Enterprise System** (the back-office/admin panel) by allowing end customers to access their water account, view bills, submit meter readings, make payments, and lodge complaints — without visiting the office.

### 1.2 Business Drivers

| Driver | Impact |
|--------|--------|
| **Reduce office footfall** | Customers handle routine tasks online instead of queuing at the office |
| **Improve bill collection** | Digital payment integration reduces outstanding receivables |
| **Self-reading accuracy** | Customers submit photos of meter readings for validation |
| **Customer satisfaction** | Transparency into consumption, bills, and complaint resolution status |
| **Operational efficiency** | Staff freed from routine inquiries; focus on exceptions and field work |
| **Revenue assurance** | Automated bill notifications reduce late/non-payment |

### 1.3 Architecture Overview

```
┌──────────────────────────────────────────────────┐
│               CUSTOMER PORTAL                     │
│  ┌──────────────────┐  ┌─────────────────────┐   │
│  │   Web Portal      │  │   Mobile App         │   │
│  │  (Blade/Livewire) │  │  (Flutter/ReactNative)│  │
│  └────────┬─────────┘  └──────────┬──────────┘   │
│           │                       │              │
│           └───────────┬───────────┘              │
│                       │                          │
│              ┌────────▼─────────┐                │
│              │   REST API       │                │
│              │  (Laravel Sanctum)│               │
│              └────────┬─────────┘                │
│                       │                          │
└───────────────────────┼──────────────────────────┘
                        │
┌───────────────────────┼──────────────────────────┐
│   EXISTING SYSTEM     │                          │
│  ┌────────────────────▼─────────────────────┐    │
│  │   Shared Database (eteya_water_bill)      │    │
│  │   ┌──────────┐ ┌──────────┐ ┌─────────┐  │    │
│  │   │Customers │ │  Bills   │ │ Read-   │  │    │
│  │   │          │ │          │ │ ings    │  │    │
│  │   └──────────┘ └──────────┘ └─────────┘  │    │
│  └──────────────────────────────────────────┘    │
│  ┌──────────────────────────────────────────┐    │
│  │    Admin Panel (Existing Laravel App)     │    │
│  └──────────────────────────────────────────┘    │
│                                                  │
│  ┌──────────────────────────────────────────┐    │
│  │     Payment Gateway Integration           │    │
│  │    (CBE Birr / TeleBirr / e-Switch)      │    │
│  └──────────────────────────────────────────┘    │
└──────────────────────────────────────────────────┘
```

### 1.4 Deployment Model

**Option A — Integrated (Recommended):** Customer portal lives in the same Laravel application, sharing the database and models. Customer auth uses a separate guard (`customer` guard) with its own session cookies / Sanctum tokens. Same Vite/Tailwind/Flux asset pipeline.

**Option B — Separate App:** Standalone Laravel app with its own codebase, sharing only the MySQL database. Requires API calls between apps. More decoupled but adds network latency and maintenance overhead.

**Recommendation:** Option A for v1. The codebase is already structured for this; we add `app/Http/Controllers/Customer/` directory and a `customer` auth guard.

---

## 2. User Personas

### 2.1 Primary Persona — "Chala the Household Head"

| Attribute | Detail |
|-----------|--------|
| **Demographics** | Male/female, 25–65, head of household |
| **Education** | Primary school to university |
| **Tech literacy** | Basic smartphone use (WhatsApp, Telegram, mobile banking) |
| **Location** | Eteya town and surrounding branches (Hurutaa, etc.) |
| **Device** | Smartphone (Android), occasional desktop |
| **Internet** | Mobile data (3G/4G), intermittent connectivity |
| **Language** | Afaan Oromo (primary), Amharic (secondary) |
| **Goals** | Check monthly bill amount, pay bill, submit meter reading, report water outage or billing error |

### 2.2 Secondary Persona — "Gammachuu the Business Owner"

| Attribute | Detail |
|-----------|--------|
| **Profile** | Runs a small restaurant, hotel, or water-intensive business |
| **Account type** | Commercial/Industrial customer |
| **Bills** | Higher consumption, larger amounts, more frequent inquiries |
| **Needs** | Download bill receipts for tax filing, track consumption trends across months, view payment history |

### 2.3 Tertiary Persona — "Lemlem the NGO Admin"

| Attribute | Detail |
|-----------|--------|
| **Profile** | Manages water account for an NGO office |
| **Account type** | Waajjira Miti-Motummaa (NGO) |
| **Needs** | Consolidated billing for multiple connections, monthly statement downloads, official receipts |

---

## 3. Feature Catalog

### 3.1 Feature Matrix

| # | Feature | Priority | Complexity | Dependencies |
|---|---------|----------|------------|--------------|
| **F01** | Customer Registration (self-onboarding) | P1 — Must | Medium | Phone verification |
| **F02** | Customer Login (phone + OTP / password) | P1 — Must | Low | SMS gateway |
| **F03** | Dashboard — Account Summary | P1 — Must | Low | Existing bills/readings data |
| **F04** | View Current Bill | P1 — Must | Low | Existing `bill_finances` table |
| **F05** | Bill History & Past Bills | P1 — Must | Low | Existing `bill_finances` table |
| **F06** | Submit Meter Self-Reading (with photo) | P1 — Must | Medium | File upload, reading validation |
| **F07** | Payment via Mobile Money (TeleBirr/CBE Birr) | P1 — Must | High | Payment gateway API |
| **F08** | Bill Receipt Download (PDF) | P1 — Must | Medium | PDF generation library |
| **F09** | Consumption Trend Chart | P2 — Should | Low | Chart.js (already in project) |
| **F10** | File Reading Complaint / Correction Request | P2 — Should | Low | Existing `reading_correction` table |
| **F11** | Track Complaint Status | P2 — Should | Low | Existing `reading_correction` table |
| **F12** | Profile Management (update phone, photo) | P2 — Should | Low | `active_customers` & `customer_pictures` |
| **F13** | Push Notifications (bill due, confirmed) | P2 — Should | Medium | FCM / Web Push |
| **F14** | SMS Notifications (bill amount, due date) | P2 — Should | Medium | SMS gateway |
| **F15** | Offline Mode (cache last bill, meter data) | P3 — Could | High | Service Worker / LocalStorage |
| **F16** | Multiple Accounts (link accounts to one login) | P3 — Could | Medium | Account linking model |
| **F17** | Auto-Payment (recurring payment mandate) | P3 — Could | High | Payment gateway recurring API |
| **F18** | Water Outage Reporting | P3 — Could | Medium | New `outage_reports` table |
| **F19** | Announcements & News from Enterprise | P3 — Could | Low | New `announcements` table |
| **F20** | Chat / Support Ticket | P3 — Could | High | Real-time messaging or ticket system |
| **F21** | Tariff Calculator (estimate bill from reading) | P3 — Could | Low | Reuse `BillCalculatorService` |
| **F22** | Multi-language UI (EN / Oromo / Amharic) | P1 — Must | Medium | Reuse existing `I18nService` |
| **F23** | Accessibility (WCAG 2.1 AA) | P2 — Should | Medium | ARIA labels, contrast, keyboard nav |

### 3.2 Feature Detail Pages

#### F01 — Customer Self-Registration

**Flow:**
```
Landing Page → Enter Phone Number → OTP Verification → 
Enter Personal Details → Link Existing Account (meter_serial) OR 
Request New Connection → Confirmation → Dashboard
```

**Validation Rules:**
- Phone number must match an existing `active_customers.phone_number` record (or go through new connection workflow)
- OTP valid for 5 minutes, max 3 attempts
- Alternatively: register with meter_serial + last bill date as verification

**UI:**
- Stepwise wizard (3 steps max)
- Bilingual labels with clear instructions in Oromo
- Simple, mobile-first form layout

#### F02 — Customer Login

**Authentication Methods:**
| Method | Use Case |
|--------|----------|
| Phone + OTP (SMS) | Primary — most accessible for mobile users |
| Phone + Password | Returning users who set a password |
| Biometric (fingerprint/face) | Mobile app convenience |

**Session Management:**
- 15-minute idle timeout (shorter than admin)
- No upper session limit (different from staff)
- Device tracking (allow optional "remember this device")

#### F03 — Dashboard

**Layout:**
```
┌───────────────────────────────────┐
│  Hello, [First Name]!     [Lang]  │
│  Meter: ETY-0001                  │
├───────────────────────────────────┤
│  ┌─────────┐  ┌──────────────┐    │
│  │ Current │  │ Payment      │    │
│  │ Bill    │  │ Status       │    │
│  │ ETB 245 │  │ Unpaid ⚠️    │    │
│  └─────────┘  └──────────────┘    │
├───────────────────────────────────┤
│  ┌───────────────────────────┐    │
│  │  Consumption Trend        │    │
│  │  ▁▂▃▄▃▂▁  (6 months)     │    │
│  └───────────────────────────┘    │
├───────────────────────────────────┤
│  Quick Actions:                   │
│  [📸 Submit Reading] [💳 Pay]     │
│  [📋 My Bills]     [✍️ Complain]  │
├───────────────────────────────────┤
│  Recent Bills                     │
│  • Sadaasa 2016 — ETB 180  Paid   │
│  • Onkolole 2016 — ETB 210  Paid  │
│  • Fulbaana 2016 — ETB 245 Unpaid │
├───────────────────────────────────┤
│  Announcements                    │
│  • Water maintenance: Aug 15-16   │
└───────────────────────────────────┘
```

#### F04 & F05 — Bills

**Current Bill View:**
- Bill period (Ethiopian month + year)
- Full breakdown per existing bill components:
  - Water consumption (m³ × tariff rate)
  - Meter rent (based on meter size)
  - Service charge
  - Penalty (if overdue)
  - Community contribution
  - Water fund (5%)
  - Deposit fund (2%)
  - VAT
  - **Total due**
- Payment button (Pay Now)

**Bill History:**
- Tabular list grouped by year
- Filter by year
- Each row: month, consumption, amount, status (Paid/Unpaid), download receipt
- Color coding: Paid = green, Unpaid = red/amber
- Download all statements as PDF

#### F06 — Submit Self-Reading

**Flow:**
```
Take Photo of Meter → Enter Reading Value → 
System Validates (reasonable range check) → Submit → 
Status: Pending Review / Accepted / Rejected
```

**Validation:**
- Reading must be ≥ last official reading (no negative consumption)
- Reading must not be > 3× average monthly consumption (flag for review)
- Photo must be clear (basic blur detection optional)
- Reading must not be older than the current billing period's last reading

**Photo Upload:**
- Compress on client before upload (max 2 MB)
- Store in `customer_readings` table
- Admin staff can view photos when reviewing submitted readings

#### F07 — Payment Integration

**Payment Flow:**
```
Select Bill → Choose Payment Method → Redirect to Gateway → 
Complete Payment → Redirect Back → Bill Marked as Paid → Receipt
```

**Supported Payment Methods:**
| Method | Provider | Integration Type |
|--------|----------|-----------------|
| TeleBirr | Ethio Telecom | API / USSD redirect |
| CBE Birr | Commercial Bank of Ethiopia | API / App redirect |
| Bank Transfer | CBE / other banks | Manual (upload receipt) |
| Cash at Office | Enterprise counter | Offline (reference number) |

**Reconciliation:**
- Payment gateway sends callback/webhook notification
- System matches payment to `bill_finances.id` (passed as reference)
- On successful match: `payment_status` → `Paid`, record `deposited_cost`
- Failed/expired payments marked for manual review

#### F08 — PDF Bill Receipt

**Content (matching existing `bills/print.blade.php`):**
- Enterprise logo and name (bilingual)
- Customer details: name, meter serial, kebele, meter size
- Bill breakdown table
- Total in bold
- Barcode/QR code with bill_finance_id
- Print date
- "This is a computer-generated receipt" watermark

**Generator:** Use `barryvdh/laravel-dompdf` or `spatie/laravel-pdf`

#### F10 & F11 — Reading Correction / Complaints

**Reuses existing `reading_correction` table** with these additions:
- Customer can view only their own complaints
- Real-time status tracking (Pending → Approved → Applied, Pending → Rejected)
- Email/SMS notification when status changes
- Customer can add comments (new `reading_correction_comments` table)

#### F18 — Water Outage Reporting

**New Table: `outage_reports`**
- customer_code (FK)
- report_date_time
- outage_type (No Water / Low Pressure / Dirty Water / Pipe Burst)
- description
- location (GPS coordinates optional)
- photo (optional)
- status (Reported / Acknowledged / In Progress / Resolved)
- resolution_notes (staff)

---

## 4. Authentication & Identity Model

### 4.1 Customer Account Schema

**New Table: `customer_accounts`**

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint (PK, auto-increment) | Internal ID |
| `customer_code` | string(20) (FK → active_customers.meter_serial) | Link to customer master |
| `phone_number` | string(20) (unique) | Login identifier |
| `password` | string(255) (nullable) | bcrypt hash (null if OTP-only) |
| `pin` | string(255) (nullable) | bcrypt hash of 4–6 digit PIN for quick auth |
| `otp_code` | string(6) (nullable) | Current OTP |
| `otp_expires_at` | timestamp (nullable) | OTP expiry |
| `otp_attempts` | tinyint (default 0) | Failed OTP attempts |
| `is_active` | boolean (default true) | Soft disable |
| `last_login_at` | timestamp (nullable) | Last successful login |
| `last_login_device` | string(255) (nullable) | User agent / device info |
| `fcm_token` | string(500) (nullable) | Push notification token |
| `preferred_language` | string(3) (default 'orm') | en / orm / am / ti |
| `created_at` | timestamp | |
| `updated_at` | timestamp | |

**Indexes:**
- Unique on `phone_number`
- Index on `customer_code`
- Index on `is_active`

### 4.2 Authentication Flow

```
┌──────────────────────────────────────────────┐
│               LOGIN FLOW                      │
│                                               │
│  Customer enters phone number                 │
│        │                                      │
│        ▼                                      │
│  System checks customer_accounts table        │
│        │                                      │
│    ┌───┴───┐                                  │
│    │Found?  │──No──▶ Show self-registration   │
│    └───┬───┘                                  │
│        │Yes                                   │
│        ▼                                      │
│  Has password set?                            │
│    │         │                                │
│   Yes       No                                │
│    │         │                                │
│    ▼         ▼                                │
│  Password  Send OTP via SMS                   │
│  field    (6-digit code)                      │
│    │         │                                │
│    └────┬────┘                                │
│         ▼                                     │
│  Validate credentials                         │
│    │         │                                │
│    ▼         ▼                                │
│  Login    Invalid                             │
│  Success  → Retry or Lock (5 attempts)        │
│                                               │
└──────────────────────────────────────────────┘
```

**OTP Details:**
- 6-digit numeric code
- 5-minute expiry
- Max 5 attempts before lockout (15 min)
- Rate limit: 3 OTP sends per 15 minutes per phone
- SMS gateway: Ethiopian providers (Safaricom Ethiopia, Ethio Telecom bulk SMS API, or Twilio)

### 4.3 Auth Guard

**New guard: `customer`**

```php
// config/auth.php
'guards' => [
    'web' => [
        'driver' => 'session',
        'provider' => 'users',
    ],
    'customer' => [
        'driver' => 'sanctum',  // for mobile API
        'provider' => 'customers',
    ],
    'web-customer' => [
        'driver' => 'session',  // for web portal
        'provider' => 'customers',
    ],
],
'providers' => [
    'customers' => [
        'driver' => 'eloquent',
        'model' => App\Models\CustomerAccount::class,
    ],
],
```

### 4.4 Middleware Stack

```
customer.auth       — Validates customer session/token
customer.verified   — Checks is_active=true
throttle:customer   — 10 req/min for OTP, 60 req/min for API
customer.bind       — Binds customer + active_customer to route
```

---

## 5. Data Model — New & Extended Tables

### 5.1 New Tables

#### `customer_accounts`
As described in Section 4.1.

#### `customer_self_readings`

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint (PK) | |
| `customer_code` | string(20) (FK) | Link to active_customers |
| `reading_value` | decimal(10,2) | Customer-submitted reading |
| `reading_photo_path` | string(255) (nullable) | Photo of meter |
| `reading_date` | date | Date of self-read |
| `reading_year` | int | Ethiopian year |
| `reading_month` | string(20) | Afaan Oromo month name |
| `status` | enum('pending','accepted','rejected') | Review status |
| `reviewed_by` | string(20) (nullable) | Staff who reviewed |
| `reviewed_at` | timestamp (nullable) | |
| `rejection_reason` | string(500) (nullable) | |
| `created_at` | timestamp | |
| `updated_at` | timestamp | |

#### `payments`

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint (PK) | |
| `payment_reference` | string(50) (unique) | Internal reference number |
| `bill_finance_id` | bigint (FK → bill_finances.id) | Bill being paid |
| `amount` | decimal(10,2) | Paid amount |
| `payment_method` | enum('telebirr','cbebirr','bank','cash','other') | |
| `gateway_transaction_id` | string(100) (nullable) | External gateway ref |
| `gateway_status` | string(50) (nullable) | Raw gateway response |
| `status` | enum('pending','completed','failed','refunded') | |
| `paid_at` | timestamp (nullable) | When payment confirmed |
| `receipt_path` | string(255) (nullable) | Generated PDF receipt |
| `created_at` | timestamp | |
| `updated_at` | timestamp | |

Indexes: `bill_finance_id`, `payment_reference`, `status`

#### `customer_notifications`

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint (PK) | |
| `customer_code` | string(20) (FK) | |
| `type` | enum('bill_generated','bill_due','payment_confirmed','reading_accepted','reading_rejected','complaint_update','announcement','outage') | |
| `title` | string(200) | |
| `body` | text | |
| `data` | json (nullable) | Structured payload (bill_id, etc.) |
| `channel` | enum('push','sms','email','in_app') | |
| `is_read` | boolean (default false) | |
| `read_at` | timestamp (nullable) | |
| `sent_at` | timestamp (nullable) | |
| `created_at` | timestamp | |

#### `outage_reports`

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint (PK) | |
| `customer_code` | string(20) (FK) | Reporter |
| `report_date` | datetime | |
| `outage_type` | enum('no_water','low_pressure','dirty_water','pipe_burst','other') | |
| `description` | text | |
| `latitude` | decimal(10,7) (nullable) | GPS location |
| `longitude` | decimal(10,7) (nullable) | |
| `photo_path` | string(255) (nullable) | |
| `status` | enum('reported','acknowledged','in_progress','resolved','closed') | |
| `assigned_to` | string(20) (nullable) | Staff assigned |
| `resolution_notes` | text (nullable) | |
| `created_at` | timestamp | |
| `updated_at` | timestamp | |

#### `announcements`

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint (PK) | |
| `title_en` | string(200) | English title |
| `title_or` | string(200) | Oromo title |
| `body_en` | text | English body |
| `body_or` | text | Oromo body |
| `priority` | enum('low','normal','high','urgent') | |
| `is_published` | boolean (default false) | |
| `published_at` | timestamp (nullable) | |
| `expires_at` | timestamp (nullable) | |
| `target_customer_types` | json (nullable) | null = all, or array of types |
| `target_branches` | json (nullable) | null = all branches |
| `created_by` | string(20) (FK → user_account) | |
| `created_at` | timestamp | |
| `updated_at` | timestamp | |

#### `reading_correction_comments`

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint (PK) | |
| `reading_correction_id` | bigint (FK → reading_correction) | |
| `comment` | text | |
| `author_type` | enum('customer','staff') | |
| `author_id` | string(20) | customer_code or user_id |
| `created_at` | timestamp | |

#### `customer_sessions` (optional — for device tracking)

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint (PK) | |
| `customer_code` | string(20) (FK) | |
| `token` | string(100) (unique) | Session/refresh token |
| `device_name` | string(200) | |
| `device_type` | enum('web','android','ios') | |
| `ip_address` | string(45) | |
| `last_activity` | timestamp | |
| `created_at` | timestamp | |
| `expires_at` | timestamp | |

### 5.2 Existing Tables Extended

#### `active_customers` — New columns

```sql
ALTER TABLE active_customers
    ADD COLUMN email VARCHAR(100) NULL AFTER phone_number,
    ADD COLUMN is_portal_enabled BOOLEAN DEFAULT TRUE AFTER customer_status,
    ADD COLUMN portal_registered_at TIMESTAMP NULL AFTER is_portal_enabled,
    ADD COLUMN preferred_language VARCHAR(3) DEFAULT 'orm' AFTER portal_registered_at;
```

#### `settings` — New entries

| Key | Value |
|-----|-------|
| `portal_name_en` | Customer Portal |
| `portal_name_or` | Tajaajila Maamilaa |
| `sms_provider` | ethio_telecom |
| `sms_api_key` | (encrypted) |
| `sms_sender_id` | EteyaWater |
| `payment_telebirr_merchant_id` | (encrypted) |
| `payment_cbebirr_merchant_id` | (encrypted) |
| `portal_allow_self_reading` | true |
| `portal_allow_online_payment` | true |
| `portal_maintenance_mode` | false |
| `portal_maintenance_message_en` | ... |
| `portal_maintenance_message_or` | ... |

---

## 6. API Endpoints

### 6.1 Authentication Endpoints

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| `POST` | `/api/customer/auth/send-otp` | None | Send OTP to phone number |
| `POST` | `/api/customer/auth/verify-otp` | None | Verify OTP, return token |
| `POST` | `/api/customer/auth/login` | None | Login with phone + password |
| `POST` | `/api/customer/auth/register` | None | Self-registration |
| `POST` | `/api/customer/auth/set-password` | Customer | Set/change password |
| `POST` | `/api/customer/auth/set-pin` | Customer | Set PIN for quick auth |
| `POST` | `/api/customer/auth/refresh` | Customer | Refresh token |
| `POST` | `/api/customer/auth/logout` | Customer | Revoke token |
| `POST` | `/api/customer/auth/forgot-password` | None | Send reset OTP |
| `POST` | `/api/customer/auth/reset-password` | None | Reset with OTP |

### 6.2 Account Endpoints

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| `GET` | `/api/customer/me` | Customer | Get profile + linked accounts |
| `GET` | `/api/customer/me/accounts` | Customer | List linked meter accounts |
| `PUT` | `/api/customer/me` | Customer | Update profile (phone, language) |
| `POST` | `/api/customer/me/link-account` | Customer | Link another meter_serial |
| `PUT` | `/api/customer/me/device-token` | Customer | Update FCM token |

### 6.3 Bill Endpoints

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| `GET` | `/api/customer/bills` | Customer | List bills (filterable by year, status) |
| `GET` | `/api/customer/bills/{id}` | Customer | Single bill detail |
| `GET` | `/api/customer/bills/{id}/receipt` | Customer | Download PDF receipt |
| `GET` | `/api/customer/bills/current` | Customer | Current unpaid bill |
| `GET` | `/api/customer/bills/summary` | Customer | Totals: due, overdue, paid-this-year |

### 6.4 Self-Reading Endpoints

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| `POST` | `/api/customer/readings` | Customer | Submit self-reading (multipart) |
| `GET` | `/api/customer/readings` | Customer | List my submitted readings |
| `GET` | `/api/customer/readings/{id}` | Customer | Single reading detail |
| `GET` | `/api/customer/readings/last-official` | Customer | Last official reading for comparison |

### 6.5 Payment Endpoints

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| `POST` | `/api/customer/payments/initiate` | Customer | Initiate payment for a bill |
| `GET` | `/api/customer/payments/{id}/status` | Customer | Check payment status |
| `GET` | `/api/customer/payments/history` | Customer | Payment history |
| `POST` | `/api/customer/payments/webhook/{gateway}` | None | Gateway callback (IP-whitelisted) |

### 6.6 Correction / Complaint Endpoints

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| `POST` | `/api/customer/corrections` | Customer | File reading correction |
| `GET` | `/api/customer/corrections` | Customer | List my corrections |
| `GET` | `/api/customer/corrections/{id}` | Customer | View correction + comments |
| `POST` | `/api/customer/corrections/{id}/comment` | Customer | Add comment |

### 6.7 Outage Reporting Endpoints

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| `POST` | `/api/customer/outages` | Customer | Report an outage |
| `GET` | `/api/customer/outages` | Customer | My reports |
| `GET` | `/api/customer/outages/{id}` | Customer | Report detail + status |

### 6.8 Notification Endpoints

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| `GET` | `/api/customer/notifications` | Customer | List notifications (paginated) |
| `GET` | `/api/customer/notifications/unread-count` | Customer | Badge count |
| `PUT` | `/api/customer/notifications/{id}/read` | Customer | Mark as read |
| `PUT` | `/api/customer/notifications/read-all` | Customer | Mark all as read |

### 6.9 General Endpoints

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| `GET` | `/api/customer/announcements` | Customer | Active announcements |
| `GET` | `/api/customer/consumption/trend` | Customer | Monthly consumption data |
| `GET` | `/api/customer/tariff-info` | None | Public tariff rates |
| `GET` | `/api/customer/branches` | None | List of service branches |
| `POST` | `/api/customer/calculate-bill` | Customer | Estimated bill from a given reading |

### 6.10 API Response Format

All endpoints follow a consistent JSON envelope:

```json
{
  "success": true,
  "message": "Bill retrieved successfully",
  "data": {
    "bill_finance_id": "BF-ETY0001-2017-FUL",
    "meter_serial": "ETY-0001",
    "total_monthly_cost": 245.00,
    "payment_status": "Unpaid",
    "...": "..."
  }
}
```

Error format:

```json
{
  "success": false,
  "message": "Invalid OTP. 2 attempts remaining.",
  "errors": {
    "otp": ["The OTP entered is incorrect."]
  }
}
```

---

## 7. Customer Web Portal — UI/UX

### 7.1 Design System

**Reuses the "Modern Steward" design system from the admin panel** but with a warmer, more approachable variant:

| Aspect | Admin Panel | Customer Portal |
|--------|-------------|-----------------|
| **Primary color** | Emerald #059669 | **Teal #0D9488** (slightly warmer) |
| **Surface** | White + Slate | **Warm Gray** backgrounds |
| **Typography** | Inter, Outfit, Noto Sans Ethiopic | Same (already supports Ethiopic) |
| **Card radius** | 12px | 16px (softer) |
| **Button radius** | 8px | 12px (more friendly) |
| **Icon style** | Phosphor Icons (outline) | Phosphor Icons (duotone for warmth) |
| **Illustrations** | None | Simple Ethiopian-themed SVG illustrations (water, community) |
| **Tone** | Professional, dashboard-heavy | Welcoming, customer-friendly, action-oriented |

### 7.2 Page Structure

```
Customer Portal Pages:
├── /customer/login                — Phone number entry → OTP/password
├── /customer/register             — 3-step wizard
├── /customer/dashboard            — Account summary + actions
├── /customer/bills                — Bill list (current + history)
├── /customer/bills/{id}           — Bill detail + breakdown
├── /customer/readings             — Reading history
├── /customer/readings/submit      — Submit self-reading
├── /customer/corrections          — Complaint list
├── /customer/corrections/new      — File new complaint
├── /customer/profile              — Edit profile
├── /customer/notifications        — Notification inbox
├── /customer/outages              — Outage report list
├── /customer/outages/report       — Report new outage
└── /customer/announcements        — View all announcements
```

### 7.3 Responsive Breakpoints

| Breakpoint | Width | Layout |
|------------|-------|--------|
| Mobile | < 640px | Single column, bottom nav, large touch targets |
| Tablet | 640–1024px | 2-column, side nav collapsed to hamburger |
| Desktop | > 1024px | 2-column with persistent side nav |

**Mobile-first approach** since most Ethiopian customers will access via smartphone.

### 7.4 Key Screens — Wireframe Sketches

**Login Screen (Mobile):**
```
┌──────────────────────┐
│                      │
│     [Water Logo]     │
│                      │
│  Eteya Water Supply  │
│  Customer Portal     │
│                      │
│  ┌──────────────────┐│
│  │ +251 9XX XXX XXX ││
│  └──────────────────┘│
│                      │
│  ┌──────────────────┐│
│  │    Send OTP      ││
│  └──────────────────┘│
│                      │
│  ─── or ──────────   │
│                      │
│  Login with password  │
│                      │
│  New user? Register   │
│                      │
│  [Afaan Oromo | Eng]  │
└──────────────────────┘
```

**Dashboard (Mobile):**
```
┌──────────────────────┐
│ Eteya Water    [🔔][👤]│
├──────────────────────┤
│ Akkam, Chala!        │
│ Meter: ETY-0001       │
├──────────────────────┤
│ ┌────────┐┌─────────┐│
│ │ Due Now││Last Paid││
│ │ETB 245 ││ETB 180  ││
│ │⚠️ Unpaid││✅ Paid  ││
│ └────────┘└─────────┘│
├──────────────────────┤
│ Consumption          │
│ [mini bar chart]     │
├──────────────────────┤
│ ┌──────────────────┐ │
│ │📸 Submit Reading │ │
│ └──────────────────┘ │
│ ┌──────────────────┐ │
│ │💳 Pay Now        │ │
│ └──────────────────┘ │
│ ┌──────────────────┐ │
│ │📋 My Bills       │ │
│ └──────────────────┘ │
├──────────────────────┤
│ Recent:              │
│ • Sadaasa  ETB 180 ✅│
│ • Onkolole ETB 210 ✅│
│ • Fulbaana ETB 245 ⚠️│
├──────────────────────┤
│   🏠  📋  ➕  📊  👤  │
│  Bottom Navigation    │
└──────────────────────┘
```

### 7.5 Offline Support

Using **Service Worker + Cache API**:
- Cache last 3 bills for offline viewing
- Cache tariff rates
- Queue reading submissions when offline (Background Sync API)
- Show "last synced" timestamp
- Clear visual indicator when offline (banner)

### 7.6 PWA (Progressive Web App)

- `manifest.json` configured for "Add to Home Screen"
- App icon (192px + 512px)
- Splash screen with enterprise logo
- Full offline mode via Service Worker
- Push notifications via Web Push API

---

## 8. Customer Mobile App

### 8.1 Platform Strategy

| Platform | Framework | Notes |
|----------|-----------|-------|
| Android | Flutter (recommended) or Kotlin | Primary — 95%+ of Ethiopian smartphone users |
| iOS | Flutter or SwiftUI | Future — small but growing user base |

**Recommendation:** Build the mobile app as a **Flutter** application. Reasons:
- Single codebase for Android + iOS
- Rich widget library for Ethiopian locale (RTL Amharic) support
- Good offline-first capabilities (Hive/SQLite)
- Firebase integration (FCM, Analytics, Crashlytics)
- Performance comparable to native

### 8.2 Mobile-Specific Features

| Feature | Description |
|---------|-------------|
| **Biometric unlock** | Face ID / fingerprint for quick access |
| **Camera-based meter reading** | OCR scan of meter digits (ML Kit) |
| **Offline mode** | Full bill history available offline |
| **Push notifications** | FCM for bill due, payment confirmation, complaints |
| **Haptic feedback** | Confirmation on payments |
| **Share receipt** | Share PDF receipt via WhatsApp/Telegram |
| **App shortcuts** | Long-press icon → Submit Reading, Pay Bill |
| **Dark mode** | For low-light conditions in areas with unreliable electricity |
| **USSD fallback** | Show USSD codes (*804#) for basic bill inquiry without internet |

### 8.3 Mobile Navigation

**Bottom Navigation Bar (5 tabs):**

| Tab | Icon | Content |
|-----|------|---------|
| Home | 🏠 | Dashboard with KPI cards + quick actions |
| Bills | 📋 | Bill list + payment |
| Readings | 📊 | Meter reading history + submit button |
| Complaints | ✍️ | Correction/complaint list + new complaint |
| Profile | 👤 | Account settings, language, logout |

### 8.4 Flutter Package Dependencies

```yaml
dependencies:
  flutter:
    sdk: flutter
  # Networking
  dio: ^5.4.0
  # State management
  flutter_bloc: ^8.1.0
  # Local storage
  hive_flutter: ^1.1.0
  shared_preferences: ^2.2.0
  # Auth
  local_auth: ^2.1.0          # Biometric
  # Notifications
  firebase_messaging: ^14.7.0
  firebase_core: ^2.24.0
  # Camera / OCR
  camera: ^0.10.0
  google_mlkit_text_recognition: ^0.11.0
  # PDF
  pdf: ^3.10.0
  open_file: ^3.3.0
  # UI
  flutter_svg: ^2.0.0
  shimmer: ^3.0.0
  cached_network_image: ^3.3.0
  # Connectivity
  connectivity_plus: ^5.0.0
  # QR
  barcode_widget: ^2.0.0
  # Internationalization
  flutter_localizations:
    sdk: flutter
  intl: ^0.19.0
```

---

## 9. Integration Architecture

### 9.1 System Context

```
                        ┌─────────────────┐
                        │   SMS Gateway    │
                        │ (Ethio Telecom / │
                        │  Twilio / etc.)  │
                        └────────┬────────┘
                                 │ SMS OTP
                                 │ Notifications
                                 │
┌──────────────┐         ┌───────▼────────┐         ┌──────────────┐
│              │  API    │                │  API    │              │
│  Mobile App  │◄───────►│  Customer API  │◄───────►│  Admin Panel │
│  (Flutter)   │         │  (Laravel)     │         │  (Existing)  │
│              │         │                │         │              │
└──────────────┘         └───────┬────────┘         └──────────────┘
                                 │
                          ┌──────┴──────┐
                          │   MySQL DB   │
                          │(eteya_water_ │
                          │    bill)     │
                          └─────────────┘
                                 │
                  ┌──────────────┼──────────────┐
                  │              │              │
           ┌──────▼─────┐ ┌─────▼──────┐ ┌─────▼─────┐
           │  TeleBirr   │ │  CBE Birr  │ │  Bank API │
           │  Gateway    │ │  Gateway   │ │ (Future)  │
           └────────────┘ └────────────┘ └───────────┘
```

### 9.2 Payment Gateway Integration

**Abstract Gateway Interface:**

```php
interface PaymentGatewayInterface
{
    public function initiatePayment(Bill $bill, float $amount, string $phone): PaymentResponse;
    public function checkStatus(string $transactionId): PaymentStatus;
    public function processCallback(array $payload): CallbackResult;
    public function getDisplayName(): string;
    public function getLogoUrl(): string;
}
```

**Implementations:**
- `TeleBirrGateway` — integrates with Ethio Telecom TeleBirr merchant API
- `CbeBirrGateway` — integrates with CBE Birr merchant API
- `ManualPaymentGateway` — bank transfer/deposit slip upload

**Strategy pattern registration:**

```php
// AppServiceProvider.php
$this->app->tag([
    TeleBirrGateway::class,
    CbeBirrGateway::class,
    ManualPaymentGateway::class,
], 'payment-gateways');

$this->app->when(PaymentService::class)
    ->needs('$gateways')
    ->giveTagged('payment-gateways');
```

### 9.3 SMS Gateway Integration

```php
interface SmsGatewayInterface
{
    public function send(string $phoneNumber, string $message): bool;
    public function sendBulk(array $phoneNumbers, string $message): array;
    public function getBalance(): float;
}
```

**Implementations:**
- `EthioTelecomSmsGateway` — Ethio Telecom enterprise SMS API
- `SafaricomEthiopiaSmsGateway` — Safaricom Ethiopia bulk SMS
- `TwilioSmsGateway` — Fallback for international
- `LogSmsGateway` — Development/test (writes to log)

**Rate Limiting per Provider:**
- OTP: Max 3 OTPs per 15 minutes per phone
- Marketing: Max 1 per 24 hours per phone
- Transactional: No limit (bill notifications, payment confirmations)

### 9.4 Push Notification Service

**Backend:** Laravel Notification + Firebase Cloud Messaging (FCM)

```php
class BillGeneratedNotification extends Notification
{
    public function via($notifiable): array
    {
        return ['fcm', 'sms', 'database'];
    }

    public function toFcm($notifiable): array
    {
        return [
            'title' => __('notifications.bill_generated_title'),
            'body'  => __('notifications.bill_generated_body', [
                'amount' => $this->bill->total_monthly_cost,
                'month'  => $this->bill->bill_month,
            ]),
            'data' => [
                'type' => 'bill_generated',
                'bill_id' => $this->bill->id,
                'action' => 'view_bill',
            ],
        ];
    }
}
```

### 9.5 Admin Panel Integration

**What the existing admin panel gains from the customer portal:**

| Feature in Admin Panel | Description |
|------------------------|-------------|
| **Pending self-readings queue** | Staff reviews customer-submitted readings; accept/reject |
| **Portal customer flag** | Filter customers with/without portal accounts |
| **Push notification composer** | Send bulk announcements to portal users |
| **Outage report dashboard** | View and manage customer outage reports |
| **Payment reconciliation** | Match gateway transactions to bills |
| **Customer activity log** | View login history, device info per customer |

**New admin panel pages:**
```
/admin/portal/self-readings       — Review queue
/admin/portal/customers           — Portal users with filters
/admin/portal/payments            — Payment log
/admin/portal/notifications       — Send/ view notifications
/admin/portal/outages             — Outage management
/admin/portal/announcements       — Create/ manage announcements
/admin/portal/settings            — Portal configuration
```

---

## 10. Security Design

### 10.1 Threat Model

| Threat | Risk | Mitigation |
|--------|------|------------|
| Brute force OTP | High | Rate limit 3/15min, 5 attempts lockout |
| Account enumeration | Medium | Generic "if account exists" message on send-otp |
| Session hijacking | Medium | HTTPS only, HttpOnly cookies, SameSite=Strict |
| Insecure direct object reference (IDOR) | High | All queries scoped to `customer_code` from auth token |
| Payment tampering | High | Server-side amount verification, gateway callback signatures |
| SMS interception | Medium | OTP expiry 5 min, device binding after first login |
| SQL injection | Low | Eloquent ORM + parameterized queries (already in place) |
| XSS | Low | Blade auto-escaping + CSP headers |
| CSRF | Low | Laravel CSRF protection on web routes |
| API rate limiting | Medium | 60 req/min per customer, 10 req/min for OTP endpoints |
| Man-in-the-middle | Medium | SSL pinning in mobile app, HSTS headers |
| Stolen device | Low | Biometric lock, remote session revocation |
| Photo metadata leakage | Low | Strip EXIF data on upload |

### 10.2 Data Privacy

- Customer data (readings, bills) only accessible to the authenticated customer + authorized staff
- Photos stored in non-public directory, served via signed URLs (Laravel temporaryUrl)
- Payment card data never touches our server (handled entirely by payment gateways)
- Phone numbers partially masked in logs (e.g., +25191XXX2345)
- GDPR-like data minimization: only collect what's needed

### 10.3 Secure Headers

```php
// In AppServiceProvider or middleware
response()->headers->set('X-Frame-Options', 'DENY');
response()->headers->set('X-Content-Type-Options', 'nosniff');
response()->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
response()->headers->set('Permissions-Policy', 'camera=self, microphone=none, geolocation=self');
response()->headers->set('Content-Security-Policy', 
    "default-src 'self'; " .
    "script-src 'self' 'unsafe-inline' 'unsafe-eval'; " .
    "style-src 'self' 'unsafe-inline'; " .
    "img-src 'self' data: blob: https:; " .
    "connect-src 'self' https://*.telebirr.et https://*.cbebirr.com"
);
```

### 10.4 Mobile App Security

- SSL certificate pinning (OkHttp CertificatePinner / Dio interceptor)
- Encrypted local storage (flutter_secure_storage for tokens)
- Root/jailbreak detection
- Obfuscation via ProGuard (Android) / R8
- No sensitive data in app logs
- App attestation (SafetyNet / App Check)

---

## 11. Implementation Roadmap

### Phase 1 — Foundation (Weeks 1–4)

| Sprint | Deliverables |
|--------|-------------|
| **Sprint 1** | • Database migrations for new tables<br>• `CustomerAccount` model + relationships<br>• `customer` auth guard + middleware<br>• Seeder: create customer accounts for test data |
| **Sprint 2** | • `/api/customer/auth/*` endpoints<br>• LogSmsGateway (dev SMS gateway)<br>• OTP generation, validation, rate limiting<br>• Customer login page (Blade/Livewire) |
| **Sprint 3** | • Customer registration flow<br>• Phone verification via OTP<br>• Link existing meter_serial |
| **Sprint 4** | • `/api/customer/me` + profile endpoints<br>• Customer dashboard page<br>• `/api/customer/bills/*` endpoints |

**Milestone:** Customer can register, log in, and view bills.

### Phase 2 — Core Features (Weeks 5–8)

| Sprint | Deliverables |
|--------|-------------|
| **Sprint 5** | • Bill detail view + PDF receipt generation<br>• Bill history with year filter<br>• Consumption trend from last 6 readings |
| **Sprint 6** | • Self-reading submission<br>• Photo upload + validation<br>• Reading history list |
| **Sprint 7** | • Correction/complaint submission<br>• Complaint list + status tracking<br>• Comment thread on complaints |
| **Sprint 8** | • Admin panel integration (review queue)<br>• Staff can approve/reject self-readings<br>• Staff can respond to complaints |

**Milestone:** Complete self-service cycle: view bill, submit reading, file complaint.

### Phase 3 — Payments (Weeks 9–12)

| Sprint | Deliverables |
|--------|-------------|
| **Sprint 9** | • Payment gateway abstraction layer<br>• `TeleBirrGateway` implementation<br>• `CbeBirrGateway` implementation<br>• Payment initiation flow |
| **Sprint 10** | • Webhook/callback handling<br>• Payment status polling<br>• Reconciliation logic<br>• Payment history |
| **Sprint 11** | • Manual payment (upload receipt)<br>• Payment receipt generation<br>• Staff payment reconciliation dashboard |
| **Sprint 12** | • End-to-end testing with sandbox gateways<br>• Error handling (timeout, declined, expired)<br>• Refund flow (if supported) |

**Milestone:** Customers can pay bills through mobile money.

### Phase 4 — Notifications & Engagement (Weeks 13–15)

| Sprint | Deliverables |
|--------|-------------|
| **Sprint 13** | • `customer_notifications` service<br>• FCM push notifications<br>• SMS notifications via production gateway<br>• In-app notification bell |
| **Sprint 14** | • Announcements CRUD (admin)<br>• Announcements display (customer)<br>• Outage reporting + management |
| **Sprint 15** | • Notification preferences (opt-in/out per channel)<br>• Email notifications (for customers with email)<br>• Daily/weekly digest option |

**Milestone:** Full notification ecosystem active.

### Phase 5 — Mobile App (Weeks 16–20)

| Sprint | Deliverables |
|--------|-------------|
| **Sprint 16** | • Flutter project setup<br>• API client (Dio) with auth interceptor<br>• Login/register screens<br>• Token management + secure storage |
| **Sprint 17** | • Dashboard screen<br>• Bill list + detail screens<br>• PDF receipt view |
| **Sprint 18** | • Self-reading submission (camera)<br>• Offline reading queue<br>• Complaint submission |
| **Sprint 19** | • Payment integration (WebView / app switch)<br>• Push notifications<br>• Biometric unlock |
| **Sprint 20** | • PWA configuration for web portal<br>• App store submission prep<br>• Beta testing with selected customers |

**Milestone:** Mobile app in beta.

### Phase 6 — Polish & Launch (Weeks 21–24)

| Sprint | Deliverables |
|--------|-------------|
| **Sprint 21** | • Penetration testing of customer API<br>• OWASP Top 10 audit<br>• Load testing (concurrent customer logins) |
| **Sprint 22** | • UAT with enterprise staff<br>• Customer pilot group (20–50 users)<br>• Feedback collection + bug fixes |
| **Sprint 23** | • Translations verified for all 4 languages<br>• Accessibility audit (WCAG 2.1 AA)<br>• Performance optimization (Lighthouse > 80) |
| **Sprint 24** | • Production deployment<br>• SMS gateway production setup<br>• Community awareness campaign<br>• Launch! |

---

## 12. Technology Recommendations

### 12.1 New Composer Dependencies

```json
{
    "require": {
        "laravel/sanctum": "^4.0",
        "barryvdh/laravel-dompdf": "^3.0",
        "spatie/laravel-pdf": "^1.0",
        "kreait/laravel-firebase": "^5.0",
        "spatie/laravel-data": "^4.0",
        "spatie/laravel-query-builder": "^6.0",
        "libphonenumber-for-php": "^0.3"
    },
    "require-dev": {
        "spatie/laravel-ray": "^1.0"
    }
}
```

### 12.2 New NPM Dependencies

```json
{
    "devDependencies": {
        "workbox-webpack-plugin": "^7.0",
        "workbox-precaching": "^7.0",
        "workbox-routing": "^7.0",
        "workbox-strategies": "^7.0",
        "idb": "^8.0"
    }
}
```

### 12.3 Infrastructure

| Component | Recommendation | Notes |
|-----------|---------------|-------|
| **Web server** | Nginx + PHP-FPM | Already in place |
| **Queue worker** | Laravel Horizon + Redis | For async notifications, payment callbacks, PDF generation |
| **Cache** | Redis | For OTP storage, rate limiting, session |
| **File storage** | Local (shared with admin) or S3-compatible | For meter photos, receipts |
| **SSL** | Let's Encrypt (Certbot) | HTTPS mandatory |
| **CDN** | Cloudflare (free tier) | DDoS protection, static asset caching |
| **Monitoring** | Laravel Telescope + Sentry | Error tracking, performance monitoring |
| **CI/CD** | GitHub Actions | Automated tests + deployment |

### 12.4 Ethiopian-Specific Infrastructure

| Service | Provider | Purpose |
|---------|----------|---------|
| **SMS OTP** | Ethio Telecom Enterprise SMS | Primary OTP delivery |
| **SMS OTP (fallback)** | Twilio / Africa's Talking | Backup provider |
| **Payment** | TeleBirr Merchant API | Mobile money payments |
| **Payment** | CBE Birr API | Bank-linked mobile payments |
| **Push** | Firebase Cloud Messaging | Free for unlimited devices |
| **Hosting** | Ethiopian datacenter or AWS Frankfurt | Low latency for local traffic |

---

## Appendix A — Migration Files (Key SQL)

### `create_customer_accounts_table`

```sql
CREATE TABLE customer_accounts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_code VARCHAR(20) NOT NULL,
    phone_number VARCHAR(20) NOT NULL UNIQUE,
    password VARCHAR(255) NULL,
    pin VARCHAR(255) NULL,
    otp_code VARCHAR(6) NULL,
    otp_expires_at TIMESTAMP NULL,
    otp_attempts TINYINT UNSIGNED DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    last_login_at TIMESTAMP NULL,
    last_login_device VARCHAR(255) NULL,
    fcm_token VARCHAR(500) NULL,
    preferred_language VARCHAR(3) DEFAULT 'orm',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX idx_customer_code (customer_code),
    INDEX idx_phone_active (phone_number, is_active),
    CONSTRAINT fk_ca_customer_code 
        FOREIGN KEY (customer_code) 
        REFERENCES active_customers(meter_serial) 
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### `create_customer_self_readings_table`

```sql
CREATE TABLE customer_self_readings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_code VARCHAR(20) NOT NULL,
    reading_value DECIMAL(10,2) NOT NULL,
    reading_photo_path VARCHAR(255) NULL,
    reading_date DATE NOT NULL,
    reading_year INT NOT NULL,
    reading_month VARCHAR(20) NOT NULL,
    status ENUM('pending','accepted','rejected') DEFAULT 'pending',
    reviewed_by VARCHAR(20) NULL,
    reviewed_at TIMESTAMP NULL,
    rejection_reason VARCHAR(500) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX idx_customer_code_status (customer_code, status),
    INDEX idx_year_month (reading_year, reading_month),
    CONSTRAINT fk_csr_customer_code 
        FOREIGN KEY (customer_code) 
        REFERENCES active_customers(meter_serial) 
        ON DELETE CASCADE,
    CONSTRAINT fk_csr_reviewed_by 
        FOREIGN KEY (reviewed_by) 
        REFERENCES user_account(user_id) 
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### `create_payments_table`

```sql
CREATE TABLE payments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    payment_reference VARCHAR(50) NOT NULL UNIQUE,
    bill_finance_id BIGINT UNSIGNED NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_method ENUM('telebirr','cbebirr','bank','cash','other') NOT NULL,
    gateway_transaction_id VARCHAR(100) NULL,
    gateway_status VARCHAR(50) NULL,
    status ENUM('pending','completed','failed','refunded') DEFAULT 'pending',
    paid_at TIMESTAMP NULL,
    receipt_path VARCHAR(255) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX idx_bill_finance (bill_finance_id),
    INDEX idx_status (status),
    CONSTRAINT fk_payment_bill 
        FOREIGN KEY (bill_finance_id) 
        REFERENCES bill_finances(id) 
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## Appendix B — Route File Structure

### `routes/customer-web.php` (Web Portal)

```php
<?php

use Illuminate\Support\Facades\Route;

// Guest routes
Route::middleware(['guest:web-customer'])->group(function () {
    Route::get('/login', fn() => view('customer.auth.login'))->name('customer.login');
    Route::get('/register', fn() => view('customer.auth.register'))->name('customer.register');
});

// Authenticated routes
Route::middleware(['auth:web-customer', 'customer.verified'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('customer.dashboard');
    Route::get('/bills', BillController::class)->name('customer.bills');
    Route::get('/bills/{id}', [BillController::class, 'show'])->name('customer.bills.show');
    Route::get('/readings', ReadingController::class)->name('customer.readings');
    Route::get('/corrections', CorrectionController::class)->name('customer.corrections');
    Route::get('/profile', ProfileController::class)->name('customer.profile');
    Route::get('/notifications', NotificationController::class)->name('customer.notifications');
    // ... etc
});
```

### `routes/customer-api.php` (Mobile API)

```php
<?php

use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Public
    Route::post('auth/send-otp', [AuthController::class, 'sendOtp']);
    Route::post('auth/verify-otp', [AuthController::class, 'verifyOtp']);
    Route::post('auth/register', [AuthController::class, 'register']);

    // Protected
    Route::middleware(['auth:sanctum', 'customer.verified'])->group(function () {
        Route::get('me', [ProfileController::class, 'me']);
        Route::get('bills', [BillController::class, 'index']);
        Route::get('bills/current', [BillController::class, 'current']);
        Route::post('readings', [ReadingController::class, 'submit']);
        Route::post('corrections', [CorrectionController::class, 'store']);
        Route::post('payments/initiate', [PaymentController::class, 'initiate']);
        // ... etc
    });

    // Webhooks (no auth, IP-whitelisted in middleware)
    Route::post('payments/webhook/{gateway}', [PaymentController::class, 'webhook'])
        ->middleware('payment.webhook');
});
```

---

## Appendix C — Eloquent Model Relationships

```php
// CustomerAccount model
class CustomerAccount extends Authenticatable
{
    protected $guard = 'customer';

    protected $fillable = [
        'customer_code', 'phone_number', 'password', 'pin',
        'preferred_language', 'fcm_token', 'is_active',
    ];

    protected $hidden = ['password', 'pin', 'otp_code', 'remember_token'];

    // Each customer account is linked to one active_customer record
    public function activeCustomer(): BelongsTo
    {
        return $this->belongsTo(ActiveCustomer::class, 'customer_code', 'meter_serial');
    }

    // Route notifications by FCM
    public function routeNotificationForFcm()
    {
        return $this->fcm_token;
    }

    // Route notifications by SMS
    public function routeNotificationForSms()
    {
        return $this->phone_number;
    }
}

// ActiveCustomer model — add relationships
class ActiveCustomer extends Model
{
    // A customer may have a portal account
    public function portalAccount(): HasOne
    {
        return $this->hasOne(CustomerAccount::class, 'customer_code', 'meter_serial');
    }

    // Self-readings submitted by this customer
    public function selfReadings(): HasMany
    {
        return $this->hasMany(CustomerSelfReading::class, 'customer_code', 'meter_serial');
    }

    // Bills (already exists as billFinances() in original code)
    public function bills(): HasMany
    {
        return $this->hasMany(BillFinance::class, 'meter_serial', 'meter_serial');
    }
}
```

---

## Appendix D — OTP Service

```php
class OtpService
{
    public function __construct(
        private SmsGatewayInterface $sms,
        private Cache $cache,
    ) {}

    public function generate(string $phone): string
    {
        $this->assertNotRateLimited($phone);

        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        Cache::put(
            "otp:{$phone}",
            ['code' => Hash::make($otp), 'attempts' => 0],
            now()->addMinutes(5)
        );

        $this->sms->send($phone, "Your Eteya Water verification code: {$otp}. Valid for 5 minutes.");

        Cache::increment("otp_sent:{$phone}");
        Cache::expire("otp_sent:{$phone}", now()->addMinutes(15));

        return $otp; // Only in dev; in prod, return void
    }

    public function verify(string $phone, string $code): bool
    {
        $stored = Cache::get("otp:{$phone}");

        if (! $stored) {
            throw new OtpExpiredException('OTP has expired. Request a new one.');
        }

        if ($stored['attempts'] >= 5) {
            Cache::forget("otp:{$phone}");
            throw new OtpLockedException('Too many attempts. Request a new OTP after 15 minutes.');
        }

        if (! Hash::check($code, $stored['code'])) {
            $stored['attempts']++;
            Cache::put("otp:{$phone}", $stored, now()->addMinutes(5));
            throw new InvalidOtpException('Invalid OTP.');
        }

        Cache::forget("otp:{$phone}");
        Cache::forget("otp_sent:{$phone}");

        return true;
    }

    private function assertNotRateLimited(string $phone): void
    {
        $sent = Cache::get("otp_sent:{$phone}", 0);
        if ($sent >= 3) {
            throw new OtpRateLimitException('Too many OTP requests. Please wait 15 minutes.');
        }
    }
}
```

---

## Appendix E — Key Flutter Screens (Pseudocode)

### Login Screen

```dart
class LoginScreen extends StatefulWidget {
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: SafeArea(
        child: Padding(
          padding: EdgeInsets.all(24),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              // Logo
              SvgPicture.asset('assets/logo.svg', height: 80),
              SizedBox(height: 32),

              // Title
              Text(
                AppLocalizations.of(context)!.welcome,
                style: Theme.of(context).textTheme.headlineMedium,
              ),
              SizedBox(height: 8),
              Text(AppLocalizations.of(context)!.enterPhoneNumber),

              SizedBox(height: 24),

              // Phone input
              PhoneNumberInput(
                initialCountryCode: '+251',
                onChanged: (phone) => _phone = phone,
              ),

              SizedBox(height: 16),

              // Send OTP button
              ElevatedButton(
                onPressed: _isLoading ? null : _sendOtp,
                child: Text(AppLocalizations.of(context)!.sendOtp),
              ),

              SizedBox(height: 16),

              // Alternative: login with password
              TextButton(
                onPressed: () => Navigator.pushNamed(context, '/login-password'),
                child: Text(AppLocalizations.of(context)!.loginWithPassword),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
```

---

## Appendix F — Success Metrics for the Customer Portal

| Metric | Target | Measurement |
|--------|--------|-------------|
| **Customer registration rate** | 30% of active customers within 6 months | Registered / Total active |
| **Self-reading adoption** | 50% of readings submitted via portal | Portal readings / Total readings |
| **Digital payment rate** | 40% of bills paid online within 12 months | Online payments / Total payments |
| **Office visit reduction** | 25% fewer in-person visits | Pre/post comparison |
| **Bill collection speed** | Average 5 days faster collection | Avg (paid_at - bill date) pre/post |
| **Customer satisfaction** | CSAT > 80% | In-app survey after key actions |
| **App rating** | 4.0+ on Google Play | Play Store rating |
| **Error rate** | < 0.5% of API requests | Error responses / Total responses |
| **Uptime** | 99.5% | Uptime monitoring |
| **Support tickets** | < 5 unresolved at any time | Ticket backlog |

---

## Document Revision History

| Version | Date | Author | Changes |
|---------|------|--------|---------|
| 1.0 | 2026-08-10 | Generated from codebase analysis | Initial draft |

---

*This document is a comprehensive blueprint. Each section can be expanded into detailed technical specifications, UI mockups, and test plans during implementation sprints.*
