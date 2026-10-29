<?php

declare(strict_types=1);

return [
    'db' => [
        'host' => 'localhost',
        'dbname' => 'finance_app',
        'user' => 'root',
        'pass' => 'root',
        'charset' => 'utf8mb4',
    ],
    'upload_dir' => dirname(__DIR__) . '/storage/uploads',
    'text_file' => dirname(__DIR__) . '/storage/data.txt',
];
