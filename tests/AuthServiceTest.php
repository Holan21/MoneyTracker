<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(AuthService::class)]
class AuthServiceTest extends TestCase
{
    private AuthService $service;

    protected function setUp(): void
    {
        $this->service = new AuthService();
    }

    // -------------------------------------------------------------------------
    // validateLogin()
    // -------------------------------------------------------------------------

    public function testValidateLoginReturnsCredentials(): void
    {
        $result = $this->service->validateLogin([
            'login'    => 'admin',
            'password' => 'secret',
        ]);

        $this->assertSame('admin', $result['login']);
        $this->assertSame('secret', $result['password']);
    }

    public function testValidateLoginTrimsLogin(): void
    {
        $result = $this->service->validateLogin([
            'login'    => '  admin  ',
            'password' => 'secret',
        ]);

        $this->assertSame('admin', $result['login']);
    }

    #[DataProvider('invalidLoginProvider')]
    public function testValidateLoginThrows(array $input): void
    {
        $this->expectException(ValidationException::class);
        $this->service->validateLogin($input);
    }

    public static function invalidLoginProvider(): array
    {
        return [
            'empty login'    => [['login' => '',      'password' => 'x']],
            'empty password' => [['login' => 'admin', 'password' => '']],
            'both empty'     => [['login' => '',      'password' => '']],
            'missing keys'   => [[]],
        ];
    }

    // -------------------------------------------------------------------------
    // validateRegister()
    // -------------------------------------------------------------------------

    public function testValidateRegisterReturnsNormalizedData(): void
    {
        $result = $this->service->validateRegister([
            'name'             => '  Zaun  ',
            'login'            => 'zaun01',
            'password'         => 'pass',
            'password_confirm' => 'pass',
        ]);

        $this->assertSame('Zaun',   $result['name']);
        $this->assertSame('zaun01', $result['login']);
        $this->assertSame('pass',   $result['password']);
    }

    #[DataProvider('invalidRegisterProvider')]
    public function testValidateRegisterThrows(array $input, string $message): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage($message);
        $this->service->validateRegister($input);
    }

    public static function invalidRegisterProvider(): array
    {
        return [
            'name too short' => [
                ['name' => 'A', 'login' => 'user1', 'password' => 'pass', 'password_confirm' => 'pass'],
                'Name is too short',
            ],
            'login too short' => [
                ['name' => 'User', 'login' => 'ab', 'password' => 'pass', 'password_confirm' => 'pass'],
                'Login must be 3-32 letters, numbers or ._-',
            ],
            'login with spaces' => [
                ['name' => 'User', 'login' => 'bad login', 'password' => 'pass', 'password_confirm' => 'pass'],
                'Login must be 3-32 letters, numbers or ._-',
            ],
            'password too short' => [
                ['name' => 'User', 'login' => 'user1', 'password' => 'abc', 'password_confirm' => 'abc'],
                'Password must be at least 4 characters',
            ],
            'passwords mismatch' => [
                ['name' => 'User', 'login' => 'user1', 'password' => 'pass', 'password_confirm' => 'other'],
                'Passwords do not match',
            ],
            'name required' => [
                ['name' => '  ', 'login' => 'user1', 'password' => 'pass', 'password_confirm' => 'pass'],
                'Name is required',
            ],
            'name too long' => [
                ['name' => str_repeat('a', 101), 'login' => 'user1', 'password' => 'pass', 'password_confirm' => 'pass'],
                'Name is too long',
            ],
        ];
    }

    // -------------------------------------------------------------------------
    // verifyPassword()
    // -------------------------------------------------------------------------

    public function testVerifyPasswordCorrect(): void
    {
        $hash = password_hash('secret', PASSWORD_DEFAULT);
        $this->assertTrue($this->service->verifyPassword('secret', $hash));
    }

    public function testVerifyPasswordIncorrect(): void
    {
        $hash = password_hash('secret', PASSWORD_DEFAULT);
        $this->assertFalse($this->service->verifyPassword('wrong', $hash));
    }

    public function testVerifyPasswordIsCaseSensitive(): void
    {
        $hash = password_hash('Secret', PASSWORD_DEFAULT);
        $this->assertFalse($this->service->verifyPassword('secret', $hash));
    }
}
