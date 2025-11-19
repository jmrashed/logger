<?php

require_once 'vendor/autoload.php';

use DevLogger\Logger;

// Test instance methods
$logger = new Logger();
$logger->info('Test message');
echo 'Logged successfully';