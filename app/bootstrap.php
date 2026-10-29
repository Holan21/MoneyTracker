<?php

declare(strict_types=1);

spl_autoload_register(static function (string $class): void {
    $roots = [
        __DIR__ . '/Core/',
        __DIR__ . '/Models/',
        __DIR__ . '/Repositories/',
        __DIR__ . '/Controllers/',
        __DIR__ . '/Services/',
        __DIR__ . '/Validators/',
        __DIR__ . '/Exceptions/',
        __DIR__ . '/Modules/SecureUpload/',
    ];

    foreach ($roots as $root) {
        $file = $root . $class . '.php';
        if (is_file($file)) {
            require_once $file;
            return;
        }
    }
});

function config(?string $key = null): mixed
{
    static $config;
    if ($config === null) {
        $config = require dirname(__DIR__) . '/config/config.php';
    }
    if ($key === null) {
        return $config;
    }
    return $config[$key] ?? null;
}
