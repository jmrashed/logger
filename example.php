<?php

/**
 * Example usage of the Logger library
 */

require_once 'Logger.php';

$logger = new \DevLogger\Logger();

// Basic logging examples
$logger->debug('Debug message for troubleshooting');
$logger->info('Application started successfully');
$logger->warning('This feature will be deprecated soon');
$logger->error('Failed to connect to database');
$logger->critical('System is out of memory');

// Logging with context data
$logger->info('User login attempt', [
    'user_id' => 123,
    'email' => 'user@example.com',
    'ip_address' => '192.168.1.1'
]);

$logger->error('Database query failed', [
    'query' => 'SELECT * FROM users WHERE id = ?',
    'params' => [123],
    'error' => 'Connection timeout'
]);

// Logging in different scenarios
try {
    // Simulate some operation
    throw new Exception('Something went wrong');
} catch (Exception $e) {
    $logger->error('Exception caught', [
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
}

echo "Logging examples completed. Check logs/application.log\n";





