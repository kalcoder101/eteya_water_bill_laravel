<!DOCTYPE html>
<html lang="{{ current_lang() }}" class="light">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ $pageTitle ?? config('app.name') }} — {{ config('app.name') }}</title>

<!-- Tailwind CSS 4 + Flux UI (compiled via Vite) -->
@vite(['resources/css/app.css', 'resources/js/app.js'])

@fluxAppearance

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Outfit:wght@500;600;700;800&family=Noto+Sans+Ethiopic:wght@400;500;600;700&display=swap" rel="stylesheet">

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
</head>
<body class="min-h-screen bg-white antialiased">

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
            'icon'    => 'home',
            'accent'  => 'emerald',
            'items' => [
                ['page' => 'dashboard',        'label' => t('Dashboard'),        'desc' => 'Overview stats, charts & recent activity logs',  'icon' => 'home',     'route' => 'dashboard'],
                ['page' => 'customer-service', 'label' => t('Customer Service'), 'desc' => 'Register, update & search active customers',    'icon' => 'users',    'route' => 'customer-service.index'],
                ['page' => 'bills',             'label' => t('Bills & Printing'), 'desc' => 'Calculate monthly bills & print receipts',     'icon' => 'receipt-percent',   'route' => 'bills.index'],
            ]
        ],
        [
            'title'   => t('Reports & Ledger'),
            'icon'    => 'book-open',
            'accent'  => 'indigo',
            'items' => [
                ['page' => 'customer-ledger',      'label' => t('Customers Ledger'),   'desc' => 'Customer billing history & printable ledger',    'icon' => 'book-open',   'route' => 'customer-ledger.index'],
                ['page' => 'customer-statistics', 'label' => t('Detail Statistics'),  'desc' => 'Pivot reports: kebele × type × status',        'icon' => 'chart-bar',   'route' => 'customer-statistics.index'],
                ['page' => 'reading-correction',   'label' => t('Reading Correction'), 'desc' => 'Manage meter reading complaint approvals',    'icon' => 'wrench-screwdriver',     'route' => 'reading-correction.index'],
            ]
        ],
        [
            'title'   => t('Administration'),
            'icon'    => 'shield-check',
            'accent'  => 'gold',
            'items' => [
                ['page' => 'account-register', 'label' => t('Account Register'), 'desc' => 'Register staff accounts & manage job roles', 'icon' => 'user-plus', 'route' => 'account-register.index'],
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

    // Active nav item (for topbar page identity)
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
     FLUX NATIVE SIDEBAR — EOS Modern Steward Redesign
     ============================================================ -->
<flux:sidebar id="mainSidebar" class="sidebar flex flex-col bg-white border border-slate-200 rounded-[18px] shadow-[0_8px_30px_rgba(15,23,42,0.06),0_2px_8px_rgba(15,23,42,0.04)]">

    <!-- 1. Brand header -->
    <div class="sidebar-brand shrink-0 flex items-center gap-3 px-4 py-4 border-b border-slate-200 bg-white">
        <flux:brand href="{{ route('dashboard') }}" logo="{{ $baseUrl }}/assets/images/Owater-logo.png" name="{{ $brandShort ?? t('WaterSteward') }}" class="flex-1">
            <span class="tag text-[10px] text-slate-500 truncate block">Water Supply & Sewerage Enterprise</span>
        </flux:brand>
        <flux:button size="sm" variant="subtle" icon="panel-left" class="sidebar-collapse-toggle" onclick="toggleSidebar(event)" title="{{ t('Collapse / Expand Sidebar') }}" />
    </div>

    <!-- 2. Category Navigation via Flux Navlist -->
    <div class="sidebar-categories-container flex-1 p-3 space-y-3">
        <flux:navlist>
            @foreach ($navGroups as $idx => $group)
                <flux:navlist.group heading="{{ $group['title'] }}" expandable class="sidebar-category-group {{ ($group['hasActive'] || ($currentPage === 'dashboard' && $idx === 0)) ? 'open' : '' }}">
                    @foreach ($group['items'] as $item)
                        <flux:navlist.item
                            :href="route($item['route'])"
                            wire:navigate
                            :icon="$item['icon']"
                            :current="$currentPage === $item['page']"
                        >
                            {{ $item['label'] }}
                        </flux:navlist.item>
                    @endforeach
                </flux:navlist.group>

                <!-- Category flyout panel (collapsed hover) -->
                <div class="category-flyout-panel">
                    <div class="bg-white border border-slate-200 rounded-2xl shadow-[0_16px_40px_rgba(15,23,42,0.16)] p-3">
                        <div class="flex items-center gap-2 pb-2.5 mb-2 border-b border-slate-200 text-[10px] font-bold uppercase tracking-wider text-slate-400">
                            <span class="{{ $group['accent']['icon'] }}">{!! icon($group['icon'], 14) !!}</span>
                            <span>{{ $group['title'] }}</span>
                        </div>
                        <div class="flex flex-col gap-1">
                            @foreach ($group['items'] as $item)
                                <a href="{{ route($item['route']) }}" wire:navigate class="flex items-start gap-2.5 px-2.5 py-2 rounded-lg transition {{ $currentPage === $item['page'] ? $group['accent']['soft'] : $group['accent']['hover'] }}">
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
            @endforeach
        </flux:navlist>
    </div>

    <!-- 3. User Profile Footer -->
    <div class="sidebar-footer shrink-0 px-4 py-3.5 border-t border-slate-200 bg-white">
        <div class="sidebar-user flex items-center gap-2.5 min-w-0">
            <flux:profile :avatar="$photoUrl" :name="$fullName" :extra="get_role_display($user?->job_role ?? '')" class="flex-1" />
            <flux:button variant="subtle" size="sm" icon="arrow-right-on-rectangle" class="logout-link" title="{{ t('Logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" />
        </div>
        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
            @csrf
        </form>
    </div>
</flux:sidebar>

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
            <x-input
                type="text"
                icon="search"
                placeholder="Search (Ctrl + K)"
                onclick="openModal('quickCmdModal')"
                readonly
                class="py-2.5 cursor-pointer pr-12"
            />
            <kbd class="absolute right-2 top-1/2 -translate-y-1/2 hidden lg:inline-flex items-center px-1.5 py-0.5 rounded-md bg-white border border-slate-200 text-[10px] font-semibold text-slate-400">Ctrl K</kbd>
        </div>


        <!-- User → Flux dropdown -->
        <flux:dropdown position="bottom" align="end" class="shrink-0">
            <flux:profile :avatar="$photoUrl" :name="$fullName" />

            <flux:menu>
                <div class="px-3 py-2 text-xs">
                    <div class="font-bold text-slate-900 truncate">{{ $fullName }}</div>
                    <div class="text-slate-500 truncate">{{ get_role_display($user?->job_role ?? '') }}</div>
                </div>

                <flux:menu.separator />

                <flux:menu.group heading="Language">
                    @foreach ($languages as $code => $info)
                        @php $isActive = $code === $currentLang; @endphp
                        <flux:menu.item
                            :href="request()->fullUrlWithQuery(['lang' => $code])"
                            :active="$isActive"
                        >{{ $info[0] }} ({{ $info[2] }})</flux:menu.item>
                    @endforeach
                </flux:menu.group>

                <flux:menu.separator />

                <form method="POST" action="{{ route('logout') }}" id="flux-logout-form" style="display:none">@csrf</form>
                <flux:menu.item icon="arrow-right-on-rectangle" variant="danger" x-data x-on:click="document.getElementById('flux-logout-form').submit()">
                    {{ t('Logout') }}
                </flux:menu.item>
            </flux:menu>
        </flux:dropdown>
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

        {{ $slot ?? '' }}
        @yield('content')
    </main>
</div>
</div>

<script>
// Splash loader (smooth initial presentation & SPA navigation handler)
(function() {
    var splash = document.getElementById('eosSplashScreen');
    var bar = document.getElementById('splashProgressBar');
    if (!splash) return;

    var done = false;
    function finish() {
        if (done) return;
        done = true;
        splash.style.opacity = '0';
        splash.style.pointerEvents = 'none';
        splash.style.transition = 'opacity 0.4s cubic-bezier(0.4, 0, 0.2, 1)';
        setTimeout(function() {
            if (splash && splash.parentNode) {
                splash.parentNode.removeChild(splash);
            }
        }, 450);
    }

    // Dismiss instantly on Livewire SPA navigation (wire:navigate)
    document.addEventListener('livewire:navigated', finish);

    if (typeof gsap !== 'undefined') {
        gsap.fromTo('#splashLogo',
            { scale: 0.88, opacity: 0.8 },
            { scale: 1.05, opacity: 1, duration: 0.5, yoyo: true, repeat: 1, ease: 'power1.inOut' }
        );
        if (bar) {
            gsap.to(bar, { width: '100%', duration: 0.75, ease: 'power2.out', onComplete: finish });
        } else {
            setTimeout(finish, 800);
        }
    } else {
        if (bar) bar.style.width = '100%';
        setTimeout(finish, 800);
    }

    setTimeout(finish, 1100);
})();
</script>

<flux:toast />

@fluxScripts
</body>
</html>
