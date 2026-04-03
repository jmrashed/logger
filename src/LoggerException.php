<?php

declare(strict_types=1);

namespace DevLogger;

use Exception;
use Throwable;

class LoggerException extends Exception
{
    private ?string $context = null;

    public function __construct(
        string $message = '',
        int $code = 0,
        ?Throwable $previous = null,
        ?string $context = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->context = $context;
    }

    public function getContext(): ?string
    {
        return $this->context;
    }

    public static function forDirectoryCreation(string $path): self
    {
        return new self(
            "Failed to create log directory: {$path}",
            1001,
            null,
            $path
        );
    }

    public static function forFileWrite(string $path): self
    {
        return new self(
            "Failed to write to log file: {$path}",
            1002,
            null,
            $path
        );
    }

    public static function forInvalidPath(string $path): self
    {
        return new self(
            "Invalid log path detected: {$path}",
            1003,
            null,
            $path
        );
    }

    public static function forRotation(string $path): self
    {
        return new self(
            "Failed to rotate log file: {$path}",
            1004,
            null,
            $path
        );
    }

    public static function forJsonEncoding(string $data): self
    {
        return new self(
            'Failed to encode data to JSON',
            1005,
            null,
            $data
        );
    }
}