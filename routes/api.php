<?php

use App\Http\Controllers\LoanController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/loan', [LoanController::class, 'getLoansByCustomer']);
Route::post('/loan', [LoanController::class, 'createLoanApplication']);
Route::put('/loan/{loan_number}/approve', [LoanController::class, 'approveLoanApplication']);
Route::put('/loan/{loan_number}/pay', [LoanController::class, 'receivePaymentOnLoan']);
