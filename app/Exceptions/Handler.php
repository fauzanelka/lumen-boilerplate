<?php

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        ValidationException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Throwable  $exception
     * @return void
     *
     * @throws \Exception
     */
    public function report(Throwable $exception)
    {
        parent::report($exception);
    }

    /**
     * Get status code from exception
     *
     * @param  \Throwable $exception
     * @return int
     */
    private function getStatusCodeFromException(Throwable $exception)
    {
        if ($exception instanceof ModelNotFoundException) {
            $flattenException = FlattenException::create($exception, 404);
        } elseif ($exception instanceof AuthorizationException) {
            $flattenException = FlattenException::create($exception, 403);
        } else {
            $flattenException = FlattenException::create($exception);
        }

        return $flattenException->getStatusCode();
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $exception)
    {
        if ($exception instanceof HttpResponseException) {
            return $exception->getResponse();
        } elseif ($exception instanceof ValidationException && $exception->getResponse()) {
            return $exception->getResponse();
        }

        $statusCode = $this->getStatusCodeFromException($exception);
        $error['error'] = Response::$statusTexts[$statusCode];
        if (env('APP_DEBUG', false)) {
            $error['message'] = $exception->getMessage();
            $error['file'] = $exception->getFile() . ':' . $exception->getLine();
            $error['trace'] = explode('\n', $exception->getTraceAsString());
        }

        return response()->json($error, $statusCode, [], JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
    }
}
