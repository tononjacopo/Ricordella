<?php
require_once '../config/db.php';
require_once '../utils/functions.php';

// Require user to be logged in and be premium
requireLogin();
header('Content-Type: application/json');

if (!isPremium()) {
    echo json_encode(['error' => 'This feature is only available for premium users']);
    exit;
}

if (isset($_GET['email']) && !empty($_GET['email'])) {
    $email = trim($_GET['email']);
    $users = searchUsersByEmail($email, $_SESSION['user_id']);
    echo json_encode($users);
} else {
    echo json_encode([]);
}
?>