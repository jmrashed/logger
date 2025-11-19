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
    private Logger $logger;

    protected function setUp(): void
    {
        $this->testLogDir = sys_get_temp_dir() . '/logger_test_logs_' . uniqid();
        if (!is_dir($this->testLogDir)) {
            mkdir($this->testLogDir, 0755, true);
        }
        $this->logger = new Logger(['logDirectory' => $this->testLogDir]);
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
        $this->logger->debug('Test debug message');

        $logContent = file_get_contents($this->testLogDir . '/application.log');
        $this->assertStringContainsString('DEBUG', $logContent);
        $this->assertStringContainsString('Test debug message', $logContent);
    }

    public function testInfoLog(): void
    {
        $this->logger->info('Test info message');

        $logContent = file_get_contents($this->testLogDir . '/application.log');
        $this->assertStringContainsString('INFO', $logContent);
        $this->assertStringContainsString('Test info message', $logContent);
    }

    public function testWarningLog(): void
    {
        $this->logger->warning('Test warning message');

        $logContent = file_get_contents($this->testLogDir . '/application.log');
        $this->assertStringContainsString('WARNING', $logContent);
        $this->assertStringContainsString('Test warning message', $logContent);
    }

    public function testErrorLog(): void
    {
        $this->logger->error('Test error message');

        $logContent = file_get_contents($this->testLogDir . '/application.log');
        $this->assertStringContainsString('ERROR', $logContent);
        $this->assertStringContainsString('Test error message', $logContent);
    }

    public function testCriticalLog(): void
    {
        $this->logger->critical('Test critical message');

        $logContent = file_get_contents($this->testLogDir . '/application.log');
        $this->assertStringContainsString('CRITICAL', $logContent);
        $this->assertStringContainsString('Test critical message', $logContent);
    }

    public function testEmergencyLog(): void
    {
        $this->logger->emergency('Test emergency message');

        $logContent = file_get_contents($this->testLogDir . '/application.log');
        $this->assertStringContainsString('EMERGENCY', $logContent);
        $this->assertStringContainsString('Test emergency message', $logContent);
    }

    public function testAlertLog(): void
    {
        $this->logger->alert('Test alert message');

        $logContent = file_get_contents($this->testLogDir . '/application.log');
        $this->assertStringContainsString('ALERT', $logContent);
        $this->assertStringContainsString('Test alert message', $logContent);
    }

    public function testNoticeLog(): void
    {
        $this->logger->notice('Test notice message');

        $logContent = file_get_contents($this->testLogDir . '/application.log');
        $this->assertStringContainsString('NOTICE', $logContent);
        $this->assertStringContainsString('Test notice message', $logContent);
    }

    public function testLogWithContext(): void
    {
        $context = ['user_id' => 123, 'action' => 'login'];
        $this->logger->info('User action', $context);

        $logContent = file_get_contents($this->testLogDir . '/application.log');
        $this->assertStringContainsString('User action', $logContent);
        $this->assertStringContainsString('"user_id":123', $logContent);
        $this->assertStringContainsString('"action":"login"', $logContent);
    }

    public function testLogFormat(): void
    {
        $this->logger->info('Test message');

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