<?php
/**
 * Scheduled Notifications Script
 * 
 * This script is intended to be run as a cron job to send automated notifications
 * such as academic warnings and birthday emails.
 * 
 * Recommended cron schedule: 0 8 * * * /usr/bin/php /path/to/cron_jobs/send_notifications.php
 * (Runs daily at 8:00 AM)
 */

// Define absolute paths
define('BASE_PATH', dirname(__DIR__));

// Include required files
require_once BASE_PATH . '/config/db_config.php';
require_once BASE_PATH . '/config/functions.php';

// Start execution time measurement
$start_time = microtime(true);

// Set to prevent execution from browser
if (isset($_SERVER['REMOTE_ADDR'])) {
    die('This script can only be run from the command line.');
}

// Log function
function log_message($message) {
    echo '[' . date('Y-m-d H:i:s') . '] ' . $message . PHP_EOL;
}

log_message('Starting scheduled notifications job...');

// 1. Send birthday wishes
log_message('Sending birthday wishes...');
$birthdayCount = sendBirthdayWishes();
log_message("Sent $birthdayCount birthday wishes.");

// 2. Send academic warnings
log_message('Sending academic warnings...');
$warningCount = sendAcademicWarnings();
log_message("Sent $warningCount academic warnings.");

// Calculate execution time
$end_time = microtime(true);
$execution_time = ($end_time - $start_time);
log_message("All notifications sent. Execution time: " . number_format($execution_time, 2) . " seconds.");

/**
 * Sample implementation for logging to file
 * Uncomment and modify as needed
 */
/*
// Log to file as well
$logFile = BASE_PATH . '/logs/notifications_' . date('Y-m-d') . '.log';
$logContent = ob_get_contents();
file_put_contents($logFile, $logContent, FILE_APPEND);
*/ 