# Development Logger Library

[![Latest Version](https://img.shields.io/packagist/v/jmrashed/logger.svg)](https://packagist.org/packages/jmrashed/logger)
[![PHP Version](https://img.shields.io/packagist/php-v/jmrashed/logger.svg)](https://packagist.org/packages/jmrashed/logger)
[![License](https://img.shields.io/github/license/jmrashed/logger.svg)](LICENSE)
[![Build Status](https://github.com/jmrashed/logger/workflows/CI/badge.svg)](https://github.com/jmrashed/logger/actions)
[![Code Coverage](https://codecov.io/gh/jmrashed/logger/branch/main/graph/badge.svg)](https://codecov.io/gh/jmrashed/logger)

A lightweight, production-ready logging utility for development environments.

## Requirements

- PHP 7.4 or higher
- Composer (recommended for installation)

## Table of Contents

- [Features](#features)
- [Installation](#installation)
- [Usage](#usage)
- [Log Levels](#log-levels)
- [Log Format](#log-format)
- [File Structure](#file-structure)
- [Configuration](#configuration)
- [Testing](#testing)
- [Contributing](#contributing)
- [License](#license)
- [Security](#security)

## Features

- Multiple log levels (DEBUG, INFO, WARNING, ERROR, CRITICAL)
- Automatic log rotation (10MB max, keeps 5 files)
- Context data support
- Thread-safe file writing
- Git-ignored log files
- PSR-4 autoloading
- Composer package support
- Comprehensive test suite
- GitHub Actions CI/CD

## Installation

### Via Composer (Recommended)

```bash
composer require jmrashed/logger
```

### Manual Installation

Download the `Logger.php` file and include it in your project.

## Usage

```php
// Using Composer autoload
require_once 'vendor/autoload.php';

// Or manual include
require_once $_SERVER['DOCUMENT_ROOT'] . '/_Logger/Logger.php';

// Basic logging
\DevLogger\Logger::info('User logged in');
\DevLogger\Logger::error('Database connection failed');
\DevLogger\Logger::warning('Deprecated function used');

// Logging with context
\DevLogger\Logger::info('User action', ['user_id' => 123, 'action' => 'login']);
\DevLogger\Logger::error('Query failed', ['query' => 'SELECT * FROM users', 'error' => $e->getMessage()]);
```

## Log Levels

- `DEBUG` - Detailed debug information
- `INFO` - General information messages
- `WARNING` - Warning messages
- `ERROR` - Error conditions
- `CRITICAL` - Critical conditions

## Log Format

```
[2024-01-15 14:30:25] [INFO] User logged in {"user_id":123,"action":"login"}
```

## File Structure

```
_Logger/
├── Logger.php              # Main logger class
├── example.php             # Usage example
├── composer.json           # Composer package configuration
├── phpunit.xml.dist        # PHPUnit configuration
├── .gitignore              # Git ignore rules
├── CHANGELOG.md            # Change log
├── CODE_OF_CONDUCT.md      # Code of conduct
├── CONTRIBUTING.md         # Contributing guidelines
├── LICENSE                 # License file
├── README.md               # This file
├── SECURITY.md             # Security policy
├── .github/                # GitHub workflows and templates
├── docs/                   # Documentation
│   ├── API.md              # API documentation
│   └── INSTALL.md          # Installation guide
└── tests/                  # Test files
    └── LoggerTest.php      # Unit tests
```

## Configuration

The logger automatically:
- Creates log directory if it doesn't exist
- Rotates logs when they exceed 10MB
- Keeps maximum 5 log files
- Uses thread-safe file writing

## Testing

Run the test suite:

```bash
composer test
```

## Contributing

Contributions are welcome! Please see our [Contributing Guide](CONTRIBUTING.md) for details.

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Security

For security issues, please see our [Security Policy](SECURITY.md).

## Author

**Md Rasheduzzzaman**
- Email: jmrashed@gmail.com
- GitHub: [@jmrashed](https://github.com/jmrashed)
- Repository: [https://github.com/jmrashed/logger](https://github.com/jmrashed/logger)