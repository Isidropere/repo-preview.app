<?php

namespace App\Services;

use App\Models\ApplicationError;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class ErrorLogService
{
    /**
     * Reports an error to the database.
     * 
     * @param Throwable $e
     * @return string The error reference ID
     */
    public function report(Throwable $e): string
    {
        $errorReference = (string) \Illuminate\Support\Str::uuid();

        try {
            // Use the container directly instead of Facades to be slightly safer
            $app = app();
            
            if ($app->resolved('db')) {
                $app->make('db')->transaction(function () use ($e, $errorReference, $app) {
                    // Check if request is bound before using request() helper
                    $hasRequest = $app->bound('request');
                    
                    \App\Models\ApplicationError::create([
                        'error_reference' => $errorReference,
                        'message'         => $e->getMessage(),
                        'stack_trace'     => $e->getTraceAsString(),
                        'url'             => $hasRequest ? request()->fullUrl() : 'N/A (Early Boot)',
                        'method'          => $hasRequest ? request()->method() : 'N/A',
                        'user_id'         => $app->resolved('auth') ? auth()->id() : null,
                        'ip_address'      => $hasRequest ? request()->ip() : null,
                        'user_agent'      => $hasRequest ? request()->userAgent() : null,
                        'input_data'      => $hasRequest ? request()->all() : null,
                    ]);
                });
            } else {
                // Fallback to standard PHP error log if DB is not ready
                error_log("ErrorLogService: DB not ready. Ref: $errorReference. Error: " . $e->getMessage());
            }

        } catch (Throwable $dbError) {
            // Last resort: standard PHP error log
            $msg = "Failed to log error to database. Reference: $errorReference. " .
                   "Original Error: {$e->getMessage()}. DB Error: {$dbError->getMessage()}";
            
            error_log($msg);
            
            try {
                if (app()->resolved('log')) {
                    \Illuminate\Support\Facades\Log::error($msg);
                }
            } catch (Throwable $ignore) {}
        }

        return $errorReference;
    }
}
