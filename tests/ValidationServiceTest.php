<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(ValidationService::class)]
class ValidationServiceTest extends TestCase
{
    // -------------------------------------------------------------------------
    // required()
    // -------------------------------------------------------------------------

    #[DataProvider('requiredProvider')]
    public function testRequired(string $value, bool $expected): void
    {
        $this->assertSame($expected, ValidationService::required($value));
    }

    public static function requiredProvider(): array
    {
        return [
            'normal string'    => ['Salary', true],
            'only spaces'      => ['   ',    false],
            'empty string'     => ['',       false],
            'single char'      => ['x',      true],
            'spaces then text' => [' hi ',   true],
        ];
    }

    // -------------------------------------------------------------------------
    // positiveNumber()
    // -------------------------------------------------------------------------

    #[DataProvider('positiveNumberProvider')]
    public function testPositiveNumber(mixed $value, bool $expected): void
    {
        $this->assertSame($expected, ValidationService::positiveNumber($value));
    }

    public static function positiveNumberProvider(): array
    {
        return [
            'positive int'      => [10,      true],
            'positive float'    => [0.01,    true],
            'float string'      => ['12.5',  true],
            'string int'        => ['1',     true],
            'zero int'          => [0,       false],
            'zero float'        => [0.0,     false],
            'negative int'      => [-1,      false],
            'negative string'   => ['-5',    false],
            'plain text'        => ['abc',   false],
            'null'              => [null,    false],
            'empty string'      => ['',      false],
        ];
    }

    // -------------------------------------------------------------------------
    // minLength()
    // -------------------------------------------------------------------------

    #[DataProvider('minLengthProvider')]
    public function testMinLength(string $value, int $min, bool $expected): void
    {
        $this->assertSame($expected, ValidationService::minLength($value, $min));
    }

    public static function minLengthProvider(): array
    {
        return [
            'exact boundary'     => ['ab',   2, true],
            'over minimum'       => ['abc',  2, true],
            'below minimum'      => ['a',    2, false],
            'empty vs 1'         => ['',     1, false],
            'spaces trimmed'     => ['  a ', 2, false],
            'unicode chars'      => ['привіт', 3, true],
        ];
    }

    // -------------------------------------------------------------------------
    // maxLength()
    // -------------------------------------------------------------------------

    #[DataProvider('maxLengthProvider')]
    public function testMaxLength(string $value, int $max, bool $expected): void
    {
        $this->assertSame($expected, ValidationService::maxLength($value, $max));
    }

    public static function maxLengthProvider(): array
    {
        return [
            'under max'      => ['ab', 2, true],
            'exact max'      => ['abc', 3, true],
            'over max'       => ['abcd', 3, false],
            'empty allowed'  => ['', 1, true],
            'spaces trimmed' => ['  ab  ', 2, true],
            'unicode over'   => ['привіт', 3, false],
        ];
    }

    // -------------------------------------------------------------------------
    // loginFormat()
    // -------------------------------------------------------------------------

    #[DataProvider('loginFormatProvider')]
    public function testLoginFormat(string $value, bool $expected): void
    {
        $this->assertSame($expected, ValidationService::loginFormat($value));
    }

    public static function loginFormatProvider(): array
    {
        return [
            'valid alphanumeric'  => ['user01',      true],
            'valid with dots'     => ['user.name',   true],
            'valid with dash'     => ['user-name',   true],
            'valid with underscore' => ['user_name', true],
            'too short (2)'       => ['ab',          false],
            'too long (33)'       => [str_repeat('a', 33), false],
            'has space'           => ['bad login',   false],
            'has @'               => ['user@mail',   false],
            'empty'               => ['',            false],
            'exact min (3)'       => ['abc',         true],
            'exact max (32)'      => [str_repeat('a', 32), true],
        ];
    }

    // -------------------------------------------------------------------------
    // sanitizeString()
    // -------------------------------------------------------------------------

    #[DataProvider('sanitizeProvider')]
    public function testSanitizeString(string $value, int $max, string $expected): void
    {
        $this->assertSame($expected, ValidationService::sanitizeString($value, $max));
    }

    public static function sanitizeProvider(): array
    {
        return [
            'trims spaces'         => ['  Hello  ',   20, 'Hello'],
            'truncates at max'     => ['abcdef',        4, 'abcd'],
            'exact max no cut'     => ['abcd',          4, 'abcd'],
            'unicode trim'         => ['  привіт  ',   20, 'привіт'],
            'unicode truncate'     => ['привіт',        3, 'при'],
            'empty string'         => ['',             10, ''],
        ];
    }

    // -------------------------------------------------------------------------
    // inList()
    // -------------------------------------------------------------------------

    #[DataProvider('inListProvider')]
    public function testInList(mixed $value, array $allowed, bool $expected): void
    {
        $this->assertSame($expected, ValidationService::inList($value, $allowed));
    }

    public static function inListProvider(): array
    {
        return [
            'value present'          => ['income',  ['income', 'expense'], true],
            'second value present'   => ['expense', ['income', 'expense'], true],
            'value absent'           => ['other',   ['income', 'expense'], false],
            'case sensitive mismatch'=> ['Income',  ['income', 'expense'], false],
            'empty list'             => ['income',  [],                    false],
            'non-string value'       => [1,         ['1', '2'],            false],
        ];
    }
}
