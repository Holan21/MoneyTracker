<?php

declare(strict_types=1);

class ValidationException extends Exception
{
    /** @var array<string, string> */
    private array $errors;

    /**
     * @param array<string, string> $errors
     */
    public function __construct(string $message = '', array $errors = [])
    {
        if ($message === '' && $errors !== []) {
            $message = (string)reset($errors);
        }
        parent::__construct($message);
        $this->errors = $errors;
    }

    /** @return array<string, string> */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
