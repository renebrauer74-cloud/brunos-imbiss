<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->redirectGuestsTo(fn () => route('admin.login'));
        $middleware->redirectUsersTo(fn () => route('admin.menu.index'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        // Upload größer als post_max_size: PHP verwirft das ganze Formular. Zurück mit Hinweis statt Fehlerseite
        // (die Session läuft hier noch nicht, daher als Query-Parameter – siehe Admin-Layout).
        $exceptions->render(function (PostTooLargeException $exception, Request $request) {
            $previous = (string) $request->headers->get('referer');
            $target = str_starts_with($previous, url('/admin')) ? strtok($previous, '?#') : url('/admin');

            return redirect()->to($target.'?zu-gross=1');
        });
    })->create();
