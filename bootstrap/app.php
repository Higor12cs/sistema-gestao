<?php

use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

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
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->respond(function (RedirectResponse|Response $response, Throwable $exception, Request $request) {
            $isServerError = in_array($response->getStatusCode(), [500, 503], true);
            $isInertia = $request->headers->get('X-Inertia') === 'true';

            if ($isServerError && $isInertia) {
                $errorMessage = 'Ocorreu um erro interno, por favor tente novamente. Se o problema persistir, entre em contato com o suporte. Código do erro: '.$response->getStatusCode().'.';

                if (app()->isLocal()) {
                    $errorMessage .= sprintf("\n%s: %s", get_class($exception), $exception->getMessage());
                }

                return response()->json([
                    'error_message' => $errorMessage,
                ], $response->getStatusCode());
            }

            if ($response->getStatusCode() === 419) {
                return back()->with([
                    'flash.banner' => 'A página expirou, por favor tente novamente.',
                ]);
            }

            return $response;
        });
    })->create();
