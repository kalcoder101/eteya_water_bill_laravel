<!DOCTYPE html>
<html lang="{{ current_lang() }}">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ $pageTitle ?? config('app.name') }} — {{ config('app.name') }}</title>

<!-- Tailwind CSS 4 (browser CDN) + EOS Modern Steward theme tokens -->
<script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
<style type="text/tailwindcss">
@theme {
    --color-primary: #059669;
    --color-primary-600: #059669;
    --color-primary-700: #047857;
    --color-primary-800: #065F46;
    --color-primary-50: #ECFDF5;
    --color-primary-100: #D1FAE5;
    --color-on-primary-container: #065F46;
    --color-surface-base: #F8FAF8;
    --color-surface-card: #FFFFFF;
    --color-text-main: #0F172A;
    --color-text-muted: #64748B;
    --color-accent-warm: #E11D48;
    --color-border-subtle: #E2E8F0;

    --shadow-card: 0 4px 20px rgba(16, 185, 129, 0.05);
    --shadow-hover: 0 10px 25px rgba(16, 185, 129, 0.12);

    --font-sans: "Inter", "Noto Sans Ethiopic", ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, Arial, sans-serif;
    --font-mono: ui-monospace, SFMono-Regular, Menlo, Consolas, "Liberation Mono", monospace;
    --font-serif: "Outfit", "Inter", ui-sans-serif, system-ui, sans-serif;
}
</style>

<link rel="stylesheet" href="{{ $baseUrl }}/assets/css/app.css?v={{ time() }}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Outfit:wght@500;600;700;800&family=Noto+Sans+Ethiopic:wght@400;500;600;700&display=swap" rel="stylesheet">

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
</head>
<body class="antialiased">

<!-- Splash loader -->
<div id="eosSplashScreen" class="fixed inset-0 z-[300] bg-white flex flex-col items-center justify-center">
    <div class="w-16 h-16 rounded-2xl bg-emerald-50 border border-emerald-200 flex items-center justify-center shadow-card mb-5" id="splashLogo">
        <img src="{{ $baseUrl }}/assets/images/Owater-logo.png" alt="Logo" class="w-10 h-10 object-contain">
    </div>
    <div class="text-sm font-bold text-slate-900 tracking-tight mb-1">{{ config('app.name') }}</div>
    <div class="text-[11px] text-slate-500 mb-5">{{ t('Water Utility Billing System') }}</div>
    <div class="w-40 h-1 bg-slate-100 rounded-full overflow-hidden">
        <div id="splashProgressBar" class="h-full w-0 bg-emerald-600 rounded-full"></div>
    </div>
</div>

<!-- Toast container -->
<div id="toastContainer" class="toast-container"></div>

<div class="app-shell">

@php
    $user = auth()->user();
    $photoUrl = $user && $user->photo
        ? route('api.user.photo', ['userId' => $user->user_id])
        : ($baseUrl.'/assets/images/sample_logo.jpg');
    $fullName = $user ? $user->fullName() : '';
    $enterpriseEN = get_setting('enterprise_name_en', 'HHD Water Supply and Sewerage Service Enterprise');
    $devCredit = get_setting('developer_credit', 'GITAN ICT Work PLC');
    $brandShort = get_setting('default_branch', 'WaterSteward');
    $currentPage = request()->segment(1) ?? 'dashboard';

    // Category accent → Tailwind class map (module accents must stay consistent)
    $accents = [
        'emerald' => [
            'icon'   => 'text-emerald-600',
            'soft'   => 'bg-emerald-50',
            'hover'  => 'hover:bg-emerald-50 hover:text-emerald-900',
            'active' => 'bg-emerald-600 text-white shadow-sm',
        ],
        'indigo' => [
            'icon'   => 'text-indigo-600',
            'soft'   => 'bg-indigo-50',
            'hover'  => 'hover:bg-indigo-50 hover:text-indigo-900',
            'active' => 'bg-indigo-600 text-white shadow-sm',
        ],
        'gold' => [
            'icon'   => 'text-amber-600',
            'soft'   => 'bg-amber-50',
            'hover'  => 'hover:bg-amber-50 hover:text-amber-900',
            'active' => 'bg-amber-600 text-white shadow-sm',
        ],
    ];

    $allGroups = [
        [
            'title'   => t('Operations'),
            'icon'    => 'dashboard',
            'accent'  => 'emerald',
            'items' => [
                ['page' => 'dashboard',        'label' => t('Dashboard'),        'desc' => 'Overview stats, charts & recent activity logs',  'icon' => 'dashboard', 'route' => 'dashboard'],
                ['page' => 'customer-service', 'label' => t('Customer Service'), 'desc' => 'Register, update & search active customers',    'icon' => 'customers', 'route' => 'customer-service.index'],
                ['page' => 'bills',             'label' => t('Bills & Printing'), 'desc' => 'Calculate monthly bills & print receipts',     'icon' => 'receipt',   'route' => 'bills.index'],
            ]
        ],
        [
            'title'   => t('Reports & Ledger'),
            'icon'    => 'ledger',
            'accent'  => 'indigo',
            'items' => [
                ['page' => 'customer-ledger',      'label' => t('Customers Ledger'),   'desc' => 'Customer billing history & printable ledger',    'icon' => 'ledger',     'route' => 'customer-ledger.index'],
                ['page' => 'customer-statistics', 'label' => t('Detail Statistics'),  'desc' => 'Pivot reports: kebele × type × status',        'icon' => 'statistics', 'route' => 'customer-statistics.index'],
                ['page' => 'reading-correction',   'label' => t('Reading Correction'), 'desc' => 'Manage meter reading complaint approvals',    'icon' => 'wrench',     'route' => 'reading-correction.index'],
            ]
        ],
        [
            'title'   => t('Administration'),
            'icon'    => 'shield',
            'accent'  => 'gold',
            'items' => [
                ['page' => 'account-register', 'label' => t('Account Register'), 'desc' => 'Register staff accounts & manage job roles', 'icon' => 'lock', 'route' => 'account-register.index'],
            ]
        ]
    ];

    $navGroups = [];
    foreach ($allGroups as $group) {
        $allowedItems = array_filter($group['items'], fn($item) => is_allowed_page($item['page'], $user?->job_role ?? ''));
        if (!empty($allowedItems)) {
            $hasActive = false;
            foreach ($allowedItems as $it) {
                if ($it['page'] === $currentPage) {
                    $hasActive = true;
                    break;
                }
            }
            $navGroups[] = [
                'title'     => $group['title'],
                'icon'      => $group['icon'],
                'accent'    => $accents[$group['accent']] ?? $accents['emerald'],
                'hasActive' => $hasActive,
                'items'     => array_values($allowedItems)
            ];
        }
    }

    $languages = available_languages();
    $currentLang = current_lang();
    $pageAction = $pageAction ?? null;

    // Active nav item (for the topbar page-identity cluster)
    $activeNavItem = null;
    foreach ($navGroups as $group) {
        foreach ($group['items'] as $it) {
            if ($it['page'] === $currentPage) {
                $activeNavItem = $it;
                break 2;
            }
        }
    }
@endphp

<!-- ============================================================
     SIDEBAR — Floating light utility card (EOS Modern Steward)
     THESIS / OWN-WORLD / STORY: see DESIGN.md.
     ============================================================ -->
<aside id="mainSidebar" class="sidebar flex flex-col bg-white border border-slate-200 rounded-[18px] shadow-[0_8px_30px_rgba(15,23,42,0.06),0_2px_8px_rgba(15,23,42,0.04)] overflow-hidden">

    <!-- 1. Brand header -->
    <div class="sidebar-brand shrink-0 flex items-center gap-3 px-5 py-4 border-b border-slate-200 bg-white">
        <div class="w-10 h-10 rounded-xl bg-emerald-50 border border-emerald-200 p-1.5 flex items-center justify-center shrink-0">
            <img src="{{ $baseUrl }}/assets/images/Owater-logo.png" alt="Logo" class="w-full h-full object-contain">
        </div>
        <div class="brand-text min-w-0 flex-1">
            <div class="name font-extrabold text-[15px] tracking-tight text-slate-900 truncate">{{ $brandShort ?? t('WaterSteward') }}</div>
            <div class="tag text-[10px] text-slate-500 truncate">Water Supply & Sewerage Enterprise</div>
        </div>
        <button type="button" class="sidebar-collapse-toggle shrink-0 w-9 h-9 rounded-[10px] bg-slate-50 border border-slate-200 text-slate-500 hover:bg-emerald-50 hover:text-emerald-700 hover:border-emerald-300 flex items-center justify-center cursor-pointer transition" onclick="toggleSidebar(event)" title="Collapse / Expand Sidebar">
            {!! icon('panel-left', 16) !!}
        </button>
    </div>

    <!-- 2. Category navigation -->
    <div class="sidebar-categories-container flex-1 overflow-y-auto overflow-x-hidden p-3 space-y-2">
    @foreach ($navGroups as $idx => $group)
        <div class="sidebar-category-group relative {{ ($group['hasActive'] || ($currentPage === 'dashboard' && $idx === 0)) ? 'open' : '' }}">
            <div class="sidebar-category-header flex items-center gap-2.5 px-3 py-2 text-[10px] font-bold uppercase tracking-wider text-slate-400 hover:text-slate-700 hover:bg-slate-50 rounded-lg cursor-pointer select-none transition {{ $group['hasActive'] ? $group['accent']['soft'].' '.$group['accent']['icon'] : '' }}"
                 onclick="toggleCategoryGroup(this)"
                 onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();toggleCategoryGroup(this);}"
                 role="button" tabindex="0"
                 aria-expanded="{{ ($group['hasActive'] || ($currentPage === 'dashboard' && $idx === 0)) ? 'true' : 'false' }}">
                <span class="cat-icon shrink-0 {{ $group['accent']['icon'] }}">{!! icon($group['icon'], 16) !!}</span>
                <span class="cat-title flex-1 min-w-0 truncate">{{ $group['title'] }}</span>
                <span class="chevron shrink-0 opacity-60 transition-transform duration-300">{!! icon('chevron-down', 12) !!}</span>
            </div>

            <div class="sidebar-category-body pt-1">
                <ul class="sidebar-nav space-y-1">
                    @foreach ($group['items'] as $item)
                        <li>
                            <a href="{{ route($item['route']) }}"
                               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-[13px] font-semibold transition {{ $currentPage === $item['page'] ? $group['accent']['active'] : 'text-slate-600 '.$group['accent']['hover'] }}">
                                <span class="icon shrink-0 flex items-center justify-center {{ $currentPage === $item['page'] ? '' : $group['accent']['icon'] }}">{!! icon($item['icon'], 18) !!}</span>
                                <span class="nav-label truncate">{{ $item['label'] }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            <!-- Category flyout panel (collapsed hover) -->
            <div class="category-flyout-panel">
                <div class="bg-white border border-slate-200 rounded-2xl shadow-[0_16px_40px_rgba(15,23,42,0.16)] p-3">
                    <div class="flex items-center gap-2 pb-2.5 mb-2 border-b border-slate-200 text-[10px] font-bold uppercase tracking-wider text-slate-400">
                        <span class="{{ $group['accent']['icon'] }}">{!! icon($group['icon'], 14) !!}</span>
                        <span>{{ $group['title'] }}</span>
                    </div>
                    <div class="flex flex-col gap-1">
                        @foreach ($group['items'] as $item)
                            <a href="{{ route($item['route']) }}" class="flex items-start gap-2.5 px-2.5 py-2 rounded-lg transition {{ $currentPage === $item['page'] ? $group['accent']['soft'] : $group['accent']['hover'] }}">
                                <span class="mt-0.5 shrink-0 {{ $group['accent']['icon'] }} opacity-90">{!! icon($item['icon'], 16) !!}</span>
                                <span class="min-w-0">
                                    <span class="block text-[13px] font-bold text-slate-800 leading-snug">{{ $item['label'] }}</span>
                                    <span class="block text-[11px] text-slate-500 leading-snug mt-0.5">{{ $item['desc'] }}</span>
                                </span>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    @endforeach
    </div>

    <!-- 3. User footer -->
    <div class="sidebar-footer shrink-0 px-4 py-3.5 border-t border-slate-200 bg-white">
        <div class="sidebar-user flex items-center gap-2.5 min-w-0">
            <div class="user-avatar w-9 h-9 rounded-full overflow-hidden shrink-0 ring-2 ring-emerald-100">
                <img src="{{ $photoUrl }}" alt="{{ $fullName }}" class="w-full h-full object-cover block">
            </div>
            <div class="user-meta flex-1 min-w-0">
                <div class="user-name text-[13px] font-bold text-slate-900 truncate">{{ $fullName }}</div>
                <div class="user-role mt-0.5 truncate"><span class="badge {{ get_role_badge($user?->job_role ?? '') }}">{{ get_role_display($user?->job_role ?? '') }}</span></div>
            </div>
            <a href="{{ route('logout') }}" class="logout-link shrink-0 w-9 h-9 rounded-lg inline-flex items-center justify-center text-slate-500 border border-slate-200 bg-slate-50 hover:bg-rose-50 hover:text-rose-600 hover:border-rose-200 transition" title="{{ t('Logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                {!! icon('logout', 16) !!}
            </a>
        </div>
        <div class="footer-meta mt-3 pt-2.5 border-t border-dashed border-slate-200 text-[10px] text-slate-500 flex flex-col gap-0.5">
            <span class="font-semibold text-slate-600">{{ $appVersion }}</span>
            <span class="opacity-90">{{ $devCredit }}</span>
        </div>
        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
            @csrf
        </form>
    </div>
</aside>

<!-- ============================================================
     MAIN AREA
     ============================================================ -->
<div class="main-area flex flex-col min-w-0">

    <!-- Topbar — floating card (EOS Modern Steward) -->
    <header class="topbar sticky top-3 z-[40] flex items-center gap-3 sm:gap-4 px-4 sm:px-5 py-3.5 mx-3 rounded-2xl bg-white/80 backdrop-blur-xl border border-slate-200/90 shadow-[0_8px_30px_rgba(15,23,42,0.06),0_2px_8px_rgba(15,23,42,0.04)]">
        <!-- Page identity cluster -->
        <div class="flex items-center gap-3 min-w-0">
            <div class="w-10 h-10 shrink-0 rounded-xl bg-emerald-50 border border-emerald-200/80 text-emerald-700 flex items-center justify-center">
                {!! icon($activeNavItem['icon'] ?? 'dashboard', 20) !!}
            </div>
            <div class="min-w-0">
                <div class="page-title text-[17px] leading-tight font-extrabold tracking-tight text-slate-900 truncate">{{ t($pageTitle ?? 'Dashboard') }}</div>
                <div class="page-subtitle mt-0.5 text-xs text-slate-500 truncate">{{ $enterpriseEN }}</div>
            </div>
        </div>

        <div class="spacer flex-1 min-w-0"></div>

        <!-- Quick search -->
        <div class="relative w-44 lg:w-64 shrink-0">
            <input type="text" class="w-full pl-8 pr-10 py-2.5 rounded-lg bg-slate-50 border border-slate-200 text-xs text-slate-700 cursor-pointer focus:outline-none focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500" placeholder="Search (Ctrl + K)" onclick="openModal('quickCmdModal')" readonly>
            <span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-400">{!! icon('search', 13) !!}</span>
            <kbd class="absolute right-2 top-1/2 -translate-y-1/2 hidden lg:inline-flex items-center px-1.5 py-0.5 rounded-md bg-white border border-slate-200 text-[10px] font-semibold text-slate-400">Ctrl K</kbd>
        </div>

        @if (! empty($pageAction))
        <a href="{{ $pageAction['href'] ?? '#' }}"
           class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs transition shadow-[0_4px_14px_rgba(5,150,105,0.35)] active:scale-[0.98] shrink-0"
           onclick="{{ $pageAction['onclick'] ?? '' }}">
            {!! icon($pageAction['icon'] ?? 'plus', 15) !!}
            <span class="hidden sm:inline">{{ $pageAction['label'] ?? 'Action' }}</span>
        </a>
        @endif

        <!-- Language switcher (segmented) -->
        <div class="hidden md:inline-flex items-center bg-slate-100 border border-slate-200 rounded-lg p-1 gap-0.5 shrink-0">
            @foreach ($languages as $code => $info)
                @php $isActive = $code === $currentLang; @endphp
                <a href="{{ request()->fullUrlWithQuery(['lang' => $code]) }}"
                   class="px-2.5 py-1 rounded-md text-[11px] font-bold uppercase tracking-wider transition {{ $isActive ? 'bg-white text-emerald-700 shadow-sm' : 'text-slate-500 hover:text-slate-700' }}"
                   title="{{ $info[0] }}">{{ $info[2] }}</a>
            @endforeach
        </div>

        <!-- User chip -->
        <div class="flex items-center gap-2.5 pl-2.5 pr-3 py-1.5 rounded-full bg-slate-50/80 border border-slate-200/80 shrink-0">
            <div class="relative flex items-center">
                <img src="{{ $photoUrl }}" alt="User photo" class="w-8 h-8 rounded-full object-cover border-2 border-white shadow-sm">
                <span class="absolute right-0 bottom-0 w-2.5 h-2.5 rounded-full bg-emerald-500 border-2 border-white"></span>
            </div>
            <div class="hidden sm:block">
                <div class="text-[13px] font-bold text-slate-900 leading-tight max-w-[140px] truncate">{{ $fullName }}</div>
                <div class="mt-0.5"><span class="badge {{ get_role_badge($user?->job_role ?? '') }}">{{ get_role_display($user?->job_role ?? '') }}</span></div>
            </div>
        </div>
    </header>

    <main class="content p-6 flex-1 max-w-[1600px] w-full mx-auto">
        <script>
        window.API_BASE = '{{ $baseUrl }}/api';
        window.apiUrl = function(path) { return window.API_BASE + '/' + path; };

        function changeLanguage(code) {
            var url = new URL(window.location.href);
            url.searchParams.set('lang', code);
            window.location.href = url.toString();
        }
        </script>

        @yield('content')
    </main>
</div>
</div>

<script>
// Splash loader (guarded, always-has-fallback)
(function() {
    var splash = document.getElementById('eosSplashScreen');
    var bar = document.getElementById('splashProgressBar');
    if (!splash) return;
    var done = false;
    function finish() {
        if (done) return;
        done = true;
        splash.style.opacity = '0';
        splash.style.transition = 'opacity 0.45s ease';
        setTimeout(function() { if (splash.parentNode) splash.parentNode.removeChild(splash); }, 500);
    }
    if (typeof gsap !== 'undefined') {
        gsap.fromTo('#splashLogo', { scale: 0.85 }, { scale: 1.05, duration: 0.6, yoyo: true, repeat: 1, ease: 'power1.inOut', clearProps: 'all' });
        gsap.to(bar, { width: '100%', duration: 0.7, ease: 'power2.out', onComplete: finish });
    } else {
        bar.style.width = '100%';
        setTimeout(finish, 700);
    }
    setTimeout(finish, 900);
})();
</script>
<script src="{{ $baseUrl }}/assets/js/app.js?v={{ time() }}"></script>
</body>
</html>
