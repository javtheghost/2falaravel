<?php

namespace App\Exceptions;
use Illuminate\Auth\AuthenticationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Log;
use Throwable;
use Twilio\Exceptions\TwilioException;

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
        $this->reportable(function (Throwable $e) {
            //
        });
    }

     /**
     * Customize the error rendering based on exception type.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Throwable $exception)
    {
        // Custom handling for 404 errors
        if ($exception instanceof NotFoundHttpException) {
            return response()->view('errors.404', [], 404);
        }

        // Custom handling for 401 errors
        if ($exception instanceof UnauthorizedHttpException) {
            return response()->view('errors.401', [], 401);
        }

                // Si el error es un 419 (Page Expired), muestra la vista personalizada
        if ($exception instanceof \Symfony\Component\HttpKernel\Exception\HttpException && $exception->getStatusCode() === 419) {
            return response()->view('errors.419', [], 419);
        }
        if ($exception instanceof TwilioException) {
            Log::error('Error al enviar SMS: ' . $exception->getMessage());
            return back()->withErrors(['message' => 'Error al enviar SMS. Inténtalo más tarde.']);
        }

        // Call the parent handler for other types of exceptions
        return parent::render($request, $exception);
    }
}
