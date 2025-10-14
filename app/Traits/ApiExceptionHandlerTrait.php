<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Exceptions\HttpResponseException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

trait ApiExceptionHandlerTrait
{

    private $status_code = null;
    private $message = '';
    private $errors = [];

    public function handleApiException($request, Throwable $exception)
    {
        $this->errors = isset($exception->errorBag) && $exception->errorBag !== "default" ? $exception->errorBag : [];
        $this->message = $exception->getMessage() ?? '';
        $this->status_code = $exception->status ?? null;

        if ($exception instanceof TokenExpiredException) {
            return $this->response(Response::HTTP_BAD_REQUEST, 'Auth token is expired', []);
        } else if ($exception instanceof TokenInvalidException) {
            return $this->response(Response::HTTP_BAD_REQUEST, 'Auth token is invalid', []);
        } else if ($exception instanceof JWTException) {
            return $this->response(Response::HTTP_BAD_REQUEST, 'Auth token not found', []);
        }
        if ($exception instanceof ModelNotFoundException) {
            return $this->response(Response::HTTP_NOT_FOUND, 'Record not found', []);
        }

        $exception = $this->prepareException($exception);

        if ($exception instanceof HttpResponseException) {
            $exception = $exception->getResponse();
        }

        if ($exception instanceof \Illuminate\Auth\AuthenticationException) {
            $exception = $this->unauthenticated($request, $exception);
        }

        if ($exception instanceof \Illuminate\Validation\ValidationException) {
            $exception = $this->convertValidationExceptionToResponse($exception, $request);
        }

        return $this->customApiResponse($exception);
    }

    private function customApiResponse($exception)
    {
        if (method_exists($exception, 'getStatusCode')) {
            $statusCode = $exception->getStatusCode();
        } else {
            $statusCode = 500;
        }

        $response = [];

        switch ($statusCode) {
            case 401:
                $response['message'] = 'Unauthorized';
                break;
            case 403:
                $response['message'] = 'Forbidden';
                break;
            case 404:
                $response['message'] = 'Not Found';
                break;
            case 405:
                $response['message'] = 'Method Not Allowed';
                break;
            case 422:
                $response['message'] = $exception->original['message'];
                $response['errors'] = $exception->original['errors'];
                break;
            default:
                $response['message'] = ($statusCode == 500) ? 'Whoops, looks like something went wrong' : $exception->getMessage();
                break;
        }

//        if (config('app.debug')) {
//            $response['trace'] = $exception->getTrace();
//            $response['code'] = $exception->getCode();
//        }

        $response['status'] = $statusCode;

        $this->status_code = $this->status_code ?? $statusCode;
        $this->message = isset($this->message) && $this->message !== '' ? $this->message : $response['message'] ?? '';
        $this->errors = (isset($this->errors) && !empty($this->errors)) ? $this->errors : $response['errors'] ?? [];

//        return response()->json([$this->errors, $this->message, $this->status_code, $exception, $response]);

//        return response()->json([$this->errors, $this->message, $this->status_code, $exception]);
//        return response()->json([$this->errors, $this->message, $this->status_code]);
//        return response()->json(array_unique($this->errors));

        return $this->response($this->status_code, $this->message, [], $this->errors ?? []);
//        return $this->response($this->status_code, $this->message, [], array_unique($this->errors) ?? []);
    }

}
