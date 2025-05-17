<?php
require_once '../config/db.php';
require_once 'functions.php';

// Require admin privileges
requireAdmin();

// Check if user ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id']) || !isset($_GET['premium'])) {
    header("Location: ../admin/user_list.php");
    exit;
}

$user_id = (int)$_GET['id'];
$premium = (int)$_GET['premium'] ? 1 : 0;

// Get the user
$user = getUserById($user_id);

// Check if the user exists
if (!$user) {
    $_SESSION['admin_message'] = "User not found.";
    $_SESSION['admin_message_type'] = "error";
    header("Location: ../admin/user_list.php");
    exit;
}

// Update premium status
$stmt = $conn->prepare("UPDATE users SET is_premium = ? WHERE id = ?");
$stmt->bind_param("ii", $premium, $user_id);

if ($stmt->execute()) {
    $_SESSION['admin_message'] = "User premium status has been " . ($premium ? "activated" : "deactivated") . ".";
    $_SESSION['admin_message_type'] = "success";
} else {
    $_SESSION['admin_message'] = "Error updating user premium status.";
    $_SESSION['admin_message_type'] = "error";
}

$stmt->close();

// Redirect back to dashboard
header("Location: ../admin/user_list.php");
exit;
?>