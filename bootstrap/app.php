<?php

use App\Exceptions\MarkdownReadException;
use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            HandleInertiaRequests::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->report(function (MarkdownReadException $e): void {
            logger()->error($e->getMessage(), [
                'path' => $e->path,
            ]);
        })->stop();

        $exceptions->render(function (MarkdownReadException $e, Request $request): Response {
            return response('Unable to load markdown content.', Response::HTTP_INTERNAL_SERVER_ERROR);
        });
    })
    ->create();
