<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(AuthValidator::class)]
class AuthValidatorTest extends TestCase
{
    private AuthValidator $validator;

    protected function setUp(): void
    {
        $this->validator = new AuthValidator();
    }

    public function testLoginCollectsBothFieldErrors(): void
    {
        try {
            $this->validator->login(['login' => '', 'password' => '']);
            $this->fail('Expected ValidationException');
        } catch (ValidationException $e) {
            $this->assertSame('Login and password are required', $e->getMessage());
            $this->assertArrayHasKey('login', $e->getErrors());
            $this->assertArrayHasKey('password', $e->getErrors());
        }
    }

    #[DataProvider('validLoginProvider')]
    public function testLoginAccepts(array $input, string $expectedLogin): void
    {
        $result = $this->validator->login($input);
        $this->assertSame($expectedLogin, $result['login']);
        $this->assertSame($input['password'], $result['password']);
    }

    public static function validLoginProvider(): array
    {
        return [
            'plain' => [['login' => 'admin', 'password' => 'secret'], 'admin'],
            'trim'  => [['login' => '  user  ', 'password' => 'x'], 'user'],
        ];
    }

    public function testRegisterCollectsMultipleErrors(): void
    {
        try {
            $this->validator->register([
                'name'             => 'A',
                'login'            => 'ab',
                'password'         => 'abc',
                'password_confirm' => 'zzz',
            ]);
            $this->fail('Expected ValidationException');
        } catch (ValidationException $e) {
            $errors = $e->getErrors();
            $this->assertSame('Name is too short', $errors['name']);
            $this->assertSame('Login must be 3-32 letters, numbers or ._-', $errors['login']);
            $this->assertSame('Password must be at least 4 characters', $errors['password']);
            $this->assertSame('Passwords do not match', $errors['password_confirm']);
        }
    }
}
