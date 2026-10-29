<?php

declare(strict_types=1);

class FileUploadService
{
    public const MAX_BYTES = 2097152; // 2 MB

    private const ALLOWED = [
        'jpg'  => ['image/jpeg'],
        'jpeg' => ['image/jpeg'],
        'png'  => ['image/png'],
        'pdf'  => ['application/pdf'],
    ];

    public function isSafeStoredName(string $name): bool
    {
        return (bool)preg_match('/^[a-f0-9]{32}\.(jpg|jpeg|png|pdf)$/i', $name);
    }

    public function validate(array $file): string
    {
        if (!isset($file['error'], $file['name'], $file['tmp_name'], $file['size'])) {
            throw new ValidationException('Incomplete upload payload');
        }

        if ($file['error'] === UPLOAD_ERR_NO_FILE) {
            throw new ValidationException('No file uploaded');
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new ValidationException('Upload error');
        }

        if ((int)$file['size'] <= 0 || (int)$file['size'] > self::MAX_BYTES) {
            throw new ValidationException('File size is invalid');
        }

        $name = (string)$file['name'];
        if (str_contains($name, "\0") || substr_count($name, '.') > 1) {
            throw new ValidationException('Unsafe file name');
        }

        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        if (!isset(self::ALLOWED[$ext])) {
            throw new ValidationException('Invalid file type');
        }

        $this->assertMime((string)$file['tmp_name'], $ext);

        return $ext;
    }

    private function assertMime(string $tmp, string $ext): void
    {
        if (!is_file($tmp) || !is_readable($tmp)) {
            return;
        }

        $head = (string)file_get_contents($tmp, false, null, 0, 16);
        $ok = match ($ext) {
            'png'        => str_starts_with($head, "\x89PNG\r\n\x1a\n"),
            'jpg', 'jpeg'=> str_starts_with($head, "\xFF\xD8\xFF"),
            'pdf'        => str_starts_with($head, '%PDF'),
            default      => false,
        };

        if (!$ok) {
            throw new ValidationException('Invalid file mime type');
        }

        if (class_exists('finfo')) {
            $mime = (new finfo(FILEINFO_MIME_TYPE))->file($tmp);
            if (!in_array($mime, self::ALLOWED[$ext], true)) {
                throw new ValidationException('Invalid file mime type');
            }
        }
    }

    public function upload(array $file, string $targetDir): string
    {
        $ext = $this->validate($file);

        if (!is_dir($targetDir) && !mkdir($targetDir, 0775, true) && !is_dir($targetDir)) {
            throw new RuntimeException('Cannot create upload directory');
        }

        $stored = bin2hex(random_bytes(16)) . '.' . $ext;
        $target = rtrim($targetDir, '/\\') . DIRECTORY_SEPARATOR . $stored;

        $tmp = (string)$file['tmp_name'];
        $moved = is_uploaded_file($tmp)
            ? move_uploaded_file($tmp, $target)
            : (is_file($tmp) && copy($tmp, $target));

        if (!$moved) {
            throw new RuntimeException('Cannot store uploaded file');
        }

        return $stored;
    }
}
