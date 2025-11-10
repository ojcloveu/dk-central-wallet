<?php

namespace App\Libraries;

class ResponseLib
{
    public static function validateError(
        object|array $validate = [],
        int $status = 422,
        object|array $additional = []
    ) {
        $firstMessage = collect($validate)->first();
        $firstMessage = is_array($firstMessage) ? ($firstMessage[0] ?? '') : $firstMessage;
        
        // try to guess if it is laravel error bag
        try {
            $validate = $validate->errors();
            $firstMessage = $validate->first();
        } catch (\Throwable $th) { }

        return response()->json([
            'status' => 'Error has occurred .',
            'message' => $firstMessage ?? 'The given data was invalid.',
            'errors' => $validate,
            ...$additional
        ], $status);
    }
    
    public static function success(
        object|array $data = [],
        $message = null,
        $status = 200,
        object|array $additional = []
    ) {
        return response()->json([
            'status' => $defaultMsg = 'Request was successful.',
            'message' => $message ?: $defaultMsg,
            'data' => $data,
            ...$additional
        ], $status);
    }
    
    public static function error(
        object|array $data = [],
        $message = null,
        $status = 500,
        object|array $additional = []
    ) {
        return response()->json([
            'status' => $defaultMsg = 'Error has occurred .',
            'message' => $message ?: $defaultMsg,
            'data' => $data,
            ...$additional
        ], $status);
    }
}
