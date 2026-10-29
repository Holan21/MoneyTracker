<?php

declare(strict_types=1);

class TextStorage
{
    public static function path(): string
    {
        return (string)config('text_file');
    }

    public static function read(): array
    {
        $file = self::path();
        if (!is_file($file)) {
            return ['categories' => [], 'transactions' => []];
        }

        $raw  = file_get_contents($file);
        $data = json_decode($raw ?: '', true);
        if (!is_array($data)) {
            return ['categories' => [], 'transactions' => []];
        }

        return [
            'categories'   => $data['categories']   ?? [],
            'transactions' => $data['transactions'] ?? [],
        ];
    }

    public static function write(array $payload): void
    {
        $file = self::path();
        $dir  = dirname($file);
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $json = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        if ($json === false) {
            throw new RuntimeException('Cannot encode text storage');
        }

        file_put_contents($file, $json, LOCK_EX);
    }

    public static function sync(): void
    {
        $categories   = (new CategoryRepository())->all();
        $transactions = (new TransactionRepository())->all();
        self::write([
            'synced_at'    => date('c'),
            'categories'   => $categories,
            'transactions' => $transactions,
        ]);
    }
}
