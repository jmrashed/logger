<?php

declare(strict_types=1);

namespace DevLogger;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use DateTimeImmutable;

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

    private const DEFAULT_TIMESTAMP_FORMAT = 'Y-m-d H:i:s.u';

    private string $logDirectory;
    private string $defaultLogFile;
    private int $maxFileSize;
    private int $maxFiles;
    private int $minLevel;
    private bool $jsonFormat;
    private ?string $channelName = null;
    private ?string $lastError = null;
    private array $context = [];
    private ?string $timestampFormat = null;
    private bool $includeMicroseconds = false;

    private ?int $fileHandle = null;
    private ?string $lastOpenedPath = null;

    public function __construct(array $options = [])
    {
        $this->logDirectory = $this->validatePath($options['logDirectory'] ?? __DIR__ . DIRECTORY_SEPARATOR . 'logs');
        $this->defaultLogFile = $this->validateFileName($options['defaultLogFile'] ?? 'application.log');
        $this->maxFileSize = $this->validatePositiveInt($options['maxFileSize'] ?? 10485760, 10485760);
        $this->maxFiles = $this->validatePositiveInt($options['maxFiles'] ?? 5, 5);
        $this->minLevel = $this->parseLogLevel($options['minLevel'] ?? 'DEBUG');
        $this->jsonFormat = $options['jsonFormat'] ?? false;
        $this->includeMicroseconds = $options['includeMicroseconds'] ?? false;
        $this->timestampFormat = $options['timestampFormat'] ?? null;

        if (isset($options['maxFileSize']) && $options['maxFileSize'] < 1024) {
            $this->maxFileSize = 1024;
        }
    }

    public function __destruct()
    {
        $this->closeFileHandle();
    }

    private function validatePath(string $path): string
    {
        $normalized = rtrim($path, DIRECTORY_SEPARATOR);
        
        if (strpos($normalized, '..') !== false) {
            throw new LoggerException("Path traversal detected: {$path}", 1003);
        }
        
        return $normalized;
    }

    private function validateFileName(string $filename): string
    {
        if (preg_match('/[\x00-\x1F\x7F]/', $filename)) {
            throw new LoggerException("Invalid filename: {$filename}", 1003);
        }

        $forbiddenChars = ['/', '\\', "\0"];
        foreach ($forbiddenChars as $char) {
            if (strpos($filename, $char) !== false) {
                throw new LoggerException("Forbidden character in filename: {$filename}", 1003);
            }
        }

        return $filename;
    }

    private function validatePositiveInt(mixed $value, int $default): int
    {
        if (!is_int($value) || $value <= 0) {
            return $default;
        }
        return $value;
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

    public function setTimestampFormat(string $format): self
    {
        $this->timestampFormat = $format;
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
        if (preg_match('/[\r\n\t]/', $name)) {
            throw new LoggerException("Invalid channel name: contains forbidden characters", 1003);
        }
        
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
        $logFile = $this->getLogPath();
        if (!file_exists($logFile)) {
            return true;
        }
        
        $result = file_put_contents($logFile, '', LOCK_EX);
        
        if ($result === false) {
            $this->lastError = 'Failed to clear log file';
            return false;
        }
        
        return true;
    }

    public function readLogs(int $lines = 100, bool $reverse = false): array
    {
        $logFile = $this->getLogPath();
        
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

    public function getLogDirectory(): string
    {
        return $this->logDirectory;
    }

    public function getLogFileName(): string
    {
        return $this->defaultLogFile;
    }

    public function getMaxFileSize(): int
    {
        return $this->maxFileSize;
    }

    public function getMaxFiles(): int
    {
        return $this->maxFiles;
    }

    public function isJsonFormat(): bool
    {
        return $this->jsonFormat;
    }

    public function getMinLevel(): int
    {
        return $this->minLevel;
    }

    protected function doLog(string $level, mixed $message, array $context = []): void
    {
        try {
            $this->ensureLogDirectory();

            $logFile = $this->getLogPath();

            if ($this->shouldRotate($logFile)) {
                $this->rotateLog($logFile);
            }

            $logEntry = $this->jsonFormat 
                ? $this->formatJsonEntry($level, $message, $context)
                : $this->formatLogEntry($level, $message, $context);

            $result = $this->writeToFile($logFile, $logEntry);

            if ($result === false) {
                $this->lastError = 'Failed to write to log file';
            }

        } catch (LoggerException $e) {
            $this->lastError = $e->getMessage();
        } catch (\Exception $e) {
            $this->lastError = $e->getMessage();
        }
    }

    private function writeToFile(string $path, string $data): int|false
    {
        if ($this->fileHandle === null || $this->lastOpenedPath !== $path) {
            $this->closeFileHandle();
            
            $handle = fopen($path, 'a');
            
            if ($handle === false) {
                $this->lastError = "Cannot open file: {$path}";
                return false;
            }
            
            $this->fileHandle = (int) $handle;
            $this->lastOpenedPath = $path;
        }

        if (flock($this->fileHandle, LOCK_EX)) {
            $result = fwrite($this->fileHandle, $data);
            flock($this->fileHandle, LOCK_UN);
            return $result;
        }

        return file_put_contents($path, $data, FILE_APPEND | LOCK_EX);
    }

    private function closeFileHandle(): void
    {
        if ($this->fileHandle !== null) {
            fclose($this->fileHandle);
            $this->fileHandle = null;
            $this->lastOpenedPath = null;
        }
    }

    private function shouldRotate(string $logFile): bool
    {
        if (!file_exists($logFile)) {
            return false;
        }

        $fileSize = @filesize($logFile);
        return $fileSize !== false && $fileSize >= $this->maxFileSize;
    }

    protected function formatLogEntry(string $level, mixed $message, array $context): string
    {
        $timestamp = $this->getTimestamp();
        $contextString = !empty($context) ? ' ' . $this->safeJsonEncode($context) : '';
        $channel = $this->channelName ? " [{$this->channelName}]" : '';

        return "[{$timestamp}] [{$level}]{$channel} " . (string)$message . "{$contextString}" . PHP_EOL;
    }

    protected function formatJsonEntry(string $level, mixed $message, array $context): string
    {
        $entry = [
            'timestamp' => $this->getTimestamp(),
            'level' => $level,
            'message' => $message,
            'channel' => $this->channelName,
            'context' => $context,
        ];

        return json_encode($entry, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL;
    }

    private function getTimestamp(): string
    {
        if ($this->timestampFormat !== null) {
            return (new DateTimeImmutable())->format($this->timestampFormat);
        }
        
        if ($this->includeMicroseconds) {
            return (new DateTimeImmutable())->format(self::DEFAULT_TIMESTAMP_FORMAT);
        }
        
        return (new DateTimeImmutable())->format('Y-m-d H:i:s');
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
            if ($value instanceof \DateTimeInterface) {
                return $value->format(\DateTimeInterface::ISO8601);
            }
            if ($value instanceof \JsonSerializable) {
                return ['__json' => $value->jsonSerialize()];
            }
            return [
                '__class' => get_class($value),
                '__toString' => method_exists($value, '__toString') ? (string) $value : null
            ];
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
            return '{"__json_error": "Encoding failed", "error": "' . json_last_error_msg() . '"}';
        }
        
        return $json;
    }

    protected function ensureLogDirectory(): void
    {
        if (!is_dir($this->logDirectory)) {
            if (!mkdir($this->logDirectory, 0755, true)) {
                throw LoggerException::forDirectoryCreation($this->logDirectory);
            }
        }
    }

    protected function rotateLog(string $logFile): void
    {
        $this->closeFileHandle();

        for ($i = $this->maxFiles - 1; $i > 0; $i--) {
            $oldFile = $logFile . '.' . $i;
            $newFile = $logFile . '.' . ($i + 1);

            if (file_exists($oldFile)) {
                if ($i === $this->maxFiles - 1) {
                    if (!@unlink($oldFile)) {
                        $this->lastError = "Failed to delete old log file: {$oldFile}";
                    }
                } else {
                    if (!@rename($oldFile, $newFile)) {
                        $this->lastError = "Failed to rotate log file: {$oldFile} -> {$newFile}";
                    }
                }
            }
        }

        if (!@rename($logFile, $logFile . '.1')) {
            throw LoggerException::forRotation($logFile);
        }
    }
}