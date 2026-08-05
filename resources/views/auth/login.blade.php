<!DOCTYPE html>
<html lang="{{ current_lang() }}">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ t('Sign In') }} — {{ config('app.name') }}</title>

<script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
<style type="text/tailwindcss">
@theme {
    --color-primary: #059669;
    --color-primary-hover: #047857;
    --color-surface-base: #F8FAF8;
    --color-surface-card: #FFFFFF;
    --font-sans: "Inter", "Noto Sans Ethiopic", ui-sans-serif, system-ui, -apple-system, sans-serif;
}
</style>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Noto+Sans+Ethiopic:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="antialiased font-sans bg-slate-50 text-slate-800 min-h-screen flex flex-col justify-between selection:bg-emerald-500 selection:text-white">

<!-- Ambient Background Decorative Elements -->
<div class="fixed inset-0 pointer-events-none overflow-hidden z-0">
    <div class="absolute -top-40 -left-40 w-96 h-96 rounded-full bg-emerald-500/10 blur-3xl"></div>
    <div class="absolute top-1/2 -right-40 w-96 h-96 rounded-full bg-teal-500/10 blur-3xl"></div>
    <div class="absolute -bottom-40 left-1/3 w-96 h-96 rounded-full bg-emerald-600/5 blur-3xl"></div>
</div>

<main class="relative z-10 flex-1 flex items-center justify-center p-4 sm:p-6 lg:p-8">
    <div class="w-full max-w-[420px]">
        <!-- Brand Header -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-white shadow-md shadow-emerald-500/10 border border-slate-200/80 p-2.5 mb-4 group transition-transform duration-300 hover:scale-105">
                <img src="{{ $baseUrl }}/assets/images/Owater-logo.png" alt="Logo" class="w-full h-full object-contain">
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">{{ t('WaterSteward Enterprise System') }}</h1>
            <p class="text-xs text-slate-500 font-medium mt-1">{{ t('Water Utility Billing & Management System') }}</p>
        </div>

        <!-- Login Card -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xl shadow-slate-200/50 p-6 sm:p-8">
            <div class="mb-6">
                <h2 class="text-lg font-bold text-slate-900">{{ t('Sign In') }}</h2>
                <p class="text-xs text-slate-500 mt-0.5">{{ t('Enter your account credentials to access system') }}</p>
            </div>

            <!-- Error Alerts -->
            @if (session('errors'))
                @php $err = session('errors')->get('username')[0] ?? (session('errors')->get('password')[0] ?? ''); @endphp
                @if ($err)
                <div class="flex items-center gap-3 rounded-xl border border-rose-200 bg-rose-50/80 text-rose-700 p-3.5 text-xs font-semibold mb-5 animate-shake">
                    {!! icon('alert', 16) !!}
                    <span>{{ $err }}</span>
                </div>
                @endif
            @endif
            @if (! empty($error))
                <div class="flex items-center gap-3 rounded-xl border border-rose-200 bg-rose-50/80 text-rose-700 p-3.5 text-xs font-semibold mb-5 animate-shake">
                    {!! icon('alert', 16) !!}
                    <span>{{ $error }}</span>
                </div>
            @endif

            <!-- Form -->
            <form method="post" action="{{ route('login.submit') }}" id="loginForm">
                @csrf
                <div class="mb-4">
                    <label for="username" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">{{ t('Username') }}</label>
                    <div class="relative">
                        <input type="text" id="username" name="username"
                               value="{{ old('username') }}"
                               placeholder="Enter username"
                               autocomplete="username" autofocus required
                               class="w-full pl-10 pr-4 py-3 bg-slate-50/50 border border-slate-200 rounded-xl text-sm font-medium text-slate-900 placeholder:text-slate-400 focus:outline-none focus:bg-white focus:border-emerald-600 focus:ring-4 focus:ring-emerald-500/10 transition-all">
                        <div class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400">
                            {!! icon('user', 16) !!}
                        </div>
                    </div>
                </div>

                <div class="mb-5">
                    <label for="password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">{{ t('Password') }}</label>
                    <div class="relative">
                        <input type="password" id="password" name="password"
                               placeholder="••••••••"
                               autocomplete="current-password" required
                               class="w-full pl-10 pr-10 py-3 bg-slate-50/50 border border-slate-200 rounded-xl text-sm font-medium text-slate-900 placeholder:text-slate-400 focus:outline-none focus:bg-white focus:border-emerald-600 focus:ring-4 focus:ring-emerald-500/10 transition-all">
                        <div class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400">
                            {!! icon('lock', 16) !!}
                        </div>
                        <button type="button" class="absolute right-3 top-1/2 -translate-y-1/2 p-1 rounded-lg text-slate-400 hover:text-emerald-700 hover:bg-emerald-50 transition" onclick="togglePasswordVisibility()" title="Toggle Password">
                            <span id="eyeIcon">{!! icon('eye', 16) !!}</span>
                        </button>
                    </div>
                </div>

                <button type="submit"
                        class="w-full py-3.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white font-bold text-sm tracking-wide shadow-lg shadow-emerald-600/25 hover:shadow-xl hover:shadow-emerald-600/35 transition-all hover:-translate-y-0.5 active:translate-y-0">
                    {{ t('SIGN IN') }}
                </button>
            </form>

            <!-- Quick Demo Accounts selector -->
            <div class="mt-6 pt-5 border-t border-slate-100">
                <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2.5 flex items-center justify-between">
                    <span>{{ t('Quick Demo Login') }}</span>
                    <span class="text-[10px] text-emerald-600 font-semibold">1-Click Fill</span>
                </div>
                <div class="grid grid-cols-3 gap-2">
                    <button type="button" onclick="fillDemo('admin', 'admin123')" class="px-2.5 py-2 rounded-lg border border-slate-200 bg-slate-50 hover:bg-emerald-50 hover:border-emerald-300 text-slate-700 hover:text-emerald-800 transition text-center group">
                        <div class="text-[11px] font-bold group-hover:text-emerald-700">Admin</div>
                        <div class="text-[9.5px] text-slate-400 group-hover:text-emerald-600">System Admin</div>
                    </button>
                    <button type="button" onclick="fillDemo('cs', 'cs123')" class="px-2.5 py-2 rounded-lg border border-slate-200 bg-slate-50 hover:bg-emerald-50 hover:border-emerald-300 text-slate-700 hover:text-emerald-800 transition text-center group">
                        <div class="text-[11px] font-bold group-hover:text-emerald-700">CS Agent</div>
                        <div class="text-[9.5px] text-slate-400 group-hover:text-emerald-600">Customer Desk</div>
                    </button>
                    <button type="button" onclick="fillDemo('chaltu', 'chaltu123')" class="px-2.5 py-2 rounded-lg border border-slate-200 bg-slate-50 hover:bg-emerald-50 hover:border-emerald-300 text-slate-700 hover:text-emerald-800 transition text-center group">
                        <div class="text-[11px] font-bold group-hover:text-emerald-700">Chaltu</div>
                        <div class="text-[9.5px] text-slate-400 group-hover:text-emerald-600">Billing Clerk</div>
                    </button>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Footer -->
<footer class="relative z-10 py-4 text-center text-xs text-slate-400">
    <p>&copy; {{ date('Y') }} {{ t('WaterSteward Water Supply and Sewerage Service Enterprise') }} &bull; {{ get_setting('developer_credit', 'GITAN ICT Work PLC') }}</p>
</footer>

<script>
function togglePasswordVisibility() {
    var field = document.getElementById('password');
    var eyeSpan = document.getElementById('eyeIcon');
    if (field.type === 'password') {
        field.type = 'text';
        eyeSpan.innerHTML = '{!! icon('eye-off', 16) !!}';
    } else {
        field.type = 'password';
        eyeSpan.innerHTML = '{!! icon('eye', 16) !!}';
    }
}

function fillDemo(u, p) {
    document.getElementById('username').value = u;
    document.getElementById('password').value = p;
    document.getElementById('password').focus();
}
</script>
</body>
</html>
