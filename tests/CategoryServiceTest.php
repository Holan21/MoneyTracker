<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(CategoryService::class)]
class CategoryServiceTest extends TestCase
{
    private CategoryService $service;

    protected function setUp(): void
    {
        $this->service = new CategoryService();
    }

    // -------------------------------------------------------------------------
    // validate()
    // -------------------------------------------------------------------------

    #[DataProvider('validProvider')]
    public function testValidateNormalizesData(array $input, string $expectedName, string $expectedType): void
    {
        $result = $this->service->validate($input);

        $this->assertSame($expectedName, $result['name']);
        $this->assertSame($expectedType, $result['type']);
        $this->assertArrayHasKey('description', $result);
    }

    public static function validProvider(): array
    {
        return [
            'expense trimmed name' => [['name' => ' Food ',  'type' => 'expense', 'description' => 'x'], 'Food',   'expense'],
            'income no description'=> [['name' => 'Salary',  'type' => 'income',  'description' => ''], 'Salary', 'income'],
        ];
    }

    public function testValidateEmptyDescriptionDefaultsToEmptyString(): void
    {
        $result = $this->service->validate(['name' => 'Food', 'type' => 'expense']);
        $this->assertSame('', $result['description']);
    }

    #[DataProvider('invalidProvider')]
    public function testValidateThrows(array $input, string $message): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage($message);
        $this->service->validate($input);
    }

    public static function invalidProvider(): array
    {
        return [
            'empty name'  => [['name' => '',    'type' => 'expense'], 'Category name is required'],
            'spaces only' => [['name' => '   ', 'type' => 'expense'], 'Category name is required'],
            'short name'  => [['name' => 'A',   'type' => 'expense'], 'Category name is too short'],
            'wrong type'  => [['name' => 'Food','type' => 'other'],   'Category type must be income or expense'],
            'missing type'=> [['name' => 'Food'],                     'Category type must be income or expense'],
            'name too long' => [['name' => str_repeat('a', 121), 'type' => 'expense'], 'Category name is too long'],
        ];
    }

    // -------------------------------------------------------------------------
    // filter()
    // -------------------------------------------------------------------------

    private static function sampleCategories(): array
    {
        return [
            ['id' => 1, 'name' => 'Salary',   'type' => 'income',  'description' => 'Monthly pay'],
            ['id' => 2, 'name' => 'Freelance', 'type' => 'income',  'description' => 'Side work'],
            ['id' => 3, 'name' => 'Food',      'type' => 'expense', 'description' => 'Groceries'],
            ['id' => 4, 'name' => 'Transport', 'type' => 'expense', 'description' => 'Tickets'],
        ];
    }

    #[DataProvider('filterProvider')]
    public function testFilterReturnsCorrectCount(array $filters, int $expectedCount): void
    {
        $result = $this->service->filter(self::sampleCategories(), $filters);
        $this->assertCount($expectedCount, $result);
    }

    public static function filterProvider(): array
    {
        return [
            'no filters'              => [[],                      4],
            'by income type'          => [['type' => 'income'],    2],
            'by expense type'         => [['type' => 'expense'],   2],
            'search partial'          => [['q' => 'sal'],          1],
            'search case-insensitive' => [['q' => 'FOOD'],         1],
            'search no match'         => [['q' => 'missing'],      0],
        ];
    }

    public function testFilterCombinesQueryAndType(): void
    {
        $result = $this->service->filter(self::sampleCategories(), [
            'q'    => 'free',
            'type' => 'income',
        ]);

        $this->assertCount(1, $result);
        $this->assertSame('Freelance', $result[0]['name']);
    }

    public function testFilterQueryMatchesButTypeMismatchReturnsEmpty(): void
    {
        $result = $this->service->filter(self::sampleCategories(), [
            'q'    => 'salary',
            'type' => 'expense',
        ]);

        $this->assertCount(0, $result);
    }

    public function testFilterPreservesAllFieldsOfMatchedItem(): void
    {
        $result = $this->service->filter(self::sampleCategories(), ['q' => 'transport']);

        $this->assertCount(1, $result);
        $this->assertSame(4,           $result[0]['id']);
        $this->assertSame('Transport', $result[0]['name']);
        $this->assertSame('expense',   $result[0]['type']);
        $this->assertSame('Tickets',   $result[0]['description']);
    }

    public function testFilterReindexesResultFromZero(): void
    {
        $result = $this->service->filter(self::sampleCategories(), ['type' => 'expense']);

        $this->assertArrayHasKey(0, $result);
        $this->assertArrayHasKey(1, $result);
        $this->assertArrayNotHasKey(2, $result);
    }

    public function testFilterEmptyInputReturnsEmptyArray(): void
    {
        $result = $this->service->filter([], ['q' => 'anything']);
        $this->assertSame([], $result);
    }

    public function testFilterThrowsOnInvalidType(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Filter type must be income or expense');
        $this->service->filter(self::sampleCategories(), ['type' => 'other']);
    }
}
