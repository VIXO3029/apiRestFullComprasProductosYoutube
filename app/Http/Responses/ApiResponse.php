<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;

class ApiResponse
{
    public static function success($message = 'Success', $statusCode = 200, $data = []): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'statusCode' => $statusCode,
            'error' => false,
            'data' => $data,
        ], $statusCode);
    }

    public static function error($message = 'Error', $statusCode, $data = []): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'statusCode' => $statusCode,
            'error' => true,
            'data' => $data,
        ], $statusCode);
    }
}
