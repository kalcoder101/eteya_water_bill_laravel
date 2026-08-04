<!DOCTYPE html>
<html lang="{{ current_lang() }}">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Login — {{ config('app.name') }}</title>
<link rel="stylesheet" href="{{ $baseUrl }}/assets/css/app.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
* { box-sizing: border-box; }
body {
    margin: 0;
    padding: 0;
    background: var(--ghost-white);
    font-family: Inter, "Segoe UI", system-ui, sans-serif;
    min-height: 100vh;
    overflow-x: hidden;
}

.login-page {
    display: grid;
    grid-template-columns: 1.05fr 1fr;
    min-height: 100vh;
}

/* ---------- Brand side ---------- */
.brand-side {
    background: linear-gradient(135deg, #27187E 0%, #1A1054 60%, #0F0838 100%);
    color: #fff;
    padding: 56px 64px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    position: relative;
    overflow: hidden;
}
.brand-side::before {
    content: '';
    position: absolute;
    top: -120px;
    right: -120px;
    width: 360px;
    height: 360px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(107, 91, 208, 0.45) 0%, transparent 70%);
    pointer-events: none;
}
.brand-side::after {
    content: '';
    position: absolute;
    bottom: -160px;
    left: -80px;
    width: 420px;
    height: 420px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(74, 58, 184, 0.30) 0%, transparent 70%);
    pointer-events: none;
}
.brand-logo-row {
    display: flex;
    align-items: center;
    gap: 14px;
    position: relative;
    z-index: 1;
}
.brand-logo-row img {
    width: 48px;
    height: 48px;
    border-radius: 10px;
    background: rgba(255,255,255,0.95);
    padding: 6px;
    box-shadow: 0 8px 24px rgba(74, 58, 184, 0.4);
}
.brand-logo-row .title { font-size: 16px; font-weight: 700; letter-spacing: -0.01em; }
.brand-logo-row .subtitle { font-size: 11.5px; opacity: 0.72; margin-top: 2px; }

.brand-hero {
    position: relative;
    z-index: 1;
}
.brand-hero .water-icon {
    width: 96px;
    height: 96px;
    border-radius: 24px;
    background: linear-gradient(135deg, rgba(255,255,255,0.18), rgba(255,255,255,0.06));
    border: 1px solid rgba(255,255,255,0.18);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    margin-bottom: 32px;
    backdrop-filter: blur(8px);
}
.brand-hero h1 {
    font-size: 44px;
    font-weight: 800;
    color: #fff;
    line-height: 1.15;
    letter-spacing: -0.025em;
    margin: 0 0 16px 0;
}
.brand-hero h1 .accent {
    background: linear-gradient(135deg, #B4A5FF, #E0D9FF);
    -webkit-background-clip: text;
    background-clip: text;
    -webkit-text-fill-color: transparent;
}
.brand-hero p {
    font-size: 15px;
    color: rgba(255,255,255,0.72);
    line-height: 1.65;
    margin: 0;
    max-width: 440px;
}
.brand-features {
    display: flex;
    gap: 24px;
    margin-top: 36px;
    flex-wrap: wrap;
}
.brand-feature {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 12.5px;
    color: rgba(255,255,255,0.85);
    font-weight: 500;
}
.brand-feature .dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: linear-gradient(135deg, #B4A5FF, #6B5BD0);
    box-shadow: 0 0 8px rgba(180, 165, 255, 0.6);
}

.brand-footer {
    position: relative;
    z-index: 1;
    font-size: 11.5px;
    color: rgba(255,255,255,0.55);
    line-height: 1.5;
}

/* ---------- Form side ---------- */
.form-side {
    background: var(--ghost-white);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 40px 32px;
    position: relative;
}
.form-side::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-image:
        radial-gradient(circle at 20% 30%, rgba(39, 24, 126, 0.04) 0, transparent 50%),
        radial-gradient(circle at 80% 70%, rgba(74, 58, 184, 0.03) 0, transparent 50%);
    pointer-events: none;
}
.login-form-container {
    width: 100%;
    max-width: 400px;
    position: relative;
    z-index: 1;
}
.login-form-container .form-header {
    margin-bottom: 32px;
}
.login-form-container .form-header h2 {
    font-size: 26px;
    font-weight: 700;
    color: var(--text-strong);
    margin: 0 0 8px 0;
    letter-spacing: -0.015em;
}
.login-form-container .form-header p {
    color: var(--text-muted);
    font-size: 13.5px;
    margin: 0;
}

.form-group { margin-bottom: 18px; }
.form-group label {
    display: block;
    font-size: 12.5px;
    font-weight: 600;
    color: var(--text-strong);
    margin-bottom: 8px;
    letter-spacing: -0.005em;
}
.form-group input {
    width: 100%;
    padding: 13px 14px;
    background: var(--surface);
    border: 1px solid var(--indigo-border);
    border-radius: var(--r-md);
    color: var(--text-body);
    font-size: 14px;
    font-family: inherit;
    transition: border-color 0.15s, box-shadow 0.15s;
}
.form-group input::placeholder { color: var(--text-muted); }
.form-group input:focus {
    outline: none;
    border-color: var(--persian-indigo-bright);
    box-shadow: 0 0 0 4px rgba(74, 58, 184, 0.12);
}
.password-wrapper { position: relative; }
.password-toggle {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    background: transparent;
    border: none;
    cursor: pointer;
    color: var(--text-muted);
    padding: 4px;
    border-radius: var(--r-sm);
    transition: color 0.15s, background 0.15s;
    display: flex;
    align-items: center;
}
.password-toggle:hover { color: var(--persian-indigo); background: var(--indigo-wash); }

.error {
    background: var(--danger-soft);
    border: 1px solid rgba(220, 38, 38, 0.20);
    border-left: 3px solid var(--danger);
    color: #991B1B;
    padding: 11px 14px;
    border-radius: var(--r-md);
    font-size: 13px;
    font-weight: 500;
    margin-bottom: 20px;
    display: flex;
    gap: 10px;
    align-items: center;
}
.error svg { flex-shrink: 0; color: var(--danger); }

.login-btn {
    width: 100%;
    padding: 13px;
    background: var(--gradient-primary);
    border: none;
    border-radius: var(--r-md);
    color: #fff;
    font-weight: 600;
    font-size: 14px;
    font-family: inherit;
    cursor: pointer;
    transition: all 0.2s ease;
    margin-top: 8px;
    box-shadow: 0 4px 12px rgba(39, 24, 126, 0.20);
    letter-spacing: 0.01em;
}
.login-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 8px 24px rgba(74, 58, 184, 0.30);
    background: var(--gradient-primary-hover);
}
.login-btn:active { transform: translateY(0); }

.login-meta {
    text-align: center;
    margin-top: 28px;
    font-size: 12.5px;
    color: var(--text-muted);
}
.login-meta a { font-weight: 600; }

.demo-accounts {
    margin-top: 32px;
    padding: 16px;
    background: var(--surface-tint);
    border: 1px dashed var(--indigo-border);
    border-radius: var(--r-md);
}
.demo-accounts .demo-title {
    font-size: 11px;
    text-transform: uppercase;
    color: var(--text-muted);
    font-weight: 700;
    letter-spacing: 0.06em;
    margin-bottom: 8px;
}
.demo-accounts .demo-row {
    display: flex;
    justify-content: space-between;
    font-size: 12px;
    color: var(--text-body);
    padding: 4px 0;
    font-family: "JetBrains Mono", "SF Mono", Consolas, monospace;
}
.demo-accounts .demo-row span:last-child {
    color: var(--persian-indigo);
    font-weight: 600;
}

@media (max-width: 960px) {
    .login-page { grid-template-columns: 1fr; }
    .brand-side { display: none; }
    .form-side { padding: 32px 24px; }
}
</style>
</head>
<body>
<div class="login-page">
    <div class="brand-side">
        <div class="brand-logo-row">
            <img src="{{ $baseUrl }}/assets/images/Owater-logo.png" alt="Logo">
            <div>
                <div class="title">{{ t('Eteya Water Bill') }}</div>
                <div class="subtitle">{{ t('Water Utility Billing System') }}</div>
            </div>
        </div>

        <div class="brand-hero">
            <div class="water-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z"/>
                </svg>
            </div>
            <h1>{{ t('Water is') }}<br><span class="accent">{{ t('Life') }}</span></h1>
            <p>{{ t('Manage water utility billing, customer accounts, meter readings and printable receipts for the Eteya Town Water Supply & Sewerage Service Enterprise.') }}</p>

            <div class="brand-features">
                <div class="brand-feature"><span class="dot"></span> {{ t('Customer Management') }}</div>
                <div class="brand-feature"><span class="dot"></span> {{ t('Bill Calculation') }}</div>
                <div class="brand-feature"><span class="dot"></span> {{ t('Reading Correction') }}</div>
            </div>
        </div>

        <div class="brand-footer">
            {{ get_setting('developer_credit', 'Designed & Developed By: GITAN ICT Work PLC') }}
        </div>
    </div>

    <div class="form-side">
        <div class="login-form-container">
            <div class="form-header">
                <h2>{{ t('Welcome back') }}</h2>
                <p>{{ t('Sign in to access your dashboard') }}</p>
            </div>

            @if (session('errors'))
                @php $err = session('errors')->get('username')[0] ?? (session('errors')->get('password')[0] ?? ''); @endphp
                @if ($err)
                <div class="error">
                    {!! icon('alert', 16) !!}
                    <span>{{ $err }}</span>
                </div>
                @endif
            @endif
            @if (! empty($error))
                <div class="error">
                    {!! icon('alert', 16) !!}
                    <span>{{ $error }}</span>
                </div>
            @endif

            <form method="post" action="{{ route('login.submit') }}">
                @csrf
                <div class="form-group">
                    <label for="username">{{ t('Username') }}</label>
                    <input type="text" id="username" name="username"
                           value="{{ old('username') }}"
                           placeholder="Enter your username"
                           autocomplete="username" autofocus required>
                </div>

                <div class="form-group">
                    <label for="password">{{ t('Password') }}</label>
                    <div class="password-wrapper">
                        <input type="password" id="password" name="password"
                               placeholder="••••••••"
                               autocomplete="current-password" required>
                        <button type="button" class="password-toggle" onclick="toggleLoginPassword()" title="Show/hide password">
                            {!! icon('eye', 18) !!}
                        </button>
                    </div>
                </div>

                <button type="submit" class="login-btn">
                    {{ t('SIGN IN') }}
                </button>
            </form>

            <div class="demo-accounts">
                <div class="demo-title">{{ t('Demo accounts') }}</div>
                <div class="demo-row"><span>admin</span><span>admin123</span></div>
                <div class="demo-row"><span>cs</span><span>cs123</span></div>
                <div class="demo-row"><span>chaltu</span><span>chaltu123</span></div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleLoginPassword() {
    const field = document.getElementById('password');
    const button = event.target.closest('button');
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
