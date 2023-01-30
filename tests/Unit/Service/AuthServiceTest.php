<?php
namespace Tests\Unit\Services;

use App\Http\Services\AuthService;
use PHPUnit\Framework\TestCase;

class AuthServiceTest extends TestCase
{

    // Tests for registerUser sevice function
    public function testIfItCreateNewUser()
    {
         $authServiceStub  = $this->createStub(AuthService::class);
         $user_details = json_encode([
            'full_name' => 'John Doe',
            'email' => 'john@gmail.com',
            'phone_number' =>' 9876543210'
        ]);
        $token='9|9WVslpXRK9AzKG5AkvajBTXRTHIWiw7VCJKaNwuO';
        $user_type='CUSTOMER';

         $authServiceStub->method('registerUser')
              ->willReturn($token);
 
        $this->assertSame($token, $authServiceStub->registerUser($user_details,$user_type));

    }

    // Tests for verifyLogin sevice function
    public function testToVerifyTheUserLogin()
    {
        $request = new \Illuminate\Http\Request();
        $authServiceStub  = $this->createStub(AuthService::class);

        $token='9|9WVslpXRK9AzKG5AkvajBTXRTHIWiw7VCJKaNwuO';

        $authServiceStub->method('verifyLogin')
            ->willReturn($token);
    
        $this->assertSame($token, $authServiceStub->verifyLogin($request));

    }
}
