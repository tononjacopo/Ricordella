<?php
require_once '../config/db.php';
require_once '../utils/functions.php';


// Require admin privileges
requireAdmin();

// Check if user ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: dashboard.php");
    exit;
}

$user_id = (int)$_GET['id'];

// Get the user
$user = getUserById($user_id);

// Check if the user exists and is not current admin
if (!$user) {
    $_SESSION['admin_message'] = "User not found.";
    $_SESSION['admin_message_type'] = "error";
    header("Location: dashboard.php");
    exit;
} elseif ($user['id'] === $_SESSION['user_id']) {
    $_SESSION['admin_message'] = "You cannot delete your own account.";
    $_SESSION['admin_message_type'] = "error";
    header("Location: dashboard.php");
    exit;
} elseif ($user['role'] === 'admin') {
    $_SESSION['admin_message'] = "Cannot delete admin accounts.";
    $_SESSION['admin_message_type'] = "error";
    header("Location: dashboard.php");
    exit;
}

// Start transaction
$conn->begin_transaction();

try {
    // Delete the user's notes first (cascading will handle note_shares and tags)
    $stmt = $conn->prepare("DELETE FROM notes WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    // Delete the user
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    $conn->commit();

    $_SESSION['admin_message'] = "User and all their data have been deleted.";
    $_SESSION['admin_message_type'] = "success";
} catch (Exception $e) {
    $conn->rollback();

    $_SESSION['admin_message'] = "Error deleting user: " . $e->getMessage();
    $_SESSION['admin_message_type'] = "error";
}

// Redirect back to dashboard
header("Location: dashboard.php");
exit;
?>