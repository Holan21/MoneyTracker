<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(FilterValidator::class)]
class FilterValidatorTest extends TestCase
{
    private FilterValidator $validator;

    protected function setUp(): void
    {
        $this->validator = new FilterValidator();
    }

    #[DataProvider('categoryFilterProvider')]
    public function testCategoriesAccepts(array $input, array $expected): void
    {
        $this->assertSame($expected, $this->validator->categories($input));
    }

    public static function categoryFilterProvider(): array
    {
        return [
            'empty'   => [[], ['q' => '', 'type' => '']],
            'trimmed' => [['q' => '  food  ', 'type' => 'expense'], ['q' => 'food', 'type' => 'expense']],
        ];
    }

    #[DataProvider('invalidCategoryFilterProvider')]
    public function testCategoriesThrows(array $input, string $message): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage($message);
        $this->validator->categories($input);
    }

    public static function invalidCategoryFilterProvider(): array
    {
        return [
            'bad type' => [['type' => 'other'], 'Filter type must be income or expense'],
            'long q'   => [['q' => str_repeat('a', 101)], 'Search query is too long'],
        ];
    }

    #[DataProvider('transactionFilterProvider')]
    public function testTransactionsAccepts(array $input, array $expected): void
    {
        $this->assertSame($expected, $this->validator->transactions($input));
    }

    public static function transactionFilterProvider(): array
    {
        return [
            'empty' => [[], [
                'q' => '', 'type' => '', 'category_id' => '', 'min_amount' => '', 'max_amount' => '',
            ]],
            'range' => [[
                'q' => 'taxi', 'type' => 'expense', 'category_id' => '2', 'min_amount' => '1', 'max_amount' => '10',
            ], [
                'q' => 'taxi', 'type' => 'expense', 'category_id' => '2', 'min_amount' => '1', 'max_amount' => '10',
            ]],
        ];
    }

    #[DataProvider('invalidTransactionFilterProvider')]
    public function testTransactionsThrows(array $input, string $message): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage($message);
        $this->validator->transactions($input);
    }

    public static function invalidTransactionFilterProvider(): array
    {
        return [
            'bad category' => [['category_id' => '-1'], 'Filter category is invalid'],
            'non numeric min' => [['min_amount' => 'abc'], 'Min amount must be a number'],
            'non numeric max' => [['max_amount' => 'x'], 'Max amount must be a number'],
            'min greater max' => [['min_amount' => '50', 'max_amount' => '10'], 'Max amount must be greater than or equal to min amount'],
        ];
    }
}
