<?php

namespace App\Exceptions;

use http\Exception\InvalidArgumentException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;
use function PHPUnit\Framework\returnArgument;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
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


        $this->renderable(function (\Exception $e, $request) {
            return $this->customApiResponse($e);
        });
    }


    private function customApiResponse($exception)
    {
        if($exception instanceof AuthenticationException){
            return response()->json([
                                        "message" => __('messages.unauthenticated'),
                                    ], 401);

        } else if($exception instanceof MethodNotAllowedHttpException){
            return response()->json([
                                        "message" => __("messages.method_not_allowed"),
                                    ], 405);

        } else if($exception instanceof NotFoundHttpException){
            return response()->json([
                                        "message" => __('messages.not_found'),
                                    ], 404);
        }

    }


}
