<?php

/**
 * Emergency cache clear for cPanel — works even when Laravel routes are cached.
 * Visit: /clear-cache.php?token=YOUR_CACHE_CLEAR_TOKEN
 * Remove this file after deployment is stable.
 */

declare(strict_types=1);

header('Content-Type: application/json');

$basePath = dirname(__DIR__);
$envPath = $basePath.'/.env';
$providedToken = (string) ($_GET['token'] ?? '');
$expectedToken = readEnvValue('CACHE_CLEAR_TOKEN', $envPath);

if ($expectedToken === null || $expectedToken === '' || ! hash_equals($expectedToken, $providedToken)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Invalid or missing token. Set CACHE_CLEAR_TOKEN in .env']);
    exit;
}

$deleted = 0;

foreach (glob($basePath.'/bootstrap/cache/*.php') ?: [] as $file) {
    if (basename($file) !== '.gitignore' && @unlink($file)) {
        $deleted++;
    }
}

foreach (glob($basePath.'/storage/framework/views/*.php') ?: [] as $file) {
    if (@unlink($file)) {
        $deleted++;
    }
}

$cacheDataPath = $basePath.'/storage/framework/cache/data';
if (is_dir($cacheDataPath)) {
    foreach (glob($cacheDataPath.'/*') ?: [] as $file) {
        if (is_file($file) && @unlink($file)) {
            $deleted++;
        }
    }
}

echo json_encode([
    'success' => true,
    'message' => 'Cache files deleted.',
    'deleted' => $deleted,
]);

function readEnvValue(string $key, string $envPath): ?string
{
    if (! is_readable($envPath)) {
        return null;
    }

    foreach (file($envPath) as $line) {
        $line = trim($line);

        if ($line === '' || str_starts_with($line, '#') || ! str_contains($line, '=')) {
            continue;
        }

        [$name, $value] = explode('=', $line, 2);

        if (trim($name) !== $key) {
            continue;
        }

        return trim($value, " \t\n\r\0\x0B\"'");
    }

    return null;
}
