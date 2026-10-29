<?php

declare(strict_types=1);

class ValidationService
{
    public static function required(string $value): bool
    {
        return trim($value) !== '';
    }

    public static function positiveNumber(mixed $value): bool
    {
        return is_numeric($value) && (float)$value > 0;
    }

    public static function inList(mixed $value, array $allowed): bool
    {
        return is_string($value) && in_array($value, $allowed, true);
    }

    public static function minLength(string $value, int $min): bool
    {
        return mb_strlen(trim($value)) >= $min;
    }

    public static function maxLength(string $value, int $max): bool
    {
        return mb_strlen(trim($value)) <= $max;
    }

    public static function loginFormat(string $value): bool
    {
        return (bool)preg_match('/^[a-zA-Z0-9._-]{3,32}$/', $value);
    }

    public static function sanitizeString(string $value, int $max = 200): string
    {
        $value = trim($value);
        if (mb_strlen($value) > $max) {
            $value = mb_substr($value, 0, $max);
        }
        return $value;
    }
}
