<?php

declare(strict_types=1);

namespace DevLogger;

use Psr\Log\LoggerInterface;

/**
 * Production-ready Logger utility for development use
 *
 * @author Md Rasheduzzzaman <jmrashed@gmail.com>
 * @link https://github.com/jmrashed/logger
 * @license MIT
 *
 * Usage:
 *   require_once '_Logger/Logger.php';
 *   $logger = new \DevLogger\Logger(['logDirectory' => '/var/log']);
 *   $logger->info('User logged in', ['user_id' => 123]);
 *   $logger->error('Database connection failed');
 */
class Logger implements LoggerInterface
{
    private const LOG_LEVELS = ['EMERGENCY', 'ALERT', 'CRITICAL', 'ERROR', 'WARNING', 'NOTICE', 'INFO', 'DEBUG'];

    private string $logDirectory;
    private string $defaultLogFile;
    private int $maxFileSize;
    private int $maxFiles;

    /**
     * Constructor for Logger instance
     *
     * @param array $options Configuration options
     */
    public function __construct(array $options = [])
    {
        $this->logDirectory = $options['logDirectory'] ?? __DIR__ . DIRECTORY_SEPARATOR . 'logs';
        $this->defaultLogFile = $options['defaultLogFile'] ?? 'application.log';
        $this->maxFileSize = $options['maxFileSize'] ?? 10485760; // 10MB
        $this->maxFiles = $options['maxFiles'] ?? 5;
    }

    /**
     * System is unusable.
      */
     public function emergency(mixed $message, array $context = []): void
    {
        $this->log('EMERGENCY', $message, $context);
    }

    /**
     * Action must be taken immediately.
      */
     public function alert(mixed $message, array $context = []): void
    {
        $this->log('ALERT', $message, $context);
    }

    /**
     * Critical conditions.
      */
     public function critical(mixed $message, array $context = []): void
    {
        $this->log('CRITICAL', $message, $context);
    }

    /**
     * Runtime errors that do not require immediate action but should typically be logged and monitored.
      */
     public function error(mixed $message, array $context = []): void
    {
        $this->log('ERROR', $message, $context);
    }

    /**
     * Exceptional occurrences that are not errors.
      */
     public function warning(mixed $message, array $context = []): void
    {
        $this->log('WARNING', $message, $context);
    }

    /**
     * Normal but significant events.
      */
     public function notice(mixed $message, array $context = []): void
    {
        $this->log('NOTICE', $message, $context);
    }

    /**
     * Interesting events.
      */
     public function info(mixed $message, array $context = []): void
    {
        $this->log('INFO', $message, $context);
    }

    /**
     * Detailed debug information.
      */
     public function debug(mixed $message, array $context = []): void
    {
        $this->log('DEBUG', $message, $context);
    }

    /**
     * Logs with an arbitrary level.
      */
     public function log($level, mixed $message, array $context = []): void
    {
        $this->doLog($level, $message, $context);
    }

    /**
     * Core logging method
      */
     protected function doLog(string $level, mixed $message, array $context = []): void
    {
        try {
            $this->ensureLogDirectory();

            $logFile = $this->logDirectory . DIRECTORY_SEPARATOR . $this->defaultLogFile;

            // Rotate log if needed
            $this->rotateLogIfNeeded($logFile);

            $logEntry = $this->formatLogEntry($level, $message, $context);

            file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);

        } catch (Exception $e) {
            // Fail silently in production, but you could add error_log here
        }
    }

    /**
     * Format log entry
      */
     protected function formatLogEntry(string $level, mixed $message, array $context): string
     {
         $timestamp = date('Y-m-d H:i:s');
         $contextString = !empty($context) ? ' ' . json_encode($context, JSON_UNESCAPED_SLASHES) : '';

         return "[{$timestamp}] [{$level}] " . (string)$message . "{$contextString}" . PHP_EOL;
     }

    /**
     * Ensure log directory exists
     */
    protected function ensureLogDirectory(): void
    {
        if (!is_dir($this->logDirectory)) {
            if (!mkdir($this->logDirectory, 0755, true)) {
                throw new RuntimeException('Cannot create log directory');
            }
        }
    }

    /**
     * Rotate log file if it exceeds max size
     */
    protected function rotateLogIfNeeded(string $logFile): void
    {
        if (!file_exists($logFile) || filesize($logFile) < $this->maxFileSize) {
            return;
        }

        // Rotate existing files
        for ($i = $this->maxFiles - 1; $i > 0; $i--) {
            $oldFile = $logFile . '.' . $i;
            $newFile = $logFile . '.' . ($i + 1);

            if (file_exists($oldFile)) {
                if ($i === $this->maxFiles - 1) {
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