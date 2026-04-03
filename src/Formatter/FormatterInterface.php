<?php

declare(strict_types=1);

namespace DevLogger\Formatter;

interface FormatterInterface
{
    public function format(string $level, string $message, array $context = []): string;
    public function formatJson(string $level, string $message, array $context = []): string;
}