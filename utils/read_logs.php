<?php
require_once 'functions.php';
requireAdmin();
if (!isset($_GET['file']) || !preg_match('/^[\w\-.]+\.log$/', $_GET['file'])) exit('File not allowed');
$logPath = __DIR__ . '/../logs/' . $_GET['file'];
$logsDir = __DIR__ . '/../logs/';

// Validate that the file is really in the logs directory
$realLogPath = realpath($logPath);
$realLogsDir = realpath($logsDir);

if (strpos($realLogPath, $realLogsDir) !== 0 || !file_exists($realLogPath)) {
    exit('File not found or access denied');
}

echo htmlspecialchars(file_get_contents($realLogPath));