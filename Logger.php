<?php

declare(strict_types=1);

/**
 * Production-ready Logger utility for development use
 * 
 * Usage:
 *   require_once '_Logger/Logger.php';
 *   Logger::info('User logged in', ['user_id' => 123]);
 *   Logger::error('Database connection failed');
 */
class Logger
{
    private const LOG_LEVELS = ['DEBUG', 'INFO', 'WARNING', 'ERROR', 'CRITICAL'];
    
    private static string $logDirectory = __DIR__ . DIRECTORY_SEPARATOR . 'logs';
    private static string $defaultLogFile = 'application.log';
    private static int $maxFileSize = 10485760; // 10MB
    private static int $maxFiles = 5;

    /**
     * Log debug message
     */
    public static function debug(string $message, array $context = []): bool
    {
        return self::log('DEBUG', $message, $context);
    }

    /**
     * Log info message
     */
    public static function info(string $message, array $context = []): bool
    {
        return self::log('INFO', $message, $context);
    }

    /**
     * Log warning message
     */
    public static function warning(string $message, array $context = []): bool
    {
        return self::log('WARNING', $message, $context);
    }

    /**
     * Log error message
     */
    public static function error(string $message, array $context = []): bool
    {
        return self::log('ERROR', $message, $context);
    }

    /**
     * Log critical message
     */
    public static function critical(string $message, array $context = []): bool
    {
        return self::log('CRITICAL', $message, $context);
    }

    /**
     * Core logging method
     */
    private static function log(string $level, string $message, array $context = []): bool
    {
        try {
            self::ensureLogDirectory();
            
            $logFile = self::$logDirectory . DIRECTORY_SEPARATOR . self::$defaultLogFile;
            
            // Rotate log if needed
            self::rotateLogIfNeeded($logFile);
            
            $logEntry = self::formatLogEntry($level, $message, $context);
            
            return file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX) !== false;
            
        } catch (Exception $e) {
            // Fail silently in production, but you could add error_log here
            return false;
        }
    }

    /**
     * Format log entry
     */
    private static function formatLogEntry(string $level, string $message, array $context): string
    {
        $timestamp = date('Y-m-d H:i:s');
        $contextString = !empty($context) ? ' ' . json_encode($context, JSON_UNESCAPED_SLASHES) : '';
        
        return "[{$timestamp}] [{$level}] {$message}{$contextString}" . PHP_EOL;
    }

    /**
     * Ensure log directory exists
     */
    private static function ensureLogDirectory(): void
    {
        if (!is_dir(self::$logDirectory)) {
            if (!mkdir(self::$logDirectory, 0755, true)) {
                throw new RuntimeException('Cannot create log directory');
            }
        }
    }

    /**
     * Rotate log file if it exceeds max size
     */
    private static function rotateLogIfNeeded(string $logFile): void
    {
        if (!file_exists($logFile) || filesize($logFile) < self::$maxFileSize) {
            return;
        }

        // Rotate existing files
        for ($i = self::$maxFiles - 1; $i > 0; $i--) {
            $oldFile = $logFile . '.' . $i;
            $newFile = $logFile . '.' . ($i + 1);
            
            if (file_exists($oldFile)) {
                if ($i === self::$maxFiles - 1) {
                    unlink($oldFile); // Delete oldest
                } else {
                    rename($oldFile, $newFile);
                }
            }
        }
        
        // Move current log to .1
        rename($logFile, $logFile . '.1');
    }
}