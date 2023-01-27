<?php

namespace App\Http\Controllers;

use App\Http\Services\LoanService;
use Illuminate\Http\Request;

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
        $loanInput = json_encode([
            'term' => $request->input('term'),
            'currency' => $request->input('currency'),
            'amount' => $request->input('amount'),
        ]);

        $data = $this->loanService->createLoan($loanInput);

        return response()->json($data, 200);
    }

    public function approveLoanApplication(Request $request, string $loan_number)
    {
        return $this->loanService->approveLoan($loan_number);
    }

    public function getLoansByCustomer(Request $request)
    {
        $user_id = 'e441ef9e-b230-4596-b259-a7d1c81e91b0';

        return $this->loanService->getLoansByCustomer($user_id);
    }

    public function receivePaymentOnLoan(Request $request, string $loan_number)
    {
        $payment_input = json_encode([
            'loan_number' => $loan_number,
            'currency' => $request->input('currency'),
            'amount' => $request->input('amount'),
        ]);

        return $this->loanService->processRepayment($payment_input);
    }
}
