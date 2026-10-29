<?php

declare(strict_types=1);

class FileController extends Controller
{
    public function show(array $params = []): void
    {
        Auth::requireLogin();

        $name = basename((string)($params['file'] ?? ''));
        if (!(new FileUploadService())->isSafeStoredName($name)) {
            http_response_code(404);
            $this->view('errors/404', ['title' => '404', 'layout' => 'auth']);
            return;
        }

        $path = rtrim((string)config('upload_dir'), '/\\') . DIRECTORY_SEPARATOR . $name;
        if (!is_file($path)) {
            http_response_code(404);
            $this->view('errors/404', ['title' => '404', 'layout' => 'auth']);
            return;
        }

        $mime = 'application/octet-stream';
        if (class_exists('finfo')) {
            $detected = (new finfo(FILEINFO_MIME_TYPE))->file($path);
            if (is_string($detected) && $detected !== '') {
                $mime = $detected;
            }
        }

        header('Content-Type: ' . $mime);
        header('Content-Length: ' . (string)filesize($path));
        header('X-Content-Type-Options: nosniff');
        header('Content-Disposition: inline; filename="' . $name . '"');
        readfile($path);
    }
}
