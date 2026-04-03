<?php

declare(strict_types=1);

namespace DevLogger;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Exception;
use RuntimeException;

class Logger implements LoggerInterface
{
    private const LOG_LEVELS = ['EMERGENCY', 'ALERT', 'CRITICAL', 'ERROR', 'WARNING', 'NOTICE', 'INFO', 'DEBUG'];

    private const LOG_LEVEL_PRIORITY = [
        'DEBUG' => 0,
        'INFO' => 1,
        'NOTICE' => 2,
        'WARNING' => 3,
        'ERROR' => 4,
        'CRITICAL' => 5,
        'ALERT' => 6,
        'EMERGENCY' => 7,
    ];

    private string $logDirectory;
    private string $defaultLogFile;
    private int $maxFileSize;
    private int $maxFiles;
    private int $minLevel;
    private bool $jsonFormat;
    private ?string $channelName = null;
    private ?string $lastError = null;
    private array $context = [];

    public function __construct(array $options = [])
    {
        $this->logDirectory = $options['logDirectory'] ?? __DIR__ . DIRECTORY_SEPARATOR . 'logs';
        $this->defaultLogFile = $options['defaultLogFile'] ?? 'application.log';
        $this->maxFileSize = $options['maxFileSize'] ?? 10485760;
        $this->maxFiles = $options['maxFiles'] ?? 5;
        $this->minLevel = $this->parseLogLevel($options['minLevel'] ?? 'DEBUG');
        $this->jsonFormat = $options['jsonFormat'] ?? false;
    }

    public function emergency(mixed $message, array $context = []): void
    {
        $this->log(LogLevel::EMERGENCY, $message, $context);
    }

    public function alert(mixed $message, array $context = []): void
    {
        $this->log(LogLevel::ALERT, $message, $context);
    }

    public function critical(mixed $message, array $context = []): void
    {
        $this->log(LogLevel::CRITICAL, $message, $context);
    }

    public function error(mixed $message, array $context = []): void
    {
        $this->log(LogLevel::ERROR, $message, $context);
    }

    public function warning(mixed $message, array $context = []): void
    {
        $this->log(LogLevel::WARNING, $message, $context);
    }

    public function notice(mixed $message, array $context = []): void
    {
        $this->log(LogLevel::NOTICE, $message, $context);
    }

    public function info(mixed $message, array $context = []): void
    {
        $this->log(LogLevel::INFO, $message, $context);
    }

    public function debug(mixed $message, array $context = []): void
    {
        $this->log(LogLevel::DEBUG, $message, $context);
    }

    public function log($level, mixed $message, array $context = []): void
    {
        if (!is_string($level)) {
            $level = (string) $level;
        }

        $level = strtoupper($level);

        if (!in_array($level, self::LOG_LEVELS, true)) {
            $level = 'INFO';
        }

        if (!$this->shouldLog($level)) {
            return;
        }

        $message = $this->sanitizeMessage($message);
        $context = $this->sanitizeContext(array_merge($this->context, $context));

        $this->doLog($level, $message, $context);
    }

    private function shouldLog(string $level): bool
    {
        $levelPriority = self::LOG_LEVEL_PRIORITY[$level] ?? 0;
        return $levelPriority >= $this->minLevel;
    }

    private function parseLogLevel(string $level): int
    {
        $level = strtoupper($level);
        return self::LOG_LEVEL_PRIORITY[$level] ?? 0;
    }

    public function setMinLevel(string $level): self
    {
        $this->minLevel = $this->parseLogLevel($level);
        return $this;
    }

    public function setJsonFormat(bool $json = true): self
    {
        $this->jsonFormat = $json;
        return $this;
    }

    public function withContext(array $context): self
    {
        $clone = clone $this;
        $clone->context = array_merge($this->context, $context);
        return $clone;
    }

    public function withoutContext(): self
    {
        $clone = clone $this;
        $clone->context = [];
        return $clone;
    }

    public function withName(string $name): self
    {
        $clone = clone $this;
        $clone->channelName = $name;
        return $clone;
    }

    public function getName(): ?string
    {
        return $this->channelName;
    }

    public function getLastError(): ?string
    {
        return $this->lastError;
    }

    public function clear(): bool
    {
        $logFile = $this->logDirectory . DIRECTORY_SEPARATOR . $this->defaultLogFile;
        if (!file_exists($logFile)) {
            return true;
        }
        return file_put_contents($logFile, '') !== false;
    }

    public function readLogs(int $lines = 100, bool $reverse = false): array
    {
        $logFile = $this->logDirectory . DIRECTORY_SEPARATOR . $this->defaultLogFile;
        if (!file_exists($logFile)) {
            return [];
        }

        $content = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($content === false) {
            return [];
        }

        if ($reverse) {
            $content = array_reverse($content);
        }

        return array_slice($content, 0, $lines);
    }

    public function getLogPath(): string
    {
        return $this->logDirectory . DIRECTORY_SEPARATOR . $this->defaultLogFile;
    }

    protected function doLog(string $level, mixed $message, array $context = []): void
    {
        try {
            $this->ensureLogDirectory();

            $logFile = $this->logDirectory . DIRECTORY_SEPARATOR . $this->defaultLogFile;

            $this->rotateLogIfNeeded($logFile);

            $logEntry = $this->jsonFormat 
                ? $this->formatJsonEntry($level, $message, $context)
                : $this->formatLogEntry($level, $message, $context);

            $result = file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);

            if ($result === false) {
                $this->lastError = 'Failed to write to log file';
            }

        } catch (Exception $e) {
            $this->lastError = $e->getMessage();
        }
    }

    protected function formatLogEntry(string $level, mixed $message, array $context): string
    {
        $timestamp = date('Y-m-d H:i:s');
        $contextString = !empty($context) ? ' ' . $this->safeJsonEncode($context) : '';
        $channel = $this->channelName ? " [{$this->channelName}]" : '';

        return "[{$timestamp}] [{$level}]{$channel} " . (string)$message . "{$contextString}" . PHP_EOL;
    }

    protected function formatJsonEntry(string $level, mixed $message, array $context): string
    {
        $entry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'level' => $level,
            'message' => $message,
            'channel' => $this->channelName,
            'context' => $context,
        ];

        return json_encode($entry, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL;
    }

    protected function sanitizeMessage(mixed $message): string
    {
        if (is_null($message)) {
            return '';
        }
        if (is_scalar($message)) {
            return (string) $message;
        }
        if (is_array($message)) {
            return $this->safeJsonEncode($message);
        }
        return (string) $message;
    }

    protected function sanitizeContext(array $context): array
    {
        $sanitized = [];
        foreach ($context as $key => $value) {
            $sanitized[$this->sanitizeKey($key)] = $this->sanitizeValue($value);
        }
        return $sanitized;
    }

    protected function sanitizeKey(mixed $key): string
    {
        if (is_scalar($key)) {
            return preg_replace('/[\r\n\t]/', '_', (string) $key);
        }
        return 'unknown';
    }

    protected function sanitizeValue(mixed $value): mixed
    {
        if (is_scalar($value)) {
            if (is_string($value)) {
                return preg_replace('/[\r\n]/', ' ', $value);
            }
            return $value;
        }
        if (is_array($value)) {
            return $this->sanitizeContext($value);
        }
        if (is_object($value)) {
            return ['__class' => get_class($value), '__toString' => method_exists($value, '__toString') ? (string) $value : null];
        }
        if (is_resource($value)) {
            return ['__type' => 'resource', '__resource_type' => get_resource_type($value)];
        }
        if (is_null($value)) {
            return null;
        }
        return (string) $value;
    }

    protected function safeJsonEncode(mixed $data): string
    {
        $json = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        
        if ($json === false) {
            return '{"__json_error": "Encoding failed"}';
        }
        
        return $json;
    }

    protected function ensureLogDirectory(): void
    {
        if (!is_dir($this->logDirectory)) {
            if (!mkdir($this->logDirectory, 0755, true)) {
                throw new RuntimeException('Cannot create log directory: ' . $this->logDirectory);
            }
        }
    }

    protected function rotateLogIfNeeded(string $logFile): void
    {
        if (!file_exists($logFile)) {
            return;
        }

        $fileSize = @filesize($logFile);
        if ($fileSize === false || $fileSize < $this->maxFileSize) {
            return;
        }

        for ($i = $this->maxFiles - 1; $i > 0; $i--) {
            $oldFile = $logFile . '.' . $i;
            $newFile = $logFile . '.' . ($i + 1);

            if (file_exists($oldFile)) {
                if ($i === $this->maxFiles - 1) {
                    @unlink($oldFile);
                } else {
                    @rename($oldFile, $newFile);
                }
            }
        }

        @rename($logFile, $logFile . '.1');
    }
}