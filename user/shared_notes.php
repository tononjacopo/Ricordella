<?php
require_once '../config/db.php';
require_once '../utils/functions.php';

// Require user to be logged in
requireLogin();

$success = '';
$error = '';

// Get shared notes
$shared_with_me = getSharedNotes($_SESSION['user_id']);
$shared_by_me = getSharedByMe($_SESSION['user_id']);

// Process unshare action
if (isset($_GET['action']) && $_GET['action'] === 'unshare' && isset($_GET['note_id']) && isset($_GET['user_id'])) {
    $note_id = (int)$_GET['note_id'];
    $user_id = (int)$_GET['user_id'];

    // Check if note belongs to current user
    $note = getNoteById($note_id);

    if ($note && $note['user_id'] == $_SESSION['user_id']) {
        // Unshare the note with the specified user
        $stmt = $conn->prepare("DELETE FROM note_shares WHERE note_id = ? AND user_id = ?");
        $stmt->bind_param("ii", $note_id, $user_id);

        if ($stmt->execute()) {
            $success = "Note sharing removed successfully.";
        } else {
            $error = "Error removing share. Please try again.";
        }

        $stmt->close();
    } else {
        $error = "You don't have permission to unshare this note.";
    }
}

// Process change permission action
if (isset($_GET['action']) && $_GET['action'] === 'change_permission' && isset($_GET['note_id']) && isset($_GET['user_id']) && isset($_GET['permission'])) {
    $note_id = (int)$_GET['note_id'];
    $user_id = (int)$_GET['user_id'];
    $permission = $_GET['permission'] === 'edit' ? 'edit' : 'view';

    // Check if note belongs to current user
    $note = getNoteById($note_id);

    if ($note && $note['user_id'] == $_SESSION['user_id']) {
        // Change permission
        $stmt = $conn->prepare("UPDATE note_shares SET permission = ? WHERE note_id = ? AND user_id = ?");
        $stmt->bind_param("sii", $permission, $note_id, $user_id);

        if ($stmt->execute()) {
            $success = "Permission updated successfully.";
        } else {
            $error = "Error updating permission. Please try again.";
        }

        $stmt->close();
    } else {
        $error = "You don't have permission to change sharing settings for this note.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Shared Notes | Ricordella</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="../assets/style/dashboard.css">
    <link rel="stylesheet" href="../assets/style/default-user.css">
    <link rel="stylesheet" href="../assets/style/font-general.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" href="../assets/img/logo-favicon.ico" type="image/x-icon">
</head>
<body>
<header>
    <div class="logo">Ricordella</div>
        <nav>
            <a href="dashboard.php" <?php echo basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'class="active"' : ''; ?>>
                My Notes
            </a>
            <a href="daily_notes.php" <?php echo basename($_SERVER['PHP_SELF']) === 'daily_notes.php' ? 'class="active"' : ''; ?>>
                Today's Notes
            </a>
            <a href="shared_notes.php" <?php echo basename($_SERVER['PHP_SELF']) === 'shared_notes.php' ? 'class="active"' : ''; ?>>
                Shared Notes
            </a>
            <a href="create_note.php" <?php echo basename($_SERVER['PHP_SELF']) === 'create_note.php' ? 'class="active"' : ''; ?>>
                Create Note
            </a>
        </nav>
    <div class="user-info">
        <span>Hello, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
        <?php if (isPremium()): ?>
            <span class="premium-badge"><i class="fas fa-crown"></i> Premium</span>
        <?php endif; ?>
        <a href="../logout.php" class="logout" title="Logout">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>
</header>

    <main>
        <h1>Shared Notes</h1>

        <?php if ($success): ?>
            <div class="alert success"><?php echo $success; ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert error"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if (!isPremium()): ?>
            <div class="alert info">
                <i class="fas fa-info-circle"></i> Note sharing is a premium feature. <a href="#">Upgrade to Premium</a> to share your notes with other users.
            </div>
        <?php endif; ?>

        <div class="shared-tabs">
            <div class="shared-tab active" data-target="shared-with-me">Shared With Me</div>
            <div class="shared-tab" data-target="shared-by-me">My Shared Notes</div>
        </div>

        <div id="shared-with-me" class="shared-tab-content active">
            <?php if (empty($shared_with_me)): ?>
                <div class="no-notes">
                    <p>There are no notes shared with you.</p>
                </div>
            <?php else: ?>
                <div class="notes-grid">
                    <?php foreach ($shared_with_me as $note): ?>
                        <div class="note priority-<?php echo strtolower($note['priority']); ?>">
                            <div class="priority-indicator"></div>
                            <div class="note-header">
                                <h3><?php echo htmlspecialchars($note['title']); ?></h3>
                                <div class="note-info">
                                    <span class="shared-by">By: <?php echo htmlspecialchars($note['username']); ?></span>
                                </div>
                            </div>
                            <div class="note-content">
                                <p data-full-content="<?php echo htmlspecialchars($note['content']); ?>"><?php echo nl2br(htmlspecialchars($note['content'])); ?></p>
                            </div>
                            <div class="note-footer">
                                <span class="priority">
                                    <?php
                                        $priorityLabels = [
                                            'Bassa' => 'Low',
                                            'Normale' => 'Normal',
                                            'Alta' => 'High',
                                            'Immediata' => 'Immediate'
                                        ];
                                        echo $priorityLabels[$note['priority']] ?? $note['priority'];
                                    ?>
                                </span>
                                <span class="shared-badge">
                                    <i class="fas fa-share-alt"></i>
                                    <?php echo $note['permission'] === 'edit' ? 'Can edit' : 'View only'; ?>
                                </span>
                                <span class="date"><?php echo formatDate($note['created_at']); ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div id="shared-by-me" class="shared-tab-content">
            <?php if (!isPremium()): ?>
                <div class="no-notes">
                    <p>Sharing notes is a premium feature.</p>
                    <p><a href="#">Upgrade to Premium</a> to share your notes with other users.</p>
                </div>
            <?php elseif (empty($shared_by_me)): ?>
                <div class="no-notes">
                    <p>You haven't shared any notes yet.</p>
                    <p>You can share your notes with other users when creating or editing a note.</p>
                </div>
            <?php else: ?>
                <?php foreach ($shared_by_me as $note): ?>
                    <div class="shared-note-card">
                        <div class="shared-note-header">
                            <h3><?php echo htmlspecialchars($note['title']); ?></h3>
                            <a href="edit_note.php?id=<?php echo $note['id']; ?>" class="btn secondary btn-sm">
                                <i class="fas fa-edit"></i> Edit Note
                            </a>
                        </div>
                        <div class="shared-with-list">
                            <h4>Shared with:</h4>
                            <ul>
                                <?php foreach ($note['shared_with'] as $share): ?>
                                    <li>
                                        <span class="shared-user">
                                            <i class="fas fa-user"></i>
                                            <?php echo htmlspecialchars($share['username']); ?>
                                        </span>
                                        <div class="share-controls">
                                            <div class="permission-toggle">
                                                <a href="?action=change_permission&note_id=<?php echo $note['id']; ?>&user_id=<?php echo $share['id']; ?>&permission=view" class="permission-btn <?php echo $share['permission'] === 'view' ? 'active' : ''; ?>">View</a>
                                                <a href="?action=change_permission&note_id=<?php echo $note['id']; ?>&user_id=<?php echo $share['id']; ?>&permission=edit" class="permission-btn <?php echo $share['permission'] === 'edit' ? 'active' : ''; ?>">Edit</a>
                                            </div>
                                            <a href="?action=unshare&note_id=<?php echo $note['id']; ?>&user_id=<?php echo $share['id']; ?>" class="unshare-btn" onclick="return confirm('Are you sure you want to stop sharing this note with <?php echo htmlspecialchars($share['username']); ?>?')">
                                                <i class="fas fa-times"></i>
                                            </a>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> Ricordella - Your Notes App</p>
    </footer>

    <!-- Delete Confirmation Popup -->
    <div id="confirm-popup">
        <p>Delete this Note?</p>
        <div>
            <button id="confirm-yes">Yes</button>
            <button id="confirm-no">No</button>
        </div>
    </div>

    <script src="../assets/script/note-actions.js"></script>
</body>
</html>