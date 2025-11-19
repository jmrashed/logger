# API Reference

## Logger Class

The main logging class providing static methods for different log levels.

### Namespace

```php
namespace DevLogger;
```

### Methods

#### `Logger::debug(string $message, array $context = []): bool`

Logs a debug message.

**Parameters:**
- `$message` (string): The log message
- `$context` (array): Optional context data

**Returns:** `bool` - True on success, false on failure

**Example:**
```php
Logger::debug('Processing user data', ['user_id' => 123]);
```

#### `Logger::info(string $message, array $context = []): bool`

Logs an info message.

**Parameters:**
- `$message` (string): The log message
- `$context` (array): Optional context data

**Returns:** `bool` - True on success, false on failure

**Example:**
```php
Logger::info('User logged in successfully');
```

#### `Logger::warning(string $message, array $context = []): bool`

Logs a warning message.

**Parameters:**
- `$message` (string): The log message
- `$context` (array): Optional context data

**Returns:** `bool` - True on success, false on failure

**Example:**
```php
Logger::warning('Deprecated function used', ['function' => 'oldMethod']);
```

#### `Logger::error(string $message, array $context = []): bool`

Logs an error message.

**Parameters:**
- `$message` (string): The log message
- `$context` (array): Optional context data

**Returns:** `bool` - True on success, false on failure

**Example:**
```php
Logger::error('Database connection failed', ['error' => $e->getMessage()]);
```

#### `Logger::critical(string $message, array $context = []): bool`

Logs a critical message.

**Parameters:**
- `$message` (string): The log message
- `$context` (array): Optional context data

**Returns:** `bool` - True on success, false on failure

**Example:**
```php
Logger::critical('System out of memory');
```

## Log Levels

The logger supports the following log levels (in order of severity):

- `DEBUG` - Detailed debug information
- `INFO` - General information messages
- `WARNING` - Warning messages that don't stop execution
- `ERROR` - Error conditions that might allow continued execution
- `CRITICAL` - Critical conditions that require immediate attention

## Log Format

All log entries follow this format:

```
[timestamp] [LEVEL] message {"key":"value",...}
```

**Example:**
```
[2024-01-15 14:30:25] [INFO] User logged in {"user_id":123,"action":"login"}
```

## Context Data

Context data is automatically JSON-encoded and appended to log messages. All data types supported by `json_encode()` are allowed.

**Supported types:**
- Strings
- Numbers
- Booleans
- Arrays
- Objects (with public properties)

**Example:**
```php
Logger::info('Complex context', [
    'user' => ['id' => 123, 'name' => 'John'],
    'timestamp' => time(),
    'active' => true
]);
```

Output:
```
[2024-01-15 14:30:25] [INFO] Complex context {"user":{"id":123,"name":"John"},"timestamp":1705324225,"active":true}
```

## Error Handling

The logger uses a fail-safe approach:

- Exceptions during logging are caught internally
- Failed log operations return `false`
- Application execution continues normally
- No log data is lost due to logging failures

## Thread Safety

The logger uses PHP's `LOCK_EX` flag for file operations, making it safe for concurrent access in multi-threaded environments.