<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(CategoryValidator::class)]
class CategoryValidatorTest extends TestCase
{
    private CategoryValidator $validator;

    protected function setUp(): void
    {
        $this->validator = new CategoryValidator();
    }

    #[DataProvider('validProvider')]
    public function testValidateAccepts(array $input, array $expected): void
    {
        $this->assertSame($expected, $this->validator->validate($input));
    }

    public static function validProvider(): array
    {
        return [
            'trimmed expense' => [
                ['name' => ' Food ', 'type' => 'expense', 'description' => '  groc  '],
                ['name' => 'Food', 'type' => 'expense', 'description' => 'groc'],
            ],
            'income empty description' => [
                ['name' => 'Salary', 'type' => 'income'],
                ['name' => 'Salary', 'type' => 'income', 'description' => ''],
            ],
        ];
    }

    public function testCollectsMultipleErrors(): void
    {
        try {
            $this->validator->validate([
                'name'        => '',
                'type'        => 'other',
                'description' => str_repeat('x', 501),
            ]);
            $this->fail('Expected ValidationException');
        } catch (ValidationException $e) {
            $errors = $e->getErrors();
            $this->assertSame('Category name is required', $errors['name']);
            $this->assertSame('Category type must be income or expense', $errors['type']);
            $this->assertSame('Description is too long', $errors['description']);
        }
    }

    public function testDoesNotSilentlyTruncateLongName(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Category name is too long');
        $this->validator->validate(['name' => str_repeat('n', 121), 'type' => 'income']);
    }
}
