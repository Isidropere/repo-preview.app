<?php

namespace App\Logging;

use Illuminate\Support\Facades\Log;
use Throwable;

trait ErrorLoggerTrait
{
    protected function logError(
        Throwable $exception,
        array $context = [],
        string $channel = 'error_tracking'
    ): void {
        $errorData = [
            'message' => $exception->getMessage(),
            'exception' => get_class($exception),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
            'code' => $exception->getCode(),
            'request' => request()?->all() ?? [],
            'context' => $context,
            'user_id' => auth()->id() ?? null,
            'ip' => request()?->ip() ?? 'cli',
            'url' => request()?->fullUrl() ?? 'cli',
            'method' => request()?->method() ?? 'cli',
            'timestamp' => now()->toDateTimeString(),
        ];

        Log::channel($channel)->error('Error occurred', $errorData);
    }

    protected function logCustomError(
        string $message,
        array $context = [],
        string $channel = 'error_tracking'
    ): void {
        $debugBacktrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5);

        $errorData = [
            'message' => $message,
            'custom_error' => true,
            'file' => $debugBacktrace[1]['file'] ?? null,
            'line' => $debugBacktrace[1]['line'] ?? null,
            'class' => $debugBacktrace[1]['class'] ?? null,
            'function' => $debugBacktrace[1]['function'] ?? null,
            'request' => request()?->all() ?? [],
            'context' => $context,
            'user_id' => auth()->id() ?? null,
            'ip' => request()?->ip() ?? 'cli',
            'url' => request()?->fullUrl() ?? 'cli',
            'method' => request()?->method() ?? 'cli',
            'timestamp' => now()->toDateTimeString(),
        ];

        Log::channel($channel)->error('Custom error occurred', $errorData);
    }
}
