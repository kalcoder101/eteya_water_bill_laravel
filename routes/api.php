<?php

use App\Http\Controllers\Api\ActiveCustomerController;
use App\Http\Controllers\Api\BillFinanceController;
use App\Http\Controllers\Api\LoggingAuditingController;
use App\Http\Controllers\Api\MeterLocationController;
use App\Http\Controllers\Api\OperationAuditingController;
use App\Http\Controllers\Api\ReadingCorrectionController;
use App\Http\Controllers\Api\SeasonalConsumptionController;
use App\Http\Controllers\Api\UserAccountDataController;
use Illuminate\Support\Facades\Route;

// ============= Public Unauthenticated API Routes =============
Route::prefix('user_account_data')->group(function (): void {
    Route::post('login', [UserAccountDataController::class, 'login']);
    Route::get('get-photo/{userId?}', [UserAccountDataController::class, 'getPhoto']);
});



// ============= Protected API Routes (Sanctum / Shared Secret) =============
Route::middleware(['api.secret'])->group(function (): void {

    // ============= user_account_data =============
    Route::prefix('user_account_data')->group(function (): void {
        Route::get('get-user-account/{userId?}', [UserAccountDataController::class, 'getUserAccount']);
        Route::get('get-name-by-job-role', [UserAccountDataController::class, 'getNameByJobRole']);
        Route::match(['get', 'post'], 'get-name-by-credentials', [UserAccountDataController::class, 'getNameByCredentials']);
        Route::match(['get', 'post'], 'check-user-password', [UserAccountDataController::class, 'checkUserPassword']);
        Route::post('update-user-account/{userId?}', [UserAccountDataController::class, 'updateUserAccount']);
    });

    // ============= active_customers =============
    Route::prefix('active_customers')->group(function (): void {
        Route::get('get-recent-code', [ActiveCustomerController::class, 'getRecentCode']);
        Route::get('get-all-active-customers', [ActiveCustomerController::class, 'getAllActiveCustomers']);
        Route::get('get-single-customer/{meterSerial?}', [ActiveCustomerController::class, 'getSingleCustomer']);
        Route::get('get-active-customer/{meterSerial?}', [ActiveCustomerController::class, 'getActiveCustomer']);
        Route::get('count', [ActiveCustomerController::class, 'count']);
        Route::get('count-status', [ActiveCustomerController::class, 'countStatus']);
        Route::get('check-customer-exists/{meterSerial?}', [ActiveCustomerController::class, 'checkCustomerExists']);
        Route::get('search', [ActiveCustomerController::class, 'search']);
        Route::get('payment-way-list', [ActiveCustomerController::class, 'paymentWayList']);
        Route::get('fetch-customers-sold-date', [ActiveCustomerController::class, 'fetchCustomersSoldDate']);
        Route::get('fetch-customers-sold-date-count', [ActiveCustomerController::class, 'fetchCustomersSoldDateCount']);
        Route::get('reports/{reportType?}', [ActiveCustomerController::class, 'reports']);
        Route::post('add-active-customer', [ActiveCustomerController::class, 'addActiveCustomer']);
        Route::post('send-active-customers', [ActiveCustomerController::class, 'sendActiveCustomers']);
        Route::put('update-customer-info', [ActiveCustomerController::class, 'updateCustomerInfo']);
        Route::put('update-sync-status', [ActiveCustomerController::class, 'updateSyncStatus']);
        Route::put('update-single-customer-status', [ActiveCustomerController::class, 'updateSingleCustomerStatus']);
        Route::put('update-start-reading', [ActiveCustomerController::class, 'updateStartReading']);
        Route::put('update-customer-type', [ActiveCustomerController::class, 'updateCustomerType']);
        Route::put('update-phone-number', [ActiveCustomerController::class, 'updatePhoneNumber']);
        Route::put('update-payment-way', [ActiveCustomerController::class, 'updatePaymentWay']);
        Route::put('update-customer-branch', [ActiveCustomerController::class, 'updateCustomerBranch']);
        Route::put('update-meter-size', [ActiveCustomerController::class, 'updateMeterSize']);
        Route::put('update-first-name', [ActiveCustomerController::class, 'updateFirstName']);
        Route::put('update-middle-name', [ActiveCustomerController::class, 'updateMiddleName']);
        Route::put('update-last-name', [ActiveCustomerController::class, 'updateLastName']);
        Route::put('update-kebele', [ActiveCustomerController::class, 'updateKebele']);
        Route::put('update-reader-block', [ActiveCustomerController::class, 'updateReaderBlock']);
        Route::put('update-bill-num', [ActiveCustomerController::class, 'updateBillNum']);
    });

    // ============= bill_finances =============
    Route::prefix('bill_finances')->group(function (): void {
        Route::get('get-bill-finance/{param1?}', [BillFinanceController::class, 'getBillFinance']);
        Route::get('customer-ledger-list', [BillFinanceController::class, 'customerLedgerList']);
    });

    // ============= seasonal_consumptions =============
    Route::prefix('seasonal_consumptions')->group(function (): void {
        Route::get('count-reading-id', [SeasonalConsumptionController::class, 'countReadingId']);
        Route::get('reading-month-list', [SeasonalConsumptionController::class, 'readingMonthList']);
    });

    // ============= reading_correction =============
    Route::prefix('reading_correction')->group(function (): void {
        Route::get('get-reading-correction/last-id', [ReadingCorrectionController::class, 'getReadingCorrection']);
        Route::post('add-reading-correction', [ReadingCorrectionController::class, 'addReadingCorrection']);
        Route::get('get-daily-reading-complain', [ReadingCorrectionController::class, 'getDailyReadingComplain']);
        Route::get('get-monthly-reading-complain', [ReadingCorrectionController::class, 'getMonthlyReadingComplain']);
        Route::get('get-annual-reading-complain', [ReadingCorrectionController::class, 'getAnnualReadingComplain']);
        Route::get('get-customer-reading-complain', [ReadingCorrectionController::class, 'getCustomerReadingComplain']);
        Route::put('update-new-reading', [ReadingCorrectionController::class, 'updateNewReading']);
        Route::put('update-approved-name', [ReadingCorrectionController::class, 'updateApprovedName']);
        Route::put('update-customer-complain', [ReadingCorrectionController::class, 'updateCustomerComplain']);
    });

    // ============= meter_location =============
    Route::prefix('meter_location')->group(function (): void {
        Route::post('add-meter-location', [MeterLocationController::class, 'addMeterLocation']);
    });

    // ============= operation_auditing =============
    Route::prefix('operation_auditing')->group(function (): void {
        Route::get('get-operation-auditing/last-id', [OperationAuditingController::class, 'lastId']);
        Route::post('add-operation', [OperationAuditingController::class, 'addOperation']);
    });

    // ============= logging_auditing =============
    Route::prefix('logging_auditing')->group(function (): void {
        Route::get('get-logging-auditing/last-id', [LoggingAuditingController::class, 'lastId']);
        Route::post('add-log', [LoggingAuditingController::class, 'addLog']);
    });
});
