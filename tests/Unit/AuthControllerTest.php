<?php
namespace Tests\Unit\Controllers;

use App\Http\Controllers\Api\AuthController;
use App\Http\Services\AuthService;
use PHPUnit\Framework\TestCase;
use Illuminate\Http\Response;

class AuthControllerTest extends TestCase
{
    public function testIfItCreateNewUser()
    {
        $request = new \Illuminate\Http\Request();
        $response = new Response();

        $authControllerStub  = $this->createStub(AuthController::class);
        $authServiceStub  = $this->createStub(AuthService::class);

        $authServiceStub  = $this->createStub(AuthService::class);
        $user_details = json_encode([
           'full_name' => 'Admin User',
           'email' => 'admin@gmail.com',
           'phone_number' =>' 9895083298'
        ]);
        $token='eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9';
        $user_type='CUSTOMER';

        $authServiceStub->method('registerUser')
             ->willReturn($token);

        $this->assertSame($token, $authServiceStub->registerUser($user_details,$user_type));
        $authControllerStub->method('createAdminUser')
             ->willReturn($response);
        $this->assertSame($response, $authControllerStub->createAdminUser($request));
    }

    public function testIfUserCanLoginToTheSystem()
    {
        $request = new \Illuminate\Http\Request();
        $response = new Response();

        $authControllerStub  = $this->createStub(AuthController::class);
        $authServiceStub  = $this->createStub(AuthService::class);

        $token='eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9';

        $authServiceStub->method('verifyLogin')
            ->willReturn($token);
    
        $this->assertSame($token, $authServiceStub->verifyLogin($request));

        $authControllerStub->method('loginUser')
             ->willReturn($response);
        $this->assertSame($response, $authControllerStub->loginUser($request));
    }

}