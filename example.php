<?php

/**
 * Example usage of the Logger library
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/_Logger/Logger.php';

// Basic logging examples
Logger::debug('Debug message for troubleshooting');
Logger::info('Application started successfully');
Logger::warning('This feature will be deprecated soon');
Logger::error('Failed to connect to database');
Logger::critical('System is out of memory');

// Logging with context data
Logger::info('User login attempt', [
    'user_id' => 123,
    'email' => 'user@example.com',
    'ip_address' => '192.168.1.1'
]);

Logger::error('Database query failed', [
    'query' => 'SELECT * FROM users WHERE id = ?',
    'params' => [123],
    'error' => 'Connection timeout'
]);

// Logging in different scenarios
try {
    // Simulate some operation
    throw new Exception('Something went wrong');
} catch (Exception $e) {
    Logger::error('Exception caught', [
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
}

echo "Logging examples completed. Check logs/application.log\n";





