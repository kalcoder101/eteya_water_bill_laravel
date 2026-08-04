<!DOCTYPE html>
<html lang="{{ current_lang() }}">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ $pageTitle ?? config('app.name') }} — {{ config('app.name') }}</title>
<link rel="stylesheet" href="{{ $baseUrl }}/assets/css/app.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="{{ $baseUrl }}/assets/js/app.js"></script>
<script>
if (localStorage.getItem('eteya_sidebar_collapsed') === 'true') {
    document.documentElement.classList.add('sidebar-collapsed-boot');
}
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

    $navItems = [
        ['page' => 'dashboard',           'label' => t('Dashboard'),           'icon' => 'dashboard', 'route' => 'dashboard'],
        ['page' => 'customer-service',    'label' => t('Customer Service'),    'icon' => 'customers', 'route' => 'customer-service.index'],
        ['page' => 'customer-ledger',      'label' => t('Customers Ledger'),    'icon' => 'ledger',    'route' => 'customer-ledger.index'],
        ['page' => 'customer-statistics', 'label' => t('Detail Statistics'),   'icon' => 'statistics','route' => 'customer-statistics.index'],
        ['page' => 'reading-correction',   'label' => t('Reading Correction'),  'icon' => 'wrench',    'route' => 'reading-correction.index'],
        ['page' => 'bills',                'label' => t('Bills & Printing'),    'icon' => 'receipt',  'route' => 'bills.index'],
    ];

    $navItems = array_filter($navItems, fn($item) => is_allowed_page($item['page'], $user?->job_role ?? ''));

    if ($user && is_allowed_page('account-register', $user->job_role)) {
        $navItems[] = ['page' => 'account-register', 'label' => t('Account Register'), 'icon' => 'lock', 'route' => 'account-register.index'];
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
    </div>

    <div class="sidebar-section-title">{{ t('Menu') }}</div>
    <ul class="sidebar-nav">
        @foreach ($navItems as $item)
            <li data-title="{{ $item['label'] }}">
                <a href="{{ route($item['route']) }}"
                   class="{{ $currentPage === $item['page'] ? 'active' : '' }}">
                    <span class="icon">{!! icon($item['icon'], 18) !!}</span>
                    <span>{{ $item['label'] }}</span>
                </a>
            </li>
        @endforeach
    </ul>

    <div class="sidebar-footer">
        <a href="{{ route('logout') }}" class="logout-link" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
            <span class="icon">{!! icon('logout', 16) !!}</span> {{ t('Logout') }}
        </a>
        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
            @csrf
        </form>
        <div>{{ $appVersion }} · <span class="badge {{ get_role_badge($user?->job_role ?? '') }}">{{ get_role_display($user?->job_role ?? '') }}</span></div>
        <div style="margin-top:4px; opacity:0.7; font-size: 10.5px;">{{ $devCredit }}</div>
    </div>
</aside>

<div class="main-area">
<header class="topbar">
    <button type="button" class="sidebar-toggle-btn" onclick="toggleSidebar()" title="Toggle Sidebar">
        {!! icon('menu', 18) !!}
    </button>
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
