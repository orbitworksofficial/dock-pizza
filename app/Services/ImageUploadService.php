<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Hardened image intake.
 *
 * Every upload is decoded and re-encoded rather than moved. That is what
 * strips EXIF/GPS and discards any payload appended after a valid image
 * header — a file that survives a full decode/encode round trip contains
 * nothing but pixels.
 */
class ImageUploadService
{
    private const MAX_EDGE = 2000;
    private const QUALITY = 82;

    /**
     * Raster types only. SVG is excluded deliberately: it can carry <script>,
     * and serving user-supplied SVG from our own origin is stored XSS.
     */
    private const ALLOWED_MIME = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp',
    ];

    /**
     * @return array{path: string, url: string, width: int, height: int, bytes: int}
     *
     * @throws RuntimeException with a message safe to show the uploader.
     */
    public function store(UploadedFile $file, string $directory = 'seo'): array
    {
        $this->assertStorageIsConfigured();
        $this->assertUploadIsSane($file);

        $mime = $this->detectMime($file);
        $image = $this->decode($file, $mime);

        try {
            $image = $this->resize($image);

            $path = trim($directory, '/') . '/' . Str::uuid()->toString() . '.webp';
            $binary = $this->encodeWebp($image);

            $disk = Storage::disk($this->disk());

            if (!$disk->put($path, $binary, 'public')) {
                throw new RuntimeException('The image could not be written to storage. Please try again.');
            }

            return [
                'path' => $path,
                'url' => $disk->url($path),
                'width' => imagesx($image),
                'height' => imagesy($image),
                'bytes' => strlen($binary),
            ];
        } finally {
            imagedestroy($image);
        }
    }

    /**
     * A missing cloud credential in production must fail loudly. Silently
     * falling back to a local disk risks writing to ephemeral storage that
     * disappears on the next deploy, losing the file with no error.
     */
    private function assertStorageIsConfigured(): void
    {
        $disk = $this->disk();

        if ($disk === 'public') {
            return;
        }

        $config = config("filesystems.disks.{$disk}");

        if (!$config) {
            throw new RuntimeException(
                "Upload disk '{$disk}' is not configured in config/filesystems.php."
            );
        }

        $required = match ($config['driver'] ?? null) {
            's3' => ['key', 'secret', 'bucket', 'region'],
            default => [],
        };

        $missing = array_values(array_filter(
            $required,
            fn (string $key) => empty($config[$key])
        ));

        if ($missing !== []) {
            throw new RuntimeException(
                "Cloud storage is not configured: disk '{$disk}' is missing "
                . implode(', ', $missing)
                . '. Refusing to upload, because writing to a local disk in production may be lost on the next deploy.'
            );
        }
    }

    private function assertUploadIsSane(UploadedFile $file): void
    {
        if (!$file->isValid()) {
            throw new RuntimeException('The upload did not complete. It may be larger than the server allows.');
        }

        $maxBytes = 12 * 1024 * 1024;

        if ($file->getSize() > $maxBytes) {
            throw new RuntimeException('That image is larger than 12 MB. Please upload a smaller file.');
        }
    }

    /**
     * Trust the file's actual content, never its extension or the
     * client-supplied Content-Type.
     */
    private function detectMime(UploadedFile $file): string
    {
        $info = @getimagesize($file->getRealPath());

        if ($info === false || empty($info['mime'])) {
            throw new RuntimeException('That file is not a readable image.');
        }

        $mime = strtolower($info['mime']);

        if (!isset(self::ALLOWED_MIME[$mime])) {
            throw new RuntimeException(
                'Unsupported image type. Please upload a JPEG, PNG, GIF or WebP. '
                . 'SVG files are not accepted for security reasons.'
            );
        }

        return $mime;
    }

    /**
     * @return \GdImage
     */
    private function decode(UploadedFile $file, string $mime)
    {
        $contents = file_get_contents($file->getRealPath());

        if ($contents === false) {
            throw new RuntimeException('The uploaded image could not be read.');
        }

        $image = @imagecreatefromstring($contents);

        if ($image === false) {
            throw new RuntimeException('That image could not be decoded. It may be corrupt or not a real image.');
        }

        return $image;
    }

    /**
     * @param  \GdImage  $image
     * @return \GdImage
     */
    private function resize($image)
    {
        $width = imagesx($image);
        $height = imagesy($image);
        $longest = max($width, $height);

        if ($longest <= self::MAX_EDGE) {
            return $image;
        }

        $scale = self::MAX_EDGE / $longest;
        $newWidth = (int) round($width * $scale);
        $newHeight = (int) round($height * $scale);

        $resized = imagecreatetruecolor($newWidth, $newHeight);

        // Preserve transparency through the resample.
        imagealphablending($resized, false);
        imagesavealpha($resized, true);

        imagecopyresampled($resized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        imagedestroy($image);

        return $resized;
    }

    /**
     * @param  \GdImage  $image
     */
    private function encodeWebp($image): string
    {
        if (!function_exists('imagewebp')) {
            throw new RuntimeException('WebP support is not available on this server (GD is missing imagewebp).');
        }

        imagealphablending($image, false);
        imagesavealpha($image, true);

        ob_start();
        $ok = imagewebp($image, null, self::QUALITY);
        $binary = (string) ob_get_clean();

        if (!$ok || $binary === '') {
            throw new RuntimeException('The image could not be converted to WebP.');
        }

        return $binary;
    }

    private function disk(): string
    {
        return (string) config('filesystems.seo_disk', config('filesystems.default', 'public'));
    }
}
