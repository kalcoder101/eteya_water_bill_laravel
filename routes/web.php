<?php

use App\Http\Controllers\AccountRegisterController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BillController;
use App\Http\Controllers\CustomerLedgerController;
use App\Http\Controllers\CustomerServiceController;
use App\Http\Controllers\CustomerStatisticsController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ImportExportController;
use App\Http\Controllers\ReadingCorrectionController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function (): void {
    Route::get('/', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/', [AuthController::class, 'login'])->name('login.submit');
});

Route::post('logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::middleware('auth')->group(function (): void {
    Route::get('dashboard', [DashboardController::class, 'index'])
        ->middleware('page.access:dashboard')
        ->name('dashboard');

    Route::get('customer-service', [CustomerServiceController::class, 'index'])
        ->middleware('page.access:customer-service')
        ->name('customer-service.index');

    Route::get('customer-ledger', [CustomerLedgerController::class, 'index'])
        ->middleware('page.access:customer-ledger')
        ->name('customer-ledger.index');

    Route::get('customer-statistics', [CustomerStatisticsController::class, 'index'])
        ->middleware('page.access:customer-statistics')
        ->name('customer-statistics.index');

    Route::livewire('reading-correction', 'pages::reading-correction')
        ->middleware('page.access:reading-correction')
        ->name('reading-correction.index');

    Route::get('bills', [BillController::class, 'index'])
        ->middleware('page.access:bills')
        ->name('bills.index');

    Route::get('bills/print/{id}', [BillController::class, 'print'])
        ->middleware('page.access:bills.print')
        ->name('bills.print');

    Route::post('bills/calculate', [BillController::class, 'calculate'])
        ->middleware('page.access:bills.calculate')
        ->name('bills.calculate');

    Route::post('bills/mark-paid/{id}', [BillController::class, 'markPaid'])
        ->middleware('page.access:bills.mark-paid')
        ->name('bills.mark-paid');

    Route::get('export/customers', [ImportExportController::class, 'exportCustomers'])
        ->middleware('page.access:export-customers')
        ->name('export.customers');

    Route::get('export/bills', [ImportExportController::class, 'exportBills'])
        ->middleware('page.access:export-bills')
        ->name('export.bills');

    Route::get('export/ledger', [ImportExportController::class, 'exportLedger'])
        ->middleware('page.access:export-ledger')
        ->name('export.ledger');

    Route::post('import/customers', [ImportExportController::class, 'importCustomers'])
        ->middleware('page.access:import-customers')
        ->name('import.customers');

    Route::livewire('account-register', 'pages::account-register')
        ->middleware('page.access:account-register')
        ->name('account-register.index');
});

// Alias for user photo on the web routes group (used by the topbar avatar)
Route::get('api/user/photo/{userId}', function ($userId) {
    $user = \App\Models\User::where('user_id', $userId)->first();
    if (! $user || ! $user->photo) {
        return response('', 404);
    }
    return response($user->photo, 200, ['Content-Type' => 'image/jpeg']);
})->middleware('auth')->name('api.user.photo');
