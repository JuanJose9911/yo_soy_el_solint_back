<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ConfigController;
use App\Http\Controllers\CreditController;
use App\Http\Controllers\InterestRateController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PaymentMethodController;
use Illuminate\Support\Facades\Route;


// Authentication section
Route::prefix('auth')->group(function () {
    Route::middleware(['auth'])->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('user', [AuthController::class, 'getAuthenticatedUser']);
        Route::get('users', [AuthController::class, 'getUsers']);
        Route::post('change-password', [AuthController::class, 'changePassword']);
        Route::put('users/{id}', [AuthController::class, 'updateUser']);
        Route::get('users/{id}', [AuthController::class, 'getUser']);
        Route::delete('users/{id}', [AuthController::class, 'removeUser']);
    });

    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);
    Route::post('recover-password', [AuthController::class, 'recoverPassword']);
    Route::post('recover-user', [AuthController::class, 'recoverUser']);
    Route::post('verification-code', [AuthController::class, 'validateCode']);

});

// Customers section
Route::prefix('customers')->middleware(['auth'])->group(function () {
    Route::get('', [ClientController::class, 'index']);
    Route::post('', [ClientController::class, 'store']);
    Route::get('{id}', [ClientController::class, 'show']);
    Route::put('{id}', [ClientController::class, 'update']);
    Route::delete('{id}', [ClientController::class, 'destroy']);
});

// Credits section
Route::prefix('credits')->middleware(['auth'])->group(function () {
    Route::get('/', [CreditController::class, 'findAll']);
    Route::get('/{id}', [CreditController::class, 'findOne']);
    Route::post('/', [CreditController::class, 'create']);
    Route::put('/{id}', [CreditController::class, 'update']);
    Route::delete('/{id}', [CreditController::class, 'delete']);
    //inactivate credit
    Route::put('/deactivate/{id}', [CreditController::class, 'deactivateCredit']);
    //Simulate credit
    Route::post('simulate', [CreditController::class, 'simulateCredit']);
    Route::get('/{id}/recalculate-fees', [CreditController::class, 'recalculateFees']);
    //Credit refinance
    Route::post('/refinance', [CreditController::class, 'refinanceCredit']);
});

// Interest Rate section
Route::prefix('interest-rates')->middleware(['auth'])->group(function () {
    Route::get('', [InterestRateController::class, 'index']);
    Route::post('', [InterestRateController::class, 'store']);
    Route::put('{id}', [InterestRateController::class, 'update']);
    Route::delete('{id}', [InterestRateController::class, 'destroy']);
});

//Payment-methods
Route::prefix('payment-methods')->middleware(['auth'])->group(function () {
    Route::get('/', [PaymentMethodController::class, 'findAll']);
    Route::get('/{id}', [PaymentMethodController::class, 'findOne']);
    Route::post('/', [PaymentMethodController::class, 'create']);
    Route::put('/{id}', [PaymentMethodController::class, 'update']);
    Route::delete('/{id}', [PaymentMethodController::class, 'delete']);
});

//Payments section
Route::prefix('payments')->middleware(['auth'])->group(function () {
    Route::post('', [PaymentController::class, 'registerPayment']);
    Route::post('/historical', [PaymentController::class, 'historicalPay']);
    Route::post('/sumar-mora', [PaymentController::class, 'addLateDue']);
    Route::get('/paid', [PaymentController::class, 'getPaid']);
    Route::post('/total', [PaymentController::class, 'totalPayment']);
    Route::get('/{id}', [PaymentController::class, 'paymentReceipt']);
    Route::post('capital', [PaymentController::class, 'capital']);
    Route::post('simulate/capital', [PaymentController::class, 'simulateCapital']);
});



Route::prefix('reports')->group(function (){
    Route::get('ReceiptPdf', [PaymentMethodController::class, 'GeneratePdfReceipt']);
    Route::get('AmortizationPdf', [PaymentMethodController::class, 'GeneratePdfAmortization']);
    //Generate Simulate credit
    Route::get('simulatePdf', [CreditController::class, 'generatePdfSimulation']);
    //Credit report
    Route::get('report', [CreditController::class, 'reportCredit']);
    //Extracto de pago
    Route::get('extracto', [CreditController::class, 'extractoPago']);
});



Route::middleware(['auth'])->group(function () {
    Route::put('/configs', [ConfigController::class, 'update']);
    Route::get('/configs', [ConfigController::class, 'get']);
    Route::post('/configs', [ConfigController::class, 'store']);
    Route::get('/cities', [CityController::class, 'index']);
    Route::get('/activity-logs', [ActivityLogController::class, 'get']);
    Route::get('/home', [ConfigController::class, 'home']);
});
