<?php

declare(strict_types=1);

namespace DevLogger\Tests;

use DevLogger\Logger;
use PHPUnit\Framework\TestCase;

/**
 * Test cases for the Logger class
 */
class LoggerTest extends TestCase
{
    private string $testLogDir;

    protected function setUp(): void
    {
        $this->testLogDir = sys_get_temp_dir() . '/logger_test_logs_' . uniqid();
        if (!is_dir($this->testLogDir)) {
            mkdir($this->testLogDir, 0755, true);
        }

        // Override the log directory for testing
        $reflection = new \ReflectionClass(Logger::class);
        $property = $reflection->getProperty('logDirectory');
        $property->setAccessible(true);
        $property->setValue($this->testLogDir);
    }

    protected function tearDown(): void
    {
        // Clean up test log files
        $files = glob($this->testLogDir . '/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        if (is_dir($this->testLogDir)) {
            rmdir($this->testLogDir);
        }
    }

    public function testDebugLog(): void
    {
        $result = Logger::debug('Test debug message');
        $this->assertTrue($result);

        $logContent = file_get_contents($this->testLogDir . '/application.log');
        $this->assertStringContains('DEBUG', $logContent);
        $this->assertStringContains('Test debug message', $logContent);
    }

    public function testInfoLog(): void
    {
        $result = Logger::info('Test info message');
        $this->assertTrue($result);

        $logContent = file_get_contents($this->testLogDir . '/application.log');
        $this->assertStringContains('INFO', $logContent);
        $this->assertStringContains('Test info message', $logContent);
    }

    public function testWarningLog(): void
    {
        $result = Logger::warning('Test warning message');
        $this->assertTrue($result);

        $logContent = file_get_contents($this->testLogDir . '/application.log');
        $this->assertStringContains('WARNING', $logContent);
        $this->assertStringContains('Test warning message', $logContent);
    }

    public function testErrorLog(): void
    {
        $result = Logger::error('Test error message');
        $this->assertTrue($result);

        $logContent = file_get_contents($this->testLogDir . '/application.log');
        $this->assertStringContains('ERROR', $logContent);
        $this->assertStringContains('Test error message', $logContent);
    }

    public function testCriticalLog(): void
    {
        $result = Logger::critical('Test critical message');
        $this->assertTrue($result);

        $logContent = file_get_contents($this->testLogDir . '/application.log');
        $this->assertStringContains('CRITICAL', $logContent);
        $this->assertStringContains('Test critical message', $logContent);
    }

    public function testLogWithContext(): void
    {
        $context = ['user_id' => 123, 'action' => 'login'];
        $result = Logger::info('User action', $context);
        $this->assertTrue($result);

        $logContent = file_get_contents($this->testLogDir . '/application.log');
        $this->assertStringContains('User action', $logContent);
        $this->assertStringContains('"user_id":123', $logContent);
        $this->assertStringContains('"action":"login"', $logContent);
    }

    public function testLogFormat(): void
    {
        Logger::info('Test message');

        $logContent = file_get_contents($this->testLogDir . '/application.log');
        $lines = explode("\n", trim($logContent));
        $lastLine = end($lines);

        // Check format: [timestamp] [LEVEL] message
        $this->assertMatchesRegularExpression('/^\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\] \[INFO\] Test message$/', $lastLine);
    }

    public function testLogDirectoryCreation(): void
    {
        // The setUp should have created the directory
        $this->assertDirectoryExists($this->testLogDir);
    }
}