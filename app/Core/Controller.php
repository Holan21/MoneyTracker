<?php

declare(strict_types=1);

class Controller
{
    public function render(string $view, array $data = []): void
    {
        $layout = $data['layout'] ?? 'app';
        unset($data['layout']);
        $data['viewFile'] = __DIR__ . '/../Views/' . $view . '.php';
        $data['user'] = $data['user'] ?? Auth::user();
        $data['isAdmin'] = Auth::isAdmin();
        extract($data, EXTR_SKIP);
        require __DIR__ . '/../Views/layouts/' . $layout . '.php';
    }

    protected function view(string $path, array $data = []): void
    {
        $this->render($path, $data);
    }

    protected function redirect(string $path): void
    {
        header('Location: ' . Url::to($path));
        exit;
    }

    protected function param(array $params, string $key, int $default = 0): int
    {
        return isset($params[$key]) ? (int)$params[$key] : $default;
    }
}
