<?php

declare(strict_types=1);

class Router
{
    private array $routes = [];

    public function get(string $path, array $handler): void
    {
        $this->routes['GET'][$this->normalize($path)] = $handler;
    }

    public function post(string $path, array $handler): void
    {
        $this->routes['POST'][$this->normalize($path)] = $handler;
    }

    public function dispatch(string $uri, string $method): void
    {
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        $base = Url::base();
        if ($base !== '' && str_starts_with($path, $base)) {
            $path = substr($path, strlen($base)) ?: '/';
        }
        $path = $this->normalize($path);
        $method = strtoupper($method);

        try {
            foreach ($this->routes[$method] ?? [] as $pattern => $handler) {
                $params = $this->match($pattern, $path);
                if ($params === null) {
                    continue;
                }

                [$class, $action] = $handler;
                $controller = new $class();
                $controller->$action($params);
                return;
            }

            http_response_code(404);
            (new Controller())->render('errors/404', [
                'title' => '404',
                'layout' => 'auth',
            ]);
        } catch (PDOException $e) {
            http_response_code(503);
            (new Controller())->render('errors/503', [
                'title' => '503 — Database unavailable',
                'layout' => 'auth',
            ]);
        }
    }

    private function match(string $pattern, string $path): ?array
    {
        $regex = preg_replace('#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#', '(?P<$1>[^/]+)', $pattern);
        if (!preg_match('#^' . $regex . '$#', $path, $matches)) {
            return null;
        }

        return array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
    }

    private function normalize(string $path): string
    {
        $path = '/' . trim($path, '/');
        return $path === '/' ? '/' : rtrim($path, '/');
    }
}
