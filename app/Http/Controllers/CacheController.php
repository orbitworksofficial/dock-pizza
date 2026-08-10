<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class CacheController extends Controller
{
    public function clear(Request $request): JsonResponse
    {
        if (! $this->tokenIsValid($request)) {
            abort(403, 'Invalid or missing token. Set CACHE_CLEAR_TOKEN in .env');
        }

        $results = [];

        foreach (['optimize:clear', 'config:clear', 'cache:clear', 'view:clear', 'route:clear'] as $command) {
            try {
                Artisan::call($command);
                $results[$command] = trim(Artisan::output()) ?: 'done';
            } catch (\Throwable $e) {
                $results[$command] = 'failed: '.$e->getMessage();
            }
        }

        $results['manual'] = $this->clearCacheFiles();

        return response()->json([
            'success' => true,
            'message' => 'Cache clear completed.',
            'results' => $results,
        ]);
    }

    private function tokenIsValid(Request $request): bool
    {
        $token = (string) env('CACHE_CLEAR_TOKEN', '');

        if ($token === '') {
            return false;
        }

        return hash_equals($token, (string) $request->query('token', ''));
    }

    private function clearCacheFiles(): string
    {
        $deleted = 0;

        foreach (glob(base_path('bootstrap/cache/*.php')) ?: [] as $file) {
            if (basename($file) !== '.gitignore' && @unlink($file)) {
                $deleted++;
            }
        }

        foreach (glob(storage_path('framework/views/*.php')) ?: [] as $file) {
            if (@unlink($file)) {
                $deleted++;
            }
        }

        $cacheDataPath = storage_path('framework/cache/data');
        if (is_dir($cacheDataPath)) {
            foreach (glob($cacheDataPath.'/*') ?: [] as $file) {
                if (is_file($file) && @unlink($file)) {
                    $deleted++;
                }
            }
        }

        return "deleted {$deleted} cache files";
    }
}
