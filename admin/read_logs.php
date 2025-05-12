<?php
require_once '../utils/functions.php';
requireAdmin();
if (!isset($_GET['file']) || !preg_match('/^[\w\-\.]+\.log$/', $_GET['file'])) exit('File not allowed');
$path = realpath(__DIR__ . '/../logs/' . $_GET['file']);
$base = realpath(__DIR__ . '/../logs/');
if (strpos($path, $base) !== 0 || !file_exists($path)) exit('Not found');
echo htmlspecialchars(file_get_contents($path));