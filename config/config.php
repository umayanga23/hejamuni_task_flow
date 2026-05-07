<?php
/**
 * TaskFlow Pro — Application Configuration
 * Edit DB_USER / DB_PASS to match your MySQL setup.
 */

define('DB_HOST',    'localhost');
define('DB_PORT',    '3306');
define('DB_NAME',    'taskflow_pro');
define('DB_USER',    'root');       // ← your MySQL username
define('DB_PASS',    '');           // ← your MySQL password (blank for XAMPP default)
define('DB_CHARSET', 'utf8mb4');

define('APP_NAME',    'TaskFlow Pro');
define('APP_VERSION', '1.0.0');
define('APP_URL',     '');  // ← no trailing slash

define('SESSION_NAME',     'taskflow_sess');
define('SESSION_LIFETIME', 28800);   // 8 hours
define('CSRF_TOKEN_LENGTH', 32);
define('TASKS_PER_PAGE',    20);

// Analytics formula weights (must sum to 1.0)
define('PRODUCTIVITY_WEIGHT_COMPLETION', 0.50);
define('PRODUCTIVITY_WEIGHT_TIME_ACC',   0.30);
define('PRODUCTIVITY_WEIGHT_FOCUS',      0.20);

//define('ANTHROPIC_API_KEY', 'YOUR_API_KEY');