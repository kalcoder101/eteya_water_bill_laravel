<!DOCTYPE html>
<html lang="{{ current_lang() }}">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Login — {{ config('app.name') }}</title>

<script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
<style type="text/tailwindcss">
@theme {
    --color-primary: #059669;
    --color-primary-600: #059669;
    --color-primary-700: #047857;
    --color-primary-800: #065F46;
    --color-primary-50: #ECFDF5;
    --color-primary-100: #D1FAE5;
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
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Outfit:wght@500;600;700;800&family=Noto+Sans+Ethiopic:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="antialiased font-sans text-slate-700 bg-surface-base min-h-screen">

<div class="grid min-h-screen grid-cols-1 lg:grid-cols-[1.05fr_1fr]">

    <!-- ================= Brand side ================= -->
    <div class="relative hidden lg:flex flex-col justify-between overflow-hidden p-12 xl:p-16 text-white bg-gradient-to-br from-emerald-800 via-emerald-700 to-teal-900">
        <!-- ambient glows -->
        <div class="pointer-events-none absolute -top-32 -right-32 w-96 h-96 rounded-full bg-[radial-gradient(circle,rgba(110,231,183,0.35)_0%,transparent_70%)]"></div>
        <div class="pointer-events-none absolute -bottom-40 -left-24 w-[420px] h-[420px] rounded-full bg-[radial-gradient(circle,rgba(5,150,105,0.35)_0%,transparent_70%)]"></div>

        <!-- logo row -->
        <div class="relative z-10 flex items-center gap-3.5">
            <img src="{{ $baseUrl }}/assets/images/Owater-logo.png" alt="Logo" class="w-12 h-12 rounded-xl bg-white/95 p-1.5 shadow-[0_8px_24px_rgba(5,150,105,0.35)]">
            <div>
                <div class="text-base font-bold tracking-tight">{{ t('Eteya Water Bill') }}</div>
                <div class="text-[11px] text-emerald-100/80 mt-0.5">{{ t('Water Utility Billing System') }}</div>
            </div>
        </div>

        <!-- hero -->
        <div class="relative z-10 max-w-md">
            <div class="w-24 h-24 rounded-3xl border border-white/25 bg-white/10 backdrop-blur flex items-center justify-center mb-8 shadow-lg">
                <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z"/>
                </svg>
            </div>
            <h1 class="font-serif font-bold text-4xl xl:text-[44px] leading-[1.15] tracking-tight text-white">
                {{ t('Water is') }}<br><span class="text-emerald-200">{{ t('Life') }}</span>
            </h1>
            <p class="mt-4 text-[15px] leading-relaxed text-emerald-50/80">
                {{ t('Manage water utility billing, customer accounts, meter readings and printable receipts for the Eteya Town Water Supply & Sewerage Service Enterprise.') }}
            </p>

            <div class="mt-9 flex flex-wrap gap-6">
                <div class="flex items-center gap-2 text-xs font-medium text-emerald-50/90">
                    <span class="w-2 h-2 rounded-full bg-emerald-300 shadow-[0_0_8px_rgba(110,231,183,0.6)]"></span> {{ t('Customer Management') }}
                </div>
                <div class="flex items-center gap-2 text-xs font-medium text-emerald-50/90">
                    <span class="w-2 h-2 rounded-full bg-emerald-300 shadow-[0_0_8px_rgba(110,231,183,0.6)]"></span> {{ t('Bill Calculation') }}
                </div>
                <div class="flex items-center gap-2 text-xs font-medium text-emerald-50/90">
                    <span class="w-2 h-2 rounded-full bg-emerald-300 shadow-[0_0_8px_rgba(110,231,183,0.6)]"></span> {{ t('Reading Correction') }}
                </div>
            </div>
        </div>

        <div class="relative z-10 text-[11.5px] leading-relaxed text-emerald-100/55">
            {{ get_setting('developer_credit', 'Designed & Developed By: GITAN ICT Work PLC') }}
        </div>
    </div>

    <!-- ================= Form side ================= -->
    <div class="relative flex items-center justify-center px-6 py-12 sm:px-10">
        <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_20%_30%,rgba(5,150,105,0.05)_0,transparent_50%),radial-gradient(circle_at_80%_70%,rgba(5,150,105,0.04)_0,transparent_50%)]"></div>

        <div class="relative z-10 w-full max-w-[400px]">
            <div class="mb-8">
                <h2 class="text-[26px] font-bold tracking-tight text-slate-900 mb-2">{{ t('Welcome back') }}</h2>
                <p class="text-[13.5px] text-slate-500">{{ t('Sign in to access your dashboard') }}</p>
            </div>

            @if (session('errors'))
                @php $err = session('errors')->get('username')[0] ?? (session('errors')->get('password')[0] ?? ''); @endphp
                @if ($err)
                <div class="flex items-center gap-2.5 rounded-lg border border-rose-200 border-l-4 border-l-rose-500 bg-rose-50 text-rose-700 px-3.5 py-3 text-[13px] font-medium mb-5">
                    {!! icon('alert', 16) !!}
                    <span>{{ $err }}</span>
                </div>
                @endif
            @endif
            @if (! empty($error))
                <div class="flex items-center gap-2.5 rounded-lg border border-rose-200 border-l-4 border-l-rose-500 bg-rose-50 text-rose-700 px-3.5 py-3 text-[13px] font-medium mb-5">
                    {!! icon('alert', 16) !!}
                    <span>{{ $error }}</span>
                </div>
            @endif

            <form method="post" action="{{ route('login.submit') }}">
                @csrf
                <div class="mb-[18px]">
                    <label for="username" class="block text-xs font-semibold text-slate-900 mb-2">{{ t('Username') }}</label>
                    <input type="text" id="username" name="username"
                           value="{{ old('username') }}"
                           placeholder="Enter your username"
                           autocomplete="username" autofocus required
                           class="w-full px-3.5 py-3.5 bg-white border border-slate-200 rounded-lg text-sm text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition">
                </div>

                <div class="mb-[18px]">
                    <label for="password" class="block text-xs font-semibold text-slate-900 mb-2">{{ t('Password') }}</label>
                    <div class="relative">
                        <input type="password" id="password" name="password"
                               placeholder="••••••••"
                               autocomplete="current-password" required
                               class="w-full px-3.5 py-3.5 pr-11 bg-white border border-slate-200 rounded-lg text-sm text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition">
                        <button type="button" class="absolute right-3 top-1/2 -translate-y-1/2 p-1 rounded text-slate-400 hover:text-emerald-700 hover:bg-emerald-50 transition" onclick="toggleLoginPassword()" title="Show/hide password">
                            {!! icon('eye', 18) !!}
                        </button>
                    </div>
                </div>

                <button type="submit"
                        class="w-full mt-2 py-3.5 rounded-lg bg-gradient-to-br from-emerald-700 to-emerald-600 hover:from-emerald-800 hover:to-emerald-700 text-white font-bold text-sm tracking-wide shadow-[0_4px_12px_rgba(5,150,105,0.25)] hover:shadow-[0_8px_24px_rgba(5,150,105,0.35)] transition-all hover:-translate-y-px">
                    {{ t('SIGN IN') }}
                </button>
            </form>

            <div class="mt-8 rounded-lg border border-dashed border-slate-300 bg-white p-4">
                <div class="text-[10.5px] uppercase tracking-widest text-slate-500 font-bold mb-2">{{ t('Demo accounts') }}</div>
                <div class="font-mono text-xs text-slate-700">
                    <div class="flex justify-between py-1"><span>admin</span><span class="text-emerald-700 font-semibold">admin123</span></div>
                    <div class="flex justify-between py-1"><span>cs</span><span class="text-emerald-700 font-semibold">cs123</span></div>
                    <div class="flex justify-between py-1"><span>chaltu</span><span class="text-emerald-700 font-semibold">chaltu123</span></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleLoginPassword() {
    var field = document.getElementById('password');
    var button = event.target.closest('button');
    if (field.type === 'password') {
        field.type = 'text';
        button.innerHTML = '{!! icon('eye-off', 18) !!}';
    } else {
        field.type = 'password';
        button.innerHTML = '{!! icon('eye', 18) !!}';
    }
}
</script>
</body>
</html>
