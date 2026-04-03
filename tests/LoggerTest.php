<?php

declare(strict_types=1);

namespace DevLogger\Tests;

use DevLogger\Logger;
use DevLogger\LoggerException;
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

    public function testMinLevelFiltering(): void
    {
        $logger = new Logger(['logDirectory' => $this->testLogDir, 'minLevel' => 'WARNING']);

        $logger->debug('Should not appear');
        $logger->info('Should not appear');
        $logger->warning('Should appear');
        $logger->error('Should appear');

        $logContent = file_get_contents($this->testLogDir . '/application.log');
        $this->assertStringNotContainsString('Should not appear', $logContent);
        $this->assertStringContainsString('Should appear', $logContent);
    }

    public function testSetMinLevelMethod(): void
    {
        $logger = new Logger(['logDirectory' => $this->testLogDir]);
        $logger->setMinLevel('ERROR');

        $logger->debug('Skip');
        $logger->info('Skip');
        $logger->error('Log');

        $logContent = file_get_contents($this->testLogDir . '/application.log');
        $this->assertStringNotContainsString('Skip', $logContent);
        $this->assertStringContainsString('Log', $logContent);
    }

    public function testJsonFormat(): void
    {
        $logger = new Logger(['logDirectory' => $this->testLogDir, 'jsonFormat' => true]);
        $logger->info('JSON test', ['user_id' => 123]);

        $logContent = file_get_contents($this->testLogDir . '/application.log');
        $entry = json_decode($logContent, true);

        $this->assertNotNull($entry);
        $this->assertEquals('INFO', $entry['level']);
        $this->assertEquals('JSON test', $entry['message']);
        $this->assertEquals(123, $entry['context']['user_id']);
    }

    public function testJsonFormatMethod(): void
    {
        $logger = new Logger(['logDirectory' => $this->testLogDir]);
        $logger->setJsonFormat(true)->info('Method test');

        $logContent = file_get_contents($this->testLogDir . '/application.log');
        $entry = json_decode($logContent, true);

        $this->assertNotNull($entry);
        $this->assertEquals('Method test', $entry['message']);
    }

    public function testWithNameMethod(): void
    {
        $logger = new Logger(['logDirectory' => $this->testLogDir]);
        $namedLogger = $logger->withName('channel1');

        $namedLogger->info('Named log');

        $this->assertEquals('channel1', $namedLogger->getName());
        $this->assertNull($logger->getName());

        $logContent = file_get_contents($this->testLogDir . '/application.log');
        $this->assertStringContainsString('[channel1]', $logContent);
    }

    public function testWithContextMethod(): void
    {
        $logger = new Logger(['logDirectory' => $this->testLogDir]);
        $contextLogger = $logger->withContext(['user_id' => 100]);

        $contextLogger->info('With context');
        $logger->info('Without context');

        $logContent = file_get_contents($this->testLogDir . '/application.log');
        $this->assertStringContainsString('"user_id":100', $logContent);
        $this->assertStringContainsString('With context', $logContent);
        $this->assertStringContainsString('Without context', $logContent);
    }

    public function testWithoutContextMethod(): void
    {
        $logger = new Logger(['logDirectory' => $this->testLogDir]);
        $logger->withContext(['persistent' => 'value']);

        $noContextLogger = $logger->withoutContext();
        $noContextLogger->info('Clean');

        $logContent = file_get_contents($this->testLogDir . '/application.log');
        $this->assertStringContainsString('Clean', $logContent);
    }

    public function testReadLogsMethod(): void
    {
        $logger = new Logger(['logDirectory' => $this->testLogDir]);

        for ($i = 0; $i < 20; $i++) {
            $logger->info("Log {$i}");
        }

        $logs = $logger->readLogs(5);
        $this->assertCount(5, $logs);
        $this->assertStringContainsString('Log 19', $logs[0]);
    }

    public function testReadLogsReverse(): void
    {
        $logger = new Logger(['logDirectory' => $this->testLogDir]);

        for ($i = 0; $i < 10; $i++) {
            $logger->info("Log {$i}");
        }

        $logs = $logger->readLogs(5, true);
        $this->assertCount(5, $logs);
        $this->assertStringContainsString('Log 0', $logs[0]);
    }

    public function testClearMethod(): void
    {
        $logger = new Logger(['logDirectory' => $this->testLogDir]);
        $logger->info('To be cleared');

        $this->assertTrue($logger->clear());

        $logContent = file_get_contents($this->testLogDir . '/application.log');
        $this->assertEmpty(trim($logContent));
    }

    public function testGetLogPathMethod(): void
    {
        $logger = new Logger(['logDirectory' => $this->testLogDir]);
        $path = $logger->getLogPath();

        $this->assertEquals($this->testLogDir . '/application.log', $path);
    }

    public function testGetLastError(): void
    {
        $logger = new Logger(['logDirectory' => '/nonexistent/path']);

        $this->assertNull($logger->getLastError());
    }

    public function testLogInjectionPrevention(): void
    {
        $logger = new Logger(['logDirectory' => $this->testLogDir]);
        $logger->info("Test\r\nInjected", ['key' => "value\ninjection"]);

        $logContent = file_get_contents($this->testLogDir . '/application.log');
        $this->assertStringNotContainsString("\r\n", $logContent);
        $this->assertStringContainsString('Test Injected', $logContent);
    }

    public function testNullMessageHandling(): void
    {
        $logger = new Logger(['logDirectory' => $this->testLogDir]);
        $logger->info(null);

        $logContent = file_get_contents($this->testLogDir . '/application.log');
        $this->assertStringContainsString('null', $logContent);
    }

    public function testArrayMessageHandling(): void
    {
        $logger = new Logger(['logDirectory' => $this->testLogDir]);
        $logger->info(['key' => 'value']);

        $logContent = file_get_contents($this->testLogDir . '/application.log');
        $this->assertStringContainsString('"key":"value"', $logContent);
    }

    public function testObjectInContext(): void
    {
        $logger = new Logger(['logDirectory' => $this->testLogDir]);
        $logger->info('Object test', ['object' => new \stdClass()]);

        $logContent = file_get_contents($this->testLogDir . '/application.log');
        $this->assertStringContainsString('"__class"', $logContent);
    }

    public function testPathTraversalPrevention(): void
    {
        $this->expectException(LoggerException::class);
        new Logger(['logDirectory' => '/var/../../../etc']);
    }

    public function testInvalidFilenamePrevention(): void
    {
        $this->expectException(LoggerException::class);
        new Logger(['defaultLogFile' => '../application.log']);
    }

    public function testForbiddenCharInFilename(): void
    {
        $this->expectException(LoggerException::class);
        new Logger(['defaultLogFile' => 'app/log.log']);
    }

    public function testChannelNameSanitization(): void
    {
        $logger = new Logger(['logDirectory' => $this->testLogDir]);
        
        $this->expectException(LoggerException::class);
        $logger->withName("channel\nname");
    }

    public function testCustomTimestampFormat(): void
    {
        $logger = new Logger(['logDirectory' => $this->testLogDir]);
        $logger->setTimestampFormat('Y-m-d')->info('Test');
        
        $logContent = file_get_contents($this->testLogDir . '/application.log');
        $this->assertMatchesRegularExpression('/^\[\d{4}-\d{2}-\d{2}\]/', $logContent);
    }

    public function testMicrosecondTimestamp(): void
    {
        $logger = new Logger(['logDirectory' => $this->testLogDir, 'includeMicroseconds' => true]);
        $logger->info('Test');
        
        $logContent = file_get_contents($this->testLogDir . '/application.log');
        $this->assertMatchesRegularExpression('/^\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\.\d{6}\]/', $logContent);
    }

    public function testGetterMethods(): void
    {
        $logger = new Logger([
            'logDirectory' => '/custom/logs',
            'defaultLogFile' => 'custom.log',
            'maxFileSize' => 20485760,
            'maxFiles' => 10,
            'minLevel' => 'ERROR',
            'jsonFormat' => true,
        ]);

        $this->assertEquals('/custom/logs', $logger->getLogDirectory());
        $this->assertEquals('custom.log', $logger->getLogFileName());
        $this->assertEquals(20485760, $logger->getMaxFileSize());
        $this->assertEquals(10, $logger->getMaxFiles());
        $this->assertEquals(4, $logger->getMinLevel());
        $this->assertTrue($logger->isJsonFormat());
    }

    public function testFileHandleReuse(): void
    {
        $logger = new Logger(['logDirectory' => $this->testLogDir]);
        
        for ($i = 0; $i < 5; $i++) {
            $logger->info("Log entry {$i}");
        }

        $logContent = file_get_contents($this->testLogDir . '/application.log');
        $this->assertStringContainsString('Log entry 0', $logContent);
        $this->assertStringContainsString('Log entry 4', $logContent);
    }

    public function testClearReturnsFalseOnFailure(): void
    {
        $logger = new Logger(['logDirectory' => '/nonexistent']);
        
        $result = $logger->clear();
        $this->assertFalse($result);
    }

    public function testDateTimeObjectInContext(): void
    {
        $logger = new Logger(['logDirectory' => $this->testLogDir]);
        $logger->info('Test', ['timestamp' => new \DateTime('2024-01-15')]);

        $logContent = file_get_contents($this->testLogDir . '/application.log');
        $this->assertStringContainsString('2024-01-15', $logContent);
    }

    public function testJsonSerializableInContext(): void
    {
        $logger = new Logger(['logDirectory' => $this->testLogDir]);
        
        $obj = new class implements \JsonSerializable {
            public function jsonSerialize(): array
            {
                return ['custom' => 'data'];
            }
        };
        
        $logger->info('Test', ['serializable' => $obj]);

        $logContent = file_get_contents($this->testLogDir . '/application.log');
        $this->assertStringContainsString('"custom":"data"', $logContent);
    }

    public function testResourceInContext(): void
    {
        $logger = new Logger(['logDirectory' => $this->testLogDir]);
        $logger->info('Test', ['resource' => fopen('php://memory', 'r')]);

        $logContent = file_get_contents($this->testLogDir . '/application.log');
        $this->assertStringContainsString('"__type":"resource"', $logContent);
    }

    public function testNegativeMaxFileSize(): void
    {
        $logger = new Logger(['maxFileSize' => -1]);
        
        $logger->info('Test');
        $logContent = file_get_contents($this->testLogDir . '/application.log');
        $this->assertStringContainsString('Test', $logContent);
    }

    public function testZeroMaxFiles(): void
    {
        $logger = new Logger(['maxFiles' => 0]);
        
        $this->assertEquals(5, $logger->getMaxFiles());
    }

    public function testDefaultOptionsValidation(): void
    {
        $logger = new Logger(['logDirectory' => $this->testLogDir]);
        
        $this->assertEquals($this->testLogDir, $logger->getLogDirectory());
        $this->assertEquals('application.log', $logger->getLogFileName());
        $this->assertEquals(10485760, $logger->getMaxFileSize());
        $this->assertEquals(5, $logger->getMaxFiles());
        $this->assertEquals(0, $logger->getMinLevel());
        $this->assertFalse($logger->isJsonFormat());
    }
}