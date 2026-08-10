@extends('layouts.app')

@section('content')

<!-- Page Header & Banner -->
<div class="gsap-hero flex flex-wrap items-end justify-between gap-4 mb-6">
    <div class="min-w-0">
        <h2 class="m-0 text-[22px] font-bold tracking-tight text-slate-900 flex items-center gap-3">
            <span class="w-10 h-10 rounded-xl bg-emerald-50 border border-emerald-200/80 text-emerald-700 inline-flex items-center justify-center shrink-0">{!! icon('book-open', 20) !!}</span>
            <span>{{ t('Customers Ledger Statement') }}</span>
        </h2>
        <p class="mt-2 text-[13px] text-slate-500">
            {{ t('View comprehensive water consumption and billing history by customer and year') }}
        </p>
    </div>
</div>

<!-- Customer Ledger Livewire 4 Island -->
<livewire:islands.customer-ledger-island :meter-serial="$meterSerial" :year="$year" />

@endsection
