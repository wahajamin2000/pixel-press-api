<?php

namespace App\Traits;

use Symfony\Component\HttpFoundation\Response;

trait ApiResponseTrait
{
    public function response($response_code, $message, $data = [], $errors = [])
    {
        $response = [];

        switch ($response_code) {
            case Response::HTTP_OK:
                $response = $this->responseOK($response_code, $message, $data, $errors);
                break;
            case Response::HTTP_BAD_REQUEST:
                $response = $this->responseBadRequest($response_code, $message, $data, $errors);
                break;
            case Response::HTTP_NOT_FOUND:
                $response = $this->responseNotFound($response_code, $message, $data, $errors);
                break;
            case Response::HTTP_UNPROCESSABLE_ENTITY:
                $response = $this->responseUnprocessableEntity($response_code, $message, $data, $errors);
                break;
            case Response::HTTP_INTERNAL_SERVER_ERROR:
                $response = $this->responseInternalServerError($response_code, $message, $data, $errors);
                break;
            default:
                $response = $this->somethingWentWrong($response_code, $message, $data, $errors);
                break;
        }


        return response()->json($response, $response_code);//, [], JSON_NUMERIC_CHECK
    }

    private function responseOK($response_code, $message, array $data, $errors = [])
    {
        return $this->responseBody(true, $response_code, $message, $data, $errors);
    }

    private function responseBadRequest($response_code, $message, array $data, $errors = [])
    {
        return $this->responseBody(false, $response_code, $message, $data, $errors);
    }

    private function responseNotFound($response_code, $message, array $data, $errors = [])
    {
        return $this->responseBody(false, $response_code, $message, $data, $errors);
    }

    private function responseUnprocessableEntity($response_code, $message, array $data, $errors = [])
    {
        return $this->responseBody(false, $response_code, $message, $data, $errors);
    }

    private function responseInternalServerError($response_code, $message, array $data, $errors = [])
    {
        return $this->responseBody(false, $response_code, $message, $data, $errors);
    }

    private function somethingWentWrong($response_code, $message, array $data, $errors = [])
    {
        return $this->responseBody(false, $response_code, $message, $data, $errors);
    }

    private function responseBody(bool $success, $response_code, $message, $data, $errors)
    {
        return $response = $this->prepareResponseData($success, $response_code, $message, $data, $errors);
    }

    private function prepareResponseData(bool $success, $response_code, $message, $data, $errors)
    {
        $response = [];
        $response['success'] = $success ? 1 : 0;
        $response['response_code'] = $response_code;
        $response['message'] = $response_code == Response::HTTP_UNPROCESSABLE_ENTITY
            ? implode("\n", array_unique(array_flatten($errors)))
//            implode("\n", array_unique(call_user_func_array('array_merge', $errors)))
            : $message;
        $response['data'] = $data;

        return $response;
    }
}
