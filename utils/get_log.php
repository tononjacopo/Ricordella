<?php
require_once '../config/db.php';
require_once 'functions.php';
requireAdmin();

// Sanitize and validate file parameter
if (isset($_GET['file']) && !empty($_GET['file'])) {
    $file = basename($_GET['file']); // Sanitize to prevent directory traversal
    $logPath = __DIR__ . '/../logs/' . $file;

    // Check if file exists and is in the logs directory
    if (file_exists($logPath) && is_file($logPath) && preg_match('/\.log$/', $file)) {
        $content = file_get_contents($logPath);
        echo htmlspecialchars($content);
        exit;
    }
}

// File not found or invalid
header("HTTP/1.0 404 Not Found");
echo "File not found or access denied";