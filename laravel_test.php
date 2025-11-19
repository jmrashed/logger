<?php

/**
 * Laravel integration test for the Logger library
 * Simulates using the logger in a Laravel application
 */

require_once 'vendor/autoload.php';

use DevLogger\Logger;
use Illuminate\Support\Facades\Log; // This won't work without Laravel, but for simulation

// In Laravel, you would register the logger in config/logging.php or as a service

echo "Testing Logger integration in Laravel-like environment...\n";

// Simulate Laravel's logging configuration
// In Laravel, you could set this logger as the default logger

$logger = new Logger([
    'logDirectory' => __DIR__ . '/logs',
    'defaultLogFile' => 'laravel.log'
]);

// Simulate Laravel's Log facade usage
// In Laravel, Log::info('message', context) would use the configured logger

$logger->emergency('Laravel emergency log');
$logger->alert('Laravel alert log');
$logger->critical('Laravel critical log');
$logger->error('Laravel error log');
$logger->warning('Laravel warning log');
$logger->notice('Laravel notice log');
$logger->info('Laravel info log', ['user_id' => 789, 'action' => 'login']);
$logger->debug('Laravel debug log', ['request_id' => 'xyz789']);

// Test with Laravel-like context
$logger->info('User action in Laravel', [
    'user' => ['id' => 101, 'email' => 'user@laravel.com'],
    'request' => ['method' => 'GET', 'uri' => '/dashboard'],
    'session' => ['id' => 'sess123'],
    'ip' => '127.0.0.1'
]);

echo "Laravel-style logs written to logs/laravel.log\n";
echo "In a real Laravel app, you would configure this logger in config/logging.php\n";
echo "Example config:\n";
echo "'channels' => [\n";
echo "    'custom' => [\n";
echo "        'driver' => 'custom',\n";
echo "        'via' => DevLogger\Logger::class,\n";
echo "        'logDirectory' => storage_path('logs'),\n";
echo "    ],\n";
echo "],\n";