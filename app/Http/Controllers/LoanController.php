<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Services\LoanService;

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
            'amount' => $request->input('amount')
        ]);

        $data = $this->loanService->createLoan($loanInput);

        return response()->json($data, 200);
    }

    public function approveLoanApplication(Request $request, String $loan_number)
    {
        return $this->loanService->approveLoan($loan_number);
    }
}
