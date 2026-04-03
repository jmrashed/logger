# API Reference

## Logger Class

The main logging class implementing PSR-3 LoggerInterface.

### Namespace

```php
namespace DevLogger;
```

### Constructor

```php
public function __construct(array $options = [])
```

**Options:**

| Option | Type | Default | Description |
|--------|------|---------|-------------|
| `logDirectory` | string | `__DIR__ . '/logs'` | Directory for log files |
| `defaultLogFile` | string | `application.log` | Name of the log file |
| `maxFileSize` | int | `10485760` (10MB) | Max size before rotation |
| `maxFiles` | int | `5` | Number of rotated files to keep |
| `minLevel` | string | `DEBUG` | Minimum log level to record |
| `jsonFormat` | bool | `false` | Output logs in JSON format |
| `includeMicroseconds` | bool | `false` | Include microseconds in timestamp |
| `timestampFormat` | string | `null` | Custom DateTime format |

### PSR-3 Log Methods

All methods return `void` and accept mixed message and array context.

```php
public function emergency(mixed $message, array $context = []): void
public function alert(mixed $message, array $context = []): void
public function critical(mixed $message, array $context = []): void
public function error(mixed $message, array $context = []): void
public function warning(mixed $message, array $context = []): void
public function notice(mixed $message, array $context = []): void
public function info(mixed $message, array $context = []): void
public function debug(mixed $message, array $context = []): void
```

### Configuration Methods

```php
public function setMinLevel(string $level): self
public function setJsonFormat(bool $json = true): self
public function setTimestampFormat(string $format): self
```

### Fluent API Methods

```php
public function withContext(array $context): self
public function withoutContext(): self
public function withName(string $name): self
```

### Utility Methods

```php
public function getName(): ?string
public function getLastError(): ?string
public function getLogPath(): string
public function getLogDirectory(): string
public function getLogFileName(): string
public function getMaxFileSize(): int
public function getMaxFiles(): int
public function isJsonFormat(): bool
public function getMinLevel(): int
```

### Log Management Methods

```php
public function clear(): bool
public function readLogs(int $lines = 100, bool $reverse = false): array
```

### Log Levels

The logger supports all 8 PSR-3 log levels (in order of severity):

- `EMERGENCY` - System is unusable (7)
- `ALERT` - Action must be taken immediately (6)
- `CRITICAL` - Critical conditions (5)
- `ERROR` - Runtime errors (4)
- `WARNING` - Warning messages (3)
- `NOTICE` - Normal but significant (2)
- `INFO` - General information (1)
- `DEBUG` - Detailed debug info (0)

### Log Format

**Text format:**
```
[timestamp] [LEVEL] [channel] message {"key":"value",...}
```

Example:
```
[2024-01-15 14:30:25] [INFO] [payment] User logged in {"user_id":123,"action":"login"}
```

**JSON format:**
```json
{"timestamp":"2024-01-15T14:30:25+00:00","level":"INFO","message":"User logged in","channel":"payment","context":{"user_id":123}}
```

### Error Handling

The logger uses a fail-safe approach:
- Exceptions are caught internally and stored via `getLastError()`
- Failed log operations don't stop application execution
- No log data is lost due to logging failures

### Custom Exceptions

```php
use DevLogger\LoggerException;

throw LoggerException::forDirectoryCreation('/path');
throw LoggerException::forFileWrite('/path/file.log');
throw LoggerException::forInvalidPath('/path');
throw LoggerException::forRotation('/path/file.log');
throw LoggerException::forJsonEncoding('data');
```

### Thread Safety

The logger uses PHP's `LOCK_EX` flag for file operations and file handle caching, making it safe for concurrent access.

### Path Security

The logger includes:
- Path traversal detection (rejects `..` in paths)
- Filename validation (rejects forbidden characters)
- Channel name sanitization
- Context key/value sanitization

### Usage Examples

```php
use DevLogger\Logger;

// Basic usage
$logger = new Logger();
$logger->info('Application started');

// With configuration
$logger = new Logger([
    'logDirectory' => '/var/log/myapp',
    'defaultLogFile' => 'app.log',
    'maxFileSize' => 20 * 1024 * 1024, // 20MB
    'maxFiles' => 10,
    'minLevel' => 'WARNING',
    'jsonFormat' => false,
]);

// Level filtering
$logger->setMinLevel('ERROR'); // Only ERROR and above

// JSON output
$logger->setJsonFormat(true)->info('JSON log');

// Custom timestamp
$logger->setTimestampFormat('Y-m-d H:i:s.u');

// Channel naming (PSR-3)
$paymentLogger = $logger->withName('payment');
$paymentLogger->info('Payment processed');

// Persistent context
$userLogger = $logger->withContext(['user_id' => 123]);
$userLogger->info('User action');
$userLogger->info('Another action'); // user_id still included

// Read logs
$logs = $logger->readLogs(100);
$recent = $logger->readLogs(10, true);

// Clear logs
$logger->clear();

// Error handling
$logger->error('Something failed');
if ($logger->getLastError()) {
    echo "Last error: " . $logger->getLastError();
}
```