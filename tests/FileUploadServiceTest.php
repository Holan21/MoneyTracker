<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(FileUploadService::class)]
class FileUploadServiceTest extends TestCase
{
    private FileUploadService $service;

    protected function setUp(): void
    {
        $this->service = new FileUploadService();
    }

    // -------------------------------------------------------------------------
    // validate() — rejection cases
    // -------------------------------------------------------------------------

    #[DataProvider('invalidFileProvider')]
    public function testValidateThrowsOnInvalidInput(array $file, string $message): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage($message);
        $this->service->validate($file);
    }

    public static function invalidFileProvider(): array
    {
        return [
            'missing keys' => [
                ['name' => 'a.png'],
                'Incomplete upload payload',
            ],
            'no file chosen' => [
                ['error' => UPLOAD_ERR_NO_FILE, 'name' => '', 'tmp_name' => '', 'size' => 0],
                'No file uploaded',
            ],
            'php ini size exceeded' => [
                ['error' => UPLOAD_ERR_INI_SIZE, 'name' => 'a.png', 'tmp_name' => '/tmp/x', 'size' => 10],
                'Upload error',
            ],
            'form size exceeded' => [
                ['error' => UPLOAD_ERR_FORM_SIZE, 'name' => 'a.png', 'tmp_name' => '/tmp/x', 'size' => 10],
                'Upload error',
            ],
            'file too large' => [
                ['error' => UPLOAD_ERR_OK, 'name' => 'a.png', 'tmp_name' => '/tmp/x', 'size' => FileUploadService::MAX_BYTES + 1],
                'File size is invalid',
            ],
            'zero size file' => [
                ['error' => UPLOAD_ERR_OK, 'name' => 'a.png', 'tmp_name' => '/tmp/x', 'size' => 0],
                'File size is invalid',
            ],
            'null byte in name' => [
                ['error' => UPLOAD_ERR_OK, 'name' => "evil\0.png", 'tmp_name' => '/tmp/x', 'size' => 10],
                'Unsafe file name',
            ],
            'double extension' => [
                ['error' => UPLOAD_ERR_OK, 'name' => 'shell.php.png', 'tmp_name' => '/tmp/x', 'size' => 10],
                'Unsafe file name',
            ],
            'executable type' => [
                ['error' => UPLOAD_ERR_OK, 'name' => 'virus.exe', 'tmp_name' => '/tmp/x', 'size' => 10],
                'Invalid file type',
            ],
            'script type' => [
                ['error' => UPLOAD_ERR_OK, 'name' => 'hack.php', 'tmp_name' => '/tmp/x', 'size' => 10],
                'Invalid file type',
            ],
        ];
    }

    // -------------------------------------------------------------------------
    // validate() — acceptance cases
    // -------------------------------------------------------------------------

    #[DataProvider('validFileProvider')]
    public function testValidateAcceptsAllowedTypes(string $filename, string $expectedExt): void
    {
        $ext = $this->service->validate([
            'error'    => UPLOAD_ERR_OK,
            'name'     => $filename,
            'tmp_name' => 'non-existent-so-mime-check-skipped',
            'size'     => 1024,
        ]);

        $this->assertSame($expectedExt, $ext);
    }

    public static function validFileProvider(): array
    {
        return [
            'jpeg file' => ['receipt.jpeg', 'jpeg'],
            'jpg file'  => ['photo.jpg',    'jpg'],
            'png file'  => ['scan.png',     'png'],
            'pdf file'  => ['invoice.pdf',  'pdf'],
        ];
    }

    public function testValidateReturnsLowercaseExtension(): void
    {
        $ext = $this->service->validate([
            'error'    => UPLOAD_ERR_OK,
            'name'     => 'receipt.PNG',
            'tmp_name' => 'non-existent',
            'size'     => 512,
        ]);

        $this->assertSame('png', $ext);
    }

    // -------------------------------------------------------------------------
    // MAX_BYTES constant
    // -------------------------------------------------------------------------

    public function testMaxBytesIs2MB(): void
    {
        $this->assertSame(2 * 1024 * 1024, FileUploadService::MAX_BYTES);
    }

    public function testFileAtExactMaxBytesIsAccepted(): void
    {
        $ext = $this->service->validate([
            'error'    => UPLOAD_ERR_OK,
            'name'     => 'receipt.png',
            'tmp_name' => 'non-existent',
            'size'     => FileUploadService::MAX_BYTES,
        ]);

        $this->assertSame('png', $ext);
    }

    public function testValidateThrowsOnMimeMismatch(): void
    {
        $tmp = tempnam(sys_get_temp_dir(), 'upl');
        file_put_contents($tmp, 'not-an-image');

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Invalid file mime type');

        try {
            $this->service->validate([
                'error'    => UPLOAD_ERR_OK,
                'name'     => 'receipt.png',
                'tmp_name' => $tmp,
                'size'     => filesize($tmp),
            ]);
        } finally {
            @unlink($tmp);
        }
    }

    public function testUploadStoresAllowedPngAndReturnsHashedName(): void
    {
        $tmp = tempnam(sys_get_temp_dir(), 'upl');
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==');
        file_put_contents($tmp, $png);
        $dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'secure-upload-' . bin2hex(random_bytes(4));

        $stored = $this->service->upload([
            'error'    => UPLOAD_ERR_OK,
            'name'     => 'receipt.png',
            'tmp_name' => $tmp,
            'size'     => filesize($tmp),
        ], $dir);

        $this->assertMatchesRegularExpression('/^[a-f0-9]{32}\.png$/', $stored);
        $this->assertFileExists($dir . DIRECTORY_SEPARATOR . $stored);

        @unlink($tmp);
        @unlink($dir . DIRECTORY_SEPARATOR . $stored);
        @rmdir($dir);
    }

    public function testUploadThrowsWhenSourceFileMissing(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Cannot store uploaded file');

        $this->service->upload([
            'error'    => UPLOAD_ERR_OK,
            'name'     => 'receipt.png',
            'tmp_name' => sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'missing-upload-source',
            'size'     => 12,
        ], sys_get_temp_dir());
    }

    #[DataProvider('storedNameProvider')]
    public function testIsSafeStoredName(string $name, bool $expected): void
    {
        $this->assertSame($expected, $this->service->isSafeStoredName($name));
    }

    public static function storedNameProvider(): array
    {
        return [
            'valid png'     => [str_repeat('a', 32) . '.png', true],
            'valid pdf'     => [str_repeat('b', 32) . '.pdf', true],
            'uppercase ext' => [str_repeat('c', 32) . '.JPG', true],
            'too short'     => [str_repeat('a', 31) . '.png', false],
            'path traversal'=> ['../' . str_repeat('a', 32) . '.png', false],
            'php file'      => [str_repeat('a', 32) . '.php', false],
            'empty'         => ['', false],
        ];
    }

    public function testModuleIsIsolatedUnderSecureUpload(): void
    {
        $path = (new ReflectionClass(FileUploadService::class))->getFileName();
        $this->assertNotFalse($path);
        $this->assertStringContainsString('Modules' . DIRECTORY_SEPARATOR . 'SecureUpload', $path);
    }
}
