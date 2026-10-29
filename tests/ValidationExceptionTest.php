<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ValidationException::class)]
class ValidationExceptionTest extends TestCase
{
    public function testUsesFirstFieldErrorAsMessageWhenEmpty(): void
    {
        $e = new ValidationException('', ['title' => 'Title is required', 'amount' => 'Amount must be positive']);
        $this->assertSame('Title is required', $e->getMessage());
        $this->assertSame('Amount must be positive', $e->getErrors()['amount']);
    }

    public function testKeepsExplicitMessage(): void
    {
        $e = new ValidationException('Invalid credentials', ['login' => 'Invalid credentials']);
        $this->assertSame('Invalid credentials', $e->getMessage());
        $this->assertSame(['login' => 'Invalid credentials'], $e->getErrors());
    }
}
