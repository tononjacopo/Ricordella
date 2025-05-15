<?php
require_once '../config/db.php';
require_once '../utils/functions.php';


// Require user to be logged in
requireLogin();

// Check if note ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: user_list.php");
    exit;
}

$note_id = (int)$_GET['id'];

// Check if the note exists and belongs to the user
$stmt = $conn->prepare("SELECT id FROM notes WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $note_id, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Note doesn't exist or doesn't belong to the user
    header("Location: user_list.php");
    exit;
}

// Delete the note
$stmt = $conn->prepare("DELETE FROM notes WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $note_id, $_SESSION['user_id']);
$stmt->execute();
$stmt->close();

// Redirect back to dashboard
header("Location: user_list.php");
exit;
?>