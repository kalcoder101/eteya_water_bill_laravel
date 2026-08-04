<?php

use App\Models\Setting;
use App\Services\I18nService;

if (! function_exists('e_html')) {
    /**
     * HTML-escape a value (Laravel's e() is fine, but the original PHP code used
     * a slightly stricter ENT_QUOTES | ENT_HTML5 flag set — keep it consistent).
     */
    function e_html($value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}

if (! function_exists('fmt_date')) {
    function fmt_date(?string $date): string
    {
        if (! $date) {
            return '';
        }

        $timestamp = strtotime($date);

        return $timestamp ? date('d/m/Y H:i:s', $timestamp) : $date;
    }
}

if (! function_exists('faan_oromo_months')) {
    /**
     * Afaan Oromo month names (Ethiopian calendar mapping used by original app).
     */
    function faan_oromo_months(): array
    {
        return [
            'Fulbaana',
            'Onkolooleessa',
            'Sadaasa',
            'Muddee',
            'Ammajii',
            'Gurraandhala',
            'Bitooteessa',
            'Ebla',
            'Caamsaa',
            'Waxabajjii',
            'Adoolessa',
            'Hagayya',
        ];
    }
}

if (! function_exists('meter_sizes')) {
    function meter_sizes(): array
    {
        return ['1/2"', '3/4"', '1"', '1 and 1/2"', '2"'];
    }
}

if (! function_exists('customer_types')) {
    function customer_types(): array
    {
        return [
            'Dhunfaa',
            'Daldaltoota fi Industry',
            'Waajjira Motummaa',
            'Waajjira Miti-Motummaa',
            'Boonoo',
        ];
    }
}

if (! function_exists('customer_statuses')) {
    function customer_statuses(): array
    {
        return ['Active', 'DC', 'Updated', 'Deleted'];
    }
}

if (! function_exists('payment_ways')) {
    function payment_ways(): array
    {
        return ['BANK', 'NON_BANK'];
    }
}

if (! function_exists('get_setting')) {
    function get_setting(string $key, $default = null)
    {
        $row = Setting::find($key);

        return $row ? $row->setting_value : $default;
    }
}

if (! function_exists('page_role_permissions')) {
    /**
     * Mirror of the original page→role whitelist used to gate web pages.
     */
    function page_role_permissions(): array
    {
        return [
            'dashboard'              => ['System Admin', 'Manager', 'Customer Service', 'Bill Reader'],
            'customer-service'       => ['System Admin', 'Manager', 'Customer Service'],
            'customer-ledger'         => ['System Admin', 'Manager', 'Customer Service'],
            'customer-statistics'     => ['System Admin', 'Manager', 'Customer Service'],
            'reading-correction'     => ['System Admin', 'Manager', 'Customer Service'],
            'bills'                   => ['System Admin', 'Manager', 'Customer Service'],
            'bills.print'             => ['System Admin', 'Manager', 'Customer Service'],
            'bills.calculate'         => ['System Admin', 'Manager', 'Customer Service'],
            'bills.mark-paid'         => ['System Admin', 'Manager', 'Customer Service'],
            'export-customers'        => ['System Admin', 'Manager', 'Customer Service'],
            'export-bills'            => ['System Admin', 'Manager', 'Customer Service'],
            'export-ledger'           => ['System Admin', 'Manager', 'Customer Service'],
            'import-customers'        => ['System Admin', 'Manager', 'Customer Service'],
            'account-register'        => ['System Admin'],
            'account-register.save'   => ['System Admin'],
            'account-register.delete' => ['System Admin'],
        ];
    }
}

if (! function_exists('is_allowed_page')) {
    function is_allowed_page(string $page, string $role): bool
    {
        $permissions = page_role_permissions();
        if (! isset($permissions[$page])) {
            return true;
        }

        return in_array($role, $permissions[$page], true);
    }
}

if (! function_exists('job_roles_list')) {
    function job_roles_list(): array
    {
        $cacheKey = 'job_roles_list_v1';
        if (cache()->has($cacheKey)) {
            return cache($cacheKey);
        }

        try {
            $roles = \App\Models\JobRole::where('is_active', true)
                ->orderBy('role_name')
                ->get();
        } catch (\Throwable $e) {
            $roles = collect();
        }

        if ($roles->isEmpty()) {
            // Fallback if the table is empty / not seeded yet
            return [
                'System Admin'       => ['display' => 'System Administrator', 'badge' => 'badge-danger'],
                'Manager'            => ['display' => 'Operations Manager',   'badge' => 'badge-warning'],
                'Customer Service'   => ['display' => 'Customer Service Officer', 'badge' => 'badge-info'],
                'Secretary'          => ['display' => 'Secretary',            'badge' => 'badge-success'],
                'Bill Reader'        => ['display' => 'Meter Reader',          'badge' => 'badge-primary'],
                'Finance Officer'    => ['display' => 'Finance & Accounts',    'badge' => 'badge-secondary'],
                'Operations Officer' => ['display' => 'Field Operations',      'badge' => 'badge-success'],
            ];
        }

        $out = [];
        foreach ($roles as $r) {
            $out[$r->role_name] = [
                'display' => $r->display_name,
                'badge'   => $r->color_badge,
            ];
        }
        cache()->put($cacheKey, $out, now()->addHour());

        return $out;
    }
}

if (! function_exists('get_role_display')) {
    function get_role_display(string $role): string
    {
        return job_roles_list()[$role]['display'] ?? $role;
    }
}

if (! function_exists('get_role_badge')) {
    function get_role_badge(string $role): string
    {
        return job_roles_list()[$role]['badge'] ?? 'badge-default';
    }
}

if (! function_exists('icon')) {
    /**
     * SVG icon helper — returns inline SVG icons (no emoji).
     * Mirrors the original `includes/icons.php` exactly.
     */
    function icon(string $name, int $size = 16, string $class = ''): string
    {
        $paths = [
            'dashboard'     => '<rect x="3" y="3" width="7" height="9" rx="1"/><rect x="14" y="3" width="7" height="5" rx="1"/><rect x="14" y="12" width="7" height="9" rx="1"/><rect x="3" y="16" width="7" height="5" rx="1"/>',
            'customers'     => '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
            'ledger'        => '<path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/><line x1="8" y1="7" x2="16" y2="7"/><line x1="8" y1="11" x2="16" y2="11"/>',
            'statistics'    => '<line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/>',
            'wrench'        => '<path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>',
            'receipt'       => '<path d="M4 2v20l2-1 2 1 2-1 2 1 2-1 2 1 2-1 2 1V2l-2 1-2-1-2 1-2-1-2 1-2-1-2 1z"/><line x1="8" y1="7" x2="16" y2="7"/><line x1="8" y1="11" x2="16" y2="11"/><line x1="8" y1="15" x2="13" y2="15"/>',
            'lock'          => '<rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>',
            'logout'        => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/>',
            'login'         => '<path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/>',
            'search'        => '<circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>',
            'plus'          => '<line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>',
            'edit'          => '<path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>',
            'trash'         => '<polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>',
            'sync'          => '<polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/>',
            'download'      => '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/>',
            'upload'        => '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/>',
            'print'         => '<polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/>',
            'check'         => '<polyline points="20 6 9 17 4 12"/>',
            'x'             => '<line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>',
            'arrow-left'    => '<line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/>',
            'arrow-right'   => '<line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/>',
            'panel-left'    => '<rect x="3" y="3" width="18" height="18" rx="3"/><path d="M9 3v18"/><path d="m14 15-3-3 3-3"/>',
            'panel-right'   => '<rect x="3" y="3" width="18" height="18" rx="3"/><path d="M9 3v18"/><path d="m11 9 3 3-3 3"/>',
            'alert'         => '<path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>',
            'info'          => '<circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/>',
            'phone'         => '<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>',
            'map-pin'       => '<path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>',
            'tag'           => '<path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/>',
            'credit-card'   => '<rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/>',
            'building'      => '<rect x="4" y="2" width="16" height="20" rx="2"/><line x1="9" y1="6" x2="9" y2="6.01"/><line x1="15" y1="6" x2="15" y2="6.01"/><line x1="9" y1="10" x2="9" y2="10.01"/><line x1="15" y1="10" x2="15" y2="10.01"/><line x1="9" y1="14" x2="9" y2="14.01"/><line x1="15" y1="14" x2="15" y2="14.01"/><line x1="9" y1="18" x2="9" y2="18.01"/><line x1="15" y1="18" x2="15" y2="18.01"/>',
            'user'          => '<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',
            'clock'         => '<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>',
            'water'         => '<path d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z"/>',
            'globe'         => '<circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>',
            'shield'        => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>',
            'zap'           => '<polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>',
            'file-text'     => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/>',
            'filter'        => '<polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/>',
            'menu'          => '<line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/>',
            'chevron-down'  => '<polyline points="6 9 12 15 18 9"/>',
            'refresh'       => '<polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/>',
            'send'          => '<line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/>',
            'transfer'      => '<polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/><polyline points="7 23 3 19 7 15"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/>',
            'database'      => '<ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/>',
            'settings'      => '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/>',
            'eye'           => '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>',
            'eye-off'       => '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>',
            'users'         => '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
            'book-open'     => '<path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>',
            'bar-chart'     => '<line x1="12" y1="20" x2="12" y2="10"/><line x1="18" y1="20" x2="18" y2="4"/><line x1="6" y1="20" x2="6" y2="16"/>',
            'line-chart'    => '<polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>',
            'pie-chart'     => '<path d="M21.21 15.89A10 10 0 1 1 8 2.83"/><path d="M22 12A10 10 0 0 0 12 2v10z"/>',
        ];

        $path = $paths[$name] ?? $paths['info'];

        return '<svg xmlns="http://www.w3.org/2000/svg" width="'.$size.'" height="'.$size.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="'.$class.'">'.$path.'</svg>';
    }
}

if (! function_exists('t')) {
    /**
     * Translate a UI string using the active session language.
     * Delegates to App\Services\I18nService.
     */
    function t(string $key, array $params = []): string
    {
        return app(I18nService::class)->translate($key, $params);
    }
}

if (! function_exists('available_languages')) {
    function available_languages(): array
    {
        return [
            'en'  => ['English',      'English',      'EN'],
            'orm' => ['Afaan Oromoo', 'Afaan Oromoo', 'OR'],
            'am'  => ['Amharic',     'አማርኛ',          'AM'],
            'ti'  => ['Tigrigna',     'ትግርኛ',          'TI'],
        ];
    }
}

if (! function_exists('current_lang')) {
    function current_lang(): string
    {
        return app(I18nService::class)->currentLang();
    }
}

if (! function_exists('generate_customer_code')) {
    /**
     * Generate the next customer code for the given kebele, mirroring the
     * original `includes/functions.php::generate_customer_code()`.
     */
    function generate_customer_code(string $kebele): string
    {
        $prefix = 'ETY';
        if ($kebele !== '') {
            $prefix = 'ETY'.str_pad($kebele, 2, '0', STR_PAD_LEFT);
        }

        $last = \App\Models\ActiveCustomer::where('meter_serial', 'like', $prefix.'-%')
            ->orderBy('meter_serial', 'desc')
            ->first();

        $next = 1;
        if ($last) {
            $parts = explode('-', $last->meter_serial);
            $next  = (int) end($parts) + 1;
        }

        return $prefix.'-'.str_pad($next, 4, '0', STR_PAD_LEFT);
    }
}

if (! function_exists('generate_bill_finance_id')) {
    function generate_bill_finance_id(string $meterSerial, string $year, string $month): string
    {
        $slug  = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $meterSerial), 0, 8));
        $mSlug = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $month), 0, 3));

        return sprintf('BF-%s-%s-%s', $slug, $year, $mSlug);
    }
}

if (! function_exists('generate_meter_reading_id')) {
    function generate_meter_reading_id(): string
    {
        $last = \App\Models\SeasonalConsumption::orderBy('id', 'desc')->first();
        if ($last && preg_match('/RD-(\d+)/', $last->meter_reading_id, $m)) {
            return 'RD-'.str_pad((int) $m[1] + 1, 3, '0', STR_PAD_LEFT);
        }

        return 'RD-001';
    }
}
