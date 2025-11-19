<?php

/**
 * Vanilla PHP integration test for the Logger library
 */

require_once 'vendor/autoload.php';

use DevLogger\Logger;

// Test basic functionality
echo "Testing Logger in vanilla PHP...\n";

$logger = new Logger([
    'logDirectory' => __DIR__ . '/logs',
    'defaultLogFile' => 'vanilla_test.log'
]);

// Log various levels
$logger->emergency('System is down!');
$logger->alert('Alert: High CPU usage');
$logger->critical('Critical error occurred');
$logger->error('Database connection failed');
$logger->warning('This is a warning');
$logger->notice('Notice: Something happened');
$logger->info('Info: User logged in', ['user_id' => 123]);
$logger->debug('Debug: Processing request', ['request_id' => 'abc123']);

// Test with context
$logger->info('Complex context test', [
    'user' => ['id' => 456, 'name' => 'John Doe'],
    'request' => ['method' => 'POST', 'url' => '/api/login'],
    'timestamp' => time()
]);

// Test log rotation (if file gets large)
for ($i = 0; $i < 50; $i++) {
    $logger->info('Log entry for rotation test: ' . $i . str_repeat('x', 100));
}

echo "Logs written to logs/vanilla_test.log\n";
echo "Check the log file for output.\n";