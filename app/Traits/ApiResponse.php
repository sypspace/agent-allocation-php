<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    /**
     * Respon sukses.
     *
     * @param mixed $data
     * @param string $message
     * @param int $statusCode
     * @return JsonResponse
     */
    public function success($data = null, int $statusCode = 200): JsonResponse
    {
        return response()->json([
            'status' => $statusCode,
            'data' => $data,
        ], $statusCode);
    }

    /**
     * Respon error.
     *
     * @param string $message
     * @param int $statusCode
     * @param mixed $errors
     * @return JsonResponse
     */
    public function error(string $errors = 'An error occurred', int $statusCode = 400): JsonResponse
    {
        return response()->json([
            'status' => $statusCode,
            'errors' => $errors,
        ], $statusCode);
    }

    /**
     * Respon Unauthorized.
     *
     * @param void
     * @return JsonResponse
     **/
    public function unauthorize(): JsonResponse
    {
        return response()->json([
            'status' => 401,
            'errors' => 'Unauthorized',
        ], 401);
    }
}
