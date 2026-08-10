<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

$basePath = dirname(__DIR__);

$app = Application::configure(basePath: $basePath)
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();

// cPanel: auto-use project root as public path when build lives at /build
// (document root = public_html, manifest at public_html/build/manifest.json)
$publicPath = env('APP_PUBLIC_PATH');

if (! $publicPath && ! is_file($basePath.'/public/build/manifest.json') && is_file($basePath.'/build/manifest.json')) {
    $publicPath = $basePath;
}

if ($publicPath) {
    $app->usePublicPath($publicPath);
}

return $app;
