<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        // Throwable
        $this->reportable(function (Throwable $e) {
            //
        });

        // HttpException Handler
        $this->renderable(function (Exception $e, $request) {
            if ($request->is('api/*') || $request->is('*/interface') || $request->is('*/interface/*')) {
                if($e instanceof HttpException){
                    $code = $e->getCode() > 0 ? $e->getCode() : $e->getStatusCode();
                    $statusCode = $e->getStatusCode();
                } else {
                    $code = $e->getCode() > 0 ? $e->getCode() : 500;
                    $statusCode = 500;
                }
                return response()->json([
                    'code' => $code,
                    'message' => $e->getMessage() ?? pathinfo($e::class,PATHINFO_BASENAME),
                ], $statusCode);
            }
        });
    }
}
