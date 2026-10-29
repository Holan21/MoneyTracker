<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(TransactionService::class)]
class TransactionServiceTest extends TestCase
{
    private TransactionService $service;

    protected function setUp(): void
    {
        $this->service = new TransactionService();
    }

    // -------------------------------------------------------------------------
    // validate()
    // -------------------------------------------------------------------------

    #[DataProvider('validDataProvider')]
    public function testValidateNormalizesData(array $input, array $expected): void
    {
        $result = $this->service->validate($input);

        $this->assertSame($expected['title'],       $result['title']);
        $this->assertSame($expected['amount'],      $result['amount']);
        $this->assertSame($expected['type'],        $result['type']);
        $this->assertSame($expected['category_id'], $result['category_id']);
    }

    public static function validDataProvider(): array
    {
        return [
            'income trimmed title' => [
                ['title' => '  Salary  ', 'amount' => '1000',  'type' => 'income',  'category_id' => 1, 'occurred_at' => '2026-08-01 09:00:00'],
                ['title' => 'Salary',     'amount' => 1000.0,  'type' => 'income',  'category_id' => 1],
            ],
            'expense decimal' => [
                ['title' => 'Food',       'amount' => '25.50', 'type' => 'expense', 'category_id' => 2, 'occurred_at' => '2026-08-02 12:00:00'],
                ['title' => 'Food',       'amount' => 25.5,    'type' => 'expense', 'category_id' => 2],
            ],
            'amount rounded to 2 decimals' => [
                ['title' => 'Coffee',     'amount' => '4.999', 'type' => 'expense', 'category_id' => 1, 'occurred_at' => '2026-08-03 08:00:00'],
                ['title' => 'Coffee',     'amount' => 5.0,     'type' => 'expense', 'category_id' => 1],
            ],
        ];
    }

    #[DataProvider('invalidDataProvider')]
    public function testValidateThrows(array $input, string $message): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage($message);
        $this->service->validate($input);
    }

    public static function invalidDataProvider(): array
    {
        return [
            'empty title' => [
                ['title' => '',     'amount' => '10', 'type' => 'expense', 'category_id' => 1],
                'Title is required',
            ],
            'title too short' => [
                ['title' => 'A',    'amount' => '10', 'type' => 'expense', 'category_id' => 1],
                'Title is too short',
            ],
            'title too long' => [
                ['title' => str_repeat('a', 201), 'amount' => '10', 'type' => 'expense', 'category_id' => 1],
                'Title is too long',
            ],
            'whitespace title' => [
                ['title' => '   ',  'amount' => '10', 'type' => 'expense', 'category_id' => 1],
                'Title is required',
            ],
            'negative amount' => [
                ['title' => 'Test', 'amount' => '-5', 'type' => 'expense', 'category_id' => 1],
                'Amount must be positive',
            ],
            'zero amount' => [
                ['title' => 'Test', 'amount' => '0',  'type' => 'expense', 'category_id' => 1],
                'Amount must be positive',
            ],
            'invalid type' => [
                ['title' => 'Test', 'amount' => '5',  'type' => 'transfer','category_id' => 1],
                'Type must be income or expense',
            ],
            'missing category' => [
                ['title' => 'Test', 'amount' => '5',  'type' => 'expense', 'category_id' => 0],
                'Category is required',
            ],
            'invalid date' => [
                ['title' => 'Test', 'amount' => '5',  'type' => 'expense', 'category_id' => 1, 'occurred_at' => 'not-a-date'],
                'Invalid date',
            ],
        ];
    }

    public function testValidateMissingDateDefaultsToCurrentTime(): void
    {
        $before = date('Y-m-d H:i:s');
        $result = $this->service->validate([
            'title'       => 'Test',
            'amount'      => '10',
            'type'        => 'expense',
            'category_id' => 1,
        ]);
        $after = date('Y-m-d H:i:s');

        $this->assertGreaterThanOrEqual($before, $result['occurred_at']);
        $this->assertLessThanOrEqual($after,     $result['occurred_at']);
    }

    // -------------------------------------------------------------------------
    // filter()
    // -------------------------------------------------------------------------

    private static function sampleTransactions(): array
    {
        return [
            ['title' => 'Salary',        'amount' => 1000, 'type' => 'income',  'category_id' => 1],
            ['title' => 'Food Delivery', 'amount' => 20,   'type' => 'expense', 'category_id' => 2],
            ['title' => 'Taxi',          'amount' => 8,    'type' => 'expense', 'category_id' => 3],
            ['title' => 'Freelance',     'amount' => 350,  'type' => 'income',  'category_id' => 1],
        ];
    }

    #[DataProvider('filterProvider')]
    public function testFilter(array $filters, int $expectedCount): void
    {
        $result = $this->service->filter(self::sampleTransactions(), $filters);
        $this->assertCount($expectedCount, $result);
    }

    public static function filterProvider(): array
    {
        return [
            'no filters'            => [[],                              4],
            'by income type'        => [['type' => 'income'],           2],
            'by expense type'       => [['type' => 'expense'],          2],
            'search by title'       => [['q' => 'sal'],                 1],
            'search case-insensitive' => [['q' => 'TAXI'],              1],
            'by category'           => [['category_id' => '2'],         1],
            'min amount'            => [['min_amount' => '100'],        2],
            'max amount'            => [['max_amount' => '20'],         2],
            'no match'              => [['q' => 'nothing'],             0],
        ];
    }

    public function testFilterCombinesAllCriteria(): void
    {
        $result = $this->service->filter(self::sampleTransactions(), [
            'q'           => 'food',
            'type'        => 'expense',
            'category_id' => '2',
            'min_amount'  => '10',
            'max_amount'  => '50',
        ]);

        $this->assertCount(1, $result);
        $this->assertSame('Food Delivery', $result[0]['title']);
    }

    public function testFilterReindexesResult(): void
    {
        $result = $this->service->filter(self::sampleTransactions(), ['type' => 'expense']);

        $this->assertArrayHasKey(0, $result);
        $this->assertArrayHasKey(1, $result);
        $this->assertArrayNotHasKey(2, $result);
    }

    // -------------------------------------------------------------------------
    // summarize()
    // -------------------------------------------------------------------------

    public function testSummarizeCalculatesCorrectly(): void
    {
        $items = [
            ['amount' => 1000, 'type' => 'income'],
            ['amount' => 200,  'type' => 'expense'],
            ['amount' => 50,   'type' => 'expense'],
        ];

        $result = $this->service->summarize($items);

        $this->assertSame(1000.0, $result['income']);
        $this->assertSame(250.0,  $result['expense']);
        $this->assertSame(750.0,  $result['balance']);
    }

    public function testSummarizeIncomeShare(): void
    {
        $items = [
            ['amount' => 75, 'type' => 'income'],
            ['amount' => 25, 'type' => 'expense'],
        ];

        $result = $this->service->summarize($items);

        $this->assertSame(75.0, $result['income_share']);
    }

    public function testSummarizeEmptyReturnsZeros(): void
    {
        $result = $this->service->summarize([]);

        $this->assertSame(0.0, $result['income']);
        $this->assertSame(0.0, $result['expense']);
        $this->assertSame(0.0, $result['balance']);
        $this->assertSame(0.0, $result['income_share']);
    }

    public function testSummarizeNegativeBalanceWhenExpenseExceedsIncome(): void
    {
        $items = [
            ['amount' => 100, 'type' => 'income'],
            ['amount' => 300, 'type' => 'expense'],
        ];

        $result = $this->service->summarize($items);

        $this->assertSame(-200.0, $result['balance']);
    }

    // -------------------------------------------------------------------------
    // recentByType()
    // -------------------------------------------------------------------------

    public function testRecentByTypeFiltersCorrectType(): void
    {
        $items = [
            ['type' => 'income',  'title' => 'A'],
            ['type' => 'expense', 'title' => 'B'],
            ['type' => 'income',  'title' => 'C'],
        ];

        $result = $this->service->recentByType($items, 'income', 10);

        $this->assertCount(2, $result);
        $this->assertSame('A', $result[0]['title']);
        $this->assertSame('C', $result[1]['title']);
    }

    public function testRecentByTypeRespectsLimit(): void
    {
        $items = array_fill(0, 10, ['type' => 'income', 'title' => 'x']);

        $result = $this->service->recentByType($items, 'income', 3);

        $this->assertCount(3, $result);
    }

    public function testRecentByTypeThrowsOnUnknownType(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Unknown transaction type');
        $this->service->recentByType([], 'transfer');
    }

    // -------------------------------------------------------------------------
    // assertCategoryMatches()
    // -------------------------------------------------------------------------

    public function testAssertCategoryMatchesAcceptsSameType(): void
    {
        $this->service->assertCategoryMatches(['id' => 1, 'type' => 'expense'], 'expense');
        $this->addToAssertionCount(1);
    }

    public function testAssertCategoryMatchesThrowsWhenMissing(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Category not found');
        $this->service->assertCategoryMatches(false, 'expense');
    }

    public function testAssertCategoryMatchesThrowsOnTypeMismatch(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Transaction type must match the category type');
        $this->service->assertCategoryMatches(['id' => 1, 'type' => 'income'], 'expense');
    }

    public function testFilterThrowsOnInvalidType(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Filter type must be income or expense');
        $this->service->filter(self::sampleTransactions(), ['type' => 'transfer']);
    }

    public function testFilterThrowsWhenMinGreaterThanMax(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Max amount must be greater than or equal to min amount');
        $this->service->filter(self::sampleTransactions(), [
            'min_amount' => '100',
            'max_amount' => '10',
        ]);
    }
}
