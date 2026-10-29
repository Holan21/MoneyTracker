<?php

declare(strict_types=1);

/**
 * Model-layer validator: collects field errors and throws ValidationException.
 * Controllers must not contain validation rules.
 */
abstract class Validator
{
    /** @var array<string, string> */
    protected array $errors = [];

    protected function addError(string $field, string $message): void
    {
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = $message;
        }
    }

    protected function failIfErrors(): void
    {
        if ($this->errors !== []) {
            throw new ValidationException((string)reset($this->errors), $this->errors);
        }
    }
}
