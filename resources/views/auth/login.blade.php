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
    --font-sans: "Inter", "Noto Sans Ethiopic", ui-sans-serif, system-ui, -apple-system, sans-serif;
}
@keyframes shake {
    0%, 100% { transform: translateX(0); }
    20%, 60% { transform: translateX(-4px); }
    40%, 80% { transform: translateX(4px); }
}
.animate-shake { animation: shake 0.4s ease-in-out; }
</style>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Noto+Sans+Ethiopic:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="min-h-screen flex flex-col sm:justify-center items-center py-10 bg-gray-100 font-sans antialiased selection:bg-emerald-500 selection:text-white">

<!-- Login Card (compact Breeze style) -->
<div class="w-full sm:max-w-sm px-6 py-6 bg-white shadow-md overflow-hidden sm:rounded-lg">

    <!-- Brand Header inside Card -->
    <div class="flex flex-col items-center text-center mb-6">
        <a href="{{ url('/') }}" class="inline-flex items-center justify-center mb-2.5">
            <img src="{{ $baseUrl }}/assets/images/Owater-logo.png" alt="Logo" class="w-12 h-12 object-contain">
        </a>
        <h1 class="text-base font-bold text-gray-900 leading-snug">{{ t('WaterSteward Enterprise System') }}</h1>
        <p class="text-xs font-medium text-gray-500 mt-0.5">{{ t('Water Utility Billing & Management') }}</p>
    </div>

    @php
        $err = '';
        if (session('errors')) {
            $err = session('errors')->get('username')[0] ?? (session('errors')->get('password')[0] ?? '');
        }
        if (empty($err) && ! empty($error)) {
            $err = $error;
        }
    @endphp

    @if ($err)
        <div class="flex items-center gap-2 mb-4 rounded-md border border-red-300 bg-red-50 px-3 py-2 text-xs font-medium text-red-700 animate-shake">
            {!! icon('alert', 14) !!}
            <span>{{ $err }}</span>
        </div>
    @endif

    <form method="post" action="{{ route('login.submit') }}" id="loginForm">
        @csrf

        <!-- Username -->
        <div>
            <label for="username" class="block font-medium text-sm text-gray-700">{{ t('Username') }}</label>
            <input type="text" id="username" name="username"
                   value="{{ old('username') }}"
                   placeholder="{{ t('Enter username') }}"
                   autocomplete="username" autofocus required
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm px-3 py-2 text-gray-900 placeholder:text-gray-400 focus:border-emerald-500 focus:ring-emerald-500 focus:outline-none">
        </div>

        <!-- Password -->
        <div class="mt-4">
            <label for="password" class="block font-medium text-sm text-gray-700">{{ t('Password') }}</label>
            <div class="relative mt-1">
                <input type="password" id="password" name="password"
                       placeholder="••••••••"
                       autocomplete="current-password" required
                       class="block w-full rounded-md border-gray-300 shadow-sm text-sm pl-3 pr-10 py-2 text-gray-900 placeholder:text-gray-400 focus:border-emerald-500 focus:ring-emerald-500 focus:outline-none">
                <button type="button" class="absolute right-1.5 top-1/2 -translate-y-1/2 p-1 rounded text-gray-400 hover:text-emerald-700 hover:bg-emerald-50 transition" onclick="togglePasswordVisibility()" title="Toggle Password">
                    <span id="eyeIcon">{!! icon('eye', 15) !!}</span>
                </button>
            </div>
        </div>

        <!-- Remember me + help -->
        <div class="flex items-center justify-between mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" name="remember"
                       class="rounded border-gray-300 text-emerald-600 shadow-sm focus:ring-emerald-500" checked>
                <span class="ms-2 text-sm text-gray-600">{{ t('Remember me') }}</span>
            </label>
            <span class="text-xs text-gray-400">{{ t('Contact admin for password reset') }}</span>
        </div>

        <!-- Submit -->
        <x-button type="submit" variant="primary" class="mt-5 w-full justify-center" size="lg">
            {{ t('Sign in') }}
        </x-button>
    </form>

    <!-- Quick Demo Accounts (compact) -->
    <div class="mt-5 pt-4 border-t border-gray-100">
        <div class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2">{{ t('Quick Demo Login') }}</div>
        <div class="grid grid-cols-3 gap-1.5">
            <button type="button" onclick="fillDemo('admin', 'admin123')" class="px-2 py-1.5 rounded-md border border-gray-200 bg-gray-50 hover:bg-emerald-50 hover:border-emerald-300 text-gray-600 hover:text-emerald-800 transition text-center">
                <span class="block text-[11px] font-bold">Admin</span>
                <span class="block text-[9px] text-gray-400">System Admin</span>
            </button>
            <button type="button" onclick="fillDemo('cs', 'cs123')" class="px-2 py-1.5 rounded-md border border-gray-200 bg-gray-50 hover:bg-emerald-50 hover:border-emerald-300 text-gray-600 hover:text-emerald-800 transition text-center">
                <span class="block text-[11px] font-bold">CS Agent</span>
                <span class="block text-[9px] text-gray-400">Customer Desk</span>
            </button>
            <button type="button" onclick="fillDemo('chaltu', 'chaltu123')" class="px-2 py-1.5 rounded-md border border-gray-200 bg-gray-50 hover:bg-emerald-50 hover:border-emerald-300 text-gray-600 hover:text-emerald-800 transition text-center">
                <span class="block text-[11px] font-bold">Chaltu</span>
                <span class="block text-[9px] text-gray-400">Billing Clerk</span>
            </button>
        </div>
    </div>
</div>


<script>
function togglePasswordVisibility() {
    var field = document.getElementById('password');
    var eyeSpan = document.getElementById('eyeIcon');
    if (field.type === 'password') {
        field.type = 'text';
        eyeSpan.innerHTML = '{!! icon('eye-off', 15) !!}';
    } else {
        field.type = 'password';
        eyeSpan.innerHTML = '{!! icon('eye', 15) !!}';
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
