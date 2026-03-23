<?php

// Copy this file to environmentVariables.php and fill in your values.
// Alternatively, set these as system environment variables.

define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('USERNAME', getenv('DB_USERNAME') ?: 'ChannelZeroNews');
define('DB_PASSWORD', getenv('DB_PASSWORD') ?: '');
define('DB_NAME', getenv('DB_NAME') ?: 'ChannelZeroNews');
define('HOST_PASSWORD', getenv('HOST_PASSWORD') ?: '');

?>
