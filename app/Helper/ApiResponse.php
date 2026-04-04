<?php

namespace App\Helper;
class ApiResponse
{
    public static function success($data = null, $message = 'Success'): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => $message,
            'data' => $data
        ]);
    }

    public static function error($message = 'Error', $code = 400): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'status' => false,
            'message' => $message
        ], $code);
    }
}
