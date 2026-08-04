<!DOCTYPE html>
<html lang="or">
<head>
<meta charset="UTF-8">
<title>Bill — {{ $bill->meter_serial }} — {{ $bill->bill_month }} {{ $bill->bill_year }}</title>
<style>
* { box-sizing: border-box; }
body {
    font-family: "Nyala", "Noto Sans Ethiopic", "Segoe UI", sans-serif;
    background: var(--ghost-white, #F7F7FF);
    margin: 0;
    padding: 24px;
    display: flex;
    flex-direction: column;
    align-items: center;
    color: #1f2937;
}
.bill {
    width: 240px;
    background: #fff;
    border: 1px solid #d1d5db;
    padding: 16px;
    font-size: 11px;
    line-height: 1.45;
    color: #1f2937;
    box-shadow: 0 8px 24px rgba(39, 24, 126, 0.12);
}
.bill .header { text-align: center; font-weight: 700; font-size: 12px; color: #27187E; margin-bottom: 6px; }
.bill .subtitle { font-size: 10px; color: #4b5563; }
.bill hr { border: 0; border-top: 1px dashed #6b7280; margin: 8px 0; }
.bill .row { display: flex; justify-content: space-between; margin: 3px 0; }
.bill .row span:first-child { color: #4b5563; }
.bill .section { font-weight: 700; margin-top: 10px; border-top: 1px dashed #6b7280; padding-top: 6px; color: #27187E; font-size: 11px; }
.bill .total { font-weight: 700; border-top: 2px solid #27187E; padding-top: 8px; margin-top: 8px; font-size: 12px; color: #27187E; }
.bill .slogan { text-align: center; font-weight: 700; margin-top: 12px; font-size: 10px; color: #27187E; }
.bill .sign { margin-top: 14px; display: flex; justify-content: space-between; font-size: 9px; color: #4b5563; }
.toolbar {
    position: fixed;
    top: 16px;
    right: 16px;
    background: #fff;
    padding: 10px 14px;
    border: 1px solid #ebe4d2;
    border-radius: 8px;
    box-shadow: 0 4px 16px rgba(0,0,0,0.10);
    display: flex;
    gap: 8px;
}
.toolbar button {
    padding: 7px 16px;
    cursor: pointer;
    border: 1px solid #27187E;
    background: #27187E;
    color: #fff;
    border-radius: 6px;
    font-weight: 600;
    font-family: inherit;
}
.toolbar button:hover { background: #1A1054; }
.toolbar a {
    padding: 7px 16px;
    border: 1px solid #E5E3F5;
    border-radius: 6px;
    text-decoration: none;
    color: #4b5563;
    font-weight: 600;
}
.toolbar a:hover { background: #ECEBFA; color: #27187E; }
@media print {
    body { background: #fff; padding: 0; }
    .no-print { display: none; }
    @page { size: 80mm auto; margin: 2mm; }
    .bill { border: 1px solid #4b5563; }
}
</style>
</head>
<body>

<div class="toolbar no-print">
    <button onclick="window.print()">Print</button>
    <a href="{{ route('bills.index') }}">← Back to Bills</a>
</div>

<div class="bill" id="bill">
    <div class="header">
        {{ $enterpriseOR }}<br>
        Magaalaa {{ $townName }}<br>
        <small>Water Supply & Sewerage Service Enterprise</small>
    </div>
    <hr>

    <div class="row"><strong>Lakk/Bill #:</strong> <span>{{ $billNumber }}</span></div>
    <div class="row"><strong>Koddi Mamilaa:</strong> <span>{{ $bill->meter_serial }}</span></div>
    <div class="row"><strong>Customer:</strong> <span>{{ $fullName }}</span></div>
    <div class="row"><strong>Guyaa/Date:</strong> <span>{{ $printDate }}</span></div>
    <div class="row"><strong>Sa'aa/Time:</strong> <span>{{ $printTime }}</span></div>

    <hr>

    <div class="section">1. Bill Cost — Ji'a: {{ $bill->bill_month }} {{ $bill->bill_year }}</div>
    <div class="row"><span>Previous R:</span><span>{{ number_format($prevReading, 2) }}</span></div>
    <div class="row"><span>Current R:</span><span>{{ number_format($curReading, 2) }}</span></div>
    <div class="row"><span>Use (m³):</span><span>{{ number_format($consumption, 2) }}</span></div>
    <div class="row"><span>Bill Cost:</span><span>{{ number_format($bill->consumption_cost, 2) }}</span></div>
    <div class="row" style="font-weight:bold;"><span>Subtotal:</span><span>{{ number_format($bill->consumption_cost, 2) }}</span></div>

    <div class="section">3. M.Rent / Service</div>
    <div class="row"><span>M. Rent:</span><span>{{ number_format($bill->meter_price, 2) }}</span></div>
    <div class="row"><span>Service:</span><span>{{ number_format($bill->service_price, 2) }}</span></div>
    <div class="row" style="font-weight:bold;"><span>Subtotal:</span><span>{{ number_format($bill->meter_price + $bill->service_price, 2) }}</span></div>

    <div class="section">5. Water Fund Cost</div>
    <div class="row"><span>Meter Rent:</span><span>{{ number_format($bill->state_price * 0.4, 2) }}</span></div>
    <div class="row"><span>Water Cost:</span><span>{{ number_format($bill->state_price * 0.6, 2) }}</span></div>
    <div class="row" style="font-weight:bold;"><span>Subtotal:</span><span>{{ number_format($bill->state_price, 2) }}</span></div>

    <div class="section">6. Deposit (Dhala)</div>
    <div class="row"><span>Deposit Cost:</span><span>{{ number_format($bill->deposited_cost, 2) }}</span></div>

    <div class="section">7. Penalty &amp; Community</div>
    <div class="row"><span>Penalty:</span><span>{{ number_format($bill->penalty_cost, 2) }}</span></div>
    <div class="row"><span>Community:</span><span>{{ number_format($bill->community_cost, 2) }}</span></div>

    <div class="total">
        <div class="row"><span>Waliigala / Total:</span><span>{{ number_format($bill->total_monthly_cost, 2) }} ETB</span></div>
    </div>

    <div class="row" style="margin-top: 6px;">
        <span><strong>Qab. Maallaqaa:</strong></span>
        <span><strong>Mallatto:</strong></span>
    </div>
    <div class="row" style="margin-top: 10px;">
        <span>{{ $collector }}</span>
        <span>______________</span>
    </div>

    <hr>
    <div class="slogan">{{ $slogan }} (Water is Life!!!)</div>
</div>

</body>
</html>
