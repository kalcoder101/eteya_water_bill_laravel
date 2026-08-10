# Product

<!-- impeccable:product-schema 1 -->

## Platform

web

## Users

Office clerks at the Eteya Town Water Supply & Sewerage Service Enterprise (Oromia, Ethiopia). They work on desktop browsers on office PCs over the enterprise LAN, registering and managing water-meter customers, updating records, importing/exporting data, and syncing with the backend.

## Product Purpose

The Customer Service module manages the full lifecycle of water-meter customer records: register a customer (3-step wizard), search/filter the registry in real time, apply 22 field-level update operations, change status (Active / DC / Updated / Deleted), import customers from Excel/CSV, export to CSV, sync records to the backend, and record GPS meter locations. It is one module of the WaterSteward Enterprise billing system.

## Positioning

A faithful Laravel 11 rewrite of the original GITAN ICT procedural PHP system and its Spring Boot backend: 56 `/api/*` endpoints and 13 database tables reproduced field-for-field so the legacy JavaFX desktop client still works against the same contract. The web UI is the modern front for that exact business logic.

## Operating Context

- Desktop-first usage on office PCs (LAN), single-user session auth, bilingual (Afaan Oromo + English) with a language switcher.
- Role-based access across 7 job roles; sidebar nav and page actions are gated by `is_allowed_page()`.
- The Customer Service page coexists with the legacy JavaFX client hitting the same 56 API endpoints; behavior parity matters.
- Multi-step registration wizard, modal-based edit/import/prompt flows, reactive Livewire search table, Chart.js analytics, GSAP entrance animations.

## Capabilities and Constraints

- Confirmed page capabilities: KPI stat cards, Chart.js status/branch charts, reactive Livewire customer search & registry (filters: status, kebele, customer type; sort; pagination 15), 3-step registration modal, Excel/CSV import, CSV export, edit modal with 22 update operations, status transitions, sync, GPS location, quick-action FAB.
- Constraint: redesign is **visual + structural only** — all existing JS, API calls, modal flows, multi-step registration, and the Livewire search component must keep working unchanged. The stray `</div>` nesting in the current blade (content section wrapping modals) must be cleaned up so modals live outside the main content wrapper.
- Constraint: bilingual copy via `t()`; keep all translation keys.
- Undecided: any behavior/UX rework beyond visual + structural cleanup is out of scope unless the user later asks.

## Brand Commitments

- Product name: WaterSteward Enterprise (brand short "WaterSteward" from settings).
- Logo asset: `public/assets/images/Owater-logo.png`.
- Bilingual identity (Afaan Oromo + English) is binding.
- Per user decision, the incumbent "Modern Steward" emerald look is **not** binding for this page — a fresh look is allowed; the logo and bilingual content stay.

## Evidence on Hand

- `README.md` — full module/API/model inventory.
- `DESIGN.md` — incumbent "Modern Steward" token set (emerald, Inter/Outfit/Noto Sans Ethiopic).
- `app/Http/Controllers/CustomerServiceController.php` — page data + `pageAction`.
- `resources/views/customer-service/index.blade.php` — current page (824 lines) with all modal/JS flows.
- `app/Livewire/CustomerSearch.php` + `resources/views/livewire/customer-search.blade.php` — reactive registry.
- `public/assets/css/app.css` + `public/assets/js/app.js` — incumbent styling/animation helpers.
- No real customer screenshots or usage footage available; do not fabricate.

## Product Principles

1. Behavior parity: never break an API call, update operation, import/export flow, or the legacy contract while restyling.
2. Speed for the clerk: the registry, filters, and status/update actions are the daily grind — keep them one glance and one click away.
3. Density with clarity: rich records (code, name, meter specs, type, branch, status, actions) must stay scannable in a desktop table.
4. Structure honesty: valid, clean DOM nesting and correct section boundaries even when the page is large and modal-heavy.
5. Bilingual integrity: every label stays translatable; no hard-coded English-only strings added.

## Accessibility & Inclusion

- Desktop-only confirmed audience; keyboard-focusable controls and adequate contrast maintained for office screens. No stricter standard was established.
