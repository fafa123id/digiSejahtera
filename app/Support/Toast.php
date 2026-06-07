<?php

namespace App\Support;

use Illuminate\Support\Str;

class Toast
{
    public static function success(string $message): array
    {
        return self::make(
            message: $message,
            type: 'success',
        );
    }

    public static function error(string $message): array
    {
        return self::make(
            message: $message,
            type: 'error',
        );
    }

    private static function make(
        string $message,
        string $type
    ): array {
        return [
            'id' => (string) Str::uuid(),
            'type' => $type,
            'message' => $message,
        ];
    }
}