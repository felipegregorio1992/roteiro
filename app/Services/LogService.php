<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class LogService
{
    /**
     * Log de atividades do usuário
     */
    public static function logActivity(string $action, array $data = [], ?Request $request = null): void
    {
        $logData = [
            'action' => $action,
            'user_id' => Auth::id(),
            'user_name' => Auth::user()?->name,
            'timestamp' => now(),
            'ip_address' => $request?->ip() ?? request()->ip(),
            'user_agent' => $request?->userAgent() ?? request()->userAgent(),
            'data' => $data,
        ];

        Log::channel('activities')->info('User activity', $logData);
    }

    /**
     * Log de erros de validação
     */
    public static function logValidationError(string $form, array $errors, array $input = []): void
    {
        $logData = [
            'form' => $form,
            'user_id' => Auth::id(),
            'errors' => $errors,
            'input' => $input,
            'timestamp' => now(),
            'ip_address' => request()->ip(),
        ];

        Log::channel('validation')->warning('Validation failed', $logData);
    }

    /**
     * Log de operações de arquivo
     */
    public static function logFileOperation(string $operation, string $filename, array $data = []): void
    {
        $logData = [
            'operation' => $operation,
            'filename' => $filename,
            'user_id' => Auth::id(),
            'timestamp' => now(),
            'data' => $data,
        ];

        Log::channel('files')->info('File operation', $logData);
    }

    /**
     * Log de performance
     */
    public static function logPerformance(string $operation, float $duration, array $metrics = []): void
    {
        $logData = [
            'operation' => $operation,
            'duration_ms' => $duration,
            'user_id' => Auth::id(),
            'timestamp' => now(),
            'metrics' => $metrics,
        ];

        Log::channel('performance')->info('Performance metric', $logData);
    }

    /**
     * Log de segurança
     */
    public static function logSecurity(string $event, array $data = []): void
    {
        $logData = [
            'security_event' => $event,
            'user_id' => Auth::id(),
            'timestamp' => now(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'data' => $data,
        ];

        Log::channel('security')->warning('Security event', $logData);
    }

    /**
     * Log de backup
     */
    public static function logBackup(string $operation, array $data = []): void
    {
        $logData = [
            'backup_operation' => $operation,
            'user_id' => Auth::id(),
            'timestamp' => now(),
            'data' => $data,
        ];

        Log::channel('backup')->info('Backup operation', $logData);
    }

    /**
     * Log de cache
     */
    public static function logCache(string $operation, string $key, array $data = []): void
    {
        $logData = [
            'cache_operation' => $operation,
            'cache_key' => $key,
            'user_id' => Auth::id(),
            'timestamp' => now(),
            'data' => $data,
        ];

        Log::channel('cache')->debug('Cache operation', $logData);
    }
}
