<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Services\LoanService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class LoanController extends Controller
{
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    private $loanService;

    public function __construct()
    {
        $this->loanService = new LoanService();
    }

    public function createLoanApplication(Request $request)
    {

        if (!Gate::allows('create-loan')) {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied'
            ], 403);
        }

        $logged_user = auth()->user();

        try {
            $loanInput = json_encode([
                'term' => $request->input('term'),
                'currency' => $request->input('currency'),
                'amount' => $request->input('amount'),
            ]);

            $data = $this->loanService->createLoan($loanInput, $logged_user);

            return response()->json([
                'status' => true,
                'data' => $data
            ], 200);
        } catch (\Throwable $t) {
            return response()->json([
                'status' => false,
                'message' => $t->getMessage()
            ], 500);
        }
    }

    public function approveLoanApplication(Request $request, string $loan_number)
    {
        if (!Gate::allows('approve-loan')) {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied'
            ], 403);
        }

        $logged_user = auth()->user();

        try {
            $approve_loan = $this->loanService->approveLoan($loan_number, $logged_user);

            return response()->json([
                'status' => true,
                'message' => $approve_loan
            ], 200);
        } catch (\Throwable $t) {
            return response()->json([
                'status' => false,
                'message' => $t->getMessage()
            ], 500);
        }
    }

    public function rejectLoanApplication(Request $request, string $loan_number)
    {
        if (!Gate::allows('reject-loan')) {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied'
            ], 403);
        }

        $logged_user = auth()->user();

        try {
            $reject_loan = $this->loanService->rejectLoan($loan_number, $request->reject_reason, $logged_user);

            return response()->json([
                'status' => true,
                'message' => $reject_loan
            ], 200);
        } catch (\Throwable $t) {
            return response()->json([
                'status' => false,
                'message' => $t->getMessage()
            ], 500);
        }
    }

    public function getLoansByCustomer()
    {
        if (!Gate::allows('view-loan-customer')) {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied'
            ], 403);
        }

        try {
            $logged_user = auth()->user();

            $loans = $this->loanService->getLoansByCustomer($logged_user->id);

            return response()->json([
                'status' => true,
                'data' => $loans
            ], 200);
        } catch (\Throwable $t) {
            return response()->json([
                'status' => false,
                'message' => $t->getMessage()
            ], 500);
        }
    }

    public function getLoansByAdmin(Request $request)
    {
        if (!Gate::allows('view-loan-admin')) {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied'
            ], 403);
        }

        try {
            $status = $request->status ? $request->status : null;

            $loans = $this->loanService->getLoansByAdmin($status);

            return response()->json([
                'status' => true,
                'data' => $loans
            ], 200);
        } catch (\Throwable $t) {
            return response()->json([
                'status' => false,
                'message' => $t->getMessage()
            ], 500);
        }
    }


    public function getLoanById($loan_id)
    {
        if (!Gate::allows('view-loan-by-id')) {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied'
            ], 403);
        }

        $logged_user = auth()->user();

        try {
            $loan = $this->loanService->getLoanById($loan_id, $logged_user);

            return response()->json([
                'status' => true,
                'data' => $loan
            ], 200);
        } catch (\Throwable $t) {
            return response()->json([
                'status' => false,
                'message' => $t->getMessage()
            ], 500);
        }
    }

    public function receivePaymentOnLoan(Request $request, string $loan_number)
    {

        if (!Gate::allows('repay-loan-customer')) {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied'
            ], 403);
        }

        try {
            $payment_input = json_encode([
                'loan_number' => $loan_number,
                'currency' => $request->input('currency'),
                'amount' => $request->input('amount'),
            ]);

            $processed_payment = $this->loanService->processRepayment($payment_input);

            return response()->json([
                'status' => true,
                'message' => $processed_payment
            ], 200);
        } catch (\Throwable $t) {
            return response()->json([
                'status' => false,
                'message' => $t->getMessage()
            ], 500);
        }
    }
}
