<?php
require_once __DIR__ . '/../config/db.php';

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Check if user is admin
 */
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Check if user is premium
 */
function isPremium() {
    return isset($_SESSION['is_premium']) && $_SESSION['is_premium'] === true;
}

/**
 * Redirect user if not logged in
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: /php_ricordella/login.php");
        exit;
    }
}

/**
 * Redirect user if not admin
 */
function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header("Location: /php_ricordella/user/dashboard.php");
        exit;
    }
}

/**
 * Get user by ID
 */
function getUserById($userId) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    return $user;
}

/**
 * Get notes by user ID
 */
function getNotesByUserId($userId, $orderBy = "created_at DESC") {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM notes WHERE user_id = ? ORDER BY $orderBy");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $notes = [];
    while ($note = $result->fetch_assoc()) {
        $notes[] = $note;
    }
    $stmt->close();
    return $notes;
}

/**
 * Get note by ID
 */
function getNoteById($noteId) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM notes WHERE id = ?");
    $stmt->bind_param("i", $noteId);
    $stmt->execute();
    $result = $stmt->get_result();
    $note = $result->fetch_assoc();
    $stmt->close();
    return $note;
}

/**
 * Get shared notes for a user
 */
function getSharedNotes($userId) {
    global $conn;
    $sql = "SELECT n.*, u.username, ns.permission 
            FROM notes n 
            JOIN note_shares ns ON n.id = ns.note_id 
            JOIN users u ON n.user_id = u.id
            WHERE ns.user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $notes = [];
    while ($note = $result->fetch_assoc()) {
        $notes[] = $note;
    }
    $stmt->close();
    return $notes;
}

/**
 * Get today's notes
 */
function getTodaysNotes($userId) {
    global $conn;
    $today = date('Y-m-d');
    $sql = "SELECT * FROM notes WHERE user_id = ? AND DATE(created_at) = ? ORDER BY priority DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $userId, $today);
    $stmt->execute();
    $result = $stmt->get_result();
    $notes = [];
    while ($note = $result->fetch_assoc()) {
        $notes[] = $note;
    }
    $stmt->close();
    return $notes;
}

/**
 * Format date for display
 */
function formatDate($date) {
    return date('d M Y, H:i', strtotime($date));
}

/**
 * Count notes for a user
 */
function countUserNotes($userId) {
    global $conn;
    $stmt = $conn->prepare("SELECT COUNT(*) AS count FROM notes WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    $stmt->close();
    return $data['count'];
}

/**
 * Get all users (for admin)
 */
function getAllUsers() {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM users ORDER BY created_at DESC");
    $stmt->execute();
    $result = $stmt->get_result();
    $users = [];
    while ($user = $result->fetch_assoc()) {
        $users[] = $user;
    }
    $stmt->close();
    return $users;
}
?>