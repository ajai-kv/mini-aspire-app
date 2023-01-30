<?php

namespace App\Http\Controllers\Api;

use App\Enums\UserType;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Services\AuthService;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Create User
     * @param Request $request
     * @return User 
     */
    private $authService;

    public function __construct()
    {
        $this->authService = new AuthService();
    }

    public function createAdminUser(Request $request)
    {
        try {
            $validateUser = Validator::make(
                $request->all(),
                [
                    'full_name' => 'required',
                    'email' => 'required|email|unique:user,email',
                    'password' => 'required',
                    'phone_number' => 'required|unique:user,phone_number',
                ]
            );

            if ($validateUser->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validateUser->errors()
                ], 400);
            }

            $user_details = json_encode([
                'full_name' => $request->full_name,
                'email' => $request->email,
                'password' => $request->password,
                'phone_number' => $request->phone_number
            ]);

            $user = json_decode($this->authService->registerUser($user_details, UserType::ADMIN->value));

            return response()->json([
                'status' => true,
                'message' => 'User Created Successfully',
                'token' => $user->token
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function createCustomerUser(Request $request)
    {
        try {
            $validateUser = Validator::make(
                $request->all(),
                [
                    'full_name' => 'required',
                    'email' => 'required|email|unique:user,email',
                    'password' => 'required',
                    'phone_number' => 'required|unique:user,phone_number',
                    'date_of_birth' => 'required',
                    'address' => 'required',
                    'identification_document' => 'required',
                    'document_number' => 'required',
                ]
            );
            

            if ($validateUser->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validateUser->errors()
                ], 400);
            }


            $user_details = json_encode([
                'full_name' => $request->full_name,
                'email' => $request->email,
                'password' => $request->password,
                'phone_number' => $request->phone_number,
                'date_of_birth' => $request->date_of_birth,
                'address' => $request->address,
                'identification_document' => $request->identification_document,
                'document_number' => $request->document_number,
            ]);

            $user = json_decode($this->authService->registerUser($user_details, UserType::CUSTOMER->value));

            return response()->json([
                'status' => true,
                'message' => 'User Created Successfully',
                'token' => $user->token
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Login The User
     * @param Request $request
     * @return User
     */
    public function loginUser(Request $request)
    {
        try {
            $validateUser = Validator::make(
                $request->all(),
                [
                    'email' => 'required|email',
                    'password' => 'required'
                ]
            );

            if ($validateUser->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validateUser->errors()
                ], 400);
            }

            $user = json_decode($this->authService->verifyLogin($request));
            return response()->json([
                'status' => true,
                'message' => 'User Logged In Successfully',
                'token' => $user->token
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 401);
        }
    }
}
