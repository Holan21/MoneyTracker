<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(TransactionValidator::class)]
class TransactionValidatorTest extends TestCase
{
    private TransactionValidator $validator;

    protected function setUp(): void
    {
        $this->validator = new TransactionValidator();
    }

    #[DataProvider('validProvider')]
    public function testValidateNormalizes(array $input, array $expected): void
    {
        $result = $this->validator->validate($input);
        $this->assertSame($expected['title'], $result['title']);
        $this->assertSame($expected['amount'], $result['amount']);
        $this->assertSame($expected['type'], $result['type']);
        $this->assertSame($expected['category_id'], $result['category_id']);
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $result['occurred_at']);
    }

    public static function validProvider(): array
    {
        return [
            'html datetime local' => [
                ['title' => '  Taxi  ', 'amount' => '8.1', 'type' => 'expense', 'category_id' => '3', 'occurred_at' => '2026-08-01T09:00'],
                ['title' => 'Taxi', 'amount' => 8.1, 'type' => 'expense', 'category_id' => 3],
            ],
            'sql datetime' => [
                ['title' => 'Pay', 'amount' => 100, 'type' => 'income', 'category_id' => 1, 'occurred_at' => '2026-08-01 09:00:00'],
                ['title' => 'Pay', 'amount' => 100.0, 'type' => 'income', 'category_id' => 1],
            ],
        ];
    }

    public function testCollectsMultipleErrors(): void
    {
        try {
            $this->validator->validate([
                'title'       => '',
                'amount'      => '-1',
                'type'        => 'transfer',
                'category_id' => 0,
                'occurred_at' => 'not-a-date',
            ]);
            $this->fail('Expected ValidationException');
        } catch (ValidationException $e) {
            $errors = $e->getErrors();
            $this->assertCount(5, $errors);
            $this->assertSame('Title is required', $errors['title']);
            $this->assertSame('Amount must be positive', $errors['amount']);
            $this->assertSame('Type must be income or expense', $errors['type']);
            $this->assertSame('Category is required', $errors['category_id']);
            $this->assertSame('Invalid date', $errors['occurred_at']);
        }
    }

    public function testAmountTooLargeThrows(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Amount is too large');
        $this->validator->validate([
            'title'       => 'Big',
            'amount'      => '100000000',
            'type'        => 'income',
            'category_id' => 1,
        ]);
    }
}
