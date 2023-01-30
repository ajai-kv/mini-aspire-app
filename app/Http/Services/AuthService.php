<?php

namespace App\Http\Services;

use App\Enums\UserType;
use App\Models\Customer;
use App\Models\User;
use Error;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\UnauthorizedException;

class AuthService
{
    public function registerUser($user_details, $user_type)
    {
        try {
            $user_details = json_decode($user_details);

            DB::beginTransaction();

            $user = User::create([
                'full_name' => $user_details->full_name,
                'email' => $user_details->email,
                'password' => Hash::make($user_details->password),
                'phone_number' => $user_details->phone_number,
                'type' => $user_type,
            ]);

            $user->refresh();

            if ($user_type === UserType::CUSTOMER->value) {
                Customer::create([
                    'user_id' => $user->id,
                    'date_of_birth' => $user_details->date_of_birth,
                    'address' => $user_details->address,
                    'identification_document' => $user_details->identification_document,
                    'document_reference_number' => $user_details->document_number
                ]);
            }

            DB::commit();

            return json_encode([
                'token' => $user->createToken("API TOKEN")->plainTextToken
            ]);
        } catch (\Throwable $t) {
            DB::rollback();
            throw $t;
        }
    }

    public function verifyLogin(Request $request)
    {
        try {
            if (!Auth::attempt($request->only(['email', 'password']))) {
                throw new Error('Email & Password does not match with our record');
            }

            $user = User::where('email', $request->email)
                ->where('is_active', 'true')
                ->first();

            if (!$user) {
                throw new Error('User not found. Please contact support');
            }

            return json_encode([
                'token' => $user->createToken("API TOKEN")->plainTextToken
            ]);
        } catch (Exception $e) {
            throw new UnauthorizedException($e);
        }
    }
}
