<?php

namespace App\Http\Controllers\Api;

use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests, ApiResponseTrait;


    /**
     * Standard API response method
     */
    protected function response($statusCode, $message, $data = [])
    {
        return response()->json([
            'success' => $statusCode >= 200 && $statusCode < 300,
            'response_code' => $statusCode,
            'message' => $message,
            'data' => $data
        ], $statusCode);
    }

    /**
     * Success response shortcut
     */
    protected function successResponse($message, $data = [])
    {
        return $this->response(Response::HTTP_OK, $message, $data);
    }

    /**
     * Created response shortcut
     */
    protected function createdResponse($message, $data = [])
    {
        return $this->response(Response::HTTP_CREATED, $message, $data);
    }

    /**
     * Error response shortcut
     */
    protected function errorResponse($message, $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR, $data = [])
    {
        return $this->response($statusCode, $message, $data);
    }

    /**
     * Not found response shortcut
     */
    protected function notFoundResponse($message = 'Resource not found')
    {
        return $this->response(Response::HTTP_NOT_FOUND, $message, []);
    }

    /**
     * Validation error response shortcut
     */
    protected function validationErrorResponse($message = 'Validation failed', $errors = [])
    {
        return $this->response(Response::HTTP_UNPROCESSABLE_ENTITY, $message, ['errors' => $errors]);
    }

    /**
     * Forbidden response shortcut
     */
    protected function forbiddenResponse($message = 'Access forbidden')
    {
        return $this->response(Response::HTTP_FORBIDDEN, $message, []);
    }

    /**
     * Unauthorized response shortcut
     */
    protected function unauthorizedResponse($message = 'Unauthorized')
    {
        return $this->response(Response::HTTP_UNAUTHORIZED, $message, []);
    }

}
