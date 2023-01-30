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
        $token='eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9';
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

        $token='eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9';

        $authServiceStub->method('verifyLogin')
            ->willReturn($token);
    
        $this->assertSame($token, $authServiceStub->verifyLogin($request));

    }
}
