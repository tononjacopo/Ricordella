<?php
require_once '../config/db.php';
require_once 'functions.php';

// Require user to be logged in and be premium
requireLogin();
header('Content-Type: application/json');

if (!isPremium()) {
    echo json_encode(['success' => false, 'error' => 'This feature is only available for premium users']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['action']) || !isset($data['note_id']) || !isset($data['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Missing required parameters']);
    exit;
}

$action = $data['action'];
$note_id = (int) $data['note_id'];
$user_id = (int) $data['user_id'];

// Check if note exists and belongs to current user
$note = getNoteById($note_id);
if (!$note || $note['user_id'] != $_SESSION['user_id']) {
    echo json_encode(['success' => false, 'error' => 'You do not have permission to modify this note']);
    exit;
}

// Check if trying to share with self
if ($user_id == $_SESSION['user_id']) {
    echo json_encode(['success' => false, 'error' => 'You cannot share or unshare a note with yourself']);
    exit;
}

if ($action === 'share') {
    if (!isset($data['permission'])) {
        echo json_encode(['success' => false, 'error' => 'Missing permission for sharing']);
        exit;
    }

    $permission = $data['permission'];

    // Check if user exists
    $userToShare = getUserById($user_id);
    if (!$userToShare) {
        echo json_encode(['success' => false, 'error' => 'User not found']);
        exit;
    }

    if (shareNoteWithUser($note_id, $user_id, $permission)) {
        $sharedUsers = getNoteSharedUsers($note_id);
        echo json_encode(['success' => true, 'message' => 'Note shared successfully', 'shared_users' => $sharedUsers]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to share note']);
    }

} elseif ($action === 'unshare') {
    // Unshare the note
    $stmt = $conn->prepare("DELETE FROM note_shares WHERE note_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $note_id, $user_id);
    $result = $stmt->execute();
    $stmt->close();

    if ($result) {
        // Check if there are any shares left
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM note_shares WHERE note_id = ?");
        $stmt->bind_param("i", $note_id);
        $stmt->execute();
        $count = $stmt->get_result()->fetch_assoc()['count'];
        $stmt->close();

        // If no more shares, mark note as not shared
        if ($count == 0) {
            $stmt = $conn->prepare("UPDATE notes SET is_shared = 0 WHERE id = ?");
            $stmt->bind_param("i", $note_id);
            $stmt->execute();
            $stmt->close();
        }

        $sharedUsers = getNoteSharedUsers($note_id);
        echo json_encode(['success' => true, 'message' => 'Note unshared successfully', 'shared_users' => $sharedUsers]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to unshare note']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid action']);
}
?>
