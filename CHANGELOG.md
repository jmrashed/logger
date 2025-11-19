# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- Comprehensive integration tests for vanilla PHP and Laravel environments
- Additional test cases for edge cases (large context, special characters, permissions)
- Cross-platform compatibility improvements

### Changed
- Fixed example.php to use instance methods instead of static calls
- Improved test suite with more robust assertions

### Fixed
- Cross-platform path handling in tests
- Example code corrections

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