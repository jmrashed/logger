<?php

/**
 * Example usage of the Logger library
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/_Logger/Logger.php';

// Basic logging examples
\DevLogger\Logger::debug('Debug message for troubleshooting');
\DevLogger\Logger::info('Application started successfully');
\DevLogger\Logger::warning('This feature will be deprecated soon');
\DevLogger\Logger::error('Failed to connect to database');
\DevLogger\Logger::critical('System is out of memory');

// Logging with context data
\DevLogger\Logger::info('User login attempt', [
    'user_id' => 123,
    'email' => 'user@example.com',
    'ip_address' => '192.168.1.1'
]);

\DevLogger\Logger::error('Database query failed', [
    'query' => 'SELECT * FROM users WHERE id = ?',
    'params' => [123],
    'error' => 'Connection timeout'
]);

// Logging in different scenarios
try {
    // Simulate some operation
    throw new Exception('Something went wrong');
} catch (Exception $e) {
    \DevLogger\Logger::error('Exception caught', [
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
}

echo "Logging examples completed. Check logs/application.log\n";





