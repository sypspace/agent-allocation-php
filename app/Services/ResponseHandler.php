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
    public static function success($data = null, int $statusCode = 200): JsonResponse
    {
        return response()->json([
            'status' => $statusCode,
            'data' => $data,
        ], $statusCode);
    }

    /**
     * Format respon error.
     *
     * @param mixed $errors
     * @param int $statusCode
     * @return JsonResponse
     */
    public static function error(string $errors = 'An error occurred', int $statusCode = 400): JsonResponse
    {
        return response()->json([
            'status' => $statusCode,
            'errors' => $errors,
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
