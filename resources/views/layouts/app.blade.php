<!DOCTYPE html>
<html lang="{{ current_lang() }}">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ $pageTitle ?? config('app.name') }} — {{ config('app.name') }}</title>
<link rel="stylesheet" href="{{ $baseUrl }}/assets/css/app.css?v={{ time() }}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="{{ $baseUrl }}/assets/js/app.js?v={{ time() }}"></script>
<script>
window.toggleSidebar = function(e) {
    if (e && e.preventDefault) e.preventDefault();
    var shell = document.querySelector('.app-shell');
    if (!shell) return;
    var collapsed = shell.classList.toggle('sidebar-collapsed');
    localStorage.setItem('eteya_sidebar_collapsed', collapsed ? 'true' : 'false');
};

window.toggleCategoryGroup = function(headerEl) {
    var shell = document.querySelector('.app-shell');
    if (shell && shell.classList.contains('sidebar-collapsed')) {
        shell.classList.remove('sidebar-collapsed');
        localStorage.setItem('eteya_sidebar_collapsed', 'false');
    }

    var group = headerEl.closest('.sidebar-category-group');
    if (!group) return;
    var isAlreadyOpen = group.classList.contains('open');
    document.querySelectorAll('.sidebar-category-group').forEach(function(g) {
        g.classList.remove('open');
    });
    if (!isAlreadyOpen) {
        group.classList.add('open');
    }
};

document.addEventListener('DOMContentLoaded', function() {
    if (localStorage.getItem('eteya_sidebar_collapsed') === 'true') {
        var shell = document.querySelector('.app-shell');
        if (shell) shell.classList.add('sidebar-collapsed');
    }
});
</script>
</head>
<body>
<div class="app-shell">
<script>
if (localStorage.getItem('eteya_sidebar_collapsed') === 'true') {
    document.querySelector('.app-shell').classList.add('sidebar-collapsed');
}
</script>

@php
    $user = auth()->user();
    $photoUrl = $user && $user->photo
        ? route('api.user.photo', ['userId' => $user->user_id])
        : ($baseUrl.'/assets/images/sample_logo.jpg');
    $fullName = $user ? $user->fullName() : '';
    $enterpriseEN = get_setting('enterprise_name_en', 'HHD Water Supply and Sewerage Service Enterprise');
    $devCredit = get_setting('developer_credit', 'GITAN ICT Work PLC');
    $currentPage = request()->segment(1) ?? 'dashboard';

    $allGroups = [
        [
            'title' => t('Operations'),
            'icon'  => 'dashboard',
            'items' => [
                ['page' => 'dashboard',        'label' => t('Dashboard'),        'desc' => 'Overview stats, charts & recent activity logs',  'icon' => 'dashboard', 'route' => 'dashboard'],
                ['page' => 'customer-service', 'label' => t('Customer Service'), 'desc' => 'Register, update & search active customers',    'icon' => 'customers', 'route' => 'customer-service.index'],
                ['page' => 'bills',             'label' => t('Bills & Printing'), 'desc' => 'Calculate monthly bills & print receipts',     'icon' => 'receipt',   'route' => 'bills.index'],
            ]
        ],
        [
            'title' => t('Reports & Ledger'),
            'icon'  => 'ledger',
            'items' => [
                ['page' => 'customer-ledger',      'label' => t('Customers Ledger'),   'desc' => 'Customer billing history & printable ledger',    'icon' => 'ledger',     'route' => 'customer-ledger.index'],
                ['page' => 'customer-statistics', 'label' => t('Detail Statistics'),  'desc' => 'Pivot reports: kebele × type × status',        'icon' => 'statistics', 'route' => 'customer-statistics.index'],
                ['page' => 'reading-correction',   'label' => t('Reading Correction'), 'desc' => 'Manage meter reading complaint approvals',    'icon' => 'wrench',     'route' => 'reading-correction.index'],
            ]
        ],
        [
            'title' => t('Administration'),
            'icon'  => 'shield',
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
                'hasActive' => $hasActive,
                'items'     => array_values($allowedItems)
            ];
        }
    }

    $languages = available_languages();
    $currentLang = current_lang();
    $pageAction = $pageAction ?? null;
@endphp

<aside class="sidebar">
    <div class="sidebar-brand">
        <img src="{{ $baseUrl }}/assets/images/Owater-logo.png" alt="Logo" class="brand-logo">
        <div class="brand-text">
            <div class="name">{{ t('Dashboard') }}</div>
            <div class="tag">Water Supply & Sewerage Enterprise</div>
        </div>
        <button type="button" class="sidebar-collapse-toggle" onclick="toggleSidebar(event)" title="Collapse / Expand Sidebar">
            {!! icon('panel-left', 18) !!}
        </button>
    </div>

    <div class="sidebar-categories-container">
    @foreach ($navGroups as $idx => $group)
        <div class="sidebar-category-group {{ ($group['hasActive'] || ($currentPage === 'dashboard' && $idx === 0)) ? 'open' : '' }}">
            <div class="sidebar-category-header" onclick="toggleCategoryGroup(this)">
                <span class="cat-icon">{!! icon($group['icon'], 16) !!}</span>
                <span class="cat-title">{{ $group['title'] }}</span>
                <span class="chevron">{!! icon('chevron-down', 12) !!}</span>
            </div>

            <div class="sidebar-category-body">
                <ul class="sidebar-nav">
                    @foreach ($group['items'] as $item)
                        <li data-title="{{ $item['label'] }}">
                            <a href="{{ route($item['route']) }}"
                               class="{{ $currentPage === $item['page'] ? 'active' : '' }}">
                                <span class="icon">{!! icon($item['icon'], 18) !!}</span>
                                <span>{{ $item['label'] }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            <!-- Category Flyout Panel in Collapsed Mode -->
            <div class="category-flyout-panel">
                <div class="flyout-card-inner">
                    <div class="flyout-header">
                        <span class="cat-icon">{!! icon($group['icon'], 16) !!}</span>
                        <span class="cat-title">{{ $group['title'] }}</span>
                    </div>
                    <div class="flyout-body">
                        @foreach ($group['items'] as $item)
                            <a href="{{ route($item['route']) }}" class="flyout-item {{ $currentPage === $item['page'] ? 'active' : '' }}">
                                <span class="icon">{!! icon($item['icon'], 16) !!}</span>
                                <div class="details">
                                    <div class="title">{{ $item['label'] }}</div>
                                    <div class="sub">{{ $item['desc'] }}</div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    @endforeach
    </div>

    <div class="sidebar-footer">
        <a href="{{ route('logout') }}" class="logout-link" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
            <span class="icon">{!! icon('logout', 18) !!}</span> <span class="label">{{ t('Logout') }}</span>
        </a>
        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
            @csrf
        </form>
        <div class="footer-info-badge">{{ $appVersion }} · <span class="badge {{ get_role_badge($user?->job_role ?? '') }}">{{ get_role_display($user?->job_role ?? '') }}</span></div>
        <div class="footer-info-credit">{{ $devCredit }}</div>
    </div>
</aside>

<div class="main-area">
<header class="topbar">
    <div>
        <div class="page-title">{{ $pageTitle ?? t('Dashboard') }}</div>
        <div class="page-subtitle">{{ $enterpriseEN }}</div>
    </div>
    <div class="spacer"></div>

    @if (! empty($pageAction))
    <a href="{{ $pageAction['href'] ?? '#' }}"
       class="btn btn-primary topbar-action"
       onclick="{{ $pageAction['onclick'] ?? '' }}">
        {!! icon($pageAction['icon'] ?? 'plus', 16) !!}
        <span>{{ $pageAction['label'] ?? 'Action' }}</span>
    </a>
    @endif

    <div class="lang-switcher">
        {!! icon('globe', 16) !!}
        <select id="langSelect" onchange="changeLanguage(this.value)" title="Language">
            @foreach ($languages as $code => $info)
                <option value="{{ $code }}" @if($code === $currentLang) selected @endif>
                    {{ $info[1] }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="user-chip">
        <img src="{{ $photoUrl }}" alt="User photo">
        <div>
            <div class="name">{{ $fullName }}</div>
            <div class="role"><span class="badge {{ get_role_badge($user?->job_role ?? '') }}">{{ get_role_display($user?->job_role ?? '') }}</span></div>
        </div>
    </div>
</header>

<main class="content">
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
</body>
</html>
