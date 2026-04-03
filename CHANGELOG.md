# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- **LoggerException class** - Custom exception class with context and factory methods
- **Formatter interface** - For extensible log formatting
- **Path traversal protection** - Detects and blocks `..` in paths
- **Filename validation** - Blocks forbidden characters in filenames
- **Channel name validation** - Prevents injection in channel names
- **Input validation** - Validates log levels, file sizes, max files
- **DateTimeImmutable** - Uses immutable date for better performance
- **File handle caching** - Reuses file handles for performance
- **Custom timestamp format** - `setTimestampFormat()` method
- **Microsecond timestamps** - Optional microsecond precision
- **Additional getter methods** - For all configuration options
- **Enhanced object handling** - Supports DateTime and JsonSerializable
- **Resource handling** - Handles resource types in context
- **PHPStan configuration** - Static analysis support
- **Enhanced CI pipeline** - Static analysis and coding standards jobs

### Changed
- Refactored to use `src/` directory for PSR-4 autoloading
- Improved error handling with proper exception types
- Enhanced rotation logic with better error tracking
- Updated to use action/checkout@v4 and action/cache@v4

### Fixed
- Duplicate test method name
- Path handling in default log directory

### Removed
- N/A

## [1.0.2] - 2025-11-19

### Added
- Initial release of the Development Logger Library
- Support for multiple log levels (DEBUG, INFO, WARNING, ERROR, CRITICAL)
- Automatic log rotation (10MB max, keeps 5 files)
- Context data support in log entries
- Thread-safe file writing
- PSR-4 autoloading support
- Composer package configuration
- PHPUnit test suite
- GitHub Actions CI workflow

### Changed
- Migrated to namespaced class structure (`DevLogger\Logger`)
- Updated examples to use fully qualified class names

### Fixed
- N/A (initial release)

### Removed
- N/A (initial release)

## [1.0.0] - 2024-01-15

### Added
- Basic logging functionality
- Log levels: DEBUG, INFO, WARNING, ERROR, CRITICAL
- Automatic log directory creation
- Log rotation mechanism
- Context data support
- Thread-safe operations

### Changed
- N/A (initial release)

### Fixed
- N/A (initial release)

### Removed
- N/A (initial release)