<!DOCTYPE html>
<html lang="or">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Bill — {{ $bill->meter_serial }} — {{ $bill->bill_month }} {{ $bill->bill_year }}</title>

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
<style>
@media print {
    body { background: #fff !important; padding: 0 !important; }
    .no-print { display: none !important; }
    @page { size: 80mm auto; margin: 2mm; }
    .bill { border: 1px solid #4b5563 !important; }
}
</style>
</head>
<body class="antialiased font-sans text-slate-700 bg-surface-base min-h-screen p-6">

<div class="toolbar no-print fixed top-4 right-4 z-10 bg-white px-3 py-2.5 border border-slate-200 rounded-lg shadow-md flex items-center gap-2">
    <x-button variant="primary" icon="print" onclick="window.print()">Print</x-button>
    <x-button variant="secondary" icon="arrow-left" :href="route('bills.index')">Back to Bills</x-button>
</div>

<div class="bill mx-auto my-4 w-[240px] bg-white p-3 border border-slate-300 rounded font-mono text-[10px] leading-tight text-slate-800 shadow-[0_8px_24px_rgba(39,24,126,0.12)]" id="bill">
    <div class="text-center font-bold text-[11px] text-emerald-800 mb-1.5">
        {{ $enterpriseOR }}<br>
        Magaalaa {{ $townName }}<br>
        <span class="text-[9px] text-slate-500 font-normal">Water Supply & Sewerage Service Enterprise</span>
    </div>
    <hr class="border-0 border-t border-dashed border-slate-500 my-2">

    <div class="flex justify-between"><strong>Lakk/Bill #:</strong> <span>{{ $billNumber }}</span></div>
    <div class="flex justify-between"><strong>Koddi Mamilaa:</strong> <span>{{ $bill->meter_serial }}</span></div>
    <div class="flex justify-between"><strong>Customer:</strong> <span>{{ $fullName }}</span></div>
    <div class="flex justify-between"><strong>Guyaa/Date:</strong> <span>{{ $printDate }}</span></div>
    <div class="flex justify-between"><strong>Sa'aa/Time:</strong> <span>{{ $printTime }}</span></div>

    <hr class="border-0 border-t border-dashed border-slate-500 my-2">

    <div class="font-bold mt-2.5 border-t border-dashed border-slate-500 pt-1.5 text-emerald-800 text-[11px]">1. Bill Cost — Ji'a: {{ $bill->bill_month }} {{ $bill->bill_year }}</div>
    <div class="flex justify-between"><span class="text-slate-500">Previous R:</span><span>{{ number_format($prevReading, 2) }}</span></div>
    <div class="flex justify-between"><span class="text-slate-500">Current R:</span><span>{{ number_format($curReading, 2) }}</span></div>
    <div class="flex justify-between"><span class="text-slate-500">Use (m³):</span><span>{{ number_format($consumption, 2) }}</span></div>
    <div class="flex justify-between"><span class="text-slate-500">Bill Cost:</span><span>{{ number_format($bill->consumption_cost, 2) }}</span></div>
    <div class="flex justify-between font-bold"><span>Subtotal:</span><span>{{ number_format($bill->consumption_cost, 2) }}</span></div>

    <div class="font-bold mt-2.5 border-t border-dashed border-slate-500 pt-1.5 text-emerald-800 text-[11px]">3. M.Rent / Service</div>
    <div class="flex justify-between"><span class="text-slate-500">M. Rent:</span><span>{{ number_format($bill->meter_price, 2) }}</span></div>
    <div class="flex justify-between"><span class="text-slate-500">Service:</span><span>{{ number_format($bill->service_price, 2) }}</span></div>
    <div class="flex justify-between font-bold"><span>Subtotal:</span><span>{{ number_format($bill->meter_price + $bill->service_price, 2) }}</span></div>

    <div class="font-bold mt-2.5 border-t border-dashed border-slate-500 pt-1.5 text-emerald-800 text-[11px]">5. Water Fund Cost</div>
    <div class="flex justify-between"><span class="text-slate-500">Meter Rent:</span><span>{{ number_format($bill->state_price * 0.4, 2) }}</span></div>
    <div class="flex justify-between"><span class="text-slate-500">Water Cost:</span><span>{{ number_format($bill->state_price * 0.6, 2) }}</span></div>
    <div class="flex justify-between font-bold"><span>Subtotal:</span><span>{{ number_format($bill->state_price, 2) }}</span></div>

    <div class="font-bold mt-2.5 border-t border-dashed border-slate-500 pt-1.5 text-emerald-800 text-[11px]">6. Deposit (Dhala)</div>
    <div class="flex justify-between"><span class="text-slate-500">Deposit Cost:</span><span>{{ number_format($bill->deposited_cost, 2) }}</span></div>

    <div class="font-bold mt-2.5 border-t border-dashed border-slate-500 pt-1.5 text-emerald-800 text-[11px]">7. Penalty &amp; Community</div>
    <div class="flex justify-between"><span class="text-slate-500">Penalty:</span><span>{{ number_format($bill->penalty_cost, 2) }}</span></div>
    <div class="flex justify-between"><span class="text-slate-500">Community:</span><span>{{ number_format($bill->community_cost, 2) }}</span></div>

    <div class="font-bold border-t-2 border-slate-800 pt-2 mt-2 text-[11px] text-emerald-800">
        <div class="flex justify-between"><span>Waliigala / Total:</span><span>{{ number_format($bill->total_monthly_cost, 2) }} ETB</span></div>
    </div>

    <div class="flex justify-between mt-1.5">
        <span><strong>Qab. Maallaqaa:</strong></span>
        <span><strong>Mallatto:</strong></span>
    </div>
    <div class="flex justify-between mt-2.5">
        <span>{{ $collector }}</span>
        <span>______________</span>
    </div>

    <hr class="border-0 border-t border-dashed border-slate-500 my-2">
    <div class="text-center font-bold text-emerald-800 text-[10px]">{{ $slogan }} (Water is Life!!!)</div>
</div>

</body>
</html>
