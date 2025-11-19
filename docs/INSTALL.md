# Installation Guide

## Requirements

- PHP 7.4 or higher
- Composer (for dependency management)

## Installation Methods

### Method 1: Composer (Recommended)

The recommended way to install the Development Logger Library is through Composer.

```bash
composer require jmrashed/logger
```

After installation, include the autoloader in your project:

```php
require_once 'vendor/autoload.php';
```

### Method 2: Manual Installation

1. Download the `Logger.php` file from the repository
2. Place it in your project's directory structure
3. Include the file in your code:

```php
require_once '/path/to/Logger.php';
```

## Basic Usage

After installation, you can start logging:

```php
use DevLogger\Logger;

// Basic logging
Logger::info('Application started');
Logger::error('Something went wrong');

// With context
Logger::info('User action', ['user_id' => 123, 'action' => 'login']);
```

## Configuration

The logger works out of the box with default settings:

- Log directory: `logs/` (relative to the Logger.php file)
- Log file: `application.log`
- Max file size: 10MB
- Max backup files: 5

## Troubleshooting

### Permission Issues

Ensure the web server has write permissions to the log directory:

```bash
chmod 755 logs/
```

### Log Directory Not Created

If the log directory cannot be created automatically, create it manually:

```bash
mkdir logs
echo "" > logs/.gitkeep
```

### Composer Autoload Issues

If you're having issues with autoloading, try:

```bash
composer dump-autoload