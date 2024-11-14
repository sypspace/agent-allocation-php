<?php

namespace App\Services;

use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\JsonResponse;

class ResponseHandler
{
    /**
     * Format respon sukses.
     *
     * @param mixed $data
     * @param int $statusCode
     * @return JsonResponse
     */
    public static function success($message, $data = null, int $statusCode = 200): JsonResponse
    {
        return response()->json([
            'status' => $statusCode,
            'message' => $message,
            'data' => $data
        ], $statusCode);
    }

    /**
     * Format respon error.
     *
     * @param mixed $errors
     * @param int $statusCode
     * @return JsonResponse
     */
    public static function error(string $errors = 'An error occurred', int $statusCode = 400, $data = null): JsonResponse
    {
        return response()->json([
            'status' => $statusCode,
            'errors' => $errors,
            'data' => $data
        ], $statusCode);
    }

    /**
     * Format respon unauthorized
     *
     * @param void
     * @return JsonResponse
     **/
    public static function unauthorized(): JsonResponse
    {
        return response()->json([
            'status' => 401,
            'message' => 'Unauthorized',
        ], 401);
    }
}
