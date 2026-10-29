<?php

declare(strict_types=1);

class Url
{
    public static function base(): string
    {
        $script = str_replace('\\', '/', (string)($_SERVER['SCRIPT_NAME'] ?? '/index.php'));
        $uri    = str_replace('\\', '/', parse_url((string)($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH) ?: '/');

        $dir = rtrim(dirname($script), '/');

        while ($dir !== '' && $dir !== '/') {
            if (str_starts_with($uri, $dir . '/') || $uri === $dir) {
                return $dir;
            }
            $parent = rtrim(dirname($dir), '/');
            if ($parent === $dir) {
                break;
            }
            $dir = $parent;
        }

        return '';
    }

    public static function to(string $path): string
    {
        $path = '/' . ltrim($path, '/');
        if ($path === '/') {
            return self::base() === '' ? '/' : self::base() . '/';
        }
        return self::base() . $path;
    }
}
