# Development Logger Library

A lightweight, production-ready logging utility for development environments.

## Features

- Multiple log levels (DEBUG, INFO, WARNING, ERROR, CRITICAL)
- Automatic log rotation (10MB max, keeps 5 files)
- Context data support
- Thread-safe file writing
- Git-ignored log files

## Usage

```php
// Include the logger

require_once $_SERVER['DOCUMENT_ROOT'] . '/_Logger/Logger.php';

// Basic logging
Logger::info('User logged in');
Logger::error('Database connection failed');
Logger::warning('Deprecated function used');

// Logging with context
Logger::info('User action', ['user_id' => 123, 'action' => 'login']);
Logger::error('Query failed', ['query' => 'SELECT * FROM users', 'error' => $e->getMessage()]);
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
├── Logger.php          # Main logger class
├── logs/              # Log files directory (git-ignored)
│   ├── .gitkeep       # Keeps directory in git
│   └── application.log # Main log file
├── .gitignore         # Excludes logs from git
└── README.md          # This file
```

## Configuration

The logger automatically:
- Creates log directory if it doesn't exist
- Rotates logs when they exceed 10MB
- Keeps maximum 5 log files
- Uses thread-safe file writing


## Installation

To install the logger, simply include the `Logger.php` file in your project.

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.
 
