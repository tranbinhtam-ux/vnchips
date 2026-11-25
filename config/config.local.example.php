<?php
/**
 * Server-specific configuration.
 * Copy this file to config.local.php on the server and fill in the hosting DB credentials.
 * config.local.php is loaded before the defaults in config.php, so anything defined here wins.
 */

define('DB_HOST', 'localhost');
define('DB_NAME', 'your_database_name');
define('DB_USER', 'your_database_user');
define('DB_PASS', 'your_database_password');
