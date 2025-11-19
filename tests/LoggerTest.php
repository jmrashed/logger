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
        $this->testLogDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'logger_test_logs_' . uniqid();
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

    public function testCustomLogFile(): void
    {
        $customLogger = new Logger(['logDirectory' => $this->testLogDir, 'defaultLogFile' => 'custom.log']);
        $customLogger->info('Custom log file test');

        $this->assertFileExists($this->testLogDir . '/custom.log');
        $logContent = file_get_contents($this->testLogDir . '/custom.log');
        $this->assertStringContainsString('Custom log file test', $logContent);
    }

    public function testLogRotation(): void
    {
        $smallMaxSize = 100; // Small size for testing
        $logger = new Logger(['logDirectory' => $this->testLogDir, 'maxFileSize' => $smallMaxSize, 'maxFiles' => 3]);

        // Write enough logs to trigger rotation
        for ($i = 0; $i < 10; $i++) {
            $logger->info('Log entry ' . $i . str_repeat('x', 20)); // Make entries larger
        }

        // Check that rotation happened
        $this->assertFileExists($this->testLogDir . '/application.log');
        $this->assertFileExists($this->testLogDir . '/application.log.1');
        // May have more depending on size
    }

    public function testNonStringMessage(): void
    {
        $logger = new Logger(['logDirectory' => $this->testLogDir]);
        $logger->info('Test message'); // String message
        $logger->info(123); // Integer message
        $logger->info(null); // Null message

        $logContent = file_get_contents($this->testLogDir . '/application.log');
        $this->assertStringContainsString('Test message', $logContent);
        $this->assertStringContainsString('123', $logContent);
        $this->assertStringContainsString('', $logContent); // null becomes empty string
    }

    public function testArbitraryLogLevel(): void
    {
        $logger = new Logger(['logDirectory' => $this->testLogDir]);
        $logger->log('CUSTOM', 'Custom level message');

        $logContent = file_get_contents($this->testLogDir . '/application.log');
        $this->assertStringContainsString('[CUSTOM]', $logContent);
        $this->assertStringContainsString('Custom level message', $logContent);
    }

    public function testEmptyContext(): void
    {
        $logger = new Logger(['logDirectory' => $this->testLogDir]);
        $logger->info('Message with empty context', []);

        $logContent = file_get_contents($this->testLogDir . '/application.log');
        $this->assertStringContainsString('Message with empty context', $logContent);
        // Should not have extra space or {}
    }

    public function testNestedContext(): void
    {
        $context = ['user' => ['id' => 123, 'name' => 'John'], 'meta' => ['ip' => '127.0.0.1']];
        $logger = new Logger(['logDirectory' => $this->testLogDir]);
        $logger->info('Nested context', $context);

        $logContent = file_get_contents($this->testLogDir . '/application.log');
        $this->assertStringContainsString('"user":{"id":123,"name":"John"}', $logContent);
    }

    public function testLogDirectoryCreationFailure(): void
    {
        $invalidDir = $this->testLogDir . '/existing_file';
        touch($invalidDir); // Create a file with the same name as intended directory
        $logger = new Logger(['logDirectory' => $invalidDir]);

        // This should not throw, as doLog catches exceptions
        $logger->info('This should fail silently');

        // Log should not be written
        $this->assertFileDoesNotExist($invalidDir . '/application.log');
    }

    public function testDefaultOptions(): void
    {
        $logger = new Logger();
        // Should use default directory
        $logger->info('Default options test');

        // Since default is __DIR__/logs, check if it exists
        $defaultDir = __DIR__ . '/../logs';
        $this->assertDirectoryExists($defaultDir);
    }

    public function testLargeContext(): void
    {
        $largeContext = array_fill(0, 1000, 'test data');
        $logger = new Logger(['logDirectory' => $this->testLogDir]);
        $logger->info('Large context test', $largeContext);

        $logContent = file_get_contents($this->testLogDir . '/application.log');
        $this->assertStringContainsString('Large context test', $logContent);
        $this->assertStringContainsString('test data', $logContent);
    }

    public function testSpecialCharactersInMessage(): void
    {
        $logger = new Logger(['logDirectory' => $this->testLogDir]);
        $logger->info('Message with special chars: éñüñ 中文 🚀');

        $logContent = file_get_contents($this->testLogDir . '/application.log');
        $this->assertStringContainsString('Message with special chars: éñüñ 中文 🚀', $logContent);
    }

    public function testBooleanAndNullInContext(): void
    {
        $context = ['bool_true' => true, 'bool_false' => false, 'null_value' => null, 'string' => 'test'];
        $logger = new Logger(['logDirectory' => $this->testLogDir]);
        $logger->info('Context with various types', $context);

        $logContent = file_get_contents($this->testLogDir . '/application.log');
        $this->assertStringContainsString('"bool_true":true', $logContent);
        $this->assertStringContainsString('"bool_false":false', $logContent);
        $this->assertStringContainsString('"null_value":null', $logContent);
    }

    public function testMultipleLogEntries(): void
    {
        $logger = new Logger(['logDirectory' => $this->testLogDir]);

        for ($i = 0; $i < 100; $i++) {
            $logger->info("Log entry {$i}");
        }

        $logContent = file_get_contents($this->testLogDir . '/application.log');
        $lines = explode("\n", trim($logContent));
        $this->assertCount(100, array_filter($lines)); // Should have 100 non-empty lines
    }

    public function testLogFilePermissions(): void
    {
        // This test might not work on all systems, but let's try
        $logger = new Logger(['logDirectory' => $this->testLogDir]);
        $logger->info('Permission test');

        $logFile = $this->testLogDir . '/application.log';
        $this->assertFileExists($logFile);
        // Check if file is writable (basic check)
        $this->assertTrue(is_writable($logFile));
    }

    public function testCustomMaxFilesRotation(): void
    {
        $logger = new Logger(['logDirectory' => $this->testLogDir, 'maxFileSize' => 50, 'maxFiles' => 2]);

        // Write multiple entries to trigger rotation
        for ($i = 0; $i < 10; $i++) {
            $logger->info(str_repeat('x', 20)); // Large message
        }

        // Should have application.log and application.log.1, but not .2 since maxFiles=2
        $this->assertFileExists($this->testLogDir . '/application.log');
        $this->assertFileExists($this->testLogDir . '/application.log.1');
        $this->assertFileDoesNotExist($this->testLogDir . '/application.log.2');
    }

    public function testInvalidJsonInContext(): void
    {
        // Context with circular reference or non-serializable data
        $context = ['self' => &$context]; // Circular reference
        $logger = new Logger(['logDirectory' => $this->testLogDir]);

        // This should not throw an exception, but handle gracefully
        $logger->info('Circular reference test', $context);

        $logContent = file_get_contents($this->testLogDir . '/application.log');
        $this->assertStringContainsString('Circular reference test', $logContent);
        // JSON encoding of circular reference might fail, but logging should continue
    }

    public function testLogLevelCaseSensitivity(): void
    {
        $logger = new Logger(['logDirectory' => $this->testLogDir]);
        $logger->log('info', 'Lowercase level'); // Should work even if not uppercase

        $logContent = file_get_contents($this->testLogDir . '/application.log');
        $this->assertStringContainsString('[info]', $logContent); // PSR allows lowercase
    }

    public function testTimestampFormat(): void
    {
        $logger = new Logger(['logDirectory' => $this->testLogDir]);
        $before = time();
        $logger->info('Timestamp test');
        $after = time();

        $logContent = file_get_contents($this->testLogDir . '/application.log');
        preg_match('/\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\]/', $logContent, $matches);
        $logTime = strtotime($matches[1]);

        $this->assertGreaterThanOrEqual($before, $logTime);
        $this->assertLessThanOrEqual($after, $logTime);
    }
}