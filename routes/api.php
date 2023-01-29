<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\LoanController;
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

Route::middleware('auth:sanctum')->post('/auth/register/admin', [AuthController::class, 'createAdminUser']);
Route::post('/auth/register/customer', [AuthController::class, 'createCustomerUser']);
Route::post('/auth/login', [AuthController::class, 'loginUser']);

Route::middleware('auth:sanctum')->get('/loan', [LoanController::class, 'getLoansByCustomer']);
Route::middleware('auth:sanctum')->get('/loan/admin', [LoanController::class, 'getLoansByAdmin']);
Route::middleware('auth:sanctum')->post('/loan', [LoanController::class, 'createLoanApplication']);
Route::middleware('auth:sanctum')->put('/loan/{loan_number}/approve', [LoanController::class, 'approveLoanApplication']);
Route::middleware('auth:sanctum')->put('/loan/{loan_number}/reject', [LoanController::class, 'rejectLoanApplication']);
Route::middleware('auth:sanctum')->put('/loan/{loan_number}/pay', [LoanController::class, 'receivePaymentOnLoan']);
