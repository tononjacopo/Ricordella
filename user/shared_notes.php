<?php
require_once '../config/db.php';
require_once '../utils/functions.php';


// Require user to be logged in
requireLogin();

// Get shared notes
$shared_notes = getSharedNotes($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Shared Notes | Ricordella</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="../assets/style/dashboard.css">
    <link rel="stylesheet" href="../assets/style/font-general.css">
    <link rel="icon" href="../assets/img/logo-favicon.ico" type="image/x-icon">
</head>
<body>
    <header>
        <div class="logo">Ricordella</div>
        <nav>
            <a href="dashboard.php">My Notes</a>
            <a href="daily_notes.php">Today's Notes</a>
            <a href="shared_notes.php" class="active">Shared Notes</a>
            <?php if (isPremium()): ?>
            <a href="create_note.php" class="premium">Create Note</a>
            <?php else: ?>
            <a href="create_note.php">Create Note</a>
            <?php endif; ?>
        </nav>
        <div class="user-info">
            <span>Hello, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
            <?php if (isPremium()): ?>
            <span class="premium-badge">⭐ Premium</span>
            <?php endif; ?>
            <a href="../logout.php" class="logout">Logout</a>
        </div>
    </header>

    <main>
        <h1>Shared Notes</h1>

        <?php if (empty($shared_notes)): ?>
            <div class="no-notes">
                <p>There are no notes shared with you.</p>
                <?php if (isPremium()): ?>
                <p>You can share your notes with others from the note editor.</p>
                <?php else: ?>
                <p>Premium members can share notes with other users.</p>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="notes-grid">
                <?php foreach ($shared_notes as $note): ?>
                    <div class="note priority-<?php echo strtolower($note['priority']); ?> shared">
                        <div class="note-header">
                            <h3><?php echo htmlspecialchars($note['title']); ?></h3>
                            <div class="note-info">
                                <span class="shared-by">Shared by: <?php echo htmlspecialchars($note['username']); ?></span>
                                <span class="permission"><?php echo $note['permission'] === 'edit' ? 'Can edit' : 'View only'; ?></span>
                            </div>
                        </div>
                        <div class="note-content">
                            <p><?php echo nl2br(htmlspecialchars($note['content'])); ?></p>
                        </div>
                        <div class="note-footer">
                            <span class="priority"><?php echo $note['priority']; ?></span>
                            <span class="date"><?php echo formatDate($note['created_at']); ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> Ricordella - Your Notes App</p>
    </footer>
</body>
</html>